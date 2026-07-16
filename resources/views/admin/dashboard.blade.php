@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('page-title', 'Dashboard Overview')

@push('styles')
<style>
    .welcome-card {
        margin-bottom: 25px;
        padding: 25px;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
    }

    .welcome-card h2 {
        margin-top: 0;
    }

    .brand-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .brand-card {
        padding: 24px;
        background: #ffffff;
        border-top: 5px solid var(--brand-color);
        border-radius: 14px;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
    }

    .brand-card h3 {
        margin-top: 0;
    }

    .brand-status {
        display: inline-block;
        padding: 6px 10px;
        font-size: 13px;
        color: #166534;
        background: #dcfce7;
        border-radius: 50px;
    }

    .view-store {
        display: inline-block;
        margin-top: 18px;
        color: var(--brand-color);
        font-weight: 600;
        text-decoration: none;
    }

    @media (max-width: 900px) {
        .brand-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <section class="welcome-card">
        <h2>Welcome, {{ auth()->user()->name }}</h2>

        <p>
            Manage Grey Stone, Blue Shades and Pink Touch from one dashboard.
        </p>
    </section>

    <section class="brand-grid">
        @foreach ($brands as $brand)
            <article
                class="brand-card"
                style="--brand-color: {{ $brand->primary_color }}"
            >
                <h3>{{ $brand->name }}</h3>

                <span class="brand-status">
                    Active
                </span>

                <br>

                <a
                    href="{{ route('brand.show', $brand->slug) }}"
                    target="_blank"
                    class="view-store"
                >
                    View Store
                </a>
            </article>
        @endforeach
    </section>
@endsection