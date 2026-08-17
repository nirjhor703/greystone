@extends('admin.layouts.app')

@section('title', 'Employee Dashboard | Grey Stone Admin')
@section('page-title', 'Employee Dashboard')
@section('page-subtitle', 'Employee competition, handled orders and customer performance')

@section('content')
@php
    $chartWidth = 960;
    $chartHeight = 320;
    $paddingLeft = 76;
    $paddingRight = 34;
    $paddingTop = 28;
    $paddingBottom = 38;
    $plotWidth = $chartWidth - $paddingLeft - $paddingRight;
    $plotHeight = $chartHeight - $paddingTop - $paddingBottom;

    $formatShort = function (float $value, bool $money = true): string {
        if ($value >= 100000) {
            $formatted = number_format($value / 100000, 1).'L';
        } elseif ($value >= 1000) {
            $formatted = number_format($value / 1000, 1).'K';
        } else {
            $formatted = number_format($value, 0);
        }

        return $money ? '৳'.$formatted : $formatted;
    };

    $coordsFor = function (array $series, float $max) use (
        $chartLabels,
        $paddingLeft,
        $paddingTop,
        $plotWidth,
        $plotHeight
    ) {
        return collect($series)->map(function ($value, $index) use (
            $chartLabels,
            $paddingLeft,
            $paddingTop,
            $plotWidth,
            $plotHeight,
            $max
        ) {
            $x = $paddingLeft + ($index * ($plotWidth / max($chartLabels->count() - 1, 1)));
            $ratio = $max > 0 ? ((float) $value / $max) : 0;
            $y = $paddingTop + ($plotHeight - ($ratio * $plotHeight));

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'value' => (float) $value,
                'label' => $chartLabels[$index] ?? '',
            ];
        });
    };
@endphp

