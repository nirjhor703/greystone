@extends('admin.layouts.app')

@section('title', 'Dashboard | Grey Stone Admin')
@section('page-title', 'Dashboard')
@section(
    'page-subtitle',
    'Performance overview across every store'
)

@php
    $chartWidth = 960;
    $chartHeight = 340;

    $paddingLeft = 76;
    $paddingRight = 34;
    $paddingTop = 28;
    $paddingBottom = 38;

    $plotWidth =
        $chartWidth
        - $paddingLeft
        - $paddingRight;

    $plotHeight =
        $chartHeight
        - $paddingTop
        - $paddingBottom;

    $revenueMax = max(
        $brandMetrics
            ->flatMap(
                fn ($metric) =>
                    $metric['revenue_series']
            )
            ->push(1)
            ->all()
    );

    $ordersMax = max(
        $brandMetrics
            ->flatMap(
                fn ($metric) =>
                    $metric['orders_series']
            )
            ->push(1)
            ->all()
    );

    $formatShort = function (
        float $value,
        bool $money = true
    ): string {
        if ($value >= 10000000) {
            $formatted =
                number_format(
                    $value / 10000000,
                    1
                ).'Cr';
        } elseif ($value >= 100000) {
            $formatted =
                number_format(
                    $value / 100000,
                    1
                ).'L';
        } elseif ($value >= 1000) {
            $formatted =
                number_format(
                    $value / 1000,
                    1
                ).'K';
        } else {
            $formatted =
                number_format(
                    $value,
                    0
                );
        }

        return $money
            ? '৳'.$formatted
            : $formatted;
    };

    $canEditInfo = auth()->user()?->hasAdminPermission('info.editing');
    $dashboardHelpTexts = [
        'revenue' => 'Selected period-e cancelled order bad diye total confirmed sales amount ekhane dekhay.',
        'orders' => 'Selected period-e cancelled order bad diye total order count ekhane dekhay.',
        'items' => 'Selected period-e order item quantity total kore ekhane dekhay.',
        'customers' => 'Same phone number ekbar dhore unique customer count dekhay.',
        'customer_mix' => 'New mane first-time buyer, Repeat mane age order kora customer abar order koreche.',
        'average_order' => 'Revenue ke order count diye vag kore average order value dekhay.',
        'trend_revenue' => 'Chart-er selected period-er total revenue ekhane summary hishabe dekhay.',
        'trend_orders' => 'Chart-er selected period-er total order count ekhane summary hishabe dekhay.',
        'best_revenue' => 'Selected period-er moddhe je date/point-e highest revenue hoyeche seta dekhay.',
        'best_sales' => 'Selected period-er moddhe je date/point-e highest order hoyeche seta dekhay.',
        'analytics_filters' => 'Brand, period ba date range select kore dashboard-er shob calculation filter kora jay.',
        'filter_brand' => 'Specific brand select korle dashboard-e shudhu oi brand-er data dekhabe.',
        'filter_period' => 'Daily, weekly, monthly, yearly ba custom range diye report-er shomoy change kora jay.',
        'filter_start_date' => 'Custom date range use korle kon date theke hisab shuru hobe seta ekhane dite hoy.',
        'filter_end_date' => 'Custom date range use korle kon date porjonto hisab hobe seta ekhane dite hoy.',
        'business_trend' => 'Ei chart-e selected period-e revenue/order up-down trend dekha jay.',
        'brand_performance' => 'Prottek brand-er revenue, orders, items, average order, new and repeat customer ekhane compare kora jay.',
        'recent_orders' => 'Selected period-er latest order-gula ekhane quick view hishabe thake.',
        'needs_attention' => 'Je kaj-gula urgent attention dorkar, jemon low stock ba unread notification, ekhane dekhay.',
        'low_stock' => 'Stock kom hoye jawa products ekhane count hoy, click korle product list-e niye jay.',
        'unread_notifications' => 'Notun unread notification count ekhane dekhay, click korle notification page-e niye jay.',
    ];
    $dashboardHelp = function (string $key) use ($dashboardHelpTexts, $canEditInfo) {
        static $helpCounts = [];
        $helpCounts[$key] = ($helpCounts[$key] ?? 0) + 1;
        $id = 'dashboardHelp'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key))).$helpCounts[$key];
        $storageKey = 'dashboardHelpText_'.$key;
        $text = e($dashboardHelpTexts[$key] ?? '');
        $editButton = $canEditInfo
            ? '<button type="button" class="investment-help-edit-button">Edit</button>'
            : '';

        return new \Illuminate\Support\HtmlString(
            '<button type="button" class="investment-info-button" onclick="const box=document.getElementById(\''.$id.'\'); if(box){ box.hidden = !box.hidden; }" aria-label="Help">i</button><small id="'.$id.'" class="investment-help-text" data-investment-editable-help="'.$storageKey.'" hidden><span data-investment-help-copy>'.$text.'</span>'.$editButton.'</small>'
        );
    };

    $coordsFor = function (
        array $values,
        float $maximum
    ) use (
        $chartLabels,
        $paddingLeft,
        $paddingTop,
        $plotWidth,
        $plotHeight
    ) {
        $pointCount =
            max(
                count($values) - 1,
                1
            );

        return collect($values)
            ->map(
                function (
                    $value,
                    $index
                ) use (
                    $chartLabels,
                    $maximum,
                    $pointCount,
                    $paddingLeft,
                    $paddingTop,
                    $plotWidth,
                    $plotHeight
                ): array {
                    $x =
                        $paddingLeft
                        + (
                            $index
                            * (
                                $plotWidth
                                / $pointCount
                            )
                        );

                    $y =
                        $paddingTop
                        + $plotHeight
                        - (
                            (
                                (float) $value
                                /
                                max(
                                    $maximum,
                                    1
                                )
                            )
                            * $plotHeight
                        );

                    return [
                        'x' =>
                            round(
                                $x,
                                2
                            ),

                        'y' =>
                            round(
                                $y,
                                2
                            ),

                        'value' =>
                            (float) $value,

                        'label' =>
                            $chartLabels[
                                $index
                            ] ?? '',
                    ];
                }
            );
    };
