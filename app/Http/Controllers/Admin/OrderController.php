<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderActivityLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AdminNotificationService;
use App\Services\OrderNumberService;
use App\Services\SteadfastCourierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('brand')
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('brand_id')
            ->orderBy('name')
            ->get();

        $orders = $this->orderQuery(request())
            ->with([
                'brand',
                'items',
                'steadfastSentBy',
                'qcBy',
                'qcResolvedBy',
                'confirmedBy',
            ])
            ->latest('id')
            ->get();

        if (request()->ajax()) {
            return view(
                'admin.orders.partials.table-rows',
                compact('orders')
            );
        }

        return view('admin.orders.index', compact(
            'brands',
            'products',
            'orders'
        ));
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'items',
            'brand',
            'steadfastSentBy',
            'qcBy',
            'qcResolvedBy',
            'confirmedBy',
            'activityLogs.user',
        ]);

        return response()->json([
            'status' => 'success',
            'order' => $this->orderPayload($order),
        ]);
    }

    public function store(
        Request $request,
        OrderNumberService $orderNumbers
    ): JsonResponse
    {
        $validated = $this->validateOrder($request);

        try {
            $order = DB::transaction(function () use (
                $validated,
                $orderNumbers
            ): Order {
                $brand = Brand::query()
                    ->whereKey((int) $validated['brand_id'])
                    ->firstOrFail();
                $itemsTotal = $this->itemsTotal(
                    $validated['items']
                );
                $preparedItems = $this->prepareItems(
                    $validated['items']
                );

                $order = Order::create([
                    ...$this->orderData(
                        $validated,
                        $itemsTotal
                    ),
                    'order_number' => $this->generateCode('ORD'),
                    'invoice_number' =>
                        $orderNumbers->generateInvoiceNumber($brand),
                    'courier_status' => 'not_sent',
                    'qc_status' => Order::QC_NOT_CHECKED,
                    'order_source' => Order::SOURCE_CART,
                ]);

                if ($validated['status'] === Order::STATUS_CONFIRMED) {
                    $this->markConfirmed($order);
                }

                if ($validated['status'] !== Order::STATUS_CANCELLED) {
                    $this->reserveStock($preparedItems);
                }

                $order->items()->createMany($preparedItems);

                return $order;
            });

            $this->loadOrderRelations($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Order added successfully!',
                'order' => $this->tableData($order),
            ]);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to add order.',
            ], 500);
        }
    }

    public function update(
        Request $request,
        Order $order
    ): JsonResponse {
        $validated = $this->validateOrder($request);

        try {
            DB::transaction(function () use (
                $order,
                $validated
            ): void {
                $order->load('items');
                $oldStatus = $order->status;
                $oldItems = $order->items->map->toArray()->all();
                $itemsTotal = $this->itemsTotal(
                    $validated['items']
                );
                $preparedItems = $this->prepareItems(
                    $validated['items']
                );

                if ($oldStatus !== Order::STATUS_CANCELLED) {
                    $this->restoreStock($oldItems);
                }

                if ($validated['status'] !== Order::STATUS_CANCELLED) {
                    $this->reserveStock($preparedItems);
                }

                $order->update(
                    $this->orderData(
                        $validated,
                        $itemsTotal,
                        (float) $order->coupon_discount_amount
                    )
                );

                $order->items()->delete();

                $order->items()->createMany($preparedItems);

                if (
                    $oldStatus !== Order::STATUS_CONFIRMED
                    && $validated['status'] === Order::STATUS_CONFIRMED
                ) {
                    $this->markConfirmed($order);
                }

                $this->logOrderAction(
                    $order,
                    OrderActivityLog::ACTION_UPDATED,
                    $oldStatus,
                    $validated['status']
                );

                if (
                    $oldStatus !== Order::STATUS_CANCELLED
                    && $validated['status'] === Order::STATUS_CANCELLED
                ) {
                    $this->logOrderAction(
                        $order,
                        OrderActivityLog::ACTION_CANCELLED,
                        $oldStatus,
                        Order::STATUS_CANCELLED
                    );
                }
            });

            $order->refresh();
            $this->loadOrderRelations($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Order updated successfully!',
                'order' => $this->tableData($order),
            ]);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to update order.',
            ], 500);
        }
    }

    public function destroy(Order $order): JsonResponse
    {
        DB::transaction(function () use ($order): void {
            $order->load('items');

            if ($order->status !== Order::STATUS_CANCELLED) {
                $this->restoreStock(
                    $order->items->map->toArray()->all()
                );
            }

            $order->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully!',
        ]);
    }

    public function sendToSteadfast(
        Order $order,
        SteadfastCourierService $steadfast
    ): JsonResponse {
        if ($order->sent_to_steadfast_at) {
            $this->loadOrderRelations($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Order was already sent to Steadfast.',
                'order' => $this->tableData($order),
            ]);
        }

        if (($order->qc_status ?: Order::QC_NOT_CHECKED) !== Order::QC_PASSED) {
            return response()->json([
                'status' => 'error',
                'message' => 'This order must pass QC before sending to Steadfast.',
            ], 422);
        }

        try {
            $response = $steadfast->createConsignment($order);

            $order->update([
                'steadfast_consignment_id' =>
                    data_get($response, 'consignment.consignment_id')
                    ?? data_get($response, 'consignment_id')
                    ?? data_get($response, 'data.consignment_id'),
                'courier_status' =>
                    data_get($response, 'consignment.status')
                    ?? data_get($response, 'status')
                    ?? 'sent',
                'sent_to_steadfast_at' => now(),
                'steadfast_sent_by_user_id' => auth()->id(),
                'steadfast_response' => $response,
                'steadfast_error' => null,
            ]);

            $this->logOrderAction(
                $order,
                OrderActivityLog::ACTION_SENT_STEADFAST,
                null,
                'sent'
            );

            $this->loadOrderRelations($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Order sent to Steadfast successfully.',
                'order' => $this->tableData($order),
                'details' => $this->steadfastPayload($order),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $order->update([
                'steadfast_error' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
                    ?: 'Unable to send order to Steadfast.',
            ], 422);
        }
    }

    public function steadfastDetails(
        Order $order,
        SteadfastCourierService $steadfast,
        AdminNotificationService $notifications
    ): JsonResponse
    {
        if ($order->sent_to_steadfast_at) {
            try {
                $this->syncSteadfastStatus(
                    $order,
                    $steadfast,
                    $notifications
                );
            } catch (Throwable $exception) {
                report($exception);

                $order->update([
                    'steadfast_error' => $exception->getMessage(),
                ]);
            }
        }

            $this->loadOrderRelations($order);

        return response()->json([
            'status' => 'success',
            'details' => $this->steadfastPayload($order),
            'order' => $this->tableData($order),
        ]);
    }

    public function qcPassed(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_CONFIRMED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only confirmed orders can pass QC.',
            ], 422);
        }

        $oldStatus = $order->qc_status ?: Order::QC_NOT_CHECKED;

        $order->update([
            'qc_status' => Order::QC_PASSED,
            'qc_by_user_id' => auth()->id(),
            'qc_checked_at' => now(),
            'qc_issue_note' => null,
        ]);

        $this->logOrderAction(
            $order,
            OrderActivityLog::ACTION_QC_PASSED,
            $oldStatus,
            Order::QC_PASSED
        );

        $order->load(['brand', 'items', 'steadfastSentBy', 'qcBy', 'qcResolvedBy', 'confirmedBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'QC passed successfully.',
            'order' => $this->tableData($order),
        ]);
    }

    public function qcIssue(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_CONFIRMED) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only confirmed orders can be marked as QC issue.',
            ], 422);
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->qc_status ?: Order::QC_NOT_CHECKED;

        $order->update([
            'qc_status' => Order::QC_ISSUE,
            'qc_by_user_id' => auth()->id(),
            'qc_checked_at' => now(),
            'qc_issue_note' => trim($validated['note']),
        ]);

        $this->logOrderAction(
            $order,
            OrderActivityLog::ACTION_QC_ISSUE,
            $oldStatus,
            Order::QC_ISSUE,
            trim($validated['note'])
        );

        $order->load(['brand', 'items', 'steadfastSentBy', 'qcBy', 'qcResolvedBy', 'confirmedBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'QC issue marked successfully.',
            'order' => $this->tableData($order),
        ]);
    }

    public function resolveQcIssue(Request $request, Order $order): JsonResponse
    {
        if (($order->qc_status ?: Order::QC_NOT_CHECKED) !== Order::QC_ISSUE) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only QC issue orders can be resolved.',
            ], 422);
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->qc_status;

        $order->update([
            'status' => Order::STATUS_CONFIRMED,
            'qc_status' => Order::QC_NOT_CHECKED,
            'qc_resolved_by_user_id' => auth()->id(),
            'qc_resolved_at' => now(),
            'qc_issue_note' => $this->nullableTrim($validated['note'] ?? null),
        ]);

        $this->logOrderAction(
            $order,
            OrderActivityLog::ACTION_QC_RESOLVED,
            $oldStatus,
            Order::QC_NOT_CHECKED,
            $this->nullableTrim($validated['note'] ?? null)
        );

        $order->load(['brand', 'items', 'steadfastSentBy', 'qcBy', 'qcResolvedBy', 'confirmedBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'QC issue resolved. Order is ready for QC again.',
            'order' => $this->tableData($order),
        ]);
    }

    public function invoice(Order $order): View
    {
        $order->load(['brand', 'items']);

        return view('admin.orders.invoice', compact('order'));
    }

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'brand_id' => [
                'required',
                'integer',
                'exists:brands,id',
            ],
            'customer_name' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'phone' => [
                'required',
                'regex:/^01[3-9]\d{8}$/',
            ],
            'alternative_phone' => [
                'nullable',
                'regex:/^01[3-9]\d{8}$/',
            ],
            'customer_email' => [
                'nullable',
                'email',
                'max:150',
            ],
            'delivery_area' => [
                'required',
                Rule::in(['inside_dhaka', 'outside_dhaka']),
            ],
            'district' => [
                'required',
                'string',
                'max:100',
            ],
            'area_thana' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],
            'road_no' => [
                'required',
                'string',
                'max:100',
            ],
            'house_no' => [
                'required',
                'string',
                'max:100',
            ],
            'full_address' => [
                'required',
                'string',
                'max:250',
            ],
            'order_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'status' => [
                'required',
                Rule::in(Order::adminStatuses()),
            ],
            'payment_status' => [
                'required',
                Rule::in([
                    Order::PAYMENT_UNPAID,
                    Order::PAYMENT_PAID,
                ]),
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.size' => [
                'nullable',
                'string',
                'max:20',
            ],
            'items.*.color' => [
                'nullable',
                'string',
                'max:100',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);
    }

    private function orderQuery(Request $request)
    {
        return Order::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('courier_status', 'like', "%{$search}%")
                        ->orWhereHas('brand', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('brand_id'), function ($query) use ($request): void {
                $query->where('brand_id', $request->input('brand_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('courier_status'), function ($query) use ($request): void {
                if ($request->input('courier_status') === 'not_sent') {
                    $query->whereNull('sent_to_steadfast_at');

                    return;
                }

                $query
                    ->whereNotNull('sent_to_steadfast_at')
                    ->where('courier_status', $request->input('courier_status'));
            })
            ->when($request->filled('qc_status'), function ($query) use ($request): void {
                $query->where('qc_status', $request->input('qc_status'));
            });
    }

    private function orderData(
        array $validated,
        float $itemsTotal,
        float $couponDiscount = 0
    ): array {
        $deliveryCharge = $validated['delivery_area']
            === 'inside_dhaka'
                ? 80
                : 130;

        return [
            'brand_id' => (int) $validated['brand_id'],
            'customer_name' => trim($validated['customer_name']),
            'phone' => trim($validated['phone']),
            'alternative_phone' =>
                $this->nullableTrim($validated['alternative_phone'] ?? null),
            'customer_email' =>
                $this->nullableTrim($validated['customer_email'] ?? null),
            'delivery_area' => $validated['delivery_area'],
            'district' => trim($validated['district']),
            'area_thana' => trim($validated['area_thana']),
            'road_no' => trim($validated['road_no']),
            'house_no' => trim($validated['house_no']),
            'full_address' => trim($validated['full_address']),
            'order_note' =>
                $this->nullableTrim($validated['order_note'] ?? null),
            'payment_method' => Order::PAYMENT_COD,
            'items_total' => $itemsTotal,
            'delivery_charge' => $deliveryCharge,
            'grand_total' => max(
                $itemsTotal + $deliveryCharge - $couponDiscount,
                0
            ),
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'],
        ];
    }

    private function prepareItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item): array {
                $product = Product::query()
                    ->with('primaryImage')
                    ->findOrFail($item['product_id']);

                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->product_code,
                    'product_image' =>
                        $product->primaryImage?->image,
                    'size' => $this->nullableTrim($item['size'] ?? null),
                    'color' => $this->nullableTrim($item['color'] ?? null),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                ];
            })
            ->all();
    }

    private function reserveStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::query()
                ->whereKey($item['product_id'])
                ->lockForUpdate()
                ->first();

            $variant = $this->lockedVariantForItem($item);

            if (
                !$product
                || !$variant
                || (int) $variant->stock_quantity
                    < (int) $item['quantity']
            ) {
                throw new HttpResponseException(
                    response()->json([
                        'status' => 'error',
                        'message' =>
                            "{$item['product_name']} does not have enough stock for {$item['color']} {$item['size']}.",
                    ], 422)
                );
            }

            $variant->decrement(
                'stock_quantity',
                (int) $item['quantity']
            );

            $product->syncStockFromVariants();
        }
    }

    private function restoreStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::query()
                ->whereKey($item['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                continue;
            }

            $variant = $this->lockedVariantForItem($item);

            if ($variant) {
                $variant->increment(
                    'stock_quantity',
                    (int) $item['quantity']
                );
            }

            $product->syncStockFromVariants();
        }
    }

    private function lockedVariantForItem(array $item): ?ProductVariant
    {
        $color = $this->nullableTrim($item['color'] ?? null);
        $size = $this->nullableTrim($item['size'] ?? null);

        if (!$color || !$size) {
            return null;
        }

        return ProductVariant::query()
            ->where('product_id', (int) $item['product_id'])
            ->whereRaw('LOWER(color) = ?', [
                mb_strtolower($color),
            ])
            ->where('size', $size)
            ->lockForUpdate()
            ->first();
    }

    private function itemsTotal(array $items): float
    {
        return collect($items)->sum(
            fn (array $item): float =>
                (int) $item['quantity']
                * (float) $item['unit_price']
        );
    }

    private function orderPayload(Order $order): array
    {
        return [
            ...$this->tableData($order),
            'brand_id' => $order->brand_id,
            'customer_email' => $order->customer_email,
            'order_note' => $order->order_note,
            'items' => $order->items
                ->map(fn ($item): array => [
                    'product_id' => $item->product_id,
                    'size' => $item->size,
                    'color' => $item->color,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ])
                ->values(),
        ];
    }

    private function tableData(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'invoice_number' => $order->invoice_number,
            'brand_name' => $order->brand?->name ?? '-',
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'alternative_phone' => $order->alternative_phone,
            'delivery_area' => $order->delivery_area,
            'delivery_area_label' => $order->delivery_area
                === 'inside_dhaka'
                    ? 'Inside Dhaka'
                    : 'Outside Dhaka',
            'district' => $order->district,
            'area_thana' => $order->area_thana,
            'road_no' => $order->road_no,
            'house_no' => $order->house_no,
            'full_address' => $order->full_address,
            'payment_method' => $order->payment_method,
            'coupon_code' => $order->coupon_code,
            'coupon_discount_amount' =>
                (float) $order->coupon_discount_amount,
            'items_total' => (float) $order->items_total,
            'delivery_charge' => (float) $order->delivery_charge,
            'grand_total' => (float) $order->grand_total,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'confirmed_at' =>
                $order->confirmed_at?->format('Y-m-d H:i:s'),
            'confirmed_by' =>
                $order->confirmedBy?->name,
            'qc_status' => $order->qc_status ?: Order::QC_NOT_CHECKED,
            'qc_status_label' => $order->qcStatusLabel(),
            'qc_status_class' => $order->qcStatusBadgeClass(),
            'qc_by' => $order->qcBy?->name,
            'qc_checked_at' =>
                $order->qc_checked_at?->format('Y-m-d H:i:s'),
            'qc_issue_note' => $order->qc_issue_note,
            'qc_resolved_by' => $order->qcResolvedBy?->name,
            'qc_resolved_at' =>
                $order->qc_resolved_at?->format('Y-m-d H:i:s'),
            'courier_status' => $order->sent_to_steadfast_at
                ? $order->courier_status
                : 'Not Sent',
            'steadfast_consignment_id' =>
                $order->steadfast_consignment_id,
            'sent_to_steadfast_at' =>
                $order->sent_to_steadfast_at?->format(
                    'Y-m-d H:i:s'
                ),
            'steadfast_sent_by' =>
                $order->steadfastSentBy?->name,
            'item_count' => (int) $order->items->sum('quantity'),
            'activity_logs' => $order->relationLoaded('activityLogs')
                ? $order->activityLogs
                    ->sortByDesc('id')
                    ->take(20)
                    ->map(fn (OrderActivityLog $log): array => [
                        'action' => $log->action,
                        'note' => $log->note,
                        'old_value' => $log->old_value,
                        'new_value' => $log->new_value,
                        'user' => $log->user?->name,
                        'created_at' => $log->created_at?->format(
                            'Y-m-d H:i:s'
                        ),
                    ])
                    ->values()
                : [],
        ];
    }

    private function loadOrderRelations(Order $order): void
    {
        $order->load([
            'brand',
            'items',
            'steadfastSentBy',
            'qcBy',
            'qcResolvedBy',
            'confirmedBy',
            'activityLogs.user',
        ]);
    }

    private function markConfirmed(Order $order): void
    {
        $order->forceFill([
            'confirmed_by_user_id' => auth()->id(),
            'confirmed_at' => now(),
        ])->saveQuietly();

        $this->logOrderAction(
            $order,
            OrderActivityLog::ACTION_CONFIRMED,
            null,
            Order::STATUS_CONFIRMED
        );
    }

    private function logOrderAction(
        Order $order,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $note = null,
        ?array $meta = null
    ): void {
        OrderActivityLog::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'note' => $note,
            'meta' => $meta,
        ]);
    }

    private function steadfastPayload(Order $order): array
    {
        return [
            'invoice_number' => $order->invoice_number,
            'consignment_id' => $order->steadfast_consignment_id,
            'courier_status' => $order->sent_to_steadfast_at
                ? ($order->courier_status ?: 'sent')
                : 'Not Sent',
            'sent_at' => $order->sent_to_steadfast_at?->format(
                'Y-m-d H:i:s'
            ),
            'sent_by' => $order->steadfastSentBy?->name,
            'sent_by_email' => $order->steadfastSentBy?->email,
            'response' => $order->steadfast_response,
            'latest_status_checked_at' => data_get(
                $order->steadfast_response,
                'latest_status_checked_at'
            ),
            'error' => $order->steadfast_error,
        ];
    }

    private function syncSteadfastStatus(
        Order $order,
        SteadfastCourierService $steadfast,
        AdminNotificationService $notifications
    ): void {
        $oldStatus = $order->courier_status;
        $response = $steadfast->deliveryStatus($order);

        $deliveryStatus = data_get(
            $response,
            'delivery_status'
        );

        if (!$deliveryStatus) {
            return;
        }

        $steadfastResponse = $order->steadfast_response ?: [];

        if (!is_array($steadfastResponse)) {
            $steadfastResponse = [];
        }

        $orderStatus = match ($deliveryStatus) {
            'delivered',
            'delivered_approval_pending' =>
                Order::STATUS_DELIVERED,

            'cancelled',
            'cancelled_approval_pending' =>
                Order::STATUS_CANCELLED,

            default => $order->status,
        };

        $order->update([
            'courier_status' => $deliveryStatus,
            'status' => $orderStatus,
            'steadfast_response' => [
                ...$steadfastResponse,
                'latest_status_response' => $response,
                'latest_status_checked_at' => now()
                    ->format('Y-m-d H:i:s'),
            ],
            'steadfast_error' => null,
        ]);

        $order->refresh();

        $notifications->courierStatusChanged(
            $order,
            $oldStatus,
            $deliveryStatus
        );
    }

    private function generateCode(string $prefix): string
    {
        do {
            $code = $prefix.now()->format('ymdHis')
                .random_int(100, 999);
        } while (
            Order::query()
                ->where(
                    $prefix === 'ORD'
                        ? 'order_number'
                        : 'invoice_number',
                    $code
                )
                ->exists()
        );

        return $code;
    }

    private function nullableTrim(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
