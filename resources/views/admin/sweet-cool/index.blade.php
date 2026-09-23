@extends('admin.layouts.app')

@section('title', 'Sweet Cool | Grey Stone Admin')
@section('page-title', 'Sweet Cool')
@section('page-subtitle', 'Manage the standalone Sweet Cool page, inquiry flow, and visit bookings')

@push('styles')
<style>
    .sweet-admin-layout {
        display: grid;
        gap: 22px;
    }

    .sweet-admin-card {
        padding: 24px;
        background: #ffffff;
        border: 1px solid var(--admin-border);
        border-radius: 24px;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
    }

    .sweet-admin-card h2,
    .sweet-admin-card h3 {
        margin: 0;
    }

    .sweet-admin-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: start;
        margin-bottom: 20px;
    }

    .sweet-admin-head p {
        margin: 8px 0 0;
        color: #64748b;
    }

    .sweet-admin-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .sweet-admin-field,
    .sweet-admin-field label {
        display: grid;
        gap: 8px;
    }

    .sweet-admin-field.full {
        grid-column: 1 / -1;
    }

    .sweet-admin-field span {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
    }

    .sweet-admin-field input,
    .sweet-admin-field textarea,
    .sweet-admin-field select {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border: 1px solid #d6dfeb;
        border-radius: 14px;
        outline: none;
        font: inherit;
    }

    .sweet-admin-field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .sweet-admin-help {
        margin-top: -2px;
        color: #64748b;
        font-size: 12px;
    }

    .sweet-admin-badge-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .sweet-admin-image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .sweet-admin-image-tile {
        display: grid;
        gap: 8px;
    }

    .sweet-admin-image-tile img {
        width: 100%;
        height: 104px;
        object-fit: cover;
        border-radius: 16px;
        border: 1px solid #dbe3ec;
    }

    .sweet-admin-remove {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 12px;
    }

    .sweet-admin-role-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .sweet-admin-role-chip {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 10px;
        background: #eff6ff;
        border-radius: 999px;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

    .sweet-admin-alert {
        margin-bottom: 16px;
        padding: 14px 16px;
        border-radius: 16px;
        font-weight: 700;
    }

    .sweet-admin-alert.success {
        color: #166534;
        background: #dcfce7;
    }

    .sweet-admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sweet-admin-table th,
    .sweet-admin-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
        text-align: left;
    }

    .sweet-admin-table th {
        color: #475569;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .sweet-admin-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    @media (max-width: 900px) {
        .sweet-admin-grid,
        .sweet-admin-badge-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="sweet-admin-layout">
    <section class="sweet-admin-card">
        <div class="sweet-admin-head">
            <div>
                <h2>Sweet Cool Page Content</h2>
                <p>
                    Upload images, change copy, and control what appears on the standalone Sweet Cool page and the teaser card inside each storefront.
                </p>
            </div>
        </div>

        @if (session('sweet_cool_admin_success'))
            <div class="sweet-admin-alert success">
                {{ session('sweet_cool_admin_success') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.sweet-cool.content.update') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="sweet-admin-grid">
                <div class="sweet-admin-field">
                    <label>
                        <span>Teaser Kicker</span>
                        <input type="text" name="teaser_kicker" value="{{ old('teaser_kicker', $page->teaser_kicker) }}">
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Teaser Button</span>
                        <input type="text" name="teaser_button_text" value="{{ old('teaser_button_text', $page->teaser_button_text) }}">
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Teaser Title</span>
                        <input type="text" name="teaser_title" value="{{ old('teaser_title', $page->teaser_title) }}">
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Teaser Description</span>
                        <textarea name="teaser_description">{{ old('teaser_description', $page->teaser_description) }}</textarea>
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Hero Kicker</span>
                        <input type="text" name="hero_kicker" value="{{ old('hero_kicker', $page->hero_kicker) }}">
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Hero Button</span>
                        <input type="text" name="hero_button_text" value="{{ old('hero_button_text', $page->hero_button_text) }}">
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Hero Title</span>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}">
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Hero Description</span>
                        <textarea name="hero_description">{{ old('hero_description', $page->hero_description) }}</textarea>
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Intro Heading</span>
                        <input type="text" name="intro_heading" value="{{ old('intro_heading', $page->intro_heading) }}">
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Intro Description</span>
                        <textarea name="intro_description">{{ old('intro_description', $page->intro_description) }}</textarea>
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Section One Title</span>
                        <input type="text" name="section_one_title" value="{{ old('section_one_title', $page->section_one_title) }}">
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Section Two Title</span>
                        <input type="text" name="section_two_title" value="{{ old('section_two_title', $page->section_two_title) }}">
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Section One Body</span>
                        <textarea name="section_one_body">{{ old('section_one_body', $page->section_one_body) }}</textarea>
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Section Two Body</span>
                        <textarea name="section_two_body">{{ old('section_two_body', $page->section_two_body) }}</textarea>
                    </label>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Section Three Title</span>
                        <input type="text" name="section_three_title" value="{{ old('section_three_title', $page->section_three_title) }}">
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Section Three Body</span>
                        <textarea name="section_three_body">{{ old('section_three_body', $page->section_three_body) }}</textarea>
                    </label>
                </div>

                <div class="sweet-admin-field full">
                    <span>Promo Badges</span>
                    <div class="sweet-admin-badge-grid">
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                type="text"
                                name="promo_badges[]"
                                value="{{ old('promo_badges.'.$i, $page->promo_badges[$i] ?? '') }}"
                                placeholder="Example: 30% sale"
                            >
                        @endfor
                    </div>
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Logo Image</span>
                        <input type="file" name="logo_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </label>
                    @if ($page->logo_path)
                        <div class="sweet-admin-image-grid">
                            <div class="sweet-admin-image-tile">
                                <img src="{{ Storage::url($page->logo_path) }}" alt="Sweet Cool logo">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="sweet-admin-field">
                    <label>
                        <span>Hero Image</span>
                        <input type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp">
                    </label>
                    @if ($page->hero_image_path)
                        <div class="sweet-admin-image-grid">
                            <div class="sweet-admin-image-tile">
                                <img src="{{ Storage::url($page->hero_image_path) }}" alt="Sweet Cool hero">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Gallery Images @include('admin.partials.info-help', ['key' => 'sweet_cool_gallery_images', 'text' => 'General campaign/mixed gallery visual ekhane upload korun.'])</span>
                        <input type="file" name="gallery_uploads[]" multiple accept=".jpg,.jpeg,.png,.webp">
                    </label>
                    <div class="sweet-admin-help">Upload general campaign or mixed gallery visuals.</div>
                    @if (!empty($page->gallery_images))
                        <div class="sweet-admin-image-grid">
                            @foreach ($page->gallery_images as $image)
                                <div class="sweet-admin-image-tile">
                                    <img src="{{ Storage::url($image) }}" alt="Gallery image">
                                    <label class="sweet-admin-remove">
                                        <input type="checkbox" name="remove_gallery_images[]" value="{{ $image }}">
                                        Remove
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Factory Images @include('admin.partials.info-help', ['key' => 'sweet_cool_factory_images', 'text' => 'Production floor, machinery, team ba facility image ekhane rakhun.'])</span>
                        <input type="file" name="factory_uploads[]" multiple accept=".jpg,.jpeg,.png,.webp">
                    </label>
                    <div class="sweet-admin-help">Upload production floor, machinery, team, or facility visuals.</div>
                    @if (!empty($page->factory_images))
                        <div class="sweet-admin-image-grid">
                            @foreach ($page->factory_images as $image)
                                <div class="sweet-admin-image-tile">
                                    <img src="{{ Storage::url($image) }}" alt="Factory image">
                                    <label class="sweet-admin-remove">
                                        <input type="checkbox" name="remove_factory_images[]" value="{{ $image }}">
                                        Remove
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="sweet-admin-field full">
                    <label>
                        <span>Product Images @include('admin.partials.info-help', ['key' => 'sweet_cool_product_images', 'text' => 'Sweet Cool page-e product reference image show korte ekhane upload korun.'])</span>
                        <input type="file" name="product_uploads[]" multiple accept=".jpg,.jpeg,.png,.webp">
                    </label>
                    <div class="sweet-admin-help">Upload product references that should appear on the Sweet Cool page.</div>
                    @if (!empty($page->product_images))
                        <div class="sweet-admin-image-grid">
                            @foreach ($page->product_images as $image)
                                <div class="sweet-admin-image-tile">
                                    <img src="{{ Storage::url($image) }}" alt="Product image">
                                    <label class="sweet-admin-remove">
                                        <input type="checkbox" name="remove_product_images[]" value="{{ $image }}">
                                        Remove
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="sweet-admin-actions">
                <button type="submit" class="brand-primary-button">
                    Save Sweet Cool Content
                </button>

                <a
                    href="{{ route('sweet-cool.show') }}"
                    target="_blank"
                    class="brand-secondary-button"
                >
                    Preview Page
                </a>
            </div>
        </form>
    </section>

    <section class="sweet-admin-card">
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
                <span>Visit Bookings</span>
                <strong>{{ number_format($stats['bookings']) }}</strong>
            </article>
        </div>
    </section>

    <section class="sweet-admin-card">
        <div class="sweet-admin-head">
            <div>
                <h3>Visit Bookings @include('admin.partials.info-help', ['key' => 'sweet_cool_visit_bookings', 'text' => 'Frontend theke factory visit booking request ekhane list hoy. Same date/time duplicate block thake.'])</h3>
                <p>Booked visit slots will appear here. Duplicate date/time combinations are blocked on the frontend.</p>
            </div>
        </div>

        <div class="brand-table-wrapper">
            <table class="sweet-admin-table">
                <thead>
                    <tr>
                        <th>Visitor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Profile</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                <strong>{{ $booking->customer_name }}</strong>
                                <br>
                                <small>{{ $booking->phone }}</small>
                                @if ($booking->email)
                                    <br>
                                    <small>{{ $booking->email }}</small>
                                @endif
                            </td>
                            <td>{{ $booking->visit_date?->format('d M Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::of($booking->visit_time)->substr(0, 5) }}</td>
                            <td>{{ $booking->contact_reason ?: 'Visit request' }}</td>
                            <td>
                                <div class="sweet-admin-role-list">
                                    @forelse ($booking->role_tags ?? [] as $role)
                                        <span class="sweet-admin-role-chip">
                                            {{ str($role)->replace('-', ' ')->title() }}
                                        </span>
                                    @empty
                                        <span class="sweet-admin-help">Not shared</span>
                                    @endforelse
                                </div>
                            </td>
                            <td style="max-width: 260px;">
                                {{ $booking->message ?: 'No extra note' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No Sweet Cool bookings yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding-top: 18px;">
            {{ $bookings->links() }}
        </div>
    </section>

    <section class="sweet-admin-card">
        <div class="sweet-admin-head">
            <div>
                <h3>Sweet Cool Inquiries</h3>
                <p>Review bulk buying, factory sourcing, and buyer conversations from storefront and Sweet Cool page forms.</p>
            </div>
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
                            <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>
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
            <table class="sweet-admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Requirement</th>
                        <th>Reason</th>
                        <th>Brand / Product</th>
                        <th>Profile</th>
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
                            <td>{{ $inquiry->contact_reason ?: 'Not shared' }}</td>
                            <td>
                                <strong>{{ $inquiry->brand?->name ?? 'Sweet Cool page' }}</strong>
                                <br>
                                <small>{{ $inquiry->product?->name ?? ucfirst($inquiry->source_page) . ' page' }}</small>
                            </td>
                            <td>
                                <div class="sweet-admin-role-list">
                                    @forelse ($inquiry->role_tags ?? [] as $role)
                                        <span class="sweet-admin-role-chip">
                                            {{ str($role)->replace('-', ' ')->title() }}
                                        </span>
                                    @empty
                                        <span class="sweet-admin-help">Not shared</span>
                                    @endforelse
                                </div>
                            </td>
                            <td style="max-width: 300px;">
                                {{ \Illuminate\Support\Str::limit($inquiry->message, 160) }}
                            </td>
                            <td>{{ $inquiry->created_at?->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No Sweet Cool inquiries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding-top: 18px;">
            {{ $inquiries->links() }}
        </div>
    </section>
</div>
@endsection
