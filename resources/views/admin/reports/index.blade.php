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
                    <strong>Filters</strong>
                    <small>{{ $periodLabel }}</small>
                </div>
            </div>

            <div class="admin-search-field report-period-field">
                <label>Period</label>
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
                href="{{ route('admin.reports.index', [...request()->query(), 'report_type' => 'revenue']) }}"
                class="report-box"
            >
                <span><i class="fa-solid fa-sack-dollar"></i></span>
                <div>
                    <h3>Revenue Report</h3>
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
                    <h3>Customer Report</h3>
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
                    <h3>Product Report</h3>
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
                    <h3>Order Report</h3>
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

        <div class="report-sections">
            @include('admin.reports.partials.revenue-table')
            @include('admin.reports.partials.customer-table')
            @include('admin.reports.partials.product-table')
            @include('admin.reports.partials.order-table')
        </div>
    @endif
</section>
@endsection
