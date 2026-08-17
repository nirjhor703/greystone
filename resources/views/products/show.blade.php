<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="{{ $product->short_description ?: $product->name }}"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $product->name }} | {{ $brand->name }}
    </title>

    @if ($brand->favicon)
        <link
            rel="icon"
            href="{{ Storage::url($brand->favicon) }}"
        >
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        :root {
            --store-primary: {{ $brand->primary_color ?: '#333333' }};
            --store-secondary: {{ $brand->secondary_color ?: '#777777' }};
            --store-background: {{ $brand->background_color ?: '#ffffff' }};
            --store-button: {{ $brand->button_color ?: '#333333' }};
            --store-text: {{ $brand->text_color ?: '#171717' }};
        }

        body {
            font-family:
                {!! $brand->font_family
                    ? "'".e($brand->font_family)."', sans-serif"
                    : "'Figtree', sans-serif"
                !!};
        }

        .detail-variant-group {
            margin-top: 24px;
        }

        .detail-variant-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
        }

        .detail-variant-heading strong {
            display: block;
            color: #111827;
            font-size: 14px;
            font-weight: 900;
        }

        .detail-variant-heading small {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 11px;
        }

        .detail-variant-heading > span {
            flex: 0 0 auto;
            padding: 6px 10px;
            color: var(--store-primary);
            background: color-mix(
                in srgb,
                var(--store-primary) 8%,
                white
            );
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
        }

        .detail-color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .detail-color-option {
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: flex-start;
            gap: 7px;
            min-width: 64px;
            padding: 0;
            color: #111827;
            background: transparent;
            border: 0;
            cursor: pointer;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .detail-color-option i {
            display: inline-flex;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            background: var(--detail-color, #94a3b8);
            border: 2px solid rgba(255, 255, 255, 0.94);
            border-radius: 999px;
            box-shadow:
                0 0 0 1.5px rgba(15, 23, 42, 0.34),
                0 2px 5px rgba(15, 23, 42, 0.16),
                inset 0 1px 0 rgba(255, 255, 255, 0.22);
        }

        .detail-color-option strong {
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
        }

        .detail-color-option:hover {
            transform: translateY(-1px);
        }

        .detail-color-option.selected i {
            box-shadow:
                0 0 0 2px rgba(255, 255, 255, 0.94),
                0 0 0 5px color-mix(
                    in srgb,
                    var(--store-primary) 45%,
                    transparent
                ),
                0 8px 18px color-mix(
                    in srgb,
                    var(--store-primary) 20%,
                    transparent
                );
        }

        .detail-color-option.selected strong {
            color: var(--store-primary);
        }


        .detail-size-options {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(125px, 1fr)
            );
            gap: 10px;
        }

        .detail-size-card {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            background: #ffffff;
            border: 1px solid #dfe6ee;
            border-radius: 13px;
            transition:
                border-color 0.2s ease,
                background 0.2s ease,
                box-shadow 0.2s ease;
        }

        .detail-size-card.selected {
            background: color-mix(
                in srgb,
                var(--store-primary) 5%,
                white
            );
            border-color: var(--store-primary);
            box-shadow: 0 0 0 3px
                color-mix(
                    in srgb,
                    var(--store-primary) 9%,
                    transparent
                );
        }

        .detail-size-card-copy {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }

        .detail-size-card-copy strong {
            display: block;
            color: #111827;
            font-size: 15px;
            font-weight: 900;
        }

        .detail-size-card-copy span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
        }

        .detail-size-selected {
            display: none;
            padding: 4px 6px;
            color: var(--store-primary);
            background: color-mix(
                in srgb,
                var(--store-primary) 9%,
                white
            );
            border-radius: 999px;
            font-size: 7px;
            font-weight: 900;
        }

        .detail-size-card.selected
        .detail-size-selected {
            display: inline-flex;
        }

        .detail-size-stepper {
            display: grid;
            grid-template-columns: 34px 1fr 34px;
            min-height: 36px;
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #dbe3ec;
            border-radius: 9px;
        }

        .detail-size-stepper button,
        .detail-size-stepper span {
            display: grid;
            place-items: center;
            width: 100%;
            min-height: 36px;
            border: 0;
        }

        .detail-size-stepper button {
            color: #111827;
            background: #f8fafc;
            cursor: pointer;
            font-family: inherit;
            font-size: 17px;
            font-weight: 800;
            transition:
                color 0.2s ease,
                background 0.2s ease;
        }

        .detail-size-stepper button:hover {
            color: #ffffff;
            background: var(--store-primary);
        }

        .detail-size-stepper span {
            color: #111827;
            background: #ffffff;
            border-right: 1px solid #dbe3ec;
            border-left: 1px solid #dbe3ec;
            font-size: 12px;
            font-weight: 900;
        }

        .detail-selection-summary {
            display: none;
            margin-top: 18px;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 13px;
        }

        .detail-selection-summary.visible {
            display: block;
        }

        .detail-selection-summary-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .detail-selection-summary-header strong {
            color: #111827;
            font-size: 12px;
        }

        .detail-selection-summary-header span {
            color: var(--store-primary);
            font-size: 10px;
            font-weight: 900;
        }

        .detail-selection-items {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .detail-selection-item {
            padding: 7px 9px;
            color: #334155;
            background: #ffffff;
            border: 1px solid #dfe6ee;
            border-radius: 8px;
            font-size: 9px;
            font-weight: 800;
        }

        .detail-purchase-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 18px;
        }

        .detail-purchase-summary > div {
            padding: 13px 14px;
            background: #111827;
            border-radius: 11px;
        }

        .detail-purchase-summary span {
            display: block;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 700;
        }

        .detail-purchase-summary strong {
            display: block;
            margin-top: 5px;
            color: #ffffff;
            font-size: 17px;
            font-weight: 900;
        }

        .detail-purchase-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .detail-purchase-actions button {
            min-height: 50px;
            padding: 12px 16px;
            border-radius: 11px;
            cursor: pointer;
            font-family: inherit;
            font-size: 11px;
            font-weight: 900;
            transition:
                transform 0.2s ease,
                opacity 0.2s ease;
        }

        .detail-add-cart-button {
            color: var(--store-primary);
            background: #ffffff;
            border: 1px solid var(--store-primary);
        }

        .detail-buy-now-button {
            color: #ffffff;
            background: var(--store-button);
            border: 1px solid var(--store-button);
        }

        .detail-purchase-actions button:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .detail-purchase-actions button:disabled {
            cursor: not-allowed;
            opacity: 0.48;
        }

        .detail-variant-error {
            display: block;
            min-height: 16px;
            margin-top: 8px;
            color: #dc2626;
            font-size: 10px;
            font-weight: 700;
        }

        .detail-cart-message {
            display: none;
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 9px;
            font-size: 10px;
            font-weight: 800;
        }

        .detail-cart-message.visible {
            display: block;
        }

        .detail-cart-message.success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .detail-cart-message.error {
            color: #b91c1c;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        @media (max-width: 640px) {
            .detail-color-options,
            .detail-size-options {
                grid-template-columns: repeat(
                    2,
                    minmax(0, 1fr)
                );
            }

            .detail-purchase-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
@php
    $storeUser = auth()->user();
    $brandSlug = $brand->slug;
    $isPinkTouch = $brandSlug === 'pink-touch';
    $normalizeVariantHex = static function ($value): ?string {
        $hex = strtoupper(trim((string) ($value ?? '')));

        if ($hex === '') {
            return null;
        }

        if (preg_match('/^#([A-F0-9]{6})$/', $hex)) {
            return $hex;
        }

        if (preg_match('/^([A-F0-9]{6})$/', $hex)) {
            return '#'.$hex;
        }

        return null;
    };

    $brandLogo = $brand->mobile_logo ?: $brand->logo;
    $galleryImages = $product->images;
    $searchProductsCollection = $searchProducts ?? collect();
    $searchMaxPrice = max(
        1000,
        (int) ceil(
            $searchProductsCollection
                ->map(fn ($searchProduct) => (float) ($searchProduct->sale_price ?: $searchProduct->regular_price))
                ->max()
            ?: 1000
        )
    );

    $mainImage = $product->primaryImage
        ?? $galleryImages->first();

    $hasSale = !is_null($product->sale_price)
        && (float) $product->sale_price
            < (float) $product->regular_price;

    $unitPrice = $hasSale
        ? (float) $product->sale_price
        : (float) $product->regular_price;

    $discountPercentage = (
        $hasSale
        && (float) $product->regular_price > 0
    )
        ? round(
            (
                (
                    (float) $product->regular_price
                    - (float) $product->sale_price
                )
                / (float) $product->regular_price
            ) * 100
        )
        : 0;

    $productVariantsByColor = $product->variants
        ->where('status', true)
        ->groupBy(function ($variant) {
            return mb_strtolower(
                trim((string) $variant->color)
            );
        })
        ->map(function ($colorVariants) use (
            $normalizeVariantHex
        ) {
            $firstVariant = $colorVariants->first();

            $availableSizes = collect(
                \App\Models\Product::AVAILABLE_SIZES
            )
                ->map(function (
                    string $size
                ) use ($colorVariants) {
                    $variant = $colorVariants
                        ->firstWhere('size', $size);

                    return [
                        'size' => $size,
                        'stock' => (int) (
                            $variant?->stock_quantity ?? 0
                        ),
                    ];
                })
                ->filter(function (array $item) {
                    return $item['stock'] > 0;
                })
                ->values()
                ->all();

            return [
                'color' => (string) (
                    $firstVariant?->color ?? ''
                ),
                'color_hex' => $normalizeVariantHex(
                    $firstVariant?->color_hex
                ),

                'total_stock' => (int) $colorVariants
                    ->sum('stock_quantity'),

                'sizes' => $availableSizes,
            ];
        })
        ->filter(function (array $group) {
            return $group['total_stock'] > 0
                && count($group['sizes']) > 0;
        })
        ->values()
        ->all();

    $productVariantJson = json_encode(
        $productVariantsByColor,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    );
@endphp

<div class="storefront storefront-{{ $brandSlug }} product-detail-storefront">
    <header class="store-header {{ $isPinkTouch ? 'store-header-light' : 'store-header-dark' }}">
        <div class="store-header-inner">
            <div class="store-header-actions store-header-actions-left">
                <button
                    type="button"
                    class="mobile-menu-button"
                    id="storeMobileMenuButton"
                    aria-label="Open navigation"
                    aria-expanded="false"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <button
                    type="button"
                    class="store-header-icon-button store-wishlist-trigger"
                    data-open-wishlist
                    aria-label="Open wishlist"
                >
                    <i class="fa-regular fa-heart"></i>
                    <span class="store-wishlist-count" hidden>0</span>
                </button>
            </div>

            <a
                href="{{ route('brand.show', $brand->slug) }}"
                class="store-brand-logo"
            >
                @if ($brandLogo)
                    <img
                        src="{{ Storage::url($brandLogo) }}"
                        alt="{{ $brand->name }}"
                        class="store-logo-image"
                    >
                @else
                    <span class="store-logo-fallback">
                        {{ strtoupper(
                            substr($brand->name, 0, 2)
                        ) }}
                    </span>
                @endif

                <span class="store-brand-copy" aria-hidden="true"></span>
            </a>

            <div class="store-header-actions store-header-actions-right">
                <button
                    type="button"
                    class="store-header-icon-button"
                    id="storeSearchButton"
                    aria-label="Search products"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <button
                    type="button"
                    class="store-header-icon-button store-header-cart-button"
                    id="storeHeaderCartButton"
                    aria-label="Open cart"
                >
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span id="storeHeaderCartCount">0</span>
                </button>
            </div>

            <nav
                class="store-nav"
                id="storeNavigation"
            >
                <div class="store-nav-panel">
                    <div class="store-nav-top">
                        <div class="store-nav-auth">
                            <span class="store-nav-eyebrow">
                                {{ $storeUser ? 'Account' : 'Welcome' }}
                            </span>

                            <strong>
                                {{ $storeUser ? $storeUser->name : 'Welcome to '.$brand->name }}
                            </strong>
                        </div>

                        @if ($storeUser)
                            <div class="store-nav-auth-actions store-nav-auth-actions-single">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="store-nav-auth-button is-primary"
                                >
                                    <i class="fa-regular fa-user"></i>
                                    Account
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('logout') }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="store-nav-auth-button"
                                    >
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="store-nav-auth-actions">
                                <a
                                    href="{{ route('login') }}"
                                    class="store-nav-auth-button is-primary"
                                >
                                    <i class="fa-solid fa-right-to-bracket"></i>
                                    Login
                                </a>

                                <a
                                    href="{{ route('register') }}"
                                    class="store-nav-auth-button"
                                >
                                    <i class="fa-solid fa-user-plus"></i>
                                    Register
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="store-nav-main">
                        <a
                            href="{{ route('brand.show', $brand->slug) }}"
                            class="store-nav-link"
                        >
                            <i class="fa-solid fa-house"></i>
                            <span>Home</span>
                        </a>

                        @if (($featuredProducts ?? collect())->isNotEmpty())
                            <a
                                href="{{ route('brand.show', $brand->slug) }}#featured-products"
                                class="store-nav-link"
                            >
                                <i class="fa-solid fa-star"></i>
                                <span>Featured</span>
                            </a>
                        @endif

                        @if (($newArrivalProducts ?? collect())->isNotEmpty())
                            <a
                                href="{{ route('brand.show', $brand->slug) }}#new-arrivals"
                                class="store-nav-link"
                            >
                                <i class="fa-solid fa-bolt"></i>
                                <span>New Arrivals</span>
                            </a>
                        @endif

                        <a
                            href="{{ route('brand.show', $brand->slug) }}#categories"
                            class="store-nav-link"
                        >
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Categories</span>
                        </a>

                        <details class="store-nav-group">
                            <summary class="store-nav-link store-nav-link-toggle">
                                <span class="store-nav-link-copy">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <span>Products</span>
                                </span>

                                <i class="fa-solid fa-angle-right store-nav-chevron"></i>
                            </summary>

                            <div class="store-nav-group-body">
                                <div class="store-nav-subgroup">
                                    <span class="store-nav-subtitle">Audience</span>

                                    <div class="store-nav-chip-row">
                                        <a
                                            href="{{ route('brand.show', $brand->slug) }}?audience=men#products"
                                            class="store-nav-chip"
                                        >
                                            Men
                                        </a>

                                        <a
                                            href="{{ route('brand.show', $brand->slug) }}?audience=women#products"
                                            class="store-nav-chip"
                                        >
                                            Women
                                        </a>
                                    </div>
                                </div>

                                <div class="store-nav-subgroup">
                                    <span class="store-nav-subtitle">Category</span>

                                    <div class="store-nav-chip-row scrollable">
                                        @foreach ($categories as $category)
                                            <a
                                                href="{{ route('brand.show', $brand->slug) }}?category={{ \Illuminate\Support\Str::slug($category->slug ?: $category->name) }}#products"
                                                class="store-nav-chip"
                                            >
                                                {{ $category->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="store-nav-subgroup">
                                    <span class="store-nav-subtitle">Brands</span>

                                    <div
                                        class="brand-switch-dropdown"
                                        id="brandSwitchDropdown"
                                    >
                                        <button
                                            type="button"
                                            class="brand-switch-button"
                                            id="brandSwitchButton"
                                            aria-expanded="false"
                                        >
                                            <span class="brand-switch-current">
                                                <i class="fa-solid fa-store"></i>
                                                {{ $brand->name }}
                                            </span>

                                            <i class="fa-solid fa-angle-down"></i>
                                        </button>

                                        <div class="brand-switch-menu">
                                            @foreach ($brands as $switchBrand)
                                                <a
                                                    href="{{ route('brand.show', $switchBrand->slug) }}"
                                                    class="brand-switch-option {{ $switchBrand->id === $brand->id ? 'active' : '' }}"
                                                >
                                                    <span>
                                                        {{ $switchBrand->name }}
                                                    </span>

                                                    @if ($switchBrand->id === $brand->id)
                                                        <span>✓</span>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <a
                            href="{{ route('brand.show', $brand->slug) }}#contact"
                            class="store-nav-link"
                        >
                            <i class="fa-regular fa-address-card"></i>
                            <span>Contact</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <div
        class="store-search-overlay"
        id="storeSearchOverlay"
        aria-hidden="true"
    >
        <div class="store-search-panel">
            <div class="store-search-head">
                <div>
                    <span>Search</span>
                    <strong>{{ $brand->name }}</strong>
                </div>

                <button
                    type="button"
                    id="storeSearchClose"
                    aria-label="Close search"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="store-search-query-row">
                <label class="store-search-field">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="storeSearchInput"
                        placeholder="Search pants, men, women, code..."
                        autocomplete="off"
                    >
                </label>

                <button
                    type="button"
                    class="store-search-filter-toggle"
                    id="storeSearchFilterToggle"
                    aria-expanded="false"
                    aria-controls="storeSearchFilterPanel"
                >
                    <i class="fa-solid fa-sliders"></i>
                    Filters
                </button>
            </div>

            <div
                class="store-search-filter-panel"
                id="storeSearchFilterPanel"
                hidden
            >
                <div class="store-search-filter-row">
                    <span>Audience</span>

                    <div
                        class="store-search-chip-group"
                        data-search-filter-group="audience"
                    >
                        <button type="button" class="active" data-search-audience="all">All</button>
                        <button type="button" data-search-audience="men">Men</button>
                        <button type="button" data-search-audience="women">Women</button>
                    </div>
                </div>

                <div class="store-search-filter-row">
                    <span>Category</span>

                    <div
                        class="store-search-chip-group scrollable"
                        data-search-filter-group="category"
                    >
                        <button type="button" class="active" data-search-category="all">All</button>

                        @foreach ($categories as $category)
                            <button
                                type="button"
                                data-search-category="{{ $category->slug }}"
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="store-search-price-card">
                    <div class="store-search-price-head">
                        <span>Price range</span>
                    </div>

                    <div class="store-price-range-visual">
                        <span
                            class="store-price-value-bubble min"
                            id="storeSearchMinBubble"
                        >
                            ৳0
                        </span>

                        <span
                            class="store-price-value-bubble max"
                            id="storeSearchMaxBubble"
                        >
                            ৳{{ number_format($searchMaxPrice, 0) }}
                        </span>

                        <div class="store-price-bars" aria-hidden="true">
                            @for ($bar = 0; $bar < 28; $bar++)
                                <span style="--bar-height: {{ 8 + (($bar * 7) % 31) }}px"></span>
                            @endfor
                        </div>

                        <div
                            class="store-price-range-track"
                            style="--range-left: 0%; --range-right: 100%;"
                        ></div>

                        <input
                            type="range"
                            id="storeSearchMinRange"
                            min="0"
                            max="{{ $searchMaxPrice }}"
                            value="0"
                            step="10"
                            aria-label="Minimum price"
                        >

                        <input
                            type="range"
                            id="storeSearchMaxRange"
                            min="0"
                            max="{{ $searchMaxPrice }}"
                            value="{{ $searchMaxPrice }}"
                            step="10"
                            aria-label="Maximum price"
                        >
                    </div>
                </div>

                <div class="store-search-filter-row">
                    <span>Tags</span>

                    <div
                        class="store-search-chip-group"
                        data-search-filter-group="tag"
                    >
                        <button type="button" class="active" data-search-tag="all">All</button>
                        <button type="button" data-search-tag="sale">Sale</button>
                        <button type="button" data-search-tag="new">New</button>
                        <button type="button" data-search-tag="featured">Featured</button>
                    </div>
                </div>
            </div>

            <div class="store-search-summary">
                <span id="storeSearchCount">
                    {{ $searchProductsCollection->count() }} products
                </span>

                <button
                    type="button"
                    id="storeSearchReset"
                >
                    Reset filters
                </button>
            </div>

            <div class="store-search-results" id="storeSearchResults">
                @foreach ($searchProductsCollection as $searchProduct)
                    @php
                        $searchImage = $searchProduct->primaryImage
                            ?? $searchProduct->images->first();
                        $searchBrand = $searchProduct->brand ?? $brand;
                        $searchPrice = !is_null($searchProduct->sale_price)
                            ? (float) $searchProduct->sale_price
                            : (float) $searchProduct->regular_price;
                        $searchAudience = $searchProduct->audience ?: 'both';
                        $searchCategoryName = $searchProduct->category?->name ?? 'Product';
                        $searchCategorySlug = $searchProduct->category?->slug ?? '';
                        $searchCategoryKey = \Illuminate\Support\Str::slug(
                            $searchCategorySlug ?: $searchCategoryName
                        );
                        $searchTags = collect([
                            $searchProduct->is_featured ? 'featured' : null,
                            $searchProduct->is_new_arrival ? 'new' : null,
                            $searchProduct->isOnSale() ? 'sale' : null,
                            $searchAudience,
                            $searchAudience === 'both' ? 'men women' : null,
                        ])->filter()->implode(' ');
                    @endphp

                    <article
                        class="store-search-result"
                        data-search-item
                        data-search-text="{{ strtolower($searchProduct->name.' '.$searchProduct->product_code.' '.$searchCategoryName.' '.$searchCategorySlug.' '.$searchTags) }}"
                        data-search-category="{{ $searchCategorySlug }}"
                        data-search-category-key="{{ $searchCategoryKey }}"
                        data-search-audience="{{ $searchAudience }}"
                        data-search-price="{{ $searchPrice }}"
                        data-search-tags="{{ $searchTags }}"
                        data-search-brand-id="{{ $searchBrand->id }}"
                        data-search-brand-name="{{ $searchBrand->name }}"
                        data-search-brand-slug="{{ $searchBrand->slug }}"
                        data-search-brand-priority="{{ (int) $searchBrand->id === (int) $brand->id ? 'primary' : 'secondary' }}"
                    >
                        <a
                            href="{{ route('products.show', [
                                'brandSlug' => $searchBrand->slug,
                                'productSlug' => $searchProduct->slug,
                            ]) }}"
                            class="store-search-main"
                        >
                            <span>
                                @if ($searchImage)
                                    <img
                                        src="{{ Storage::url($searchImage->image) }}"
                                        alt="{{ $searchProduct->name }}"
                                        loading="lazy"
                                    >
                                @else
                                    {{ strtoupper(substr($searchProduct->name, 0, 1)) }}
                                @endif
                            </span>

                            <strong>{{ $searchProduct->name }}</strong>

                            <small>
                                {{ $searchCategoryName }}
                                ·
                                {{ $searchAudience === 'both' ? 'Men & Women' : ucfirst($searchAudience) }}
                            </small>
                        </a>

                        <div class="store-search-side">
                            <em class="store-search-brand-badge">
                                {{ $searchBrand->name }}
                            </em>

                            <div class="store-search-side-bottom">
                                <b>৳{{ number_format($searchPrice, 0) }}</b>

                                <button
                                    type="button"
                                    class="store-wishlist-button search"
                                    data-wishlist-button
                                    data-product-id="{{ $searchProduct->id }}"
                                    data-product-name="{{ $searchProduct->name }}"
                                    data-product-url="{{ route('products.show', [
                                        'brandSlug' => $searchBrand->slug,
                                        'productSlug' => $searchProduct->slug,
                                    ]) }}"
                                    data-product-image="{{ $searchImage ? Storage::url($searchImage->image) : '' }}"
                                    data-product-category="{{ $searchCategoryName }}"
                                    data-product-price="{{ $searchPrice }}"
                                    data-product-brand-name="{{ $searchBrand->name }}"
                                    data-product-brand-slug="{{ $searchBrand->slug }}"
                                    aria-label="Add {{ $searchProduct->name }} to wishlist"
                                >
                                    <i class="fa-regular fa-heart"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach

                <div
                    class="store-related-divider"
                    id="storeSearchRelatedDivider"
                    hidden
                >
                    <span>Want more like this</span>
                </div>

                <div
                    class="store-search-empty"
                    id="storeSearchEmpty"
                    hidden
                >
                    No products matched this search.
                </div>
            </div>
        </div>
    </div>

    <main>
        <section class="product-detail-section">
            <div class="store-container">
                <nav class="product-breadcrumb">
                    <a
                        href="{{ route(
                            'brand.show',
                            $brand->slug
                        ) }}"
                    >
                        Home
                    </a>

                    <span>›</span>

                    <a
                        href="{{ route(
                            'brand.show',
                            $brand->slug
                        ) }}#products"
                    >
                        Products
                    </a>

                    <span>›</span>

                    <span>{{ $product->name }}</span>
                </nav>

                <div class="product-detail-grid product-detail-stage">
                    <div class="product-gallery">
                        <div class="product-gallery-panel">
                            <div class="product-gallery-main">
                                <div class="product-gallery-glow"></div>

                                @if ($mainImage)
                                    <img
                                        src="{{ Storage::url(
                                            $mainImage->image
                                        ) }}"
                                        alt="{{ $product->name }}"
                                        id="productMainImage"
                                    >
                                @else
                                    <div class="product-gallery-fallback">
                                        {{ strtoupper(
                                            substr(
                                                $product->name,
                                                0,
                                                1
                                            )
                                        ) }}
                                    </div>
                                @endif

                                @if ($galleryImages->count() > 1)
                                    <button
                                        type="button"
                                        class="product-gallery-arrow previous"
                                        id="productPreviousImage"
                                        aria-label="Previous image"
                                    >
                                        ‹
                                    </button>

                                    <button
                                        type="button"
                                        class="product-gallery-arrow next"
                                        id="productNextImage"
                                        aria-label="Next image"
                                    >
                                        ›
                                    </button>
                                @endif

                                <button
                                    type="button"
                                    class="product-gallery-zoom"
                                    id="openProductImageLightbox"
                                    aria-label="View large image"
                                >
                                    ⛶
                                </button>
                            </div>

                            @if ($galleryImages->count())
                                <div
                                    class="product-gallery-thumbnails"
                                    id="productGalleryThumbnails"
                                >
                                    @foreach (
                                        $galleryImages as $index => $image
                                    )
                                        <button
                                            type="button"
                                            class="product-gallery-thumbnail {{
                                                $mainImage?->id === $image->id
                                                    ? 'active'
                                                    : ''
                                            }}"
                                            data-image-index="{{ $index }}"
                                            data-image-url="{{
                                                Storage::url(
                                                    $image->image
                                                )
                                            }}"
                                        >
                                            <img
                                                src="{{ Storage::url(
                                                    $image->image
                                                ) }}"
                                                alt="{{
                                                    $product->name
                                                }} image {{
                                                    $index + 1
                                                }}"
                                            >
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @if ($galleryImages->count() > 1)
                                <div
                                    class="product-gallery-dots"
                                    id="productGalleryDots"
                                    aria-label="Product image dots"
                                >
                                    @foreach ($galleryImages as $index => $image)
                                        <button
                                            type="button"
                                            class="product-gallery-dot {{
                                                $mainImage?->id === $image->id
                                                    ? 'active'
                                                    : ''
                                            }}"
                                            data-image-index="{{ $index }}"
                                            aria-label="Show image {{ $index + 1 }}"
                                        ></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="product-detail-info">
                        <div class="product-detail-card">
                        <div class="product-detail-labels">
                            <span class="product-detail-category">
                                {{ $brand->name }}
                            </span>

                            @if ($product->is_new_arrival)
                                <span class="product-detail-badge">
                                    New Arrival
                                </span>
                            @endif

                            @if ($hasSale)
                                <span class="product-detail-badge sale">
                                    {{ $discountPercentage }}% Off
                                </span>
                            @endif
                        </div>

                        <h1>{{ $product->name }}</h1>

                        <div class="product-detail-commerce-card">
                            <div class="product-detail-price">
                                @if ($hasSale)
                                    <strong>
                                        ৳{{ number_format(
                                            $product->sale_price,
                                            0
                                        ) }}
                                    </strong>

                                    <del>
                                        ৳{{ number_format(
                                            $product->regular_price,
                                            0
                                        ) }}
                                    </del>
                                @else
                                    <strong>
                                        ৳{{ number_format(
                                            $product->regular_price,
                                            0
                                        ) }}
                                    </strong>
                                @endif
                            </div>

                            <div
                                class="product-detail-stock {{
                                    $product->stock_quantity > 0
                                        ? 'available'
                                        : 'unavailable'
                                }}"
                            >
                                <span></span>

                                {{
                                    $product->stock_quantity > 0
                                        ? 'In Stock'
                                        : 'Out of Stock'
                                }}
                            </div>
                        </div>

                        @if ($product->short_description)
                            <p class="product-detail-summary">
                                {{
                                    $product->short_description
                                }}
                            </p>
                        @endif

                        <script
                            type="application/json"
                            id="productVariantData"
                        >{!! $productVariantJson !!}</script>

                        @if (count($productVariantsByColor))
                            <section class="detail-variant-group">
                                <div class="detail-variant-heading">
                                    <div>
                                        <strong>
                                            Select Color
                                        </strong>

                                        <small>
                                            Choose your preferred color.
                                        </small>
                                    </div>

                                    <span id="detailSelectedColor">
                                        Required
                                    </span>
                                </div>

                                <div
                                    class="detail-color-options"
                                    id="detailColorOptions"
                                ></div>

                                <small
                                    class="detail-variant-error"
                                    id="detailColorError"
                                ></small>
                            </section>

                            <section
                                class="detail-variant-group"
                                id="detailSizeSection"
                                hidden
                            >
                                <div class="detail-variant-heading">
                                    <div>
                                        <strong>
                                            Select Size & Quantity
                                        </strong>

                                        <small>
                                            Add quantity beside one or
                                            multiple sizes.
                                        </small>
                                    </div>

                                    <span id="detailSelectedSize">
                                        0 items selected
                                    </span>
                                </div>

                                <div
                                    class="detail-size-options"
                                    id="detailSizeOptions"
                                ></div>

                                <small
                                    class="detail-variant-error"
                                    id="detailSizeError"
                                ></small>
                            </section>

                            <section
                                class="detail-selection-summary"
                                id="detailSelectionSummary"
                            >
                                <div class="detail-selection-summary-header">
                                    <strong>
                                        Your Selection
                                    </strong>

                                    <span id="detailSelectionCount">
                                        0 items
                                    </span>
                                </div>

                                <div
                                    class="detail-selection-items"
                                    id="detailSelectionItems"
                                ></div>
                            </section>

                            <div class="detail-purchase-summary">
                                <div>
                                    <span>Total Quantity</span>

                                    <strong id="detailTotalQuantity">
                                        0
                                    </strong>
                                </div>

                                <div>
                                    <span>Total Price</span>

                                    <strong id="detailTotalPrice">
                                        ৳0
                                    </strong>
                                </div>
                            </div>

                            <small
                                class="detail-variant-error"
                                id="detailGeneralError"
                            ></small>

                            <div class="detail-purchase-actions">
                                <button
                                    type="button"
                                    class="detail-add-cart-button"
                                    id="detailAddToCartButton"
                                >
                                    Add Selected Items to Cart
                                </button>

                                <button
                                    type="button"
                                    class="detail-buy-now-button"
                                    id="detailBuyNowButton"
                                >
                                    Proceed to Checkout
                                </button>
                            </div>

                            <div
                                class="detail-cart-message"
                                id="detailCartMessage"
                            ></div>
                        @else
                            <div class="detail-cart-message visible error">
                                No available color and size variants
                                were found for this product.
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="product-information-section">
            <div class="store-container">
                <div
                    class="product-mobile-info-switch"
                    id="productMobileInfoSwitch"
                    aria-label="Product information sections"
                >
                    <button
                        type="button"
                        class="active"
                        data-mobile-info-target="description"
                        aria-pressed="true"
                    >
                        Details
                    </button>

                    <button
                        type="button"
                        data-mobile-info-target="info"
                        aria-pressed="false"
                    >
                        Information
                    </button>
                </div>

                <div class="product-information-grid">
                    <article
                        class="product-information-card product-description-card"
                        data-mobile-info-panel="description"
                    >
                        <span class="store-section-label">
                            Description
                        </span>

                        <h2>Product Details</h2>

                        <div class="product-description-content">
                            {!! nl2br(e(
                                $product->description
                                ?: $product->short_description
                                ?: 'No detailed description has been added yet.'
                            )) !!}
                        </div>
                    </article>

                    <aside class="product-information-sidebar">
                        <article
                            class="product-information-card"
                            data-mobile-info-panel="info"
                        >
                            <h3>Product Information</h3>

                            <dl class="product-specification-list">
                                <div>
                                    <dt>Brand</dt>
                                    <dd>{{ $brand->name }}</dd>
                                </div>

                                <div>
                                    <dt>Category</dt>

                                    <dd>
                                        {{
                                            $product->category?->name
                                            ?? '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>Material</dt>

                                    <dd>
                                        {{
                                            $product->material
                                            ?: '-'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt>Product Code</dt>

                                    <dd>
                                        {{ $product->product_code }}
                                    </dd>
                                </div>
                            </dl>
                        </article>

                        <article
                            class="product-information-card"
                            data-mobile-info-panel="info"
                        >
                            <h3>Care Instructions</h3>

                            <div class="product-care-content">
                                {!! nl2br(e(
                                    $product->care_instructions
                                    ?: 'No special care instructions.'
                                )) !!}
                            </div>
                        </article>
                    </aside>
                </div>
            </div>
        </section>

        @if ($relatedProducts->count())
            <section class="related-product-section">
                <div class="store-container">
                    <div class="store-section-heading">
                        <div>
                            <span class="store-section-label">
                                You may also like
                            </span>

                            <h2>Related Products</h2>
                        </div>

                        <p>
                            More products from
                            {{ $product->category?->name }}.
                        </p>
                    </div>

                    <div class="store-featured-slider-shell">
                        <button
                            type="button"
                            class="store-featured-slider-arrow prev"
                            data-related-slider-prev
                            aria-label="Previous related products"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <div
                            class="store-featured-slider"
                            id="storeRelatedSlider"
                        >
                            <div class="store-featured-slider-track store-product-grid store-featured-product-grid">
                                @foreach (
                                    $relatedProducts as $relatedProduct
                                )
                                    <div class="store-featured-slide">
                                        @include(
                                            'brands.partials.product-card',
                                            [
                                                'product' => $relatedProduct,
                                                'brand' => $brand,
                                            ]
                                        )
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <button
                            type="button"
                            class="store-featured-slider-arrow next"
                            data-related-slider-next
                            aria-label="Next related products"
                        >
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
        @endif
    </main>

    @include('storefront.partials.sweet-cool-section', [
        'brand' => $brand,
        'product' => $product,
    ])
    @include('storefront.partials.store-footer', ['brand' => $brand])

    <nav
        class="store-bottom-dock"
        id="productBottomDock"
        aria-label="Quick store actions"
    >
        <a
            href="{{ route('brand.show', $brand->slug) }}"
            class="store-bottom-dock-action is-active"
            data-dock-key="home"
            data-dock-action
            aria-label="Home"
        >
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>

        <button
            type="button"
            id="productDockCartButton"
            class="store-bottom-dock-action"
            data-dock-key="cart"
            data-dock-action
            aria-label="Open cart"
        >
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Cart</span>
            <strong id="productDockCartCount">0</strong>
        </button>

        <button
            type="button"
            id="productDockSearchButton"
            class="store-bottom-dock-action"
            data-dock-key="search"
            data-dock-action
            aria-label="Search products"
        >
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Search</span>
        </button>

        <a
            href="{{ $storeUser ? route('profile.edit') : route('login') }}"
            class="store-bottom-dock-action"
            data-dock-key="account"
            data-dock-action
            aria-label="{{ $storeUser ? 'Open account settings' : 'Open login page' }}"
        >
            <i class="fa-regular fa-user"></i>
            <span>Account</span>
        </a>

        <button
            type="button"
            id="productDockBackButton"
            class="store-bottom-dock-action"
            data-dock-key="back"
            data-dock-action
            aria-label="Go back"
            data-previous-url="{{ url()->previous() !== request()->fullUrl() ? url()->previous() : route('brand.show', $brand->slug).'#products' }}"
        >
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>
        </button>
    </nav>
</div>

@include('storefront.partials.variant-modal')
@include('storefront.partials.cart-drawer')
@include('storefront.partials.checkout-modal')
@include(
    'storefront.partials.new-customer-coupon-popup',
    ['brand' => $brand]
)

@if ($mainImage)
    <div
        class="product-image-lightbox"
        id="productImageLightbox"
        aria-hidden="true"
    >
        <button
            type="button"
            class="product-lightbox-close"
            id="closeProductImageLightbox"
        >
            ×
        </button>

        <img
            src="{{ Storage::url(
                $mainImage->image
            ) }}"
            alt="{{ $product->name }}"
            id="productLightboxImage"
        >
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    const mobileMenuButton =
        document.getElementById(
            'storeMobileMenuButton'
        );

    const navigation =
        document.getElementById(
            'storeNavigation'
        );

    const storeHeaderCartButton =
        document.getElementById(
            'storeHeaderCartButton'
        );

    const storeSearchButton =
        document.getElementById(
            'storeSearchButton'
        );

    const storeSearchOverlay =
        document.getElementById(
            'storeSearchOverlay'
        );

    const storeSearchClose =
        document.getElementById(
            'storeSearchClose'
        );

    const storeSearchInput =
        document.getElementById(
            'storeSearchInput'
        );

    const storeSearchFilterToggle =
        document.getElementById(
            'storeSearchFilterToggle'
        );

    const storeSearchFilterPanel =
        document.getElementById(
            'storeSearchFilterPanel'
        );

    const storeSearchMinRange =
        document.getElementById(
            'storeSearchMinRange'
        );

    const storeSearchMaxRange =
        document.getElementById(
            'storeSearchMaxRange'
        );

    const storeSearchMinBubble =
        document.getElementById(
            'storeSearchMinBubble'
        );

    const storeSearchMaxBubble =
        document.getElementById(
            'storeSearchMaxBubble'
        );

    const storePriceRangeTrack = document.querySelector(
        '.store-price-range-track'
    );

    const storeSearchCount =
        document.getElementById(
            'storeSearchCount'
        );

    const storeSearchResults =
        document.getElementById(
            'storeSearchResults'
        );

    const storeSearchRelatedDivider =
        document.getElementById(
            'storeSearchRelatedDivider'
        );

    const storeSearchReset =
        document.getElementById(
            'storeSearchReset'
        );

    const storeSearchEmpty =
        document.getElementById(
            'storeSearchEmpty'
        );

    const productDockCartButton =
        document.getElementById(
            'productDockCartButton'
        );

    const productDockSearchButton =
        document.getElementById(
            'productDockSearchButton'
        );

    const productDockBackButton =
        document.getElementById(
            'productDockBackButton'
        );

    const relatedSlider =
        document.getElementById(
            'storeRelatedSlider'
        );

    const relatedSliderPrev = document.querySelector(
        '[data-related-slider-prev]'
    );

    const relatedSliderNext = document.querySelector(
        '[data-related-slider-next]'
    );

    const productBottomDock =
        document.getElementById(
            'productBottomDock'
        );

    const productDockActions = Array.from(
        document.querySelectorAll(
            '#productBottomDock [data-dock-action]'
        )
    );
    let persistentProductDockAction = 'home';
    let productDockScrollMotionTimer = null;
    let lastProductDockScrollY = window.scrollY || 0;

    const productDockCartCount =
        document.getElementById(
            'productDockCartCount'
        );

    const floatingCartCount =
        document.getElementById(
            'floatingCartCount'
        );

    const brandSwitchButton =
        document.getElementById(
            'brandSwitchButton'
        );

    const brandSwitchDropdown =
        document.getElementById(
            'brandSwitchDropdown'
        );

    const searchPriceLimit = Number(
        storeSearchMaxRange?.max || 0
    );

    function closeMobileNavigation() {
        navigation?.classList.remove('open');
        mobileMenuButton?.classList.remove('open');
        mobileMenuButton?.setAttribute(
            'aria-expanded',
            'false'
        );
        document.body.classList.remove(
            'store-mobile-nav-open'
        );
    }

    mobileMenuButton?.addEventListener(
        'click',
        function (event) {
            event.stopPropagation();

            const isOpen = !navigation?.classList
                .contains('open');

            navigation?.classList.toggle('open', isOpen);
            mobileMenuButton.classList.toggle('open', isOpen);
            mobileMenuButton.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );
            document.body.classList.toggle(
                'store-mobile-nav-open',
                isOpen
            );
        }
    );

    function openProductCart() {
        document
            .getElementById('floatingCartButton')
            ?.click();
    }

    function openStoreSearch() {
        storeSearchOverlay?.classList.add('open');
        storeSearchOverlay?.setAttribute(
            'aria-hidden',
            'false'
        );
        syncSearchPriceUi();
        setProductDockAction('search');

        window.setTimeout(function () {
            storeSearchInput?.focus();
        }, 80);
    }

    function closeStoreSearch() {
        storeSearchOverlay?.classList.remove('open');
        storeSearchOverlay?.setAttribute(
            'aria-hidden',
            'true'
        );

        if (storeSearchInput) {
            storeSearchInput.value = '';
        }

        resetStoreSearchFilters();

        if (storeSearchFilterPanel) {
            storeSearchFilterPanel.hidden = true;
        }

        storeSearchFilterToggle?.classList.remove(
            'active'
        );
        storeSearchFilterToggle?.setAttribute(
            'aria-expanded',
            'false'
        );

        persistentProductDockAction = 'home';
        setProductDockAction('home');
    }

    storeHeaderCartButton?.addEventListener(
        'click',
        openProductCart
    );

    storeSearchButton?.addEventListener(
        'click',
        openStoreSearch
    );

    productDockCartButton?.addEventListener(
        'click',
        function () {
            persistentProductDockAction = 'home';
            setProductDockAction('cart');
            openProductCart();
        }
    );

    productDockSearchButton?.addEventListener(
        'click',
        function () {
            persistentProductDockAction = 'search';
            openStoreSearch();
        }
    );

    function setProductDockAction(key) {
        productDockActions.forEach(function (action) {
            action.classList.toggle(
                'is-active',
                action.dataset.dockKey === (key || '')
            );
        });
    }

    function formatSearchPrice(value) {
        return `৳${Number(value || 0).toLocaleString()}`;
    }

    function syncSearchPriceUi() {
        const minValue = Number(
            storeSearchMinRange?.value || 0
        );
        const maxValue = Number(
            storeSearchMaxRange?.value || searchPriceLimit
        );

        const minPercent = searchPriceLimit
            ? (minValue / searchPriceLimit) * 100
            : 0;

        const maxPercent = searchPriceLimit
            ? (maxValue / searchPriceLimit) * 100
            : 100;

        storePriceRangeTrack?.style.setProperty(
            '--range-left',
            `${minPercent}%`
        );

        storePriceRangeTrack?.style.setProperty(
            '--range-right',
            `${maxPercent}%`
        );

        if (storeSearchMinBubble) {
            storeSearchMinBubble.textContent =
                formatSearchPrice(minValue);
            storeSearchMinBubble.style.left =
                `${minPercent}%`;
        }

        if (storeSearchMaxBubble) {
            storeSearchMaxBubble.textContent =
                formatSearchPrice(maxValue);
            storeSearchMaxBubble.style.left =
                `${maxPercent}%`;
        }
    }

    function searchAudienceMatches(itemAudience) {
        const activeSearchAudience =
            storeSearchOverlay?.dataset.searchAudience
            || 'all';

        if (activeSearchAudience === 'all') {
            return true;
        }

        return itemAudience === activeSearchAudience
            || itemAudience === 'both';
    }

    function searchTagMatches(tags) {
        const activeSearchTag =
            storeSearchOverlay?.dataset.searchTag
            || 'all';

        if (activeSearchTag === 'all') {
            return true;
        }

        return tags
            .split(/\s+/)
            .includes(activeSearchTag);
    }

    function applyStoreSearchFilters() {
        const query = storeSearchInput?.value
            .trim()
            .toLowerCase() || '';

        const minPrice = Number(
            storeSearchMinRange?.value || 0
        );

        const maxPrice = Number(
            storeSearchMaxRange?.value || searchPriceLimit
        );

        const activeSearchCategory =
            storeSearchOverlay?.dataset.searchCategory
            || 'all';

        let visibleCount = 0;

        storeSearchOverlay
            ?.querySelectorAll('[data-search-item]')
            .forEach(function (item) {
                const haystack =
                    item.dataset.searchText || '';

                const itemCategory =
                    item.dataset.searchCategory || '';

                const itemCategoryKey =
                    item.dataset.searchCategoryKey || '';

                const itemAudience =
                    item.dataset.searchAudience || 'both';

                const itemTags =
                    item.dataset.searchTags || '';

                const itemPrice = Number(
                    item.dataset.searchPrice || 0
                );

                const queryMatched =
                    query.length === 0
                    || haystack.includes(query);

                const categoryMatched =
                    activeSearchCategory === 'all'
                    || itemCategory === activeSearchCategory
                    || itemCategoryKey === activeSearchCategory;

                const audienceMatched =
                    searchAudienceMatches(itemAudience);

                const tagMatched =
                    searchTagMatches(itemTags);

                const minMatched =
                    !minPrice || itemPrice >= minPrice;

                const maxMatched =
                    !maxPrice || itemPrice <= maxPrice;

                const matched =
                    queryMatched
                    && categoryMatched
                    && audienceMatched
                    && tagMatched
                    && minMatched
                    && maxMatched;

                item.hidden = !matched;

                if (matched) {
                    visibleCount += 1;
                }
            });

        regroupSearchResults();

        if (storeSearchCount) {
            storeSearchCount.textContent =
                `${visibleCount} product${visibleCount === 1 ? '' : 's'}`;
        }

        if (storeSearchEmpty) {
            storeSearchEmpty.hidden = visibleCount > 0;
        }
    }

    function regroupSearchResults() {
        if (
            !storeSearchResults
            || !storeSearchRelatedDivider
        ) {
            return;
        }

        const visibleItems = Array.from(
            storeSearchResults.querySelectorAll(
                '[data-search-item]'
            )
        ).filter(function (item) {
            return !item.hidden;
        });

        const primaryItems = visibleItems.filter(
            function (item) {
                return (
                    item.dataset.searchBrandPriority
                    === 'primary'
                );
            }
        );

        const secondaryItems = visibleItems.filter(
            function (item) {
                return (
                    item.dataset.searchBrandPriority
                    === 'secondary'
                );
            }
        );

        if (
            primaryItems.length > 0
            && secondaryItems.length > 0
        ) {
            storeSearchRelatedDivider.hidden = false;
            storeSearchResults.insertBefore(
                storeSearchRelatedDivider,
                secondaryItems[0]
            );
        } else {
            storeSearchRelatedDivider.hidden = true;
            storeSearchResults.appendChild(
                storeSearchRelatedDivider
            );
        }
    }

    function setSearchChipActive(selector, activeValue, datasetKey) {
        storeSearchOverlay
            ?.querySelectorAll(selector)
            .forEach(function (button) {
                button.classList.toggle(
                    'active',
                    button.dataset[datasetKey] === activeValue
                );
            });
    }

    function resetStoreSearchFilters() {
        if (storeSearchOverlay) {
            storeSearchOverlay.dataset.searchAudience = 'all';
            storeSearchOverlay.dataset.searchCategory = 'all';
            storeSearchOverlay.dataset.searchTag = 'all';
        }

        if (storeSearchMinRange) {
            storeSearchMinRange.value = '0';
        }

        if (storeSearchMaxRange) {
            storeSearchMaxRange.value = String(
                searchPriceLimit
            );
        }

        syncSearchPriceUi();

        setSearchChipActive(
            'button[data-search-audience]',
            'all',
            'searchAudience'
        );

        setSearchChipActive(
            'button[data-search-category]',
            'all',
            'searchCategory'
        );

        setSearchChipActive(
            'button[data-search-tag]',
            'all',
            'searchTag'
        );

        applyStoreSearchFilters();
    }

    storeSearchClose?.addEventListener(
        'click',
        closeStoreSearch
    );

    storeSearchOverlay?.addEventListener(
        'click',
        function (event) {
            if (event.target === storeSearchOverlay) {
                closeStoreSearch();
            }
        }
    );

    storeSearchFilterToggle?.addEventListener(
        'click',
        function () {
            const nextExpanded =
                storeSearchFilterToggle.getAttribute(
                    'aria-expanded'
                ) !== 'true';

            storeSearchFilterPanel.hidden =
                !nextExpanded;

            storeSearchFilterToggle.classList.toggle(
                'active',
                nextExpanded
            );

            storeSearchFilterToggle.setAttribute(
                'aria-expanded',
                nextExpanded ? 'true' : 'false'
            );
        }
    );

    storeSearchInput?.addEventListener(
        'input',
        applyStoreSearchFilters
    );

    storeSearchMinRange?.addEventListener(
        'input',
        function () {
            const minValue = Number(
                storeSearchMinRange.value || 0
            );
            const currentMax = Number(
                storeSearchMaxRange?.value
                    || searchPriceLimit
            );

            if (
                storeSearchMaxRange
                && minValue > currentMax
            ) {
                storeSearchMaxRange.value = String(
                    minValue
                );
            }

            syncSearchPriceUi();
            applyStoreSearchFilters();
        }
    );

    storeSearchMaxRange?.addEventListener(
        'input',
        function () {
            const maxValue = Number(
                storeSearchMaxRange.value
                    || searchPriceLimit
            );
            const currentMin = Number(
                storeSearchMinRange?.value || 0
            );

            if (
                storeSearchMinRange
                && maxValue < currentMin
            ) {
                storeSearchMinRange.value = String(
                    maxValue
                );
            }

            syncSearchPriceUi();
            applyStoreSearchFilters();
        }
    );

    storeSearchOverlay
        ?.querySelectorAll(
            'button[data-search-audience]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const nextValue = String(
                        button.dataset.searchAudience
                        || 'all'
                    );

                    storeSearchOverlay.dataset.searchAudience =
                        nextValue;

                    setSearchChipActive(
                        'button[data-search-audience]',
                        nextValue,
                        'searchAudience'
                    );

                    applyStoreSearchFilters();
                }
            );
        });

    storeSearchOverlay
        ?.querySelectorAll(
            'button[data-search-category]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const nextValue = String(
                        button.dataset.searchCategory
                        || 'all'
                    );

                    storeSearchOverlay.dataset.searchCategory =
                        nextValue;

                    setSearchChipActive(
                        'button[data-search-category]',
                        nextValue,
                        'searchCategory'
                    );

                    applyStoreSearchFilters();
                }
            );
        });

    storeSearchOverlay
        ?.querySelectorAll(
            'button[data-search-tag]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const nextValue = String(
                        button.dataset.searchTag || 'all'
                    );

                    storeSearchOverlay.dataset.searchTag =
                        nextValue;

                    setSearchChipActive(
                        'button[data-search-tag]',
                        nextValue,
                        'searchTag'
                    );

                    applyStoreSearchFilters();
                }
            );
        });

    storeSearchReset?.addEventListener(
        'click',
        function () {
            if (storeSearchInput) {
                storeSearchInput.value = '';
            }

            resetStoreSearchFilters();
        }
    );

    resetStoreSearchFilters();

    productDockBackButton?.addEventListener(
        'click',
        function () {
            persistentProductDockAction = 'home';
            setProductDockAction('home');

            const previousUrl = String(
                productDockBackButton.dataset.previousUrl || ''
            ).trim();

            if (previousUrl) {
                window.location.href = previousUrl;
                return;
            }

            if (window.history.length > 1) {
                window.history.back();
                return;
            }

            window.location.href =
                @json(route('brand.show', $brand->slug).'#products');
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            if (
                navigation?.classList.contains('open')
                && navigation
                && !navigation.contains(event.target)
                && !mobileMenuButton?.contains(event.target)
            ) {
                closeMobileNavigation();
            }

            if (
                brandSwitchDropdown
                && !brandSwitchDropdown.contains(event.target)
            ) {
                brandSwitchDropdown.classList.remove(
                    'open'
                );

                brandSwitchButton?.setAttribute(
                    'aria-expanded',
                    'false'
                );
            }
        }
    );

    function syncProductScrollChrome() {
        const hasScrolled =
            window.scrollY > 140;
        const currentScrollY =
            window.scrollY || 0;
        const scrollDelta =
            currentScrollY - lastProductDockScrollY;
        const dockMotionDirection = scrollDelta > 0
            ? 'up'
            : scrollDelta < 0
                ? 'down'
                : null;

        productBottomDock?.classList.toggle(
            'show',
            hasScrolled
        );

        if (
            hasScrolled
            && productBottomDock?.classList.contains('show')
        ) {
            document.body.classList.add(
                'store-dock-scrolling'
            );
            document.body.classList.remove(
                'store-dock-scroll-up',
                'store-dock-scroll-down'
            );

            if (dockMotionDirection === 'up') {
                document.body.classList.add(
                    'store-dock-scroll-up'
                );
            } else if (
                dockMotionDirection === 'down'
            ) {
                document.body.classList.add(
                    'store-dock-scroll-down'
                );
            }

            if (productDockScrollMotionTimer) {
                window.clearTimeout(
                    productDockScrollMotionTimer
                );
            }

            productDockScrollMotionTimer =
                window.setTimeout(
                    function () {
                        document.body.classList.remove(
                            'store-dock-scrolling'
                        );
                        document.body.classList.remove(
                            'store-dock-scroll-up',
                            'store-dock-scroll-down'
                        );
                    },
                    240
                );
        } else if (!hasScrolled) {
            document.body.classList.remove(
                'store-dock-scrolling'
            );
            document.body.classList.remove(
                'store-dock-scroll-up',
                'store-dock-scroll-down'
            );
        }

        lastProductDockScrollY = currentScrollY;

        setProductDockAction(
            persistentProductDockAction
        );
    }

    syncProductScrollChrome();

    window.addEventListener(
        'pageshow',
        function () {
            closeStoreSearch();
            persistentProductDockAction = 'home';
            lastProductDockScrollY = window.scrollY || 0;
            document.body.classList.remove(
                'store-dock-scroll-up',
                'store-dock-scroll-down'
            );
            setProductDockAction('home');
        }
    );

    window.addEventListener(
        'scroll',
        syncProductScrollChrome,
        {
            passive: true,
        }
    );

    if (
        floatingCartCount
        && productDockCartCount
        && 'MutationObserver' in window
    ) {
        const syncDockCartCount = function () {
            productDockCartCount.textContent =
                floatingCartCount.textContent || '0';
        };

        syncDockCartCount();

        new MutationObserver(syncDockCartCount)
            .observe(
                floatingCartCount,
                {
                    childList: true,
                    characterData: true,
                    subtree: true,
                }
            );
    }

    document.addEventListener(
        'cart-drawer:open',
        function () {
            closeStoreSearch();
            setProductDockAction('cart');
        }
    );

    document.addEventListener(
        'cart-drawer:close',
        function () {
            persistentProductDockAction = 'home';
            setProductDockAction('home');
        }
    );

    productDockActions.forEach(function (action) {
        action.addEventListener('click', function () {
            const dockKey = action.dataset.dockKey;

            if (
                dockKey
                && !['cart', 'back'].includes(dockKey)
            ) {
                persistentProductDockAction = dockKey;
                setProductDockAction(dockKey);
            }
        });
    });

    brandSwitchButton?.addEventListener(
        'click',
        function (event) {
            event.stopPropagation();

            brandSwitchDropdown?.classList
                .toggle('open');
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            if (
                brandSwitchDropdown &&
                !brandSwitchDropdown.contains(
                    event.target
                )
            ) {
                brandSwitchDropdown.classList
                    .remove('open');
            }
        }
    );

    navigation
        ?.querySelectorAll('a')
        .forEach(function (link) {
            link.addEventListener(
                'click',
                function () {
                    navigation.classList
                        .remove('open');

                    mobileMenuButton?.classList
                        .remove('open');

                    mobileMenuButton?.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Related slider
    |--------------------------------------------------------------------------
    */

    function scrollRelatedSlider(direction) {
        if (!relatedSlider) {
            return;
        }

        const slide = relatedSlider.querySelector(
            '.store-featured-slide'
        );

        const gap = 12;
        const distance = slide
            ? slide.getBoundingClientRect().width + gap
            : relatedSlider.clientWidth * 0.82;

        relatedSlider.scrollBy({
            left: direction * distance,
            behavior: 'smooth',
        });
    }

    relatedSliderPrev?.addEventListener(
        'click',
        function () {
            scrollRelatedSlider(-1);
        }
    );

    relatedSliderNext?.addEventListener(
        'click',
        function () {
            scrollRelatedSlider(1);
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    */

    const thumbnails = Array.from(
        document.querySelectorAll(
            '.product-gallery-thumbnail'
        )
    );

    const productGalleryMain =
        document.querySelector(
            '.product-gallery-main'
        );

    const galleryDots = Array.from(
        document.querySelectorAll(
            '.product-gallery-dot'
        )
    );

    const mainImage =
        document.getElementById(
            'productMainImage'
        );

    const lightboxImage =
        document.getElementById(
            'productLightboxImage'
        );

    let activeImageIndex = Math.max(
        0,
        thumbnails.findIndex(function (thumbnail) {
            return thumbnail.classList
                .contains('active');
        })
    );
    let galleryAnimationTimer = null;
    let galleryGhostImage = null;

    function getGalleryDirection(nextIndex) {
        if (thumbnails.length <= 1) {
            return 1;
        }

        if (
            activeImageIndex === thumbnails.length - 1
            && nextIndex === 0
        ) {
            return 1;
        }

        if (
            activeImageIndex === 0
            && nextIndex === thumbnails.length - 1
        ) {
            return -1;
        }

        return nextIndex >= activeImageIndex ? 1 : -1;
    }

    function animateProductGalleryChange(
        nextImageUrl,
        direction
    ) {
        if (!mainImage || !productGalleryMain) {
            return;
        }

        if (galleryAnimationTimer) {
            window.clearTimeout(galleryAnimationTimer);
            galleryAnimationTimer = null;
        }

        if (galleryGhostImage) {
            galleryGhostImage.remove();
            galleryGhostImage = null;
        }

        const ghostImage =
            mainImage.cloneNode(true);

        ghostImage.removeAttribute('id');
        ghostImage.classList.add(
            'product-gallery-image-ghost'
        );

        productGalleryMain.appendChild(ghostImage);
        galleryGhostImage = ghostImage;

        productGalleryMain.classList.add(
            'is-animating'
        );

        mainImage.style.transition = 'none';
        mainImage.style.opacity = '0';
        mainImage.style.transform =
            `translateX(${direction * 42}px) scale(0.985)`;

        mainImage.src = nextImageUrl;

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                ghostImage.style.opacity = '0';
                ghostImage.style.transform =
                    `translateX(${direction * -42}px) scale(0.985)`;

                mainImage.style.transition = '';
                mainImage.style.opacity = '1';
                mainImage.style.transform =
                    'translateX(0) scale(1)';
            });
        });

        galleryAnimationTimer = window.setTimeout(
            function () {
                ghostImage.remove();
                galleryGhostImage = null;
                productGalleryMain.classList.remove(
                    'is-animating'
                );
                mainImage.style.transition = '';
                mainImage.style.opacity = '';
                mainImage.style.transform = '';
                galleryAnimationTimer = null;
            },
            380
        );
    }

    function showProductImage(index) {
        if (!thumbnails.length || !mainImage) {
            return;
        }

        if (index < 0) {
            index = thumbnails.length - 1;
        }

        if (index >= thumbnails.length) {
            index = 0;
        }

        activeImageIndex = index;

        const thumbnail = thumbnails[index];

        const imageUrl =
            thumbnail.dataset.imageUrl;

        if (!imageUrl) {
            return;
        }

        if (mainImage.src !== imageUrl) {
            animateProductGalleryChange(
                imageUrl,
                getGalleryDirection(index)
            );
        }

        if (lightboxImage) {
            lightboxImage.src = imageUrl;
        }

        thumbnails.forEach(function (item) {
            item.classList.remove('active');
        });

        thumbnail.classList.add('active');

        galleryDots.forEach(function (item) {
            item.classList.remove('active');
        });

        if (galleryDots[index]) {
            galleryDots[index].classList.add('active');
        }
    }

    thumbnails.forEach(
        function (thumbnail, index) {
            thumbnail.addEventListener(
                'click',
                function () {
                    showProductImage(index);
                }
            );
        }
    );

    galleryDots.forEach(function (dot, index) {
        dot.addEventListener('click', function () {
            showProductImage(index);
        });
    });

    document
        .getElementById('productPreviousImage')
        ?.addEventListener(
            'click',
            function () {
                showProductImage(
                    activeImageIndex - 1
                );
            }
        );

    document
        .getElementById('productNextImage')
        ?.addEventListener(
            'click',
            function () {
                showProductImage(
                    activeImageIndex + 1
                );
            }
        );

    if (
        productGalleryMain
        && thumbnails.length > 1
    ) {
        let touchStartX = 0;
        let touchStartY = 0;
        let touchLocked = false;

        productGalleryMain.addEventListener(
            'touchstart',
            function (event) {
                const touch =
                    event.touches?.[0];

                if (!touch) {
                    return;
                }

                touchStartX = touch.clientX;
                touchStartY = touch.clientY;
                touchLocked = false;
            },
            { passive: true }
        );

        productGalleryMain.addEventListener(
            'touchmove',
            function (event) {
                if (touchLocked) {
                    return;
                }

                const touch =
                    event.touches?.[0];

                if (!touch) {
                    return;
                }

                const deltaX =
                    touch.clientX - touchStartX;
                const deltaY =
                    touch.clientY - touchStartY;

                if (
                    Math.abs(deltaX) > 36
                    && Math.abs(deltaX)
                        > Math.abs(deltaY)
                ) {
                    showProductImage(
                        deltaX < 0
                            ? activeImageIndex + 1
                            : activeImageIndex - 1
                    );

                    touchLocked = true;
                }
            },
            { passive: true }
        );

        productGalleryMain.addEventListener(
            'touchend',
            function () {
                touchLocked = false;
            },
            { passive: true }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lightbox
    |--------------------------------------------------------------------------
    */

    const lightbox =
        document.getElementById(
            'productImageLightbox'
        );

    function openLightbox() {
        if (!lightbox) {
            return;
        }

        lightbox.classList.add('open');

        lightbox.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'product-lightbox-open'
        );
    }

    function closeLightbox() {
        if (!lightbox) {
            return;
        }

        lightbox.classList.remove('open');

        lightbox.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'product-lightbox-open'
        );
    }

    document
        .getElementById(
            'openProductImageLightbox'
        )
        ?.addEventListener(
            'click',
            openLightbox
        );

    document
        .getElementById(
            'closeProductImageLightbox'
        )
        ?.addEventListener(
            'click',
            closeLightbox
        );

    lightbox?.addEventListener(
        'click',
        function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Product variants
    |--------------------------------------------------------------------------
    */

    const csrfToken = document
        .querySelector(
            'meta[name="csrf-token"]'
        )
        ?.getAttribute('content');

    const variantDataElement =
        document.getElementById(
            'productVariantData'
        );

    const colorOptions =
        document.getElementById(
            'detailColorOptions'
        );

    const sizeSection =
        document.getElementById(
            'detailSizeSection'
        );

    const sizeOptions =
        document.getElementById(
            'detailSizeOptions'
        );

    const selectedColorOutput =
        document.getElementById(
            'detailSelectedColor'
        );

    const selectedSizeOutput =
        document.getElementById(
            'detailSelectedSize'
        );

    const colorError =
        document.getElementById(
            'detailColorError'
        );

    const sizeError =
        document.getElementById(
            'detailSizeError'
        );

    const generalError =
        document.getElementById(
            'detailGeneralError'
        );

    const totalQuantityOutput =
        document.getElementById(
            'detailTotalQuantity'
        );

    const totalPriceOutput =
        document.getElementById(
            'detailTotalPrice'
        );

    const selectionSummary =
        document.getElementById(
            'detailSelectionSummary'
        );

    const selectionCount =
        document.getElementById(
            'detailSelectionCount'
        );

    const selectionItems =
        document.getElementById(
            'detailSelectionItems'
        );

    const addToCartButton =
        document.getElementById(
            'detailAddToCartButton'
        );

    const buyNowButton =
        document.getElementById(
            'detailBuyNowButton'
        );

    const cartMessage =
        document.getElementById(
            'detailCartMessage'
        );

    let variants = [];
    let selectedColor = null;
    let selectedColorHex = '';
    let selectedSizesByColor = new Map();

    try {
        variants = variantDataElement
            ? JSON.parse(
                variantDataElement.textContent
            )
            : [];
    } catch (error) {
        console.error(
            'Variant data parse failed:',
            error
        );

        variants = [];
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function money(value) {
        return `৳${Number(
            value || 0
        ).toLocaleString(
            'en-BD',
            {
                maximumFractionDigits: 2,
            }
        )}`;
    }

    function normalizeColorHex(value) {
        const raw = String(value || '')
            .trim()
            .toUpperCase();

        if (!raw) {
            return '';
        }

        if (/^#([A-F0-9]{6})$/.test(raw)) {
            return raw;
        }

        if (/^([A-F0-9]{6})$/.test(raw)) {
            return `#${raw}`;
        }

        return '';
    }

    function fallbackColorFromName(name) {
        const source = String(name || '').trim();

        if (!source) {
            return '#CBD5E1';
        }

        let hash = 0;

        for (let index = 0; index < source.length; index += 1) {
            hash = source.charCodeAt(index)
                + ((hash << 5) - hash);
        }

        return `hsl(${Math.abs(hash) % 360} 68% 58%)`;
    }

    function swatchColor(name, hex) {
        return normalizeColorHex(hex)
            || fallbackColorFromName(name);
    }

    function colorSelectionKey(
        colorName,
        colorHex
    ) {
        const hex = normalizeColorHex(
            colorHex
        );

        if (hex) {
            return `hex:${hex}`;
        }

        return `name:${String(colorName || '')
            .trim()
            .toLowerCase()}`;
    }

    function getActiveColorSelection() {
        if (!selectedColor) {
            return null;
        }

        const key = colorSelectionKey(
            selectedColor,
            selectedColorHex
        );

        if (!selectedSizesByColor.has(key)) {
            selectedSizesByColor.set(key, {
                color: selectedColor,
                colorHex: selectedColorHex,
                sizes: new Map(),
            });
        }

        return selectedSizesByColor.get(key);
    }

    function showCartMessage(
        message,
        type
    ) {
        if (!cartMessage) {
            return;
        }

        cartMessage.textContent = message;

        cartMessage.className =
            `detail-cart-message visible ${type}`;

        window.setTimeout(function () {
            cartMessage.classList
                .remove('visible');
        }, 3000);
    }

    function getGroupedSelections() {
        return Array.from(
            selectedSizesByColor.values()
        )
            .map(function (group) {
                const items = Array.from(
                    group.sizes.values()
                )
                    .filter(function (item) {
                        return Number(item.quantity) > 0;
                    })
                    .map(function (item) {
                        return {
                            size: item.size,
                            quantity: Number(item.quantity),
                            stock: Number(item.stock || 0),
                        };
                    });

                return {
                    color: group.color,
                    colorHex: normalizeColorHex(
                        group.colorHex
                    ),
                    items,
                };
            })
            .filter(function (group) {
                return group.items.length > 0;
            });
    }

    function getTotalQuantity() {
        return getGroupedSelections().reduce(
            function (total, group) {
                return total + group.items.reduce(
                    function (sum, item) {
                        return sum + Number(
                            item.quantity || 0
                        );
                    },
                    0
                );
            },
            0
        );
    }

    function getSelectedItems() {
        return getGroupedSelections().flatMap(
            function (group) {
                return group.items.map(function (item) {
                    return {
                        color: group.color,
                        colorHex: group.colorHex,
                        size: item.size,
                        quantity: item.quantity,
                        stock: item.stock,
                    };
                });
            }
        );
    }

    function updateSummary() {
        const items =
            getSelectedItems();

        const totalQuantity =
            getTotalQuantity();

        if (selectedSizeOutput) {
            selectedSizeOutput.textContent =
                totalQuantity > 0
                    ? `${items.length} size(s), ${totalQuantity} item(s)`
                    : '0 items selected';
        }

        if (totalQuantityOutput) {
            totalQuantityOutput.textContent =
                totalQuantity;
        }

        if (totalPriceOutput) {
            totalPriceOutput.textContent =
                money(
                    {{ json_encode($unitPrice) }}
                    * totalQuantity
                );
        }

        if (selectionCount) {
            selectionCount.textContent =
                `${totalQuantity} ${
                    totalQuantity === 1
                        ? 'item'
                        : 'items'
                }`;
        }

        if (selectionItems) {
            selectionItems.innerHTML = '';

            items.forEach(function (item) {
                const itemElement =
                    document.createElement('span');

                itemElement.className =
                    'detail-selection-item';

                itemElement.innerHTML =
                    `<i class="store-color-swatch" style="--swatch-color:${escapeHtml(
                        swatchColor(
                            item.color,
                            item.colorHex
                        )
                    )};"></i> ` +
                    `${escapeHtml(item.color)} / ` +
                    `${escapeHtml(item.size)} × ` +
                    `${item.quantity}`;

                selectionItems.appendChild(
                    itemElement
                );
            });
        }

        selectionSummary?.classList.toggle(
            'visible',
            totalQuantity > 0
        );

        const canPurchase =
            Boolean(selectedColor)
            && totalQuantity > 0;

        if (addToCartButton) {
            addToCartButton.dataset.selectionReady =
                canPurchase ? 'true' : 'false';
        }

        if (buyNowButton) {
            buyNowButton.dataset.selectionReady =
                canPurchase ? 'true' : 'false';
        }
    }

    function renderSizes(sizes) {
        if (!sizeOptions) {
            return;
        }

        sizeOptions.innerHTML = '';

        if (sizeSection) {
            sizeSection.hidden = false;
        }

        const activeSelection =
            getActiveColorSelection();

        const sizeSelectionMap =
            activeSelection?.sizes
            || new Map();

        sizes.forEach(function (item) {
            const size = String(
                item.size || ''
            );

            const stock = Number(
                item.stock || 0
            );

            if (stock < 1) {
                return;
            }

            const card =
                document.createElement('div');

            card.className =
                'detail-size-card';

            card.innerHTML = `
                <div class="detail-size-card-copy">
                    <div>
                        <strong>
                            ${escapeHtml(size)}
                        </strong>

                        <span>
                            Ready to order
                        </span>
                    </div>

                    <span class="detail-size-selected">
                        Selected
                    </span>
                </div>

                <div class="detail-size-stepper">
                    <button
                        type="button"
                        data-detail-size-decrease="${escapeHtml(size)}"
                        data-detail-size-stock="${stock}"
                    >
                        −
                    </button>

                    <span
                        data-detail-size-quantity="${escapeHtml(size)}"
                    >
                        ${Number(
                            sizeSelectionMap.get(size)
                                ?.quantity || 0
                        )}
                    </span>

                    <button
                        type="button"
                        data-detail-size-increase="${escapeHtml(size)}"
                        data-detail-size-stock="${stock}"
                    >
                        +
                    </button>
                </div>
            `;

            card.classList.toggle(
                'selected',
                Number(
                    sizeSelectionMap.get(size)
                        ?.quantity || 0
                ) > 0
            );

            sizeOptions.appendChild(card);
        });

        updateSummary();
    }

    function renderColors() {
        if (!colorOptions) {
            return;
        }

        colorOptions.innerHTML = '';

        variants.forEach(function (group) {
            const color = String(
                group.color || ''
            );

            const colorHex = normalizeColorHex(
                group.color_hex || ''
            );

            const button =
                document.createElement('button');

            button.type = 'button';

            button.className =
                'detail-color-option';

            button.style.setProperty(
                '--detail-color',
                swatchColor(color, colorHex)
            );

            button.innerHTML = `
                <i aria-hidden="true"></i>
                <strong>${escapeHtml(color)}</strong>
            `;

            button.addEventListener(
                'click',
                function () {
                    colorOptions
                        .querySelectorAll(
                            '.detail-color-option'
                        )
                        .forEach(function (item) {
                            item.classList
                                .remove('selected');
                        });

                    button.classList.add(
                        'selected'
                    );

                    selectedColor = color;
                    selectedColorHex = colorHex;

                    if (selectedColorOutput) {
                        selectedColorOutput.textContent =
                            `${color} selected`;
                    }

                    if (colorError) {
                        colorError.textContent = '';
                    }

                    if (sizeError) {
                        sizeError.textContent = '';
                    }

                    if (generalError) {
                        generalError.textContent = '';
                    }

                    renderSizes(
                        Array.isArray(group.sizes)
                            ? group.sizes
                            : []
                    );
                }
            );

            colorOptions.appendChild(button);
        });
    }

    function updateSizeQuantity(
        size,
        quantity,
        stock
    ) {
        const safeQuantity = Math.max(
            0,
            Math.min(
                Number(quantity || 0),
                Number(stock || 0)
            )
        );

        const activeSelection =
            getActiveColorSelection();

        if (!activeSelection) {
            return;
        }

        if (safeQuantity === 0) {
            activeSelection.sizes.delete(size);
        } else {
            activeSelection.sizes.set(size, {
                size,
                quantity: safeQuantity,
                stock: Number(stock),
            });
        }

        const output = sizeOptions?.querySelector(
            `[data-detail-size-quantity="${CSS.escape(size)}"]`
        );

        if (output) {
            output.textContent =
                safeQuantity;
        }

        output
            ?.closest('.detail-size-card')
            ?.classList.toggle(
                'selected',
                safeQuantity > 0
            );

        if (sizeError) {
            sizeError.textContent = '';
        }

        updateSummary();
    }

    document.addEventListener(
        'click',
        function (event) {
            const increaseButton =
                event.target.closest(
                    '[data-detail-size-increase]'
                );

            if (increaseButton) {
                const size =
                    increaseButton.dataset
                        .detailSizeIncrease;

                const stock = Number(
                    increaseButton.dataset
                        .detailSizeStock || 0
                );

                const current = Number(
                    getActiveColorSelection()
                        ?.sizes.get(size)
                        ?.quantity || 0
                );

                if (current >= stock) {
                    showCartMessage(
                        `Only ${stock} item(s) available for ${size}.`,
                        'error'
                    );

                    return;
                }

                updateSizeQuantity(
                    size,
                    current + 1,
                    stock
                );

                return;
            }

            const decreaseButton =
                event.target.closest(
                    '[data-detail-size-decrease]'
                );

            if (decreaseButton) {
                const size =
                    decreaseButton.dataset
                        .detailSizeDecrease;

                const stock = Number(
                    decreaseButton.dataset
                        .detailSizeStock || 0
                );

                const current = Number(
                    getActiveColorSelection()
                        ?.sizes.get(size)
                        ?.quantity || 0
                );

                updateSizeQuantity(
                    size,
                    current - 1,
                    stock
                );
            }
        }
    );

    function validateSelection() {
        if (colorError) {
            colorError.textContent = '';
        }

        if (sizeError) {
            sizeError.textContent = '';
        }

        if (!selectedColor) {
            const colorMessage =
                'Please select color first.';

            if (colorError) {
                colorError.textContent =
                    colorMessage;
            }

            showCartMessage(
                colorMessage,
                'error'
            );

            return false;
        }

        if (!getSelectedItems().length) {
            const sizeMessage =
                'Please choose your desired size.';

            if (sizeError) {
                sizeError.textContent =
                    sizeMessage;
            }

            showCartMessage(
                sizeMessage,
                'error'
            );

            return false;
        }

        return true;
    }

    async function submitCart(action) {
        if (!validateSelection()) {
            return;
        }

        const activeButton =
            action === 'buy_now'
                ? buyNowButton
                : addToCartButton;

        if (activeButton) {
            activeButton.disabled = true;

            activeButton.textContent =
                action === 'buy_now'
                    ? 'Preparing Checkout...'
                    : 'Adding...';
        }

        try {
            const groupedSelections =
                getGroupedSelections();

            let latestCart = null;
            let latestMessage = '';

            for (const group of groupedSelections) {
                const response = await fetch(
                    '/cart',
                    {
                        method: 'POST',

                        headers: {
                            Accept:
                                'application/json',

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken || '',
                        },

                        body: JSON.stringify({
                            brand_id:
                                {{ (int) $brand->id }},

                            product_id:
                                {{ (int) $product->id }},

                            color:
                                group.color,
                            color_hex:
                                group.colorHex,

                            items:
                                group.items.map(
                                    function (item) {
                                        return {
                                            size: item.size,
                                            quantity: item.quantity,
                                        };
                                    }
                                ),
                        }),
                    }
                );

                const data =
                    await response.json();

                if (!response.ok) {
                    const validationMessage =
                        data?.errors
                            ? Object.values(
                                data.errors
                            ).flat()[0]
                            : null;

                    throw new Error(
                        validationMessage
                        || data.message
                        || 'Could not add product to cart.'
                    );
                }

                latestCart = data.cart;
                latestMessage = String(
                    data?.message || ''
                ).trim();
            }

            window.dispatchEvent(
                new CustomEvent(
                    'storefront:cart-updated',
                    {
                        detail: {
                            cart: latestCart,
                        },
                    }
                )
            );

            if (action === 'buy_now') {
                showCartMessage(
                    latestMessage
                    || 'Proceeding to checkout.',
                    'success'
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'storefront:buy-now',
                        {
                            detail: {
                                cart: latestCart,
                                product_id:
                                    {{ (int) $product->id }},
                                selections:
                                    groupedSelections,
                            },
                        }
                    )
                );
            } else {
                showCartMessage(
                    latestMessage
                    || 'Selected items added to cart.',
                    'success'
                );

                document
                    .getElementById(
                        'floatingCartButton'
                    )
                    ?.click();
            }
        } catch (error) {
            if (generalError) {
                generalError.textContent =
                    error.message;
            }

            showCartMessage(
                error.message,
                'error'
            );
        } finally {
            if (addToCartButton) {
                addToCartButton.disabled = false;
                addToCartButton.textContent =
                    'Add Selected Items to Cart';
            }

            if (buyNowButton) {
                buyNowButton.disabled = false;
                buyNowButton.textContent =
                    'Proceed to Checkout';
            }

            updateSummary();
        }
    }

    addToCartButton?.addEventListener(
        'click',
        function () {
            submitCart('cart');
        }
    );

    buyNowButton?.addEventListener(
        'click',
        function () {
            submitCart('buy_now');
        }
    );

    const mobileInfoSwitch =
        document.getElementById(
            'productMobileInfoSwitch'
        );

    const mobileInfoButtons = Array.from(
        mobileInfoSwitch?.querySelectorAll(
            '[data-mobile-info-target]'
        ) || []
    );

    const mobileInfoPanels = Array.from(
        document.querySelectorAll(
            '[data-mobile-info-panel]'
        )
    );

    function setMobileInfoPanel(target) {
        mobileInfoButtons.forEach(function (button) {
            const active =
                button.dataset.mobileInfoTarget
                === target;

            button.classList.toggle(
                'active',
                active
            );

            button.setAttribute(
                'aria-pressed',
                active ? 'true' : 'false'
            );
        });

        mobileInfoPanels.forEach(function (panel) {
            const matches =
                panel.dataset.mobileInfoPanel
                === target;

            panel.classList.toggle(
                'mobile-hidden',
                window.innerWidth <= 600
                && !matches
            );
        });
    }

    mobileInfoButtons.forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                setMobileInfoPanel(
                    button.dataset.mobileInfoTarget
                    || 'description'
                );
            }
        );
    });

    if (mobileInfoButtons.length) {
        setMobileInfoPanel('description');

        window.addEventListener(
            'resize',
            function () {
                const activeTarget =
                    mobileInfoButtons.find(function (
                        button
                    ) {
                        return button.classList.contains(
                            'active'
                        );
                    })?.dataset.mobileInfoTarget
                    || 'description';

                setMobileInfoPanel(
                    activeTarget
                );
            }
        );
    }

    renderColors();

    /*
    |--------------------------------------------------------------------------
    | Keyboard
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeStoreSearch();
                closeLightbox();

                navigation?.classList
                    .remove('open');
                closeMobileNavigation();

                brandSwitchDropdown
                    ?.classList.remove('open');
            }

            if (
                event.key === 'ArrowLeft'
                && thumbnails.length > 1
                && lightbox?.classList
                    .contains('open')
            ) {
                showProductImage(
                    activeImageIndex - 1
                );
            }

            if (
                event.key === 'ArrowRight'
                && thumbnails.length > 1
                && lightbox?.classList
                    .contains('open')
            ) {
                showProductImage(
                    activeImageIndex + 1
                );
            }
        }
    );
});
</script>
</body>
</html>
