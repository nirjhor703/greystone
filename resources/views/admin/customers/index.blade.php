@extends('admin.layouts.app')

@section('title', 'Customers | Grey Stone Admin')
@section('page-title', 'Customer Leads')
@section('page-subtitle', 'Order-based customer list for marketing and follow-up')

@section('content')
<section class="brand-page-card customer-page-card">
    <div class="brand-page-header">
        <div>
            <h2>Customers Table</h2>

            <p>
                Every customer who places an order appears here with order history and contact actions.
            </p>
        </div>
    </div>

    <div class="customer-stat-grid">
        <article>
            <span>Total Customers</span>
            <strong>{{ number_format($stats['total_customers']) }}</strong>
        </article>

        <article>
            <span>Repeat Customers</span>
            <strong>{{ number_format($stats['repeat_customers']) }}</strong>
        </article>

        <article>
            <span>Email Leads</span>
            <strong>{{ number_format($stats['email_leads']) }}</strong>
        </article>

        <article>
            <span>Total Customer Spend</span>
            <strong>৳{{ number_format($stats['total_spent'], 2) }}</strong>
        </article>
    </div>

    <form
        class="admin-ajax-search"
        data-target="#customerTableBody"
        action="{{ route('admin.customers.index') }}"
    >
        <div class="admin-search-grid">
            <div class="admin-search-field">
                <label>Search</label>
                <input
                    type="search"
                    name="search"
                    placeholder="Search name, phone, email, area or brand"
                    autocomplete="off"
                >
            </div>

            <div class="admin-search-field">
                <label>Brand</label>
                <select name="brand_id">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-search-field">
                <label>Order Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    @foreach (App\Models\Order::adminStatuses() as $status)
                        <option value="{{ $status }}">
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="reset" class="brand-secondary-button">
                Reset
            </button>
        </div>
    </form>

    <div class="brand-table-wrapper">
        <table class="brand-table customer-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Brands</th>
                    <th>Location</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Order</th>
                    <th class="brand-actions-heading">Actions</th>
                </tr>
            </thead>

            <tbody id="customerTableBody">
                @include('admin.customers.partials.table-rows')
            </tbody>
        </table>
    </div>
</section>
@endsection