@endphp

@section('content')
    <section class="dashboard-hero dashboard-pro-hero">
        <div>
            <span>
                Business Intelligence
            </span>

            <h2>
                Welcome back,
                {{ auth()->user()->name }}
            </h2>

            <p>
                Monitor revenue, orders, customers and
                individual brand performance from one
                professional workspace.
            </p>
        </div>

        <div class="dashboard-hero-actions">
            <a href="{{ route('admin.orders.index') }}">
                <i class="fa-solid fa-receipt"></i>
                Orders
            </a>

            <a href="{{ route('admin.reports.index') }}">
                <i class="fa-solid fa-chart-line"></i>
                Reports
            </a>
        </div>
    </section>

    <form
        method="GET"
        action="{{ route('admin.dashboard') }}"
        class="dashboard-filter-panel"
        id="dashboardFilterForm"
    >
        <div class="dashboard-filter-heading">
            <div>
                <span>
                    Analytics filters {!! $dashboardHelp('analytics_filters') !!}
                </span>

                <strong>
                    View performance by period and brand
                </strong>
            </div>

            <a href="{{ route('admin.dashboard') }}">
                Reset filters
            </a>
        </div>

        <div class="dashboard-filter-grid">
            <label class="dashboard-filter-field">
                <span>Brand</span>

                <select name="brand_id">
                    <option value="">
                        All Brands
                    </option>

                    @foreach ($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected(
                                (int) (
                                    $filters['brand_id']
                                    ?? 0
                                )
                                === $brand->id
                            )
                        >
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="dashboard-filter-field">
                <span>Trend period</span>

                <select
                    name="period"
                    id="dashboardPeriod"
                >
                    <option
                        value="daily"
                        @selected(
                            $filters['period']
                            === 'daily'
                        )
                    >
                        Daily — Last 14 days
                    </option>

                    <option
                        value="weekly"
                        @selected(
                            $filters['period']
                            === 'weekly'
                        )
                    >
                        Weekly — Last 12 weeks
                    </option>

                    <option
                        value="monthly"
                        @selected(
                            $filters['period']
                            === 'monthly'
                        )
                    >
                        Monthly — Last 12 months
                    </option>

                    <option
                        value="yearly"
                        @selected(
                            $filters['period']
                            === 'yearly'
                        )
                    >
                        Yearly — Last 5 years
                    </option>

                    <option
                        value="custom"
                        @selected(
                            $filters['period']
                            === 'custom'
                        )
                    >
                        Custom date range
                    </option>
                </select>
            </label>

            <label
                class="dashboard-filter-field dashboard-custom-date"
            >
                <span>Start date</span>

                <input
                    type="date"
                    name="start_date"
                    value="{{ $filters['start_date'] }}"
                >
            </label>

            <label
                class="dashboard-filter-field dashboard-custom-date"
            >
                <span>End date</span>

                <input
                    type="date"
                    name="end_date"
                    value="{{ $filters['end_date'] }}"
                    max="{{ now()->format('Y-m-d') }}"
                >
            </label>

            <button
                type="submit"
                class="dashboard-filter-submit"
            >
                <i class="fa-solid fa-filter"></i>
                Apply filters
            </button>
        </div>
    </form>

    <section class="dashboard-kpi-grid dashboard-pro-kpis">
        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-wallet"></i>
            </div>

            <span>Revenue</span>

            <strong>
                ৳{{ number_format(
                    $dashboard['range_revenue'],
                    2
                ) }}
            </strong>

            <small>
                {{ $periodLabel }}
            </small>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>

            <span>Orders</span>

            <strong>
                {{ number_format(
                    $dashboard['range_orders']
                ) }}
            </strong>

            <small>
                Cancelled orders excluded
            </small>
        </article>

        <article class="dashboard-items-card" tabindex="0">
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>

            <span>Items Sold</span>

            <strong>
                {{ number_format(
                    $dashboard['range_items']
                ) }}
            </strong>

            <small>
                Total sold quantity
            </small>

            <button
                type="button"
                class="dashboard-kpi-more"
                aria-label="Show sold items"
            >
                <i class="fa-solid fa-chevron-down"></i>
            </button>

            <div class="dashboard-sold-items-popover">
                <div class="dashboard-sold-items-head">
                    <strong>Sold Items</strong>
                    <span>Top selling first</span>
                </div>

                <div class="dashboard-sold-items-list">
                    @forelse (($dashboard['sold_items'] ?? collect()) as $index => $item)
                        <div class="{{ $index === 0 ? 'top' : '' }}">
                            <span>
                                {{ $index + 1 }}
                            </span>

                            <div>
                                <strong>{{ $item['name'] }}</strong>
                                <small>
                                    {{ $item['code'] ?: 'No code' }}
                                    · {{ number_format($item['orders']) }} orders
                                </small>
                            </div>

                            <b>
                                {{ number_format($item['quantity']) }}
                                <small>sold</small>
                            </b>
                        </div>
                    @empty
                        <p>No sold items in this period.</p>
                    @endforelse
                </div>
            </div>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <span>Customers</span>

            <strong>
                {{ number_format(
                    $dashboard['range_customers']
                ) }}
            </strong>

            <small>
                Unique phone numbers
            </small>
        </article>

        <article class="dashboard-customer-mix-card">
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-user-plus"></i>
            </div>

            <span>Customer Mix {!! $dashboardHelp('customer_mix') !!}</span>

            <div class="dashboard-customer-mix">
                <div>
                    <strong>
                        {{ number_format(
                            $dashboard['new_customers']
                        ) }}
                    </strong>
                    <small>New</small>
                </div>

                <div>
                    <strong>
                        {{ number_format(
                            $dashboard['repeat_customers']
                        ) }}
                    </strong>
                    <small>Repeat</small>
                </div>
            </div>

            <small>
                First-time and returning buyers
            </small>
        </article>

        <article>
            <div class="dashboard-kpi-icon">
                <i class="fa-solid fa-chart-simple"></i>
            </div>

            <span>Average Order</span>

            <strong>
                ৳{{ number_format(
                    $dashboard[
                        'average_order_value'
                    ],
                    2
                ) }}
            </strong>

            <small>
                Average value per order
            </small>
        </article>
    </section>

    <section class="dashboard-chart-card dashboard-performance-card">
        <div class="dashboard-performance-head">
            <div>
                <span class="dashboard-section-eyebrow">
                    Combined performance
                </span>

                <h3>
                    Business Trend {!! $dashboardHelp('business_trend') !!}
                </h3>

                <p>
                    {{ $periodLabel }} ·
                    {{ $brandMetrics->count() }}
                    {{ $brandMetrics->count() === 1
                        ? 'brand'
                        : 'brands'
                    }}
                </p>
            </div>

            <div class="dashboard-metric-switch">
                <button
                    type="button"
                    class="active"
                    data-dashboard-metric="revenue"
                >
                    Revenue
                </button>

                <button
                    type="button"
                    data-dashboard-metric="orders"
                >
                    Orders
                </button>
            </div>
        </div>

        <div class="dashboard-chart-summary">
            <div>
                <span>Total Revenue</span>

                <strong>
                    ৳{{ number_format(
                        $rangeRevenue,
                        2
                    ) }}
                </strong>
            </div>

            <div>
                <span>Total Orders</span>

                <strong>
                    {{ number_format(
                        $rangeOrders
                    ) }}
                </strong>
            </div>

            <div>
                <span>Best Revenue Point</span>

                <strong>
                    {{ $formatShort(
                        $highlightRevenue
                    ) }}
                </strong>
            </div>

            <div>
                <span>Best Sales Point</span>

                <strong>
                    {{ number_format(
                        $highlightOrders
                    ) }}
                    orders
                </strong>
            </div>
        </div>

        <div class="dashboard-brand-metrics">
            @foreach ($brandMetrics as $metric)
                <article
                    style="--series-color: {{ $metric['color'] }}"
                >
                    <div class="dashboard-brand-name">
                        <span></span>

                        <strong>
                            {{ $metric['name'] }}
                        </strong>
                    </div>

                    <div>
                        <b>
                            ৳{{ number_format(
                                $metric['revenue'],
                                2
                            ) }}
                        </b>

                        <small>
                            {{ number_format(
                                $metric['orders']
                            ) }}
                            orders ·

                            {{ number_format(
                                $metric['items']
                            ) }}
                            items
                        </small>
                    </div>
                </article>
            @endforeach
        </div>

        <div
            class="dashboard-chart-panel active"
            data-chart-panel="revenue"
        >
            <div class="dashboard-chart-wrap dashboard-pro-chart">
                <svg
                    viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                    role="img"
                    aria-label="Revenue trend by brand"
                >
                    @for ($line = 0; $line < 6; $line++)
                        @php
                            $y =
                                $paddingTop
                                + (
                                    $line
                                    * (
                                        $plotHeight
                                        / 5
                                    )
                                );

                            $tick =
                                $revenueMax
                                - (
                                    $revenueMax
                                    / 5
                                    * $line
                                );
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
                            {{ $formatShort($tick) }}
                        </text>
                    @endfor

                    @foreach ($chartLabels as $index => $label)
                        @php
                            $x =
                                $paddingLeft
                                + (
                                    $index
                                    * (
                                        $plotWidth
                                        /
                                        max(
                                            $chartLabels->count()
                                            - 1,
                                            1
                                        )
                                    )
                                );
                        @endphp

                        <line
                            class="dashboard-vertical-grid"
                            x1="{{ $x }}"
                            y1="{{ $paddingTop }}"
                            x2="{{ $x }}"
                            y2="{{ $paddingTop + $plotHeight }}"
                        />
                    @endforeach

                    @foreach ($brandMetrics as $metric)
                        @php
                            $coords = $coordsFor(
                                $metric[
                                    'revenue_series'
                                ],
                                $revenueMax
                            );
                        @endphp

                        <polyline
                            pathLength="100"
                            points="{{ $coords
                                ->map(
                                    fn ($point) =>
                                        $point['x']
                                        .','
                                        .$point['y']
                                )
                                ->implode(' ')
                            }}"
                            style="--series-color: {{ $metric['color'] }}"
                        />

                        @foreach ($coords as $point)
                            @if ($point['value'] > 0)
                                @php
                                    $tooltipWidth = 148;
                                    $tooltipHeight = 68;

                                    $spaceRight =
                                        $chartWidth
                                        - $point['x'];

                                    $tooltipX =
                                        $spaceRight
                                        > (
                                            $tooltipWidth
                                            + 30
                                        )
                                            ? $point['x'] + 16
                                            : $point['x']
                                                - $tooltipWidth
                                                - 16;

                                    $tooltipX = min(
                                        max(
                                            $tooltipX,
                                            12
                                        ),
                                        $chartWidth
                                        - $tooltipWidth
                                        - 12
                                    );

                                    $tooltipY = max(
                                        $point['y']
                                        - $tooltipHeight
                                        - 14,
                                        10
                                    );
                                @endphp

                                <g
                                    class="dashboard-point-group"
                                    tabindex="0"
                                    style="--series-color: {{ $metric['color'] }}"
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

                                        <text
                                            x="13"
                                            y="20"
                                            class="dashboard-tooltip-date"
                                        >
                                            {{ $metric['name'] }}
                                        </text>

                                        <text
                                            x="13"
                                            y="40"
                                            class="dashboard-tooltip-muted"
                                        >
                                            {{ $point['label'] }}
                                        </text>

                                        <text
                                            x="13"
                                            y="59"
                                            class="dashboard-tooltip-value"
                                        >
                                            ৳{{ number_format(
                                                $point['value'],
                                                2
                                            ) }}
                                        </text>
                                    </g>
                                </g>
                            @endif
                        @endforeach
                    @endforeach
                </svg>
            </div>
        </div>

        <div
            class="dashboard-chart-panel"
            data-chart-panel="orders"
            hidden
        >
            <div class="dashboard-chart-wrap dashboard-pro-chart">
                <svg
                    viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}"
                    role="img"
                    aria-label="Sales trend by brand"
                >
                    @for ($line = 0; $line < 6; $line++)
                        @php
                            $y =
                                $paddingTop
                                + (
                                    $line
                                    * (
                                        $plotHeight
                                        / 5
                                    )
                                );

                            $tick =
                                $ordersMax
                                - (
                                    $ordersMax
                                    / 5
                                    * $line
                                );
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
                            {{ $formatShort(
                                $tick,
                                false
                            ) }}
                        </text>
                    @endfor

                    @foreach ($brandMetrics as $metric)
                        @php
                            $coords = $coordsFor(
                                $metric[
                                    'orders_series'
                                ],
                                $ordersMax
                            );
                        @endphp

                        <polyline
                            pathLength="100"
                            points="{{ $coords
                                ->map(
                                    fn ($point) =>
                                        $point['x']
                                        .','
                                        .$point['y']
                                )
                                ->implode(' ')
                            }}"
                            style="--series-color: {{ $metric['color'] }}"
                        />

                        @foreach ($coords as $point)
                            @if ($point['value'] > 0)
                                @php
                                    $tooltipWidth = 142;
                                    $tooltipHeight = 68;

                                    $tooltipX =
                                        (
                                            $chartWidth
                                            - $point['x']
                                        )
                                        > (
                                            $tooltipWidth
                                            + 30
                                        )
                                            ? $point['x'] + 16
                                            : $point['x']
                                                - $tooltipWidth
                                                - 16;

                                    $tooltipX = min(
                                        max(
                                            $tooltipX,
                                            12
                                        ),
                                        $chartWidth
                                        - $tooltipWidth
                                        - 12
                                    );

                                    $tooltipY = max(
                                        $point['y']
                                        - $tooltipHeight
                                        - 14,
                                        10
                                    );
                                @endphp

                                <g
                                    class="dashboard-point-group"
                                    tabindex="0"
                                    style="--series-color: {{ $metric['color'] }}"
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

                                        <text
                                            x="13"
                                            y="20"
                                            class="dashboard-tooltip-date"
                                        >
                                            {{ $metric['name'] }}
                                        </text>

                                        <text
                                            x="13"
                                            y="40"
                                            class="dashboard-tooltip-muted"
                                        >
                                            {{ $point['label'] }}
                                        </text>

                                        <text
                                            x="13"
                                            y="59"
                                            class="dashboard-tooltip-value"
                                        >
                                            {{ number_format(
                                                $point['value']
                                            ) }}
                                            orders
                                        </text>
                                    </g>
                                </g>
                            @endif
                        @endforeach
                    @endforeach
                </svg>
            </div>
        </div>

        <div class="dashboard-chart-labels">
            @foreach ($chartLabels as $index => $label)
                @if (
                    $index === 0
                    ||
                    $index
                    === $chartLabels->count() - 1
                    ||
                    $index
                    % max(
                        (int) ceil(
                            $chartLabels->count()
                            / 5
                        ),
                        1
                    )
                    === 0
                )
                    <span>
                        {{ $label }}
                    </span>
                @endif
            @endforeach
        </div>
    </section>

    <section class="dashboard-brand-card-grid">
        @foreach ($brandMetrics as $metric)
            <a
                href="{{ route(
                    'admin.orders.index',
                    [
                        'brand_id' =>
                            $metric['id'],
                    ]
                ) }}"
                class="dashboard-brand-card"
                style="--series-color: {{ $metric['color'] }}"
            >
                <div class="dashboard-brand-card-head">
                    <span></span>

                    <div>
                        <strong>
                            {{ $metric['name'] }}
                        </strong>

                        <small>
                            {{ $periodLabel }}
                        </small>
                    </div>
                </div>

                <b>
                    ৳{{ number_format(
                        $metric['revenue'],
                        2
                    ) }}
                </b>

                <div class="dashboard-brand-card-stats">
                    <span>
                        <strong>
                            {{ number_format(
                                $metric['orders']
                            ) }}
                        </strong>
                        Orders
                    </span>

                    <span>
                        <strong>
                            {{ number_format(
                                $metric['items']
                            ) }}
                        </strong>
                        Items
                    </span>

                    <span>
                        <strong>
                            ৳{{ number_format(
                                $metric[
                                    'average_order'
                                ],
                                0
                            ) }}
                        </strong>
                        Avg. order
                    </span>

                    <span>
                        <strong>
                            {{ number_format(
                                $metric['new_customers']
                            ) }}
                        </strong>
                        New
                    </span>

                    <span>
                        <strong>
                            {{ number_format(
                                $metric['repeat_customers']
                            ) }}
                        </strong>
                        Repeat
                    </span>
                </div>
            </a>
        @endforeach
    </section>

    <section class="dashboard-bottom-grid">
        <article class="dashboard-panel">
            <div class="dashboard-card-head">
                <div>
                    <h3>
                        Recent Orders
                    </h3>

                    <p>
                        Latest orders from the selected period.
                    </p>
                </div>

                <a href="{{ route('admin.orders.index') }}">
                    View all
                </a>
            </div>

            <div class="dashboard-order-list">
                @forelse ($recentOrders as $order)
                    <a
                        href="{{ route(
                            'admin.orders.index',
                            [
                                'search' =>
                                    $order
                                        ->invoice_number,
                            ]
                        ) }}"
                    >
                        <div>
                            <strong>
                                {{ $order->invoice_number }}
                            </strong>

                            <small>
                                {{ $order->customer_name }}
                                ·
                                {{ $order->brand?->name }}
                            </small>
                        </div>

                        <span>
                            ৳{{ number_format(
                                $order->grand_total,
                                2
                            ) }}
                        </span>
                    </a>
                @empty
                    <div class="dashboard-empty">
                        No orders found for this period.
                    </div>
                @endforelse
            </div>
        </article>

        <article class="dashboard-panel dashboard-health-panel">
            <div class="dashboard-card-head">
                <div>
                    <h3>
                        Needs Attention {!! $dashboardHelp('needs_attention') !!}
                    </h3>

                    <p>
                        Important operational signals.
                    </p>
                </div>
            </div>

            <div class="dashboard-health-list">
                <a
                    href="{{ route(
                        'admin.products.index',
                        [
                            'stock' => 'low',
                        ]
                    ) }}"
                >
                    <i class="fa-solid fa-box-open"></i>

                    <div>
                        <strong>
                            {{ number_format(
                                $dashboard[
                                    'low_stock'
                                ]
                            ) }}
                        </strong>

                        <span>
                            Low stock products
                        </span>
                    </div>
                </a>

                <a
                    href="{{ route(
                        'admin.notifications.index'
                    ) }}"
                >
                    <i class="fa-solid fa-bell"></i>

                    <div>
                        <strong>
                            {{ number_format(
                                $dashboard[
                                    'unread_notifications'
                                ]
                            ) }}
                        </strong>

                        <span>
                            Unread notifications
                        </span>
                    </div>
                </a>
            </div>
        </article>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                document
                    .querySelectorAll('[data-investment-editable-help]')
                    .forEach(function (box) {
                        const key = box.dataset.investmentEditableHelp;
                        const copy = box.querySelector('[data-investment-help-copy]');
                        const editButton = box.querySelector('.investment-help-edit-button');
                        const saved = window.localStorage.getItem(key);

                        if (saved && copy) {
                            copy.textContent = saved;
                        }

                        editButton?.addEventListener('click', function () {
                            copy?.setAttribute('contenteditable', 'true');
                            copy?.focus();
                        });

                        copy?.addEventListener('blur', function () {
                            copy.removeAttribute('contenteditable');
                            window.localStorage.setItem(key, copy.textContent.trim());
                        });

                        copy?.addEventListener('keydown', function (event) {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                copy.blur();
                            }
                        });
                    });

                const metricButtons =
                    document.querySelectorAll(
                        '[data-dashboard-metric]'
                    );

                const chartPanels =
                    document.querySelectorAll(
                        '[data-chart-panel]'
                    );

                metricButtons.forEach(
                    function (button) {
                        button.addEventListener(
                            'click',
                            function () {
                                const metric =
                                    button.dataset
                                        .dashboardMetric;

                                metricButtons.forEach(
                                    function (item) {
                                        item.classList.toggle(
                                            'active',
                                            item === button
                                        );
                                    }
                                );

                                chartPanels.forEach(
                                    function (panel) {
                                        const active =
                                            panel.dataset
                                                .chartPanel
                                            === metric;

                                        panel.hidden =
                                            !active;

                                        panel.classList.toggle(
                                            'active',
                                            active
                                        );
                                    }
                                );
                            }
                        );
                    }
                );

                const periodSelect =
                    document.getElementById(
                        'dashboardPeriod'
                    );

                const customDateFields =
                    document.querySelectorAll(
                        '.dashboard-custom-date'
                    );

                function syncCustomDates() {
                    const isCustom =
                        periodSelect?.value
                        === 'custom';

                    customDateFields.forEach(
                        function (field) {
                            field.classList.toggle(
                                'active',
                                isCustom
                            );

                            field
                                .querySelector('input')
                                ?.toggleAttribute(
                                    'disabled',
                                    !isCustom
                                );
                        }
                    );
                }

                periodSelect?.addEventListener(
                    'change',
                    syncCustomDates
                );

                syncCustomDates();

                const soldItemsCard =
                    document.querySelector(
                        '.dashboard-items-card'
                    );

                soldItemsCard?.addEventListener(
                    'click',
                    function (event) {
                        if (
                            event.target.closest(
                                '.dashboard-sold-items-popover'
                            )
                        ) {
                            return;
                        }

                        event.stopPropagation();
                        soldItemsCard.classList.toggle('open');
                    }
                );

                document.addEventListener('click', function (event) {
                    if (
                        soldItemsCard
                        && ! soldItemsCard.contains(event.target)
                    ) {
                        soldItemsCard.classList.remove('open');
                    }
                });
            }
        );
    </script>
@endpush