<section class="employee-dashboard-page">
    <form
        method="GET"
        action="{{ route('admin.employee-dashboard.index') }}"
        class="dashboard-filter-panel"
    >
        <div class="dashboard-filter-heading">
            <div>
                <span>Performance Filters</span>
                <strong>{{ $periodLabel }}</strong>
            </div>

            <a href="{{ route('admin.employee-dashboard.index') }}">
                Reset
            </a>
        </div>

        <div class="dashboard-filter-grid employee-filter-grid">
            <label class="dashboard-filter-field">
                <span>Period</span>
                <select name="period" id="employeeDashboardPeriod">
                    @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'custom' => 'Custom'] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['period'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="dashboard-filter-field">
                <span>Date</span>
                <input
                    type="date"
                    name="date"
                    value="{{ $filters['date'] }}"
                >
            </label>

            <label class="dashboard-filter-field employee-custom-date">
                <span>Start Date</span>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $filters['start_date'] }}"
                >
            </label>

            <label class="dashboard-filter-field employee-custom-date">
                <span>End Date</span>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $filters['end_date'] }}"
                >
            </label>

            <label class="dashboard-filter-field">
                <span>Brand</span>
                <select name="brand_id">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected((int) $filters['brand_id'] === (int) $brand->id)
                        >
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="dashboard-filter-submit">
                <i class="fa-solid fa-filter"></i>
                Apply
            </button>
        </div>
    </form>

    @php
        $myPerformance = $currentUserRow ?? [
            'rank' => '-',
            'name' => auth()->user()?->name ?? 'Current Admin',
            'handled_orders' => 0,
            'confirmed_actions' => 0,
            'qc_passed_actions' => 0,
            'qc_issue_actions' => 0,
            'qc_resolved_actions' => 0,
            'sent_steadfast_actions' => 0,
            'revenue' => 0,
            'new_customers' => 0,
            'items' => 0,
            'delivery_rate' => 0,
        ];
    @endphp

    <section class="dashboard-kpi-grid employee-kpi-grid">
        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-ranking-star"></i>
            </div>
            <span>My Position</span>
            <strong>#{{ $myPerformance['rank'] }}</strong>
            <small>{{ $myPerformance['name'] }}</small>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <span>My Activities</span>
            <strong>{{ number_format($myPerformance['handled_orders']) }}</strong>
            <small>Confirmed, QC, resolve and dispatch actions</small>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <span>My Revenue</span>
            <strong>৳{{ number_format($myPerformance['revenue'], 2) }}</strong>
            <small>Cancelled orders excluded</small>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <span>My New Customers</span>
            <strong>{{ number_format($myPerformance['new_customers']) }}</strong>
            <small>First orders handled by me</small>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <span>My Dispatch</span>
            <strong>{{ number_format($myPerformance['sent_steadfast_actions']) }}</strong>
            <small>{{ number_format($myPerformance['delivery_rate']) }}% touched delivery rate</small>
        </article>
    </section>

    <section class="employee-spotlight-grid">
        <article class="employee-spotlight-card">
            <span>Top Performer</span>
            @if ($summary['top_performer'])
                <strong>{{ $summary['top_performer']['name'] }}</strong>
                <p>
                    {{ number_format($summary['top_performer']['handled_orders']) }}
                    orders · ৳{{ number_format($summary['top_performer']['revenue'], 2) }}
                    revenue · {{ number_format($summary['top_performer']['new_customers']) }}
                    new customers
                </p>
            @else
                <strong>No activity yet</strong>
                <p>Performance will appear after employees send orders to Steadfast.</p>
            @endif
        </article>

        <article class="employee-spotlight-card">
            <span>Your Position</span>
            @if ($currentUserRow)
                <strong>#{{ $currentUserRow['rank'] }} · {{ $currentUserRow['name'] }}</strong>
                <p>
                    {{ number_format($currentUserRow['handled_orders']) }}
                    handled · {{ number_format($currentUserRow['delivery_rate']) }}%
                    delivery rate · {{ number_format($currentUserRow['score']) }}
                    score
                </p>
            @else
                <strong>No handled orders</strong>
                <p>Your rank will appear after you send an order to Steadfast.</p>
            @endif
        </article>
    </section>

    <section class="dashboard-performance-card employee-trend-card">
        <div class="dashboard-performance-head">
            <div>
                <span class="dashboard-section-eyebrow">
                    Employee Trend
                </span>
                <h3>Performance Trend</h3>
                <p>Top employees by activity count and touched revenue for {{ $periodLabel }}.</p>
            </div>

            <div class="dashboard-metric-switch" data-employee-trend-tools hidden>
                <button
                    type="button"
                    class="active"
                    data-employee-trend-metric="revenue"
                >
                    Revenue
                </button>

                <button
                    type="button"
                    data-employee-trend-metric="orders"
                >
                    Orders
                </button>
            </div>
        </div>

        <div class="employee-trend-buttons">
            <button
                type="button"
                class="active"
                data-employee-filter="all"
            >
                <i class="fa-solid fa-chart-simple"></i>
                All
            </button>

            @foreach ($chartRows as $row)
                <button
                    type="button"
                    data-employee-filter="{{ $row['id'] }}"
                    style="--series-color: {{ $row['color'] }}"
                >
                    <span></span>
                    {{ $row['name'] }}
                </button>
            @endforeach
        </div>

        <div class="employee-comparison-bars" data-employee-comparison>
            @if ($chartRows->isNotEmpty())
                <div class="employee-bar-chart">
                    <div class="employee-chart-y-axis">
                        @for ($line = 5; $line >= 0; $line--)
                            <span>
                                {{ $formatShort(($maxRevenue / 5) * $line) }}
                            </span>
                        @endfor
                    </div>

                    <div class="employee-chart-bars">
                        @foreach ($chartRows as $row)
                            <button
                                type="button"
                                data-employee-filter="{{ $row['id'] }}"
                                style="
                                    --series-color: {{ $row['color'] }};
                                    --bar-height: {{ max(($row['revenue'] / $maxRevenue) * 100, $row['revenue'] > 0 ? 4 : 0) }}%;
                                "
                                aria-label="Open {{ $row['name'] }} performance details"
                            >
                                <strong>
                                    ৳{{ number_format($row['revenue'], 0) }}
                                </strong>

                                <i></i>

                                <span>
                                    {{ $row['name'] }}
                                </span>

                                <small>
                                    {{ number_format($row['handled_orders']) }} actions
                                </small>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="brand-empty-state">
                    <strong>No employee activity yet</strong>
                    <span>Send orders to Steadfast to build the comparison chart.</span>
                </div>
            @endif
        </div>

        @foreach (['revenue' => $trendRevenueMax, 'orders' => $trendOrdersMax] as $metricType => $maxValue)
            <div
                class="dashboard-chart-panel {{ $metricType === 'revenue' ? 'active' : '' }}"
                data-employee-trend-panel="{{ $metricType }}"
                hidden
            >
                <div class="dashboard-chart-wrap dashboard-pro-chart">
                    <svg
                        viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                        role="img"
                        aria-label="Employee {{ $metricType }} trend"
                    >
                        @for ($line = 0; $line < 6; $line++)
                            @php
                                $y = $paddingTop + ($line * ($plotHeight / 5));
                                $tick = $maxValue - (($maxValue / 5) * $line);
                            @endphp

                            <line
                                class="dashboard-horizontal-grid"
                                x1="{{ $paddingLeft }}"
                                y1="{{ $y }}"
                                x2="{{ $chartWidth - $paddingRight }}"
                                y2="{{ $y }}"
                            />

                            <text
                                x="{{ $paddingLeft - 14 }}"
                                y="{{ $y + 4 }}"
                                text-anchor="end"
                                class="dashboard-axis-label"
                            >
                                {{ $formatShort($tick, $metricType === 'revenue') }}
                            </text>
                        @endfor

                        @foreach ($chartLabels as $index => $label)
                            @php
                                $x = $paddingLeft + ($index * ($plotWidth / max($chartLabels->count() - 1, 1)));
                            @endphp

                            <line
                                class="dashboard-vertical-grid"
                                x1="{{ $x }}"
                                y1="{{ $paddingTop }}"
                                x2="{{ $x }}"
                                y2="{{ $paddingTop + $plotHeight }}"
                            />
                        @endforeach

                        @foreach ($chartRows as $row)
                            @php
                                $seriesKey = $metricType === 'revenue'
                                    ? 'revenue_series'
                                    : 'orders_series';
                                $coords = $coordsFor($row[$seriesKey], $maxValue);
                            @endphp

                            <polyline
                                data-employee-line="{{ $row['id'] }}"
                                pathLength="100"
                                points="{{ $coords->map(fn ($point) => $point['x'].','.$point['y'])->implode(' ') }}"
                                style="--series-color: {{ $row['color'] }}"
                            />

                            @foreach ($coords as $point)
                                @if ($point['value'] > 0)
                                    @php
                                        $tooltipWidth = 148;
                                        $tooltipHeight = 68;
                                        $tooltipX = ($chartWidth - $point['x']) > ($tooltipWidth + 30)
                                            ? $point['x'] + 16
                                            : $point['x'] - $tooltipWidth - 16;
                                        $tooltipX = min(max($tooltipX, 12), $chartWidth - $tooltipWidth - 12);
                                        $tooltipY = max($point['y'] - $tooltipHeight - 14, 10);
                                    @endphp

                                    <g
                                        class="dashboard-point-group"
                                        data-employee-line="{{ $row['id'] }}"
                                        tabindex="0"
                                        style="--series-color: {{ $row['color'] }}"
                                    >
                                        <circle
                                            cx="{{ $point['x'] }}"
                                            cy="{{ $point['y'] }}"
                                            r="4.2"
                                        />

                                        <g
                                            class="dashboard-hover-tooltip"
                                            transform="translate({{ $tooltipX }} {{ $tooltipY }})"
                                        >
                                            <rect
                                                width="{{ $tooltipWidth }}"
                                                height="{{ $tooltipHeight }}"
                                                rx="12"
                                            ></rect>

                                            <text x="13" y="20" class="dashboard-tooltip-date">
                                                {{ $row['name'] }}
                                            </text>

                                            <text x="13" y="40" class="dashboard-tooltip-muted">
                                                {{ $point['label'] }}
                                            </text>

                                            <text x="13" y="59" class="dashboard-tooltip-value">
                                                @if ($metricType === 'revenue')
                                                    ৳{{ number_format($point['value'], 2) }}
                                                @else
                                                    {{ number_format($point['value']) }} orders
                                                @endif
                                            </text>
                                        </g>
                                    </g>
                                @endif
                            @endforeach
                        @endforeach
                    </svg>
                </div>
            </div>
        @endforeach

        <div
            class="dashboard-chart-labels employee-chart-labels"
            data-employee-chart-labels
            hidden
        >
            @foreach ($chartLabels as $label)
                <span>{{ $label }}</span>
            @endforeach
        </div>

        <div class="employee-detail-strip" data-employee-detail-strip hidden>
            @foreach ($chartRows as $row)
                <article
                    data-employee-detail="{{ $row['id'] }}"
                    style="--series-color: {{ $row['color'] }}"
                    hidden
                >
                    <div>
                        <span></span>
                        <strong>{{ $row['name'] }}</strong>
                        <small>{{ $row['email'] }}</small>
                    </div>

                    <p>
                        <b>৳{{ number_format($row['revenue'], 2) }}</b>
                        Revenue
                    </p>

                    <p>
                        <b>{{ number_format($row['handled_orders']) }}</b>
                        Actions
                    </p>

                    <p>
                        <b>{{ number_format($row['qc_passed_actions']) }}</b>
                        QC Passed
                    </p>

                    <p>
                        <b>{{ number_format($row['qc_resolved_actions']) }}</b>
                        Issues Resolved
                    </p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="dashboard-panel employee-table-panel">
        <div class="dashboard-card-head">
            <div>
                <h3>Detailed Performance</h3>
                <p>Use this table for daily follow-up and team coaching.</p>
            </div>
        </div>

        <div class="brand-table-wrapper">
            <table class="brand-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Actions</th>
                        <th>Confirmed</th>
                        <th>QC Passed</th>
                        <th>QC Issue</th>
                        <th>Resolved</th>
                        <th>Sent</th>
                        <th>Delivered Touched</th>
                        <th>Revenue Touched</th>
                        <th>New Customers</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['name'] }}</strong>
                                <small class="order-table-muted">{{ $row['email'] }}</small>
                            </td>
                            <td>{{ number_format($row['handled_orders']) }}</td>
                            <td>{{ number_format($row['confirmed_actions']) }}</td>
                            <td>{{ number_format($row['qc_passed_actions']) }}</td>
                            <td>{{ number_format($row['qc_issue_actions']) }}</td>
                            <td>{{ number_format($row['qc_resolved_actions']) }}</td>
                            <td>{{ number_format($row['sent_steadfast_actions']) }}</td>
                            <td>{{ number_format($row['delivered_orders']) }}</td>
                            <td>৳{{ number_format($row['revenue'], 2) }}</td>
                            <td>{{ number_format($row['new_customers']) }}</td>
                            <td>
                                <span class="status-pill status-delivered">
                                    {{ number_format($row['score']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="brand-empty-state">
                                    <strong>No performance data</strong>
                                    <span>Data will appear after orders are sent to Steadfast.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const periodSelect = document.getElementById('employeeDashboardPeriod');
            const customFields = document.querySelectorAll('.employee-custom-date');

            function syncCustomFields() {
                const isCustom = periodSelect?.value === 'custom';

                customFields.forEach(function (field) {
                    field.classList.toggle('active', isCustom);
                    field.querySelector('input')?.toggleAttribute('disabled', !isCustom);
                });
            }

            periodSelect?.addEventListener('change', syncCustomFields);
            syncCustomFields();

            const trendButtons = document.querySelectorAll('[data-employee-trend-metric]');
            const trendPanels = document.querySelectorAll('[data-employee-trend-panel]');
            const employeeButtons = document.querySelectorAll('[data-employee-filter]');
            const comparisonChart = document.querySelector('[data-employee-comparison]');
            const detailStrip = document.querySelector('[data-employee-detail-strip]');
            const detailCards = document.querySelectorAll('[data-employee-detail]');
            const trendTools = document.querySelector('[data-employee-trend-tools]');
            const chartLabels = document.querySelector('[data-employee-chart-labels]');
            let selectedEmployee = 'all';

            function activeMetric() {
                return document
                    .querySelector('[data-employee-trend-metric].active')
                    ?.dataset
                    .employeeTrendMetric || 'revenue';
            }

            function syncTrendPanels() {
                const isAll = selectedEmployee === 'all';
                const metric = activeMetric();

                if (comparisonChart) {
                    comparisonChart.hidden = !isAll;
                }

                if (trendTools) {
                    trendTools.hidden = isAll;
                }

                if (chartLabels) {
                    chartLabels.hidden = isAll;
                }

                if (detailStrip) {
                    detailStrip.hidden = isAll;
                }

                detailCards.forEach(function (card) {
                    card.hidden = isAll
                        || card.dataset.employeeDetail !== selectedEmployee;
                });

                trendPanels.forEach(function (panel) {
                    const active = !isAll
                        && panel.dataset.employeeTrendPanel === metric;

                    panel.hidden = !active;
                    panel.classList.toggle('active', active);
                });

                document
                    .querySelectorAll('[data-employee-line]')
                    .forEach(function (item) {
                        item.classList.toggle(
                            'is-hidden',
                            !isAll
                                && item.dataset.employeeLine !== selectedEmployee
                        );
                    });
            }

            trendButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const metric = button.dataset.employeeTrendMetric;

                    trendButtons.forEach(function (item) {
                        item.classList.toggle('active', item === button);
                    });

                    syncTrendPanels();
                });
            });

            employeeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    selectedEmployee = button.dataset.employeeFilter;

                    employeeButtons.forEach(function (item) {
                        item.classList.toggle(
                            'active',
                            item.dataset.employeeFilter === selectedEmployee
                        );
                    });

                    syncTrendPanels();
                });
            });

            syncTrendPanels();
        });
    </script>
@endpush
