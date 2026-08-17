<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $filters = $this->resolveFilters($request);

        $start = $filters['start'];
        $end = $filters['end'];
        $period = $filters['period'];
        $brandId = $filters['brand_id'];

        $chartPeriods = $this->buildChartPeriods(
            $start,
            $end,
            $period
        );

        $ordersQuery = Order::query()
            ->with([
                'brand',
                'items',
            ])
            ->whereBetween(
                'created_at',
                [
                    $start,
                    $end,
                ]
            );

        if ($brandId) {
            $ordersQuery->where(
                'brand_id',
                $brandId
            );
        }

        $orders = $ordersQuery->get();

        $validOrders = $orders
            ->where(
                'status',
                '!=',
                Order::STATUS_CANCELLED
            )
            ->values();

        $customerSegments = $this->customerSegments(
            $validOrders,
            $start,
            $end,
            $brandId
        );

        $visibleBrands = $brandId
            ? $brands
                ->where('id', $brandId)
                ->values()
            : $brands->values();

            $brandColors = [
                'grey-stone' => '#4b5563',
                'blue-shades' => '#3b82f6',
                'pink-touch' => '#ec4899',
            ];

        $brandMetrics = $visibleBrands
            ->map(function (
                Brand $brand,
                int $index
            ) use (
                $validOrders,
                $chartPeriods,
                $brandColors
            ): array {
                $brandOrders = $validOrders
                    ->where(
                        'brand_id',
                        $brand->id
                    )
                    ->values();

                $brandCustomerSegments =
                    $this->customerSegments(
                        $brandOrders,
                        $chartPeriods->first()['start'] ?? now(),
                        $chartPeriods->last()['end'] ?? now(),
                        $brand->id
                    );

                return [
                    'id' => $brand->id,

                    'name' => $brand->name,

                    'slug' => $brand->slug,

                    'color' =>
                        $brandColors[$brand->slug]
                        ?? (
                            $brand->primary_color
                            ?: '#18181b'
                        ),

                    'orders' =>
                        $brandOrders->count(),

                    'revenue' => (float)
                        $brandOrders->sum(
                            'grand_total'
                        ),

                    'items' => (int)
                        $brandOrders->sum(
                            function (
                                Order $order
                            ): int {
                                return (int)
                                    $order
                                        ->items
                                        ->sum(
                                            'quantity'
                                        );
                            }
                        ),

                    'customers' => (int)
                        $brandOrders
                            ->pluck('phone')
                            ->filter()
                            ->unique()
                            ->count(),

                    'new_customers' =>
                        $brandCustomerSegments['new'],

                    'repeat_customers' =>
                        $brandCustomerSegments['repeat'],

                    'average_order' =>
                        $brandOrders->count() > 0
                            ? (
                                (float)
                                $brandOrders->sum(
                                    'grand_total'
                                )
                                /
                                $brandOrders->count()
                            )
                            : 0,

                    'revenue_series' =>
                        $this->series(
                            $brandOrders,
                            $chartPeriods,
                            'revenue'
                        ),

                    'orders_series' =>
                        $this->series(
                            $brandOrders,
                            $chartPeriods,
                            'orders'
                        ),

                    'items_series' =>
                        $this->series(
                            $brandOrders,
                            $chartPeriods,
                            'items'
                        ),
                ];
            })
            ->values();

        $chartLabels = $chartPeriods
            ->pluck('label')
            ->values();

        $rangeRevenue = (float)
            $brandMetrics->sum(
                'revenue'
            );

        $rangeOrders = (int)
            $brandMetrics->sum(
                'orders'
            );

        $rangeItems = (int)
            $brandMetrics->sum(
                'items'
            );

        $rangeCustomers = (int)
            $validOrders
                ->pluck('phone')
                ->filter()
                ->unique()
                ->count();

        $averageOrderValue =
            $rangeOrders > 0
                ? $rangeRevenue / $rangeOrders
                : 0;

        $soldItemTotals = $validOrders
            ->flatMap(fn (Order $order): Collection => $order->items)
            ->groupBy(function ($item): string {
                return (string) ($item->product_id ?: 0);
            })
            ->map(function (Collection $items): array {
                return [
                    'quantity' => (int) $items->sum('quantity'),
                    'revenue' => (float) $items->sum('total_price'),
                    'orders' => $items
                        ->pluck('order_id')
                        ->unique()
                        ->count(),
                ];
            });

        $soldItems = Product::query()
            ->when(
                $brandId,
                fn ($query) =>
                    $query->where(
                        'brand_id',
                        $brandId
                    )
            )
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use (
                $soldItemTotals
            ): array {
                $totals = $soldItemTotals->get(
                    (string) $product->id,
                    [
                        'quantity' => 0,
                        'revenue' => 0,
                        'orders' => 0,
                    ]
                );

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->product_code,
                    'quantity' => (int) $totals['quantity'],
                    'revenue' => (float) $totals['revenue'],
                    'orders' => (int) $totals['orders'],
                ];
            })
            ->sortByDesc('quantity')
            ->values();

        $revenueTotals = collect(
            $chartLabels
        )->map(
            function (
                $label,
                $index
            ) use (
                $brandMetrics
            ): float {
                return (float)
                    $brandMetrics->sum(
                        function (
                            array $metric
                        ) use (
                            $index
                        ): float {
                            return (float) (
                                $metric[
                                    'revenue_series'
                                ][$index]
                                ?? 0
                            );
                        }
                    );
            }
        );

        $ordersTotals = collect(
            $chartLabels
        )->map(
            function (
                $label,
                $index
            ) use (
                $brandMetrics
            ): float {
                return (float)
                    $brandMetrics->sum(
                        function (
                            array $metric
                        ) use (
                            $index
                        ): float {
                            return (float) (
                                $metric[
                                    'orders_series'
                                ][$index]
                                ?? 0
                            );
                        }
                    );
            }
        );

        $highlightRevenue =
            (float)
            $revenueTotals->max();

        $highlightOrders =
            (int)
            $ordersTotals->max();

        $dashboard = [
            'today_orders' =>
                Order::query()
                    ->when(
                        $brandId,
                        fn ($query) =>
                            $query->where(
                                'brand_id',
                                $brandId
                            )
                    )
                    ->whereDate(
                        'created_at',
                        today()
                    )
                    ->count(),

            'today_revenue' =>
                (float)
                Order::query()
                    ->when(
                        $brandId,
                        fn ($query) =>
                            $query->where(
                                'brand_id',
                                $brandId
                            )
                    )
                    ->whereDate(
                        'created_at',
                        today()
                    )
                    ->where(
                        'status',
                        '!=',
                        Order::STATUS_CANCELLED
                    )
                    ->sum(
                        'grand_total'
                    ),

            'range_revenue' =>
                $rangeRevenue,

            'range_orders' =>
                $rangeOrders,

            'range_items' =>
                $rangeItems,

            'range_customers' =>
                $rangeCustomers,

            'new_customers' =>
                $customerSegments['new'],

            'repeat_customers' =>
                $customerSegments['repeat'],

            'average_order_value' =>
                $averageOrderValue,

            'low_stock' =>
                Product::query()
                    ->when(
                        $brandId,
                        fn ($query) =>
                            $query->where(
                                'brand_id',
                                $brandId
                            )
                    )
                    ->where(
                        'stock_quantity',
                        '<',
                        10
                    )
                    ->count(),

            'unread_notifications' =>
                class_exists(
                    AdminNotification::class
                )
                    ? AdminNotification::query()
                        ->unread()
                        ->count()
                    : 0,

            'sold_items' =>
                $soldItems,
        ];

        $recentOrders = Order::query()
            ->with('brand')
            ->whereBetween(
                'created_at',
                [
                    $start,
                    $end,
                ]
            )
            ->when(
                $brandId,
                fn ($query) =>
                    $query->where(
                        'brand_id',
                        $brandId
                    )
            )
            ->latest('id')
            ->limit(6)
            ->get();

        $periodLabel =
            $this->periodLabel(
                $period,
                $start,
                $end
            );

        return view(
            'admin.dashboard',
            compact(
                'brands',
                'visibleBrands',
                'brandMetrics',
                'chartLabels',
                'dashboard',
                'recentOrders',
                'filters',
                'periodLabel',
                'rangeRevenue',
                'rangeOrders',
                'rangeItems',
                'rangeCustomers',
                'averageOrderValue',
                'highlightRevenue',
                'highlightOrders'
            )
        );
    }

    private function resolveFilters(
        Request $request
    ): array {
        $allowedPeriods = [
            'daily',
            'weekly',
            'monthly',
            'yearly',
            'custom',
        ];

        $period = in_array(
            $request->period,
            $allowedPeriods,
            true
        )
            ? $request->period
            : 'daily';

        $brandId = $request->filled(
            'brand_id'
        )
            ? (int)
                $request->brand_id
            : null;

        switch ($period) {
            case 'weekly':
                $start = now()
                    ->subWeeks(11)
                    ->startOfWeek();

                $end = now()
                    ->endOfWeek();

                break;

            case 'monthly':
                $start = now()
                    ->subMonths(11)
                    ->startOfMonth();

                $end = now()
                    ->endOfMonth();

                break;

            case 'yearly':
                $start = now()
                    ->subYears(4)
                    ->startOfYear();

                $end = now()
                    ->endOfYear();

                break;

            case 'custom':
                $start = $request->filled(
                    'start_date'
                )
                    ? Carbon::parse(
                        $request->start_date
                    )->startOfDay()
                    : now()
                        ->subDays(13)
                        ->startOfDay();

                $end = $request->filled(
                    'end_date'
                )
                    ? Carbon::parse(
                        $request->end_date
                    )->endOfDay()
                    : now()
                        ->endOfDay();

                if ($start->greaterThan($end)) {
                    [$start, $end] = [
                        $end->copy()
                            ->startOfDay(),

                        $start->copy()
                            ->endOfDay(),
                    ];
                }

                break;

            case 'daily':
            default:
                $start = now()
                    ->subDays(13)
                    ->startOfDay();

                $end = now()
                    ->endOfDay();

                break;
        }

        return [
            'period' => $period,

            'brand_id' => $brandId,

            'start' => $start,

            'end' => $end,

            'start_date' =>
                $start->format(
                    'Y-m-d'
                ),

            'end_date' =>
                $end->format(
                    'Y-m-d'
                ),
        ];
    }

    private function buildChartPeriods(
        Carbon $start,
        Carbon $end,
        string $period
    ): Collection {
        if ($period === 'custom') {
            $days = $start
                ->copy()
                ->startOfDay()
                ->diffInDays(
                    $end->copy()
                        ->startOfDay()
                );

            if ($days <= 31) {
                $period = 'daily';
            } elseif ($days <= 180) {
                $period = 'weekly';
            } elseif ($days <= 1095) {
                $period = 'monthly';
            } else {
                $period = 'yearly';
            }
        }

        $periods = collect();

        $cursor = $start->copy();

        while (
            $cursor->lessThanOrEqualTo(
                $end
            )
        ) {
            if ($period === 'weekly') {
                $bucketStart = $cursor
                    ->copy()
                    ->startOfWeek();

                $bucketEnd = $cursor
                    ->copy()
                    ->endOfWeek();

                $periods->push([
                    'key' =>
                        $bucketStart
                            ->format(
                                'o-W'
                            ),

                    'label' =>
                        $bucketStart
                            ->format(
                                'M d'
                            ),

                    'start' =>
                        $bucketStart,

                    'end' =>
                        $bucketEnd,
                ]);

                $cursor->addWeek();

                continue;
            }

            if ($period === 'monthly') {
                $bucketStart = $cursor
                    ->copy()
                    ->startOfMonth();

                $bucketEnd = $cursor
                    ->copy()
                    ->endOfMonth();

                $periods->push([
                    'key' =>
                        $bucketStart
                            ->format(
                                'Y-m'
                            ),

                    'label' =>
                        $bucketStart
                            ->format(
                                'M Y'
                            ),

                    'start' =>
                        $bucketStart,

                    'end' =>
                        $bucketEnd,
                ]);

                $cursor->addMonth();

                continue;
            }

            if ($period === 'yearly') {
                $bucketStart = $cursor
                    ->copy()
                    ->startOfYear();

                $bucketEnd = $cursor
                    ->copy()
                    ->endOfYear();

                $periods->push([
                    'key' =>
                        $bucketStart
                            ->format(
                                'Y'
                            ),

                    'label' =>
                        $bucketStart
                            ->format(
                                'Y'
                            ),

                    'start' =>
                        $bucketStart,

                    'end' =>
                        $bucketEnd,
                ]);

                $cursor->addYear();

                continue;
            }

            $bucketStart = $cursor
                ->copy()
                ->startOfDay();

            $bucketEnd = $cursor
                ->copy()
                ->endOfDay();

            $periods->push([
                'key' =>
                    $bucketStart
                        ->format(
                            'Y-m-d'
                        ),

                'label' =>
                    $bucketStart
                        ->format(
                            'M d'
                        ),

                'start' =>
                    $bucketStart,

                'end' =>
                    $bucketEnd,
            ]);

            $cursor->addDay();
        }

        return $periods
            ->unique('key')
            ->values();
    }

    private function series(
        Collection $orders,
        Collection $periods,
        string $type
    ): array {
        return $periods
            ->map(
                function (
                    array $period
                ) use (
                    $orders,
                    $type
                ): float {
                    $periodOrders =
                        $orders->filter(
                            function (
                                Order $order
                            ) use (
                                $period
                            ): bool {
                                return $order
                                    ->created_at
                                    ->betweenIncluded(
                                        $period['start'],
                                        $period['end']
                                    );
                            }
                        );

                    if ($type === 'revenue') {
                        return (float)
                            $periodOrders->sum(
                                'grand_total'
                            );
                    }

                    if ($type === 'items') {
                        return (float)
                            $periodOrders->sum(
                                function (
                                    Order $order
                                ): int {
                                    return (int)
                                        $order
                                            ->items
                                            ->sum(
                                                'quantity'
                                            );
                                }
                            );
                    }

                    return (float)
                        $periodOrders->count();
                }
            )
            ->values()
            ->all();
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

    private function periodLabel(
        string $period,
        Carbon $start,
        Carbon $end
    ): string {
        return match ($period) {
            'daily' =>
                'Last 14 days',

            'weekly' =>
                'Last 12 weeks',

            'monthly' =>
                'Last 12 months',

            'yearly' =>
                'Last 5 years',

            'custom' =>
                $start->format('M d, Y')
                .' – '
                .$end->format('M d, Y'),

            default =>
                'Selected period',
        };
    }
}
