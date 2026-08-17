<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AdminNotificationService;
use App\Services\OrderNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function invoice(Order $order): View
    {
        $order->load([
            'brand',
            'items',
        ]);

        return view('admin.orders.invoice', compact('order'));
    }

    public function store(
        Request $request,
        AdminNotificationService $notifications,
        OrderNumberService $orderNumbers
    ): JsonResponse {
        $cart = collect(
            $request->session()->get('cart', [])
        )->values();
    
        if ($cart->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your cart is empty.',
            ], 422);
        }
    
        $validated = $request->validate(
            $this->validationRules()
        );
    
        /*
        |--------------------------------------------------------------------------
        | Normalize customer phone
        |--------------------------------------------------------------------------
        */
    
        $validated['phone'] = preg_replace(
            '/\D+/',
            '',
            (string) $validated['phone']
        );
    
        if (!empty($validated['alternative_phone'])) {
            $validated['alternative_phone'] = preg_replace(
                '/\D+/',
                '',
                (string) $validated['alternative_phone']
            );
        }
    
        /*
        |--------------------------------------------------------------------------
        | One brand per order
        |--------------------------------------------------------------------------
        */
    
        $brandIds = $cart
            ->pluck('brand_id')
            ->map(
                fn ($brandId): int =>
                    (int) $brandId
            )
            ->unique()
            ->values();
    
        if ($brandIds->count() !== 1) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'Please order from one brand at a time.',
            ], 422);
        }
    
        $brand = Brand::query()
            ->whereKey(
                (int) $brandIds->first()
            )
            ->where('is_active', true)
            ->first();
    
        if (!$brand) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'The selected brand is currently unavailable.',
            ], 422);
        }
    
        $deliveryCharge =
            $validated['delivery_area']
            === 'inside_dhaka'
                ? 80
                : 130;
    
        try {
            $order = DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $cart,
                    $brand,
                    $deliveryCharge,
                    $orderNumbers
                ): Order {
                    $itemsTotal = 0;
    
                    /*
                    |--------------------------------------------------------------------------
                    | Validate products, variants and stock
                    |--------------------------------------------------------------------------
                    */
    
                    $orderItems = $cart->map(
                        function (
                            array $cartItem
                        ) use (
                            &$itemsTotal,
                            $brand
                        ): array {
                            $productId = (int) (
                                $cartItem['product_id']
                                ?? 0
                            );
    
                            $quantity = max(
                                (int) (
                                    $cartItem['quantity']
                                    ?? 0
                                ),
                                0
                            );
    
                            $color = trim(
                                (string) (
                                    $cartItem['color']
                                    ?? ''
                                )
                            );
    
                            $size = trim(
                                (string) (
                                    $cartItem['size']
                                    ?? ''
                                )
                            );
    
                            if (
                                !$productId
                                || $quantity < 1
                                || $color === ''
                                || $size === ''
                            ) {
                                throw new HttpResponseException(
                                    response()->json([
                                        'status' => 'error',
                                        'message' =>
                                            'One or more cart items are invalid.',
                                    ], 422)
                                );
                            }
    
                            $product = Product::query()
                                ->whereKey($productId)
                                ->where(
                                    'brand_id',
                                    $brand->id
                                )
                                ->where(
                                    'status',
                                    Product::STATUS_ACTIVE
                                )
                                ->lockForUpdate()
                                ->first();
    
                            if (!$product) {
                                throw new HttpResponseException(
                                    response()->json([
                                        'status' => 'error',
                                        'message' =>
                                            'One or more products are no longer available.',
                                    ], 422)
                                );
                            }
    
                            $variant = ProductVariant::query()
                                ->where(
                                    'product_id',
                                    $product->id
                                )
                                ->whereRaw(
                                    'LOWER(color) = ?',
                                    [
                                        mb_strtolower(
                                            $color
                                        ),
                                    ]
                                )
                                ->where(
                                    'size',
                                    $size
                                )
                                ->where(
                                    'status',
                                    true
                                )
                                ->lockForUpdate()
                                ->first();
    
                            if (
                                !$variant
                                || (int) $variant->stock_quantity
                                    < $quantity
                            ) {
                                throw new HttpResponseException(
                                    response()->json([
                                        'status' => 'error',
                                        'message' =>
                                            $product->name
                                            .' is no longer available in the selected size and color.',
                                    ], 422)
                                );
                            }
    
                            $unitPrice = $this->productPrice(
                                $product
                            );
    
                            $lineTotal = round(
                                $quantity * $unitPrice,
                                2
                            );
    
                            /*
                            |--------------------------------------------------------------------------
                            | Decrease variant and total product stock
                            |--------------------------------------------------------------------------
                            */
    
                            $variant->decrement(
                                'stock_quantity',
                                $quantity
                            );

                            $remainingProductStock =
                                $product->syncStockFromVariants();
    
                            if (
                                $remainingProductStock <= 0
                            ) {
                                $product->update([
                                    'stock_status' =>
                                        Product::STOCK_OUT,
                                ]);
                            }
    
                            $itemsTotal += $lineTotal;
    
                            return [
                                'product_id' =>
                                    $product->id,
    
                                'product_name' =>
                                    $product->name,
    
                                'product_code' =>
                                    $product->product_code,
    
                                'product_image' =>
                                    $cartItem['image_url']
                                    ?? null,
    
                                'size' =>
                                    $size,
    
                                'color' =>
                                    $color,
    
                                'quantity' =>
                                    $quantity,
    
                                'unit_price' =>
                                    $unitPrice,
    
                                'total_price' =>
                                    $lineTotal,
                            ];
                        }
                    );
    
                    $itemsTotal = round(
                        $itemsTotal,
                        2
                    );
    
                    /*
                    |--------------------------------------------------------------------------
                    | Resolve and validate coupon
                    |--------------------------------------------------------------------------
                    */
    
                    $couponData = $this->resolveCoupon(
                        $request,
                        $brand->id,
                        $itemsTotal
                    );
    
                    if ($couponData) {
                        $coupon = $couponData['coupon'];
    
                        /*
                        |--------------------------------------------------------------------------
                        | Final new-customer validation
                        |--------------------------------------------------------------------------
                        |
                        | Frontend অথবা coupon apply endpoint bypass করলেও
                        | পুরোনো customer final order করতে পারবে না।
                        |
                        */
    
                        if ($coupon->new_customer_only) {
                            $hasPreviousOrder = Order::query()
                                ->where(
                                    'phone',
                                    $validated['phone']
                                )
                                ->where(
                                    'status',
                                    '!=',
                                    Order::STATUS_CANCELLED
                                )
                                ->exists();
    
                            if ($hasPreviousOrder) {
                                throw new HttpResponseException(
                                    response()->json([
                                        'status' => 'error',
    
                                        'message' =>
                                            'This offer is available only for new customers.',
    
                                        'errors' => [
                                            'coupon_code' => [
                                                'This coupon is only available for customers who have not placed an order before.',
                                            ],
                                        ],
                                    ], 422)
                                );
                            }
                        }
                    }
    
                    $couponDiscount = $couponData
                        ? (float) (
                            $couponData['snapshot'][
                                'discount_amount'
                            ]
                            ?? 0
                        )
                        : 0;
    
                    $couponDiscount = round(
                        min(
                            $couponDiscount,
                            $itemsTotal
                        ),
                        2
                    );
    
                    /*
                    |--------------------------------------------------------------------------
                    | Update coupon usage
                    |--------------------------------------------------------------------------
                    */
    
                    if ($couponData) {
                        $couponData['coupon']->increment(
                            'used_count'
                        );
                    }
    
                    $grandTotal = round(
                        max(
                            $itemsTotal
                            + $deliveryCharge
                            - $couponDiscount,
                            0
                        ),
                        2
                    );
    
                    /*
                    |--------------------------------------------------------------------------
                    | Create order
                    |--------------------------------------------------------------------------
                    */
    
                    $order = Order::create([
                        'order_number' =>
                            $this->generateOrderNumber(),
    
                        'invoice_number' =>
                            $orderNumbers->generateInvoiceNumber($brand),
    
                        'brand_id' =>
                            $brand->id,
    
                        'customer_name' =>
                            trim(
                                $validated[
                                    'customer_name'
                                ]
                            ),
    
                        'phone' =>
                            $validated['phone'],
    
                        'alternative_phone' =>
                            $validated[
                                'alternative_phone'
                            ]
                            ?? null,
    
                        'customer_email' =>
                            $validated[
                                'customer_email'
                            ]
                            ?? null,
    
                        'delivery_area' =>
                            $validated[
                                'delivery_area'
                            ],
    
                        'district' =>
                            trim(
                                $validated[
                                    'district'
                                ]
                            ),
    
                        'area_thana' =>
                            trim(
                                $validated[
                                    'area_thana'
                                ]
                            ),
    
                        'road_no' =>
                            trim(
                                $validated[
                                    'road_no'
                                ]
                            ),
    
                        'house_no' =>
                            trim(
                                $validated[
                                    'house_no'
                                ]
                            ),
    
                        'full_address' =>
                            trim(
                                $validated[
                                    'full_address'
                                ]
                            ),
    
                        'order_note' =>
                            isset(
                                $validated[
                                    'order_note'
                                ]
                            )
                                ? trim(
                                    $validated[
                                        'order_note'
                                    ]
                                )
                                : null,
    
                        'payment_method' =>
                            Order::PAYMENT_COD,
    
                        'coupon_id' =>
                            $couponData[
                                'coupon'
                            ]->id
                            ?? null,
    
                        'coupon_code' =>
                            $couponData[
                                'coupon'
                            ]->code
                            ?? null,
    
                        'coupon_discount_amount' =>
                            $couponDiscount,
    
                        'coupon_snapshot' =>
                            $couponData[
                                'snapshot'
                            ]
                            ?? null,
    
                        'items_total' =>
                            $itemsTotal,
    
                        'delivery_charge' =>
                            $deliveryCharge,
    
                        'grand_total' =>
                            $grandTotal,
    
                        'status' =>
                            Order::STATUS_PENDING,
    
                        'payment_status' =>
                            Order::PAYMENT_UNPAID,
    
                        'courier_status' =>
                            'not_sent',
    
                        'order_source' =>
                            Order::SOURCE_CART,
                    ]);
    
                    $order->items()->createMany(
                        $orderItems->all()
                    );
    
                    /*
                    |--------------------------------------------------------------------------
                    | Clear checkout session
                    |--------------------------------------------------------------------------
                    */
    
                    $request
                        ->session()
                        ->forget([
                            'cart',
                            'checkout_coupon',
                        ]);
    
                    return $order;
                }
            );
        } catch (
            HttpResponseException $exception
        ) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
    
            return response()->json([
                'status' => 'error',
    
                'message' =>
                    app()->isLocal()
                        ? $exception->getMessage()
                        : 'Unable to place order.',
            ], 500);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Create admin notification
        |--------------------------------------------------------------------------
        */
    
        try {
            $notifications->newOrder(
                $order
            );
        } catch (\Throwable $exception) {
            /*
            | Notification failure-এর কারণে order failure দেখানো হবে না।
            */
    
            report($exception);
        }
    
        return response()->json([
            'status' => 'success',
    
            'message' =>
                'Order placed successfully.',
    
            'order_code' =>
                $order->invoice_number,
    
            'order' => [
                'id' =>
                    $order->id,
    
                'order_number' =>
                    $order->order_number,
    
                'invoice_number' =>
                    $order->invoice_number,
    
                'grand_total' =>
                    (float) $order->grand_total,

                'invoice_url' => URL::temporarySignedRoute(
                    'orders.invoice',
                    now()->addDays(7),
                    $order
                ),
            ],
        ]);
    }

    private function validationRules(): array
    {
        return [
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
            'coupon_code' => [
                'nullable',
                'string',
                'max:60',
            ],
        ];
    }

    private function resolveCoupon(
        Request $request,
        int $brandId,
        float $itemsTotal
    ): ?array {
        $sessionCoupon = $request->session()->get(
            'checkout_coupon'
        );

        $code = $sessionCoupon['code']
            ?? $request->input('coupon_code');

        if (!$code) {
            return null;
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [
                mb_strtoupper(trim((string) $code)),
            ])
            ->lockForUpdate()
            ->first();

        if (!$coupon
            || !$coupon->isAvailableFor($brandId, $itemsTotal)) {
            $request->session()->forget('checkout_coupon');

            abort(response()->json([
                'status' => 'error',
                'message' => 'Coupon is no longer available for this order.',
            ], 422));
        }

        return [
            'coupon' => $coupon,
            'snapshot' => $coupon->snapshot($itemsTotal),
        ];
    }

    private function productPrice(Product $product): float
    {
        if (
            !is_null($product->sale_price)
            && (float) $product->sale_price
                < (float) $product->regular_price
        ) {
            return (float) $product->sale_price;
        }

        return (float) $product->regular_price;
    }

    private function generateOrderNumber(): string
    {
        return $this->generateUniqueCode('ORD');
    }

    private function generateInvoiceNumber(): string
    {
        return $this->generateUniqueCode('INV');
    }

    private function generateUniqueCode(string $prefix): string
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
}
