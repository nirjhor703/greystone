<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->reportData($request);

        return view('admin.reports.index', $data);
    }

    public function export(Request $request): View
    {
        $data = $this->reportData($request);

        return view('admin.reports.export', $data);
    }

    private function reportData(Request $request): array
    {
        $filters = $this->filters($request);
        $orders = $this->orders($filters)->get();
        $validOrders = $orders->where(
            'status',
            '!=',
            Order::STATUS_CANCELLED
        );

        $revenueRows = $this->revenueRows($validOrders, $filters);
        $customerRows = $this->customerRows($validOrders, $filters);
        $productRows = $this->productRows($filters);
        $customerSegments = $this->customerSegments(
            $validOrders,
            $filters['start'],
            $filters['end'],
            $filters['brand_id']
                ? (int) $filters['brand_id']
                : null
        );

        return [
            'brands' => Brand::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'filters' => $filters,
            'periodLabel' => $this->periodLabel($filters),
            'orders' => $orders,
            'revenueRows' => $revenueRows,
            'customerRows' => $customerRows,
            'productRows' => $productRows,
            'summary' => [
                'orders' => $orders->count(),
                'valid_orders' => $validOrders->count(),
                'revenue' => (float) $validOrders->sum('grand_total'),
                'items_total' => (float) $validOrders->sum('items_total'),
                'delivery_charge' =>
                    (float) $validOrders->sum('delivery_charge'),
                'discount' =>
                    (float) $validOrders->sum('coupon_discount_amount'),
                'customers' => $customerRows->count(),
                'new_customers' => $customerSegments['new'],
                'repeat_customers' => $customerSegments['repeat'],
                'products_sold' => (int) $productRows->sum('quantity'),
            ],
            'exportUrl' => route(
                'admin.reports.export',
                $request->query()
            ),
        ];
    }

    private function filters(Request $request): array
    {
        $period = $request->input('period', 'daily');
        $anchor = Carbon::parse(
            $request->input('date', now()->toDateString())
        );

        if ($period === 'weekly') {
            $start = $anchor->copy()->startOfWeek();
            $end = $anchor->copy()->endOfWeek();
        } elseif ($period === 'monthly') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
        } elseif ($period === 'custom') {
            $start = Carbon::parse(
                $request->input('start_date', now()->toDateString())
            )->startOfDay();
            $end = Carbon::parse(
                $request->input('end_date', now()->toDateString())
            )->endOfDay();
        } else {
            $period = 'daily';
            $start = $anchor->copy()->startOfDay();
            $end = $anchor->copy()->endOfDay();
        }

        return [
            'period' => $period,
            'date' => $anchor->toDateString(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'start' => $start,
            'end' => $end,
            'brand_id' => $request->input('brand_id'),
            'status' => $request->input('status'),
            'report_type' => $request->input('report_type', 'overview'),
        ];
    }

    private function orders(array $filters)
    {
        return Order::query()
            ->with(['brand', 'items'])
            ->whereBetween('created_at', [
                $filters['start'],
                $filters['end'],
            ])
            ->when($filters['brand_id'], function ($query) use ($filters): void {
                $query->where('brand_id', $filters['brand_id']);
            })
            ->when($filters['status'], function ($query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->latest('id');
    }

    private function revenueRows(
        Collection $orders,
        array $filters
    ): Collection {
        $format = $filters['period'] === 'monthly'
            ? 'Y-m-d'
            : 'Y-m-d';

        return $orders
            ->groupBy(fn (Order $order): string =>
                $order->created_at->format($format)
            )
            ->map(function (Collection $orders, string $date) use ($filters): array {
                $bucketStart = $orders
                    ->min('created_at')
                    ?->copy()
                    ->startOfDay()
                    ?? now();
                $bucketEnd = $orders
                    ->max('created_at')
                    ?->copy()
                    ->endOfDay()
                    ?? now();
                $customerSegments = $this->customerSegments(
                    $orders,
                    $bucketStart,
                    $bucketEnd,
                    $filters['brand_id']
                        ? (int) $filters['brand_id']
                        : null
                );

                return [
                    'date' => $date,
                    'orders' => $orders->count(),
                    'new_customers' => $customerSegments['new'],
                    'repeat_customers' => $customerSegments['repeat'],
                    'items_total' => (float) $orders->sum('items_total'),
                    'delivery_charge' =>
                        (float) $orders->sum('delivery_charge'),
                    'discount' =>
                        (float) $orders->sum('coupon_discount_amount'),
                    'revenue' => (float) $orders->sum('grand_total'),
                ];
            })
            ->values();
    }

    private function customerRows(
        Collection $orders,
        array $filters
    ): Collection
    {
        return $orders
            ->groupBy(fn (Order $order): string =>
                preg_replace('/\D+/', '', $order->phone) ?: 'unknown'
            )
            ->map(function (Collection $orders) use ($filters): array {
                $latest = $orders->sortByDesc('created_at')->first();
                $segments = $this->customerSegments(
                    collect([$latest]),
                    $filters['start'],
                    $filters['end'],
                    $filters['brand_id']
                        ? (int) $filters['brand_id']
                        : null
                );

                return [
                    'name' => $latest->customer_name,
                    'phone' => $latest->phone,
                    'email' => $latest->customer_email,
                    'district' => $latest->district,
                    'brands' => $orders
                        ->pluck('brand.name')
                        ->filter()
                        ->unique()
                        ->implode(', '),
                    'orders_count' => $orders->count(),
                    'total_spent' => (float) $orders
                        ->where('status', '!=', Order::STATUS_CANCELLED)
                        ->sum('grand_total'),
                    'customer_type' => $segments['repeat'] > 0
                        ? 'Repeat'
                        : 'New',
                    'last_order' => $latest->created_at,
                    'last_status' => $latest->status,
                ];
            })
            ->sortByDesc('total_spent')
            ->values();
    }

    private function productRows(array $filters): Collection
    {
        return OrderItem::query()
            ->selectRaw(
                'product_name, product_code, SUM(quantity) as quantity, SUM(total_price) as total'
            )
            ->whereHas('order', function ($query) use ($filters): void {
                $query
                    ->whereBetween('created_at', [
                        $filters['start'],
                        $filters['end'],
                    ])
                    ->where('status', '!=', Order::STATUS_CANCELLED)
                    ->when($filters['brand_id'], function ($query) use ($filters): void {
                        $query->where('brand_id', $filters['brand_id']);
                    })
                    ->when($filters['status'], function ($query) use ($filters): void {
                        $query->where('status', $filters['status']);
                    });
            })
            ->groupBy('product_name', 'product_code')
            ->orderByDesc('quantity')
            ->limit(25)
            ->get()
            ->map(fn (OrderItem $item): array => [
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'quantity' => (int) $item->quantity,
                'total' => (float) $item->total,
            ]);
    }

    private function customerSegments(
        Collection $orders,
        Carbon $start,
        Carbon $end,
        ?int $brandId = null
    ): array {
        $phones = $orders
            ->pluck('phone')
            ->map(fn ($phone): string => $this->normalizePhone($phone))
            ->filter()
            ->unique()
            ->values();

        if ($phones->isEmpty()) {
            return [
                'new' => 0,
                'repeat' => 0,
            ];
        }

        $lifetimeCounts = Order::query()
            ->select('phone')
            ->where(
                'status',
                '!=',
                Order::STATUS_CANCELLED
            )
            ->where(
                'created_at',
                '<=',
                $end
            )
            ->when(
                $brandId,
                fn ($query) =>
                    $query->where(
                        'brand_id',
                        $brandId
                    )
            )
            ->get()
            ->pluck('phone')
            ->map(fn ($phone): string => $this->normalizePhone($phone))
            ->filter()
            ->filter(fn (string $phone): bool => $phones->contains($phone))
            ->countBy();

        $repeatPhones = $phones
            ->filter(
                fn (string $phone): bool =>
                    (int) ($lifetimeCounts[$phone] ?? 0) > 1
            )
            ->values();

        return [
            'new' => $phones
                ->diff($repeatPhones)
                ->count(),

            'repeat' => $repeatPhones->count(),
        ];
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: '';
    }

    private function periodLabel(array $filters): string
    {
        return Carbon::parse($filters['start_date'])->format('d M Y')
            .' - '
            .Carbon::parse($filters['end_date'])->format('d M Y');
    }
}
