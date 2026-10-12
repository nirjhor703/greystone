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

    <section class="employee-workforce-overview">
        <button class="employee-total-card" type="button" data-open-employee-directory data-directory-url="{{ route('admin.employee.directory') }}">
            <span class="dashboard-section-eyebrow">Workforce Overview</span>
            <strong>{{ number_format($totalEmployees) }}</strong>
            <span>Total Employees <b>↗</b></span>
        </button>

        <div class="employee-department-overview">
            <div class="employee-overview-heading">
                <div>
                    <span class="dashboard-section-eyebrow">Department Distribution</span>
                    <h3>Active employees by department</h3>
                </div>
            </div>
            @if ((int) $totalEmployees === 0)
                <div class="employee-empty-state">
                    <strong>No employees assigned yet.</strong>
                    <span>Select an existing person and assign their department and designation to get started.</span>
                    <button type="button" class="brand-primary-button" data-open-employee-assignment>Assign Existing Employee</button>
                </div>
            @endif
            @forelse($departmentHeadcounts as $department)
                @php
                    $headcount = (int) ($department->profiles_count ?? 0);
                @endphp
                <div class="employee-department-row">
                    <span>{{ $department->name }}</span>
                    <div class="employee-department-bar"><i style="width: {{ $totalEmployees ? min(100, ($headcount / $totalEmployees) * 100) : 0 }}%"></i></div>
                    <strong>{{ number_format($headcount) }}</strong>
                </div>
            @empty
                <p class="employee-overview-empty">No active departments yet.</p>
            @endforelse
        </div>
    </section>

    <section class="employee-management-launchers">
        <button type="button" class="brand-primary-button" data-open-employee-assignment><i class="fa-solid fa-user-plus"></i> Assign Employee</button>
        <button type="button" class="dashboard-filter-submit" data-open-employee-directory>Open Employee Directory</button>
        <button type="button" class="dashboard-filter-submit" data-open-employee-organization>Manage Departments &amp; Designations</button>
    </section>

    <section class="employee-frontend-panel" data-employee-directory-panel hidden>
        <div class="employee-frontend-panel-head"><div><span class="dashboard-section-eyebrow">Employee Directory</span><h3>Department-wise active employees</h3></div><button type="button" class="brand-secondary-button" data-close-employee-panel>Close</button></div>
        <div class="employee-directory-filters"><input type="search" placeholder="Search employee name" data-directory-search><select data-directory-department><option value="">All departments</option></select><select data-directory-designation><option value="">All designations</option></select><button type="button" class="brand-primary-button" data-directory-apply>Search</button></div>
        <div data-directory-results><p class="employee-overview-empty">Loading employees…</p></div>
    </section>

    <section class="employee-frontend-panel" data-employee-organization-panel hidden>
        <div class="employee-frontend-panel-head"><div><span class="dashboard-section-eyebrow">Employee Organization</span><h3>Departments &amp; Designations</h3></div><button type="button" class="brand-secondary-button" data-close-employee-panel>Close</button></div>
        <div class="employee-org-toolbar"><div class="employee-org-filters"><input type="search" placeholder="Search department or designation" data-organization-search><select data-organization-department><option value="">All departments</option></select></div><div class="employee-org-actions"><button type="button" class="brand-primary-button" data-add-department>Add Department</button><button type="button" class="brand-secondary-button" data-add-designation>Add Designation</button></div></div>
        <div data-employee-organization-results><p class="employee-overview-empty">Loading organization…</p></div>
    </section>

    <div class="employee-duty-modal" data-employee-duty-modal hidden><div class="employee-duty-dialog"><div class="employee-frontend-panel-head"><div><span class="dashboard-section-eyebrow">Job Responsibilities</span><h3 data-duty-title></h3><p data-duty-department></p></div><button type="button" class="brand-secondary-button" data-close-duty-modal>Close</button></div><div class="employee-duty-actions"><button type="button" class="brand-primary-button" data-add-duty>Add Responsibility</button><button type="button" class="brand-secondary-button" data-print-duty>Print Job Description</button></div><div data-duty-list></div></div></div>

    <div class="employee-form-modal" data-employee-assignment-modal hidden>
        <div class="employee-form-dialog" role="dialog" aria-modal="true" aria-labelledby="employee-assignment-title">
            <div class="employee-frontend-panel-head"><div><span class="dashboard-section-eyebrow">Employee Management</span><h3 id="employee-assignment-title">Assign Employee</h3><p>Select an existing person and assign their department and designation.</p></div><button type="button" class="brand-secondary-button" data-close-employee-modal>Close</button></div>
            <form data-assignment-form>
                <label class="employee-form-field"><span>1. Select Person</span><input type="search" placeholder="Search name, email or phone" data-assignment-search><div class="employee-person-results" data-assignment-people></div></label>
                <input type="hidden" data-assignment-person>
                <div class="employee-selected-person" data-assignment-selected hidden></div>
                <label class="employee-form-field"><span>2. Select Department</span><select data-assignment-department required><option value="">Choose department</option></select></label>
                <label class="employee-form-field"><span>3. Select Designation</span><select data-assignment-designation required disabled><option value="">Choose designation</option></select></label>
                <p class="employee-form-error" data-assignment-error hidden></p>
                <div class="employee-form-actions"><button type="button" class="brand-secondary-button" data-remove-assignment hidden>Remove Employee Assignment</button><button type="button" class="brand-secondary-button" data-close-employee-modal>Cancel</button><button type="submit" class="brand-primary-button" data-assignment-submit>Assign Employee</button></div>
            </form>
        </div>
    </div>

    <div class="employee-form-modal" data-employee-org-modal hidden>
        <div class="employee-form-dialog" role="dialog" aria-modal="true"><div class="employee-frontend-panel-head"><div><span class="dashboard-section-eyebrow">Organization Management</span><h3 data-org-modal-title></h3></div><button type="button" class="brand-secondary-button" data-close-employee-modal>Close</button></div><form data-org-form><div data-org-fields></div><p class="employee-form-error" data-org-error hidden></p><div class="employee-form-actions"><button type="button" class="brand-secondary-button" data-close-employee-modal>Cancel</button><button type="submit" class="brand-primary-button">Save</button></div></form></div>
    </div>

    <div class="employee-form-modal" data-employee-confirm-modal hidden><div class="employee-form-dialog employee-confirm-dialog" role="dialog" aria-modal="true"><div class="employee-frontend-panel-head"><div><span class="dashboard-section-eyebrow" data-confirm-eyebrow>Form</span><h3 data-confirm-title></h3><div class="employee-confirm-content" data-confirm-message></div></div><button type="button" class="brand-secondary-button" data-close-employee-modal>Close</button></div><div class="employee-form-actions"><button type="button" class="brand-secondary-button" data-close-employee-modal>Cancel</button><button type="button" class="brand-primary-button" data-confirm-action>Confirm</button></div></div></div>

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
                <h3>Performance Trend @include('admin.partials.info-help', ['key' => 'employee_trend', 'text' => 'Selected period-e employee activity/revenue trend compare korte use korun.'])</h3>
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
                <h3>Detailed Performance @include('admin.partials.info-help', ['key' => 'employee_detailed_performance', 'text' => 'Employee-wise handled actions, delivery touch, revenue touch and score ekhane dekhay.'])</h3>
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
                        <th>Score @include('admin.partials.info-help', ['key' => 'employee_score', 'text' => 'Actions, delivery touch, revenue and customer activity mile performance score calculate hoy.'])</th>
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
