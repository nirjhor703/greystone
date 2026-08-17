@extends('admin.layouts.app')

@section('title', 'Sweet Cool | Grey Stone Admin')
@section('page-title', 'Sweet Cool')
@section('page-subtitle', 'Factory, bulk, and sourcing inquiries from the storefront')

@section('content')
<section class="brand-page-card customer-page-card">
    <div class="brand-page-header">
        <div>
            <h2>Sweet Cool Inquiries</h2>

            <p>
                Review bulk buying, factory sourcing, and wholesale conversations from all storefront pages.
            </p>
        </div>
    </div>

    <div class="customer-stat-grid">
        <article>
            <span>Total Inquiries</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </article>

        <article>
            <span>Factory Sourcing</span>
            <strong>{{ number_format($stats['factory']) }}</strong>
        </article>

        <article>
            <span>Bulk Orders</span>
            <strong>{{ number_format($stats['bulk']) }}</strong>
        </article>

        <article>
            <span>Last 7 Days</span>
            <strong>{{ number_format($stats['this_week']) }}</strong>
        </article>
    </div>

    <form
        class="admin-ajax-search"
        method="GET"
        action="{{ route('admin.sweet-cool.index') }}"
    >
        <div class="admin-search-grid">
            <div class="admin-search-field">
                <label>Search</label>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search customer, phone, company, product or brand"
                    autocomplete="off"
                >
            </div>

            <div class="admin-search-field">
                <label>Brand</label>
                <select name="brand_id">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected((string) request('brand_id') === (string) $brand->id)
                        >
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="brand-primary-button">
                Filter
            </button>

            <a
                href="{{ route('admin.sweet-cool.index') }}"
                class="brand-secondary-button"
            >
                Reset
            </a>
        </div>
    </form>

    <div class="brand-table-wrapper">
        <table class="brand-table customer-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Requirement</th>
                    <th>Brand / Product</th>
                    <th>Quantity</th>
                    <th>Preferred</th>
                    <th>Message</th>
                    <th>Time</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($inquiries as $inquiry)
                    <tr>
                        <td>
                            <strong>{{ $inquiry->customer_name }}</strong>
                            <br>
                            <small>{{ $inquiry->phone }}</small>
                            @if ($inquiry->email)
                                <br>
                                <small>{{ $inquiry->email }}</small>
                            @endif
                            @if ($inquiry->company_name)
                                <br>
                                <small>{{ $inquiry->company_name }}</small>
                            @endif
                        </td>
                        <td>{{ str($inquiry->interest_type)->replace('-', ' ')->title() }}</td>
                        <td>
                            <strong>{{ $inquiry->brand?->name ?? 'Unknown brand' }}</strong>
                            <br>
                            <small>
                                {{ $inquiry->product?->name ?? ucfirst($inquiry->source_page) . ' page' }}
                            </small>
                        </td>
                        <td>{{ $inquiry->quantity_note ?: 'Not shared' }}</td>
                        <td>{{ $inquiry->preferred_contact ? ucfirst($inquiry->preferred_contact) : 'Phone' }}</td>
                        <td style="max-width: 340px;">
                            {{ \Illuminate\Support\Str::limit($inquiry->message, 140) }}
                        </td>
                        <td>{{ $inquiry->created_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:28px;">
                            No Sweet Cool inquiries yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding-top:18px;">
        {{ $inquiries->links() }}
    </div>
</section>
@endsection
