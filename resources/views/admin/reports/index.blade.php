@extends('admin.layouts.app')

@section('title', 'Reports | Grey Stone Admin')
@section('page-title', 'Reports')
@section('page-subtitle', 'Daily, weekly and monthly business reports')

@section('content')
<section class="brand-page-card report-page-card">
    <div class="brand-page-header report-page-header">
        <div>
            <h2>
                {{ $filters['report_type'] === 'overview'
                    ? 'Report Center'
                    : ucwords(str_replace('_', ' ', $filters['report_type'])).' Details' }}
            </h2>

            <p>
                {{ $filters['report_type'] === 'overview'
                    ? 'Choose a report box to open detailed insights.'
                    : 'Review detailed report data for '.$periodLabel.'.' }}
            </p>
        </div>

        @if ($filters['report_type'] !== 'overview')
            <a
                href="{{ $exportUrl }}"
                target="_blank"
                class="brand-primary-button"
            >
                <i class="fa-solid fa-file-pdf"></i>
                A4 PDF Export
            </a>
        @endif
    </div>

    <form
        class="report-filter-form"
        action="{{ route('admin.reports.index') }}"
    >
        <input
            type="hidden"
            name="report_type"
            value="{{ $filters['report_type'] }}"
        >

        <div class="report-filter-panel">
            <div class="report-filter-title">
                <span>
                    <i class="fa-solid fa-filter"></i>
                </span>

                <div>
                    <strong>Filters @include('admin.partials.info-help', ['key' => 'reports_filters', 'text' => 'Period, date, brand and status select kore report generate kora jay.'])</strong>
                    <small>{{ $periodLabel }}</small>
                </div>
            </div>

            <div class="admin-search-field report-period-field">
                <label>Period @include('admin.partials.info-help', ['key' => 'reports_period', 'text' => 'Daily, weekly, monthly ba custom date range select korun.'])</label>
                <select name="period" id="reportPeriodSelect">
                    @foreach ([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'custom' => 'Custom',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['period'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-search-field" data-report-date-field>
                <label>Date</label>
                <input
                    type="date"
                    name="date"
                    value="{{ $filters['date'] }}"
                >
            </div>

            <div class="admin-search-field" data-report-custom-field>
                <label>Start Date</label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $filters['start_date'] }}"
                >
            </div>

            <div class="admin-search-field" data-report-custom-field>
                <label>End Date</label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $filters['end_date'] }}"
                >
            </div>

            <div class="admin-search-field">
                <label>Brand</label>
                <select name="brand_id">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected((string) $filters['brand_id'] === (string) $brand->id)
                        >
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-search-field">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    @foreach (App\Models\Order::adminStatuses() as $status)
                        <option
                            value="{{ $status }}"
                            @selected($filters['status'] === $status)
                        >
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="report-filter-actions">
                <button type="submit" class="brand-primary-button">
                    <i class="fa-solid fa-rotate"></i>
                    Generate
                </button>
            </div>
        </div>
    </form>

    @if ($filters['report_type'] === 'overview')
        <div class="report-box-grid">
            <a
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'business_accounting']) }}"
                class="report-box"
            >
                <span><i class="fa-solid fa-calculator"></i></span>
                <div>
                    <h3>Business Accounting @include('admin.partials.info-help', ['key' => 'report_box_business_accounting', 'text' => 'Order income, ledger cost, VAT/tax and investor payout ekshathe net profit hishab.'])</h3>
                    <p>Income, cost, VAT, tax and payout combined.</p>
                </div>
                <strong>৳{{ number_format($summary['net_profit'], 2) }}</strong>
                <small>Income ৳{{ number_format($summary['revenue'], 2) }} · Cost ৳{{ number_format($summary['business_costs'], 2) }}</small>
                <em>Open Details</em>
            </a>

            <a
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'revenue']) }}"
                class="report-box"
            >
                <span><i class="fa-solid fa-sack-dollar"></i></span>
                <div>
                    <h3>Revenue Report @include('admin.partials.info-help', ['key' => 'report_box_revenue', 'text' => 'Income, order count and revenue summary details.'])</h3>
                    <p>Daily, weekly or monthly income summary.</p>
                </div>
                <strong>৳{{ number_format($summary['revenue'], 2) }}</strong>
                <em>Open Details</em>
            </a>

            <a
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'customers']) }}"
                class="report-box"
            >
                <span><i class="fa-solid fa-users"></i></span>
                <div>
                    <h3>Customer Report @include('admin.partials.info-help', ['key' => 'report_box_customer', 'text' => 'New/repeat customers and spending history.'])</h3>
                    <p>New customers, repeat customers and spending history.</p>
                </div>
                <strong>{{ number_format($summary['customers']) }}</strong>
                <small>
                    {{ number_format($summary['new_customers']) }} new ·
                    {{ number_format($summary['repeat_customers']) }} repeat
                </small>
                <em>Open Details</em>
            </a>

            <a
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'products']) }}"
                class="report-box"
            >
                <span><i class="fa-solid fa-box-open"></i></span>
                <div>
                    <h3>Product Report @include('admin.partials.info-help', ['key' => 'report_box_product', 'text' => 'Product wise sold quantity and revenue performance.'])</h3>
                    <p>Best selling products and quantity sold.</p>
                </div>
                <strong>{{ number_format($summary['products_sold']) }}</strong>
                <em>Open Details</em>
            </a>

            <a
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'orders']) }}"
                class="report-box"
            >
                <span><i class="fa-solid fa-receipt"></i></span>
                <div>
                    <h3>Order Report @include('admin.partials.info-help', ['key' => 'report_box_order', 'text' => 'Invoice, order status and customer order list.'])</h3>
                    <p>Invoice, status and customer order list.</p>
                </div>
                <strong>{{ number_format($summary['orders']) }}</strong>
                <em>Open Details</em>
            </a>
        </div>
    @else
        <div class="report-detail-toolbar">
            <a
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'overview']) }}"
                class="brand-secondary-button"
            >
                <i class="fa-solid fa-arrow-left"></i>
                All Reports
            </a>
        </div>

        <div class="report-stat-grid">
            <article>
                <span>Total Orders</span>
                <strong>{{ number_format($summary['orders']) }}</strong>
            </article>

            <article>
                <span>Revenue</span>
                <strong>৳{{ number_format($summary['revenue'], 2) }}</strong>
            </article>

            <article>
                <span>Customers</span>
                <strong>{{ number_format($summary['customers']) }}</strong>
            </article>

            <article>
                <span>New Customers</span>
                <strong>{{ number_format($summary['new_customers']) }}</strong>
            </article>

            <article>
                <span>Repeat Customers</span>
                <strong>{{ number_format($summary['repeat_customers']) }}</strong>
            </article>

            <article>
                <span>Products Sold</span>
                <strong>{{ number_format($summary['products_sold']) }}</strong>
            </article>
        </div>

        @if ($filters['report_type'] === 'business_accounting')
            <div class="investment-report-summary">
                <article><span>Total Income</span><strong>৳{{ number_format($summary['revenue'], 2) }}</strong><small>Online ৳{{ number_format($summary['online_income'], 2) }} · Offline ৳{{ number_format($summary['offline_income'], 2) }}</small></article>
                <article><span>Business Costs</span><strong>৳{{ number_format($summary['business_costs'], 2) }}</strong><small>Online ৳{{ number_format($summary['online_cost'], 2) }} · Offline ৳{{ number_format($summary['offline_cost'], 2) }}</small></article>
                <article><span>VAT Collected</span><strong>৳{{ number_format($summary['vat_collected'], 2) }}</strong><small>Saved from order totals</small></article>
                <article><span>Tax Reserve</span><strong>৳{{ number_format($summary['tax_reserve'], 2) }}</strong><small>Tax paid ৳{{ number_format($summary['tax_paid'], 2) }}</small></article>
                <article><span>Investor Payout</span><strong>৳{{ number_format($summary['investor_payout'], 2) }}</strong><small>Profit payout ledger</small></article>
                <article><span>Net Profit</span><strong>৳{{ number_format($summary['net_profit'], 2) }}</strong><small>Income - cost - tax paid - investor payout</small></article>
            </div>
        @endif

        <div class="report-sections">
            @include('admin.reports.partials.revenue-table')
            @include('admin.reports.partials.customer-table')
            @include('admin.reports.partials.product-table')
            @include('admin.reports.partials.order-table')
        </div>
    @endif
</section>
@endsection
