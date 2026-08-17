<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $employees = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $employeeIds = $employees->pluck('id')->all();

        $activityLogs = OrderActivityLog::query()
            ->with(['order.items', 'user'])
            ->whereIn('user_id', $employeeIds)
            ->whereBetween('created_at', [
                $filters['start'],
                $filters['end'],
            ])
            ->when($filters['brand_id'], function ($query) use ($filters): void {
                $query->whereHas('order', function ($query) use ($filters): void {
                    $query->where('brand_id', $filters['brand_id']);
                });
            })
            ->get();

        $validOrders = $activityLogs
            ->pluck('order')
            ->filter()
            ->unique('id')
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->values();
        $chartPeriods = $this->buildChartPeriods(
            $filters['start'],
            $filters['end']
        );

        $firstOrdersByPhone = $this->firstOrdersByPhone(
            $filters['end'],
            $filters['brand_id']
        );

        $rows = $employees
            ->map(function (User $employee) use (
                $validOrders,
                $activityLogs,
                $firstOrdersByPhone,
                $chartPeriods
            ): array {
                $employeeLogs = $activityLogs
                    ->where('user_id', $employee->id)
                    ->values();

                $employeeOrders = $employeeLogs
                    ->pluck('order')
                    ->filter()
                    ->unique('id')
                    ->values();

                $validEmployeeOrders = $employeeOrders
                    ->where('status', '!=', Order::STATUS_CANCELLED)
                    ->values();

                $orderIds = $employeeOrders
                    ->pluck('id')
                    ->all();

                $phones = $employeeOrders
                    ->pluck('phone')
                    ->map(fn ($phone): string => $this->normalizePhone($phone))
                    ->filter()
                    ->unique()
                    ->values();

                $newCustomers = $phones
                    ->filter(function (string $phone) use (
                        $firstOrdersByPhone,
                        $orderIds
                    ): bool {
                        $firstOrder = $firstOrdersByPhone->get($phone);

                        return $firstOrder
                            && in_array($firstOrder->id, $orderIds, true);
                    })
                    ->count();

                $actionCounts = $employeeLogs
                    ->groupBy('action')
                    ->map(fn (Collection $logs): int => $logs->count());

                $activityCount = $employeeLogs->count();
                $deliveredCount = $validEmployeeOrders
                    ->where('status', Order::STATUS_DELIVERED)
                    ->count();
                $revenue = (float) $validEmployeeOrders->sum('grand_total');
                $items = (int) $validEmployeeOrders->sum(
                    fn (Order $order): int =>
                        (int) $order->items->sum('quantity')
                );

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'role' => $employee->roleLabel(),
                    'handled_orders' => $activityCount,
                    'confirmed_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_CONFIRMED] ?? 0),
                    'updated_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_UPDATED] ?? 0),
                    'qc_passed_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_QC_PASSED] ?? 0),
                    'qc_issue_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_QC_ISSUE] ?? 0),
                    'qc_resolved_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_QC_RESOLVED] ?? 0),
                    'sent_steadfast_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_SENT_STEADFAST] ?? 0),
                    'cancelled_actions' => (int) ($actionCounts[OrderActivityLog::ACTION_CANCELLED] ?? 0),
                    'delivered_orders' => $deliveredCount,
                    'pending_orders' => $employeeOrders
                        ->where('status', Order::STATUS_PENDING)
                        ->count(),
                    'confirmed_orders' => $employeeOrders
                        ->where('status', Order::STATUS_CONFIRMED)
                        ->count(),
                    'revenue' => $revenue,
                    'items' => $items,
                    'customers' => $phones->count(),
                    'new_customers' => $newCustomers,
                    'repeat_customers' => max(
                        $phones->count() - $newCustomers,
                        0
                    ),
                    'average_order' => $validEmployeeOrders->count() > 0
                        ? $revenue / $validEmployeeOrders->count()
                        : 0,
                    'delivery_rate' => $validEmployeeOrders->count() > 0
                        ? round(($deliveredCount / $validEmployeeOrders->count()) * 100)
                        : 0,
                    'last_handled_at' => $employeeLogs
                        ->max('created_at'),
                    'revenue_series' => $this->series(
                        $employeeLogs,
                        $chartPeriods,
                        'revenue'
                    ),
                    'orders_series' => $this->series(
                        $employeeLogs,
                        $chartPeriods,
                        'orders'
                    ),
                    'score' => ((int) ($actionCounts[OrderActivityLog::ACTION_CONFIRMED] ?? 0) * 2)
                        + ((int) ($actionCounts[OrderActivityLog::ACTION_UPDATED] ?? 0))
                        + ((int) ($actionCounts[OrderActivityLog::ACTION_QC_PASSED] ?? 0))
                        + ((int) ($actionCounts[OrderActivityLog::ACTION_QC_ISSUE] ?? 0) * 2)
                        + ((int) ($actionCounts[OrderActivityLog::ACTION_QC_RESOLVED] ?? 0) * 2)
                        + ((int) ($actionCounts[OrderActivityLog::ACTION_SENT_STEADFAST] ?? 0))
                        + ($deliveredCount * 2)
                        + ($newCustomers * 5)
                        + (int) floor($revenue / 1000)
                        - ((int) ($actionCounts[OrderActivityLog::ACTION_CANCELLED] ?? 0) * 2),
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->map(function (array $row, int $index): array {
                return [
                    ...$row,
                    'rank' => $index + 1,
                    'color' => $this->employeeColor($row['id']),
                ];
            });

        $maxRevenue = max((float) $rows->max('revenue'), 1);
        $maxOrders = max((int) $rows->max('handled_orders'), 1);
        $chartRows = $rows->values();
        $chartLabels = $chartPeriods
            ->pluck('label')
            ->values();
        $trendRevenueMax = max(
            $chartRows
                ->flatMap(fn (array $row): array => $row['revenue_series'])
                ->push(1)
                ->all()
        );
        $trendOrdersMax = max(
            $chartRows
                ->flatMap(fn (array $row): array => $row['orders_series'])
                ->push(1)
                ->all()
        );
        $currentUserRow = $rows->firstWhere(
            'id',
            $request->user()?->id ?? auth()->id()
        );

        return view('admin.employee-dashboard.index', [
            'brands' => $brands,
            'filters' => $filters,
            'periodLabel' => $this->periodLabel($filters),
            'rows' => $rows,
            'chartRows' => $chartRows,
            'chartLabels' => $chartLabels,
            'trendRevenueMax' => $trendRevenueMax,
            'trendOrdersMax' => $trendOrdersMax,
            'maxRevenue' => $maxRevenue,
            'maxOrders' => $maxOrders,
            'currentUserRow' => $currentUserRow,
            'summary' => [
                'employees' => $employees->count(),
                'active_performers' => $rows
                    ->where('handled_orders', '>', 0)
                    ->count(),
                'handled_orders' => $activityLogs->count(),
                'revenue' => (float) $validOrders->sum('grand_total'),
                'new_customers' => (int) $rows->sum('new_customers'),
                'items' => (int) $rows->sum('items'),
                'top_performer' => $rows->first(),
            ],
        ]);
    }

    private function filters(Request $request): array
    {
        $period = in_array(
            $request->input('period'),
            ['daily', 'weekly', 'monthly', 'custom'],
            true
        )
            ? $request->input('period')
            : 'monthly';

        $anchor = Carbon::parse(
            $request->input('date', now()->toDateString())
        );

        if ($period === 'daily') {
            $start = $anchor->copy()->startOfDay();
            $end = $anchor->copy()->endOfDay();
        } elseif ($period === 'weekly') {
            $start = $anchor->copy()->startOfWeek();
            $end = $anchor->copy()->endOfWeek();
        } elseif ($period === 'custom') {
            $start = Carbon::parse(
                $request->input('start_date', now()->subDays(29)->toDateString())
            )->startOfDay();
            $end = Carbon::parse(
                $request->input('end_date', now()->toDateString())
            )->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [
                    $end->copy()->startOfDay(),
                    $start->copy()->endOfDay(),
                ];
            }
        } else {
            $period = 'monthly';
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
        }

        return [
            'period' => $period,
            'date' => $anchor->toDateString(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'start' => $start,
            'end' => $end,
            'brand_id' => $request->filled('brand_id')
                ? (int) $request->input('brand_id')
                : null,
        ];
    }

    private function firstOrdersByPhone(
        Carbon $end,
        ?int $brandId
    ): Collection {
        return Order::query()
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->where('created_at', '<=', $end)
            ->when($brandId, function ($query) use ($brandId): void {
                $query->where('brand_id', $brandId);
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy(
                fn (Order $order): string =>
                    $this->normalizePhone($order->phone)
            )
            ->filter(fn (Collection $orders, string $phone): bool => filled($phone))
            ->map(fn (Collection $orders): Order => $orders->first());
    }

    private function buildChartPeriods(
        Carbon $start,
        Carbon $end
    ): Collection {
        $days = $start
            ->copy()
            ->startOfDay()
            ->diffInDays($end->copy()->startOfDay());

        $bucket = $days > 120
            ? 'month'
            : ($days > 45 ? 'week' : 'day');

        $periods = collect();
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            if ($bucket === 'month') {
                $bucketStart = $cursor->copy()->startOfMonth();
                $bucketEnd = $cursor->copy()->endOfMonth();

                $periods->push([
                    'key' => $bucketStart->format('Y-m'),
                    'label' => $bucketStart->format('M Y'),
                    'start' => $bucketStart,
                    'end' => $bucketEnd,
                ]);

                $cursor->addMonth();
                continue;
            }

            if ($bucket === 'week') {
                $bucketStart = $cursor->copy()->startOfWeek();
                $bucketEnd = $cursor->copy()->endOfWeek();

                $periods->push([
                    'key' => $bucketStart->format('o-W'),
                    'label' => $bucketStart->format('M d'),
                    'start' => $bucketStart,
                    'end' => $bucketEnd,
                ]);

                $cursor->addWeek();
                continue;
            }

            $bucketStart = $cursor->copy()->startOfDay();
            $bucketEnd = $cursor->copy()->endOfDay();

            $periods->push([
                'key' => $bucketStart->format('Y-m-d'),
                'label' => $bucketStart->format('M d'),
                'start' => $bucketStart,
                'end' => $bucketEnd,
            ]);

            $cursor->addDay();
        }

        return $periods
            ->unique('key')
            ->values();
    }

    private function series(
        Collection $logs,
        Collection $periods,
        string $type
    ): array {
        return $periods
            ->map(function (array $period) use ($logs, $type): float {
                $periodLogs = $logs
                    ->filter(
                        fn (OrderActivityLog $log): bool =>
                            $log->created_at
                            && $log->created_at->betweenIncluded(
                                $period['start'],
                                $period['end']
                            )
                    );

                if ($type === 'revenue') {
                    return (float) $periodLogs
                        ->pluck('order')
                        ->filter()
                        ->unique('id')
                        ->where('status', '!=', Order::STATUS_CANCELLED)
                        ->sum('grand_total');
                }

                return (float) $periodLogs->count();
            })
            ->values()
            ->all();
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: '';
    }

    private function employeeColor(int $userId): string
    {
        $palette = [
            '#4b5563',
            '#2563eb',
            '#db2777',
            '#16a34a',
            '#f59e0b',
            '#7c3aed',
            '#0891b2',
            '#dc2626',
            '#65a30d',
            '#9333ea',
            '#0f766e',
            '#ea580c',
        ];

        return $palette[$userId % count($palette)];
    }

    private function periodLabel(array $filters): string
    {
        return Carbon::parse($filters['start_date'])->format('d M Y')
            .' - '
            .Carbon::parse($filters['end_date'])->format('d M Y');
    }
}
