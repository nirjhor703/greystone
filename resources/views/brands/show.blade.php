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
        content="{{ $brand->meta_description ?? 'Shop products from '.$brand->name }}"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $brand->meta_title ?? $brand->name }}
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
    </style>
</head>

<body>
@php
    $brandSlug = $brand->slug;
    $isPinkTouch = $brandSlug === 'pink-touch';
    $brandLogo = $brand->mobile_logo ?: $brand->logo;
    $storeUser = auth()->user();
    $brandOrder = ['grey-stone' => 1, 'blue-shades' => 2, 'pink-touch' => 3];
    $switchBrands = $brands
        ->reject(fn ($switchBrand) => $switchBrand->id === $brand->id)
        ->sortBy(fn ($switchBrand) => $brandOrder[$switchBrand->slug] ?? 99)
        ->take(2);
    $offerBanners = collect($brand->offer_banners ?? [])->filter();
    $posterRatio = '16:7';
    $posterPixelGuide = '1600 x 700 px';
    $defaultAudience = $selectedAudience ?? 'men';
    $activeCategoryKey = $selectedCategoryKey ?? '';
    $activeCategoryName = $selectedCategory?->name ?? '';
    $productsPagination = $products ?? null;
    $searchProductsCollection = $searchProducts ?? collect();
    $searchMaxPrice = max(
        1000,
        (int) ceil(
            $searchProductsCollection
                ->map(fn ($product) => (float) ($product->sale_price ?: $product->regular_price))
                ->max()
            ?: 1000
        )
    );
@endphp

<div class="storefront storefront-{{ $brandSlug }}">
    <div
        class="store-offer-strip"
        id="storePromoStrip"
        data-active-slide="0"
    >
        <button
            type="button"
            data-store-promo-prev
            aria-label="Previous offer"
        >
            ‹
        </button>

        <div class="store-promo-window">
            <div class="store-promo-track" id="storePromoTrack">
                <div class="store-promo-slide">
                    <span id="storePromoText">
                        Free Shipping & More Offers
                    </span>
                </div>

                <div class="store-promo-slide store-promo-coupon-slide">
                    <strong
                        class="store-promo-code"
                        id="storePromoCode"
                        hidden
                    ></strong>

                    <span
                        class="store-promo-timer"
                        id="storePromoTimer"
                        hidden
                    ></span>

                    <button
                        type="button"
                        class="store-promo-apply"
                        id="storePromoApply"
                        hidden
                    >
                        Apply code
                    </button>
                </div>
            </div>
        </div>

        <button
            type="button"
            data-store-promo-next
            aria-label="Next offer"
        >
            ›
        </button>
    </div>

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
                        {{ strtoupper(substr($brand->name, 0, 2)) }}
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

            <nav class="store-nav" id="storeNavigation">
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
                                href="#featured-products"
                                class="store-nav-link"
                            >
                                <i class="fa-solid fa-star"></i>
                                <span>Featured</span>
                            </a>
                        @endif

                        @if (($newArrivalProducts ?? collect())->isNotEmpty())
                            <a
                                href="#new-arrivals"
                                class="store-nav-link"
                            >
                                <i class="fa-solid fa-bolt"></i>
                                <span>New Arrivals</span>
                            </a>
                        @endif

                        <a
                            href="#categories"
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
                                        <button
                                            type="button"
                                            class="store-nav-chip"
                                            data-nav-audience="men"
                                        >
                                            Men
                                        </button>

                                        <button
                                            type="button"
                                            class="store-nav-chip"
                                            data-nav-audience="women"
                                        >
                                            Women
                                        </button>
                                    </div>
                                </div>

                                <div class="store-nav-subgroup">
                                    <span class="store-nav-subtitle">Category</span>

                                    <div class="store-nav-chip-row scrollable">
                                        @foreach ($categories as $category)
                                            <button
                                                type="button"
                                                class="store-nav-chip"
                                                data-nav-category="{{ \Illuminate\Support\Str::slug($category->slug ?: $category->name) }}"
                                            >
                                                {{ $category->name }}
                                            </button>
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
                            href="#contact"
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
                @foreach ($searchProductsCollection as $product)
                    @php
                        $searchImage = $product->primaryImage
                            ?? $product->images->first();
                        $searchBrand = $product->brand ?? $brand;

                        $searchPrice = !is_null($product->sale_price)
                            ? (float) $product->sale_price
                            : (float) $product->regular_price;
                        $searchAudience = $product->audience ?: 'both';
                        $searchCategoryName = $product->category?->name ?? 'Product';
                        $searchCategorySlug = $product->category?->slug ?? '';
                        $searchCategoryKey = \Illuminate\Support\Str::slug(
                            $searchCategorySlug ?: $searchCategoryName
                        );
                        $searchTags = collect([
                            $product->is_featured ? 'featured' : null,
                            $product->is_new_arrival ? 'new' : null,
                            $product->isOnSale() ? 'sale' : null,
                            $searchAudience,
                            $searchAudience === 'both' ? 'men women' : null,
                        ])->filter()->implode(' ');
                    @endphp

                    <article
                        class="store-search-result"
                        data-search-item
                        data-search-text="{{ strtolower($product->name.' '.$product->product_code.' '.$searchCategoryName.' '.$searchCategorySlug.' '.$searchTags) }}"
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
                                'productSlug' => $product->slug,
                            ]) }}"
                            class="store-search-main"
                        >
                            <span>
                                @if ($searchImage)
                                    <img
                                        src="{{ Storage::url($searchImage->image) }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                    >
                                @else
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                @endif
                            </span>

                            <strong>{{ $product->name }}</strong>

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
                                    data-product-id="{{ $product->id }}"
                                    data-product-name="{{ $product->name }}"
                                    data-product-url="{{ route('products.show', [
                                        'brandSlug' => $searchBrand->slug,
                                        'productSlug' => $product->slug,
                                    ]) }}"
                                    data-product-image="{{ $searchImage ? Storage::url($searchImage->image) : '' }}"
                                    data-product-category="{{ $searchCategoryName }}"
                                    data-product-price="{{ $searchPrice }}"
                                    data-product-brand-name="{{ $searchBrand->name }}"
                                    data-product-brand-slug="{{ $searchBrand->slug }}"
                                    aria-label="Add {{ $product->name }} to wishlist"
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
        {{-- Mobile-first launch surface --}}
        <section class="store-launch">
            <div class="store-container">
                <div class="brand-split-showcase" aria-label="Brand showcase">
                    @foreach ($switchBrands as $switchBrand)
                        <a
                            href="{{ route('brand.show', $switchBrand->slug) }}"
                            class="brand-split-card brand-split-card-{{ $switchBrand->slug }}"
                        >
                            <span class="brand-split-glow"></span>

                            @if ($switchBrand->logo || $switchBrand->mobile_logo)
                                <img
                                    src="{{ Storage::url($switchBrand->mobile_logo ?: $switchBrand->logo) }}"
                                    alt="{{ $switchBrand->name }}"
                                    loading="lazy"
                                >
                            @endif

                            <strong>{{ $switchBrand->name }}</strong>
                        </a>
                    @endforeach
                </div>

                <section
                    class="store-poster-carousel"
                    aria-label="Offer banners"
                    data-poster-ratio="{{ $posterRatio }}"
                    data-poster-size="{{ $posterPixelGuide }}"
                >
                    <div class="store-poster-strip" data-offer-slider>
                        @forelse ($offerBanners as $banner)
                            <article class="store-poster-card image-card">
                                <img
                                    src="{{ Storage::url($banner) }}"
                                    alt="{{ $brand->name }} offer banner"
                                    loading="lazy"
                                >
                            </article>
                        @empty
                            <article class="store-poster-card">
                                <div>
                                    <span>Featured Items</span>
                                    <strong>50% OFF</strong>
                                    <small>Canva poster slot · {{ $posterPixelGuide }}</small>
                                </div>
                            </article>

                            <article class="store-poster-card ghost">
                                <div>
                                    <span>New Drop</span>
                                    <strong>Fresh Picks</strong>
                                    <small>Upload brand offer banners from admin.</small>
                                </div>
                            </article>
                        @endforelse
                    </div>

                    <button
                        type="button"
                        class="store-poster-arrow"
                        data-offer-next
                        aria-label="Next offer banner"
                    >
                        →
                    </button>
                </section>

            </div>
        </section>

        {{-- New Arrivals --}}
        @if (($newArrivalProducts ?? collect())->isNotEmpty())
            <section
                class="store-new-arrival-strip-section"
                id="new-arrivals"
            >
                <div class="store-container">
                    <div class="store-section-heading store-compact-heading">
                        <div>
                            <h2>New Arrivals</h2>
                        </div>
                    </div>

                    <div class="store-new-arrival-viewport">
                        <div class="store-new-arrival-strip">
                            @foreach ($newArrivalProducts->concat($newArrivalProducts) as $product)
                                @php
                                    $newArrivalImage = $product->primaryImage
                                        ?? $product->images->first();

                                    $newArrivalUrl = route('products.show', [
                                        'brandSlug' => $brand->slug,
                                        'productSlug' => $product->slug,
                                    ]);
                                @endphp

                                <a
                                    href="{{ $newArrivalUrl }}"
                                    class="store-new-arrival-card"
                                >
                                    <span class="store-new-arrival-image">
                                        @if ($newArrivalImage)
                                            <img
                                                src="{{ Storage::url($newArrivalImage->image) }}"
                                                alt="{{ $product->name }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <span class="store-new-arrival-fallback">
                                                {{
                                                    mb_strtoupper(
                                                        mb_substr(
                                                            $product->name,
                                                            0,
                                                            1
                                                        )
                                                    )
                                                }}
                                            </span>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Categories --}}
        <section
            class="store-category-section"
            id="categories"
        >
            <div class="store-container">
                <div class="store-section-heading">
                    <div>
                        <h2>Browse by Category</h2>
                    </div>
                </div>

                <div class="store-category-control-row">
                    <div
                        class="store-audience-toggle"
                        id="storeAudienceToggle"
                        data-active-audience="{{ $defaultAudience }}"
                        aria-label="Choose product audience"
                    >
                        <span
                            class="store-audience-toggle-thumb"
                            aria-hidden="true"
                        ></span>

                        <button
                            type="button"
                            class="{{ $defaultAudience === 'men' ? 'active' : '' }}"
                            data-audience-filter="men"
                            aria-pressed="{{ $defaultAudience === 'men' ? 'true' : 'false' }}"
                        >
                            Men
                        </button>

                        <button
                            type="button"
                            class="{{ $defaultAudience === 'women' ? 'active' : '' }}"
                            data-audience-filter="women"
                            aria-pressed="{{ $defaultAudience === 'women' ? 'true' : 'false' }}"
                        >
                            Women
                        </button>
                    </div>
                </div>

                <div class="store-category-sticky-shell">
                    <div class="store-category-grid store-category-wheel">
                        <a
                            href="{{ route('brand.show', array_filter([
                                'slug' => $brand->slug,
                                'audience' => $defaultAudience,
                            ])) }}#products"
                            data-category-id=""
                            data-category-slug=""
                            data-category-key=""
                            data-category-name="All Products"
                            aria-pressed="{{ $activeCategoryKey === '' ? 'true' : 'false' }}"
                            @class([
                                'store-category-card',
                                'js-category-filter',
                                'active' => $activeCategoryKey === '',
                            ])
                            onclick="return window.storefrontCategoryFilterActivate ? window.storefrontCategoryFilterActivate(this, event) : true;"
                        >
                            <div class="store-category-fallback store-category-fallback-all">
                                ALL
                            </div>

                            <div class="store-category-content">
                                <h3>All Products</h3>
                            </div>
                        </a>

                        @forelse ($categories as $category)
                            @php
                                $categoryKey = \Illuminate\Support\Str::slug(
                                    $category->slug ?: $category->name
                                );
                            @endphp

                            <a
                                href="{{ route('brand.show', array_filter([
                                    'slug' => $brand->slug,
                                    'audience' => $defaultAudience,
                                    'category' => $categoryKey ?: null,
                                ])) }}#products"
                                data-category-id="{{ $category->id }}"
                                data-category-slug="{{ $category->slug }}"
                                data-category-key="{{ $categoryKey }}"
                                data-category-name="{{ $category->name }}"
                                aria-pressed="{{ $activeCategoryKey === $categoryKey ? 'true' : 'false' }}"
                                @class([
                                    'store-category-card',
                                    'js-category-filter',
                                    'active' => $activeCategoryKey === $categoryKey,
                                ])
                                onclick="return window.storefrontCategoryFilterActivate ? window.storefrontCategoryFilterActivate(this, event) : true;"
                            >
                                @if ($category->image)
                                    <div class="store-category-image">
                                        <img
                                            src="{{ Storage::url($category->image) }}"
                                            alt="{{ $category->name }}"
                                            loading="lazy"
                                        >
                                    </div>
                                @else
                                    <div class="store-category-fallback">
                                        {{ strtoupper(substr($category->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="store-category-content">
                                    <h3>{{ $category->name }}</h3>
                                </div>
                            </a>
                        @empty
                            <div class="store-empty-state">
                                <div>
                                    <div class="store-empty-icon">
                                        ◇
                                    </div>

                                    <h3>No categories available yet</h3>

                                    <p>
                                        Add an active category for
                                        {{ $brand->name }} from the admin dashboard.
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- All Products --}}
        <section
            class="store-product-section"
            id="products"
        >
            <div class="store-container">
                <div class="store-section-heading">
                    <div>
                        <h2 id="storeProductsTitle">
                            {{ $activeCategoryName ?: 'All Products' }}
                        </h2>
                    </div>

                    <p
                        id="storeProductsDescription"
                        @if ($activeCategoryName) hidden @endif
                    >
                        {{ $defaultAudience === 'women' ? 'Women' : 'Men' }} products
                    </p>
                </div>

                <div
                    class="store-product-grid"
                    id="storeProductGrid"
                >
                    @forelse (($productsPagination?->items() ?? []) as $product)
                        @include(
                            'brands.partials.product-card',
                            [
                                'product' => $product,
                                'brand' => $brand,
                            ]
                        )
                    @empty
                        <div class="store-empty-state">
                            <div>
                                <div class="store-empty-icon">
                                    □
                                </div>

                                <h3>No products available yet</h3>

                                <p>
                                    Add an active product for
                                    {{ $brand->name }}
                                    from the admin dashboard.
                                </p>
                            </div>
                        </div>
                    @endforelse

                    <div
                        class="store-related-divider"
                        id="crossBrandProductsDivider"
                        hidden
                    >
                        <span>Want more like this</span>
                    </div>
                </div>

                <div
                    class="category-product-empty"
                    id="categoryProductEmpty"
                    hidden
                >
                    <div class="store-empty-icon">
                        □
                    </div>

                    <h3>
                        No products found
                    </h3>

                    <p id="categoryProductEmptyText">
                        No products are available in this category.
                    </p>
                </div>

                @if ($productsPagination && $productsPagination->hasPages())
                    @php
                        $paginationLinks = $productsPagination->linkCollection();
                        $previousPageUrl = $productsPagination->previousPageUrl();
                        $nextPageUrl = $productsPagination->nextPageUrl();
                    @endphp

                    <div class="store-pagination-wrap" id="storePaginationWrap">
                        <div class="store-pagination-summary">
                            <span>
                                Page {{ $productsPagination->currentPage() }}
                                of {{ $productsPagination->lastPage() }}
                            </span>

                            <strong>
                                {{ $productsPagination->total() }} products
                            </strong>
                        </div>

                        <nav
                            class="store-pagination"
                            aria-label="Product pages"
                        >
                            <a
                                href="{{ $previousPageUrl ? $previousPageUrl.'#products' : '#' }}"
                                class="store-pagination-arrow{{ $productsPagination->onFirstPage() ? ' is-disabled' : '' }}"
                                aria-label="Previous page"
                                @if ($productsPagination->onFirstPage()) aria-disabled="true" tabindex="-1" @endif
                            >
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>

                            <div class="store-pagination-pages">
                                @foreach ($paginationLinks as $link)
                                    @continue(in_array($link['label'], ['&laquo; Previous', 'Next &raquo;'], true))

                                    @if ($link['url'])
                                        <a
                                            href="{{ $link['url'] }}#products"
                                            class="store-pagination-page{{ $link['active'] ? ' is-active' : '' }}"
                                            @if ($link['active']) aria-current="page" @endif
                                        >
                                            {{ html_entity_decode(strip_tags($link['label'])) }}
                                        </a>
                                    @else
                                        <span class="store-pagination-gap">
                                            {{ html_entity_decode(strip_tags($link['label'])) }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>

                            <a
                                href="{{ $nextPageUrl ? $nextPageUrl.'#products' : '#' }}"
                                class="store-pagination-arrow{{ $nextPageUrl ? '' : ' is-disabled' }}"
                                aria-label="Next page"
                                @if (!$nextPageUrl) aria-disabled="true" tabindex="-1" @endif
                            >
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </nav>
                    </div>
                @endif
            </div>
        </section>

        {{-- Legacy hero kept for desktop rhythm --}}
        <section class="store-hero">
            <div class="store-container">
                <div class="store-hero-grid">
                    <div>
                        <span class="store-eyebrow">
                            Smart fashion shopping
                        </span>

                        <h1 class="store-hero-title">
                            Discover your style
                            <span>with {{ $brand->name }}</span>
                        </h1>

                        <p class="store-hero-description">
                            Explore carefully selected fashion products,
                            everyday essentials and new collections from
                            {{ $brand->name }}.
                        </p>

                        <div class="store-hero-actions">
                            <a
                                href="#categories"
                                class="store-primary-button"
                            >
                                Explore Categories →
                            </a>

                            <a
                                href="#products"
                                class="store-secondary-button"
                            >
                                View Products
                            </a>
                        </div>

                        <div class="store-features">
                            <div class="store-feature-item">
                                <strong>Easy</strong>
                                <span>Simple ordering</span>
                            </div>

                            <div class="store-feature-item">
                                <strong>Fresh</strong>
                                <span>Latest collections</span>
                            </div>

                            <div class="store-feature-item">
                                <strong>Trusted</strong>
                                <span>Reliable support</span>
                            </div>
                        </div>
                    </div>

                    <div class="store-visual-card">
                        <div class="store-visual-content">
                            @if ($brand->logo)
                                <img
                                    src="{{ Storage::url($brand->logo) }}"
                                    alt="{{ $brand->name }}"
                                    class="store-visual-logo"
                                >
                            @else
                                <div class="store-visual-letter">
                                    {{ strtoupper(substr($brand->name, 0, 1)) }}
                                </div>
                            @endif

                            <strong>{{ $brand->name }}</strong>

                            <span>
                                Style made for you
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Featured Products --}}
        @if (($featuredProducts ?? collect())->isNotEmpty())
            <section
                class="store-product-section store-featured-product-section"
                id="featured-products"
            >
                <div class="store-container">
                    <div class="store-section-heading">
                        <div>
                            <h2>Featured Product</h2>
                        </div>
                    </div>

                    <div class="store-featured-slider-shell">
                        <button
                            type="button"
                            class="store-featured-slider-arrow prev"
                            data-featured-slider-prev
                            aria-label="Previous featured products"
                        >
                            <i class="fa-solid fa-angle-left"></i>
                        </button>

                        <div
                            class="store-featured-slider"
                            id="storeFeaturedSlider"
                        >
                            <div class="store-featured-slider-track store-product-grid store-featured-product-grid">
                                @foreach ($featuredProducts as $product)
                                    <div class="store-featured-slide">
                                        @include(
                                            'brands.partials.product-card',
                                            [
                                                'product' => $product,
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
                            data-featured-slider-next
                            aria-label="Next featured products"
                        >
                            <i class="fa-solid fa-angle-right"></i>
                        </button>
                    </div>
                </div>
            </section>
        @endif

    </main>

    @include('storefront.partials.sweet-cool-section', ['brand' => $brand])
    @include('storefront.partials.store-footer', ['brand' => $brand])

    <nav
        class="store-bottom-dock"
        id="storeBottomDock"
        aria-label="Quick store actions"
    >
        <a
            href="{{ route('brand.show', $brand->slug) }}"
            class="store-bottom-dock-action"
            data-dock-key="home"
            data-dock-action
            aria-label="Home"
        >
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>

        <button
            type="button"
            id="storeDockCartButton"
            class="store-bottom-dock-action"
            data-dock-key="cart"
            data-dock-action
            aria-label="Open cart"
        >
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Cart</span>
            <strong id="storeDockCartCount">0</strong>
        </button>

        <button
            type="button"
            id="storeDockSearchButton"
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
            id="storeDockBackButton"
            class="store-bottom-dock-action"
            data-dock-key="back"
            data-dock-action
            aria-label="Go back"
            data-previous-url="{{ $productsPagination?->previousPageUrl() ? $productsPagination->previousPageUrl().'#products' : '' }}"
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuButton = document.getElementById(
            'storeMobileMenuButton'
        );

        const navigation = document.getElementById(
            'storeNavigation'
        );

        const brandSwitchButton = document.getElementById(
            'brandSwitchButton'
        );

        const brandSwitchDropdown = document.getElementById(
            'brandSwitchDropdown'
        );

        const storeBottomDock = document.getElementById(
            'storeBottomDock'
        );

        const cartDrawerWrapper = document.getElementById(
            'cartDrawerWrapper'
        );

        const dockBackButton = document.getElementById(
            'storeDockBackButton'
        );

        const dockCartButton = document.getElementById(
            'storeDockCartButton'
        );

        const dockSearchButton = document.getElementById(
            'storeDockSearchButton'
        );

        const dockCartCount = document.getElementById(
            'storeDockCartCount'
        );

        const dockActions = Array.from(
            document.querySelectorAll('[data-dock-action]')
        );
        let persistentDockAction = 'home';
        let dockScrollMotionTimer = null;
        let lastDockScrollY = window.scrollY || 0;

        const storeSearchButton = document.getElementById(
            'storeSearchButton'
        );

        const storeSearchOverlay = document.getElementById(
            'storeSearchOverlay'
        );

        const storeSearchClose = document.getElementById(
            'storeSearchClose'
        );

        const storeSearchInput = document.getElementById(
            'storeSearchInput'
        );

        const storeSearchFilterToggle = document.getElementById(
            'storeSearchFilterToggle'
        );

        const storeSearchFilterPanel = document.getElementById(
            'storeSearchFilterPanel'
        );

        const storeSearchMinRange = document.getElementById(
            'storeSearchMinRange'
        );

        const storeSearchMaxRange = document.getElementById(
            'storeSearchMaxRange'
        );

        const storeSearchMinPrice = document.getElementById(
            'storeSearchMinPrice'
        );

        const storeSearchMaxPrice = document.getElementById(
            'storeSearchMaxPrice'
        );

        const storeSearchMinBubble = document.getElementById(
            'storeSearchMinBubble'
        );

        const storeSearchMaxBubble = document.getElementById(
            'storeSearchMaxBubble'
        );

        const storePriceRangeTrack = document.querySelector(
            '.store-price-range-track'
        );

        const storeSearchCount = document.getElementById(
            'storeSearchCount'
        );

        const storeSearchResults = document.getElementById(
            'storeSearchResults'
        );

        const storeSearchRelatedDivider = document.getElementById(
            'storeSearchRelatedDivider'
        );

        const storeSearchReset = document.getElementById(
            'storeSearchReset'
        );

        const storeSearchEmpty = document.getElementById(
            'storeSearchEmpty'
        );

        const storeHeaderCartButton = document.getElementById(
            'storeHeaderCartButton'
        );

        const floatingCartButton = document.getElementById(
            'floatingCartButton'
        );

        const floatingCartCount = document.getElementById(
            'floatingCartCount'
        );

        const newArrivalViewport = document.querySelector(
            '.store-new-arrival-viewport'
        );

        const categoryStickyShell = document.querySelector(
            '.store-category-sticky-shell'
        );

        const featuredSlider = document.getElementById(
            'storeFeaturedSlider'
        );

        const featuredSliderPrev = document.querySelector(
            '[data-featured-slider-prev]'
        );

        const featuredSliderNext = document.querySelector(
            '[data-featured-slider-next]'
        );

        const productGrid = document.getElementById(
            'storeProductGrid'
        );

        const menuCategoryButtons = Array.from(
            document.querySelectorAll('[data-nav-category]')
        );

        const menuAudienceButtons = Array.from(
            document.querySelectorAll('[data-nav-audience]')
        );

        let newArrivalFrame = null;
        let newArrivalResumeTimer = null;
        let newArrivalLastTime = 0;
        let newArrivalPaused = false;
        let newArrivalPointerDown = false;
        let activeSearchAudience = 'all';
        let activeSearchCategory = 'all';
        let activeSearchTag = 'all';
        const searchPriceLimit = Number(
            storeSearchMaxRange?.max || 0
        );

        function getNewArrivalLoopPoint() {
            return newArrivalViewport
                ? newArrivalViewport.scrollWidth / 2
                : 0;
        }

        function keepNewArrivalLoopClean() {
            const loopPoint = getNewArrivalLoopPoint();

            if (!newArrivalViewport || loopPoint <= 0) {
                return;
            }

            if (newArrivalViewport.scrollLeft >= loopPoint) {
                newArrivalViewport.scrollLeft -= loopPoint;
            }

            if (newArrivalViewport.scrollLeft < 0) {
                newArrivalViewport.scrollLeft += loopPoint;
            }
        }

        function pauseNewArrivalAutoMove() {
            newArrivalPaused = true;
            window.clearTimeout(newArrivalResumeTimer);
        }

        function resumeNewArrivalAutoMove(delay = 180) {
            window.clearTimeout(newArrivalResumeTimer);
            newArrivalResumeTimer = window.setTimeout(
                function () {
                    if (newArrivalPointerDown) {
                        return;
                    }

                    keepNewArrivalLoopClean();
                    newArrivalLastTime = 0;
                    newArrivalPaused = false;
                },
                delay
            );
        }

        function runNewArrivalAutoMove(timestamp) {
            if (newArrivalViewport && !newArrivalPaused) {
                if (!newArrivalLastTime) {
                    newArrivalLastTime = timestamp;
                }

                const elapsed = Math.min(
                    timestamp - newArrivalLastTime,
                    40
                );

                newArrivalViewport.scrollLeft += elapsed * 0.075;
                newArrivalLastTime = timestamp;
                keepNewArrivalLoopClean();
            }

            newArrivalFrame = window.requestAnimationFrame(
                runNewArrivalAutoMove
            );
        }

        if (newArrivalViewport) {
            newArrivalViewport.scrollLeft = 0;

            [
                'pointerdown',
                'touchstart',
            ].forEach(function (eventName) {
                newArrivalViewport.addEventListener(
                    eventName,
                    function () {
                        newArrivalPointerDown = true;
                        pauseNewArrivalAutoMove();
                    },
                    { passive: true }
                );
            });

            [
                'pointerup',
                'pointercancel',
                'touchend',
                'mouseleave',
            ].forEach(function (eventName) {
                newArrivalViewport.addEventListener(
                    eventName,
                    function () {
                        newArrivalPointerDown = false;
                        resumeNewArrivalAutoMove(180);
                    },
                    { passive: true }
                );
            });

            [
                'wheel',
            ].forEach(function (eventName) {
                newArrivalViewport.addEventListener(
                    eventName,
                    function () {
                        pauseNewArrivalAutoMove();
                        resumeNewArrivalAutoMove(800);
                    },
                    { passive: true }
                );
            });

            document.addEventListener(
                'visibilitychange',
                function () {
                    newArrivalPaused = document.hidden;
                    newArrivalLastTime = 0;
                }
            );

            newArrivalFrame = window.requestAnimationFrame(
                runNewArrivalAutoMove
            );
        }

        function openStoreSearch() {
            storeSearchOverlay?.classList.add('open');
            storeSearchOverlay?.setAttribute('aria-hidden', 'false');
            syncSearchPriceUi();
            setActiveDockAction('search');

            window.setTimeout(function () {
                storeSearchInput?.focus();
            }, 80);
        }

        function closeStoreSearch() {
            storeSearchOverlay?.classList.remove('open');
            storeSearchOverlay?.setAttribute('aria-hidden', 'true');
            if (storeSearchInput) {
                storeSearchInput.value = '';
            }

            resetStoreSearchFilters();

            if (storeSearchFilterPanel) {
                storeSearchFilterPanel.hidden = true;
            }

            storeSearchFilterToggle?.classList.remove('active');
            storeSearchFilterToggle?.setAttribute(
                'aria-expanded',
                'false'
            );

            persistentDockAction = 'home';

            if (
                !cartDrawerWrapper?.classList.contains('open')
            ) {
                setActiveDockAction('home');
            }
        }

        function setActiveDockAction(key) {
            dockActions.forEach(function (action) {
                action.classList.toggle(
                    'is-active',
                    action.dataset.dockKey === (key || '')
                );
            });
        }

        function formatSearchPrice(value) {
            return `৳${Number(value || 0).toLocaleString()}`;
        }

        function syncSearchPriceUi(source = null) {
            let minValue = Number(
                (source === 'input'
                    ? storeSearchMinPrice?.value
                    : storeSearchMinRange?.value)
                || 0
            );

            let maxValue = Number(
                (source === 'input'
                    ? storeSearchMaxPrice?.value
                    : storeSearchMaxRange?.value)
                || searchPriceLimit
            );

            if (minValue > maxValue) {
                if (
                    source === 'max'
                    || source === 'max-input'
                ) {
                    minValue = maxValue;
                } else {
                    maxValue = minValue;
                }
            }

            minValue = Math.max(
                0,
                Math.min(minValue, searchPriceLimit)
            );

            maxValue = Math.max(
                0,
                Math.min(maxValue, searchPriceLimit)
            );

            if (storeSearchMinRange) {
                storeSearchMinRange.value = String(minValue);
            }

            if (storeSearchMaxRange) {
                storeSearchMaxRange.value = String(maxValue);
            }

            if (storeSearchMinPrice) {
                storeSearchMinPrice.value = String(minValue);
            }

            if (storeSearchMaxPrice) {
                storeSearchMaxPrice.value = String(maxValue);
            }

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
            if (activeSearchAudience === 'all') {
                return true;
            }

            return itemAudience === activeSearchAudience
                || itemAudience === 'both';
        }

        function searchTagMatches(tags) {
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
            activeSearchAudience = 'all';
            activeSearchCategory = 'all';
            activeSearchTag = 'all';

            if (storeSearchMinPrice) {
                storeSearchMinPrice.value = '0';
            }

            if (storeSearchMaxPrice) {
                storeSearchMaxPrice.value = String(
                    searchPriceLimit
                );
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

        storeSearchButton?.addEventListener(
            'click',
            openStoreSearch
        );

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

        storeSearchInput?.addEventListener(
            'input',
            applyStoreSearchFilters
        );

        storeSearchFilterToggle?.addEventListener(
            'click',
            function () {
                const willOpen =
                    storeSearchFilterPanel?.hasAttribute(
                        'hidden'
                    );

                if (storeSearchFilterPanel) {
                    storeSearchFilterPanel.hidden = !willOpen;
                }

                storeSearchFilterToggle.classList.toggle(
                    'active',
                    Boolean(willOpen)
                );

                storeSearchFilterToggle.setAttribute(
                    'aria-expanded',
                    willOpen ? 'true' : 'false'
                );

                if (willOpen) {
                    syncSearchPriceUi();
                }
            }
        );

        [
            storeSearchMinPrice,
            storeSearchMaxPrice,
        ].forEach(function (input) {
            input?.addEventListener(
                'input',
                function () {
                    syncSearchPriceUi('input');
                    applyStoreSearchFilters();
                }
            );
        });

        storeSearchMinRange?.addEventListener(
            'input',
            function () {
                syncSearchPriceUi('min');
                applyStoreSearchFilters();
            }
        );

        storeSearchMaxRange?.addEventListener(
            'input',
            function () {
                syncSearchPriceUi('max');
                applyStoreSearchFilters();
            }
        );

        [
            storeSearchMinPrice,
            storeSearchMaxPrice,
        ].forEach(function (input) {
            input?.addEventListener(
                'change',
                function () {
                    syncSearchPriceUi('input');
                    applyStoreSearchFilters();
                }
            );
        });

        storeSearchOverlay
            ?.querySelectorAll('button[data-search-audience]')
            .forEach(function (button) {
                button.addEventListener('click', function () {
                    activeSearchAudience =
                        button.dataset.searchAudience || 'all';

                    setSearchChipActive(
                        'button[data-search-audience]',
                        activeSearchAudience,
                        'searchAudience'
                    );

                    applyStoreSearchFilters();
                });
            });

        storeSearchOverlay
            ?.querySelectorAll('button[data-search-category]')
            .forEach(function (button) {
                button.addEventListener('click', function () {
                    activeSearchCategory =
                        button.dataset.searchCategory || 'all';

                    setSearchChipActive(
                        'button[data-search-category]',
                        activeSearchCategory,
                        'searchCategory'
                    );

                    applyStoreSearchFilters();
                });
            });

        storeSearchOverlay
            ?.querySelectorAll('button[data-search-tag]')
            .forEach(function (button) {
                button.addEventListener('click', function () {
                    activeSearchTag =
                        button.dataset.searchTag || 'all';

                    setSearchChipActive(
                        'button[data-search-tag]',
                        activeSearchTag,
                        'searchTag'
                    );

                    applyStoreSearchFilters();
                });
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

        storeHeaderCartButton?.addEventListener(
            'click',
            function () {
                floatingCartButton?.click();
            }
        );

        dockCartButton?.addEventListener(
            'click',
            function () {
                setActiveDockAction('cart');
                floatingCartButton?.click();
            }
        );

        dockSearchButton?.addEventListener(
            'click',
            openStoreSearch
        );

        if (
            floatingCartCount
            && dockCartCount
            && 'MutationObserver' in window
        ) {
            const syncDockCartCount = function () {
                dockCartCount.textContent =
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

        function closeMobileNavigation() {
            navigation?.classList.remove('open');
            mobileMenuButton?.classList.remove('open');
            mobileMenuButton?.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('store-mobile-nav-open');
        }

        function moveFeaturedSlider(direction) {
            if (!featuredSlider) {
                return;
            }

            const slide = featuredSlider.querySelector(
                '.store-featured-slide'
            );

            const slideWidth = slide?.getBoundingClientRect().width || 300;
            const gap = 12;

            featuredSlider.scrollBy({
                left: (slideWidth + gap) * direction,
                behavior: 'smooth',
            });
        }

        featuredSliderPrev?.addEventListener('click', function () {
            moveFeaturedSlider(-1);
        });

        featuredSliderNext?.addEventListener('click', function () {
            moveFeaturedSlider(1);
        });

        menuCategoryButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetCategoryKey =
                    button.dataset.navCategory || '';

                const targetCategoryCard = document.querySelector(
                    `.js-category-filter[data-category-key="${targetCategoryKey}"]`
                );

                closeMobileNavigation();

                if (targetCategoryCard instanceof HTMLElement) {
                    targetCategoryCard.click();
                } else {
                    document.getElementById('products')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }
            });
        });

        menuAudienceButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetAudience =
                    button.dataset.navAudience || '';

                const targetAudienceButton = document.querySelector(
                    `[data-audience-filter="${targetAudience}"]`
                );

                closeMobileNavigation();

                if (targetAudienceButton instanceof HTMLElement) {
                    targetAudienceButton.click();
                }

                document.getElementById('products')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            });
        });

        mobileMenuButton?.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();

                const isOpen = navigation?.classList.toggle('open');

                mobileMenuButton.classList.toggle('open', Boolean(isOpen));
                document.body.classList.toggle(
                    'store-mobile-nav-open',
                    Boolean(isOpen)
                );
                mobileMenuButton.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );
            }
        );

        brandSwitchButton?.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();

                const isOpen =
                    brandSwitchDropdown?.classList.toggle('open');

                brandSwitchButton.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );
            }
        );

        document.addEventListener(
            'click',
            function (event) {
                if (
                    brandSwitchDropdown &&
                    !brandSwitchDropdown.contains(event.target)
                ) {
                    brandSwitchDropdown.classList.remove('open');

                    brandSwitchButton?.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

                if (
                    navigation?.classList.contains('open')
                    && !navigation.contains(event.target)
                    && !mobileMenuButton?.contains(event.target)
                ) {
                    closeMobileNavigation();
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeMobileNavigation();
                    closeStoreSearch();
                }
            }
        );

        navigation
            ?.querySelectorAll('a, button[data-nav-category], button[data-nav-audience]')
            .forEach(function (link) {
                link.addEventListener('click', function () {
                    if (!link.closest('.store-nav-group-body')) {
                        closeMobileNavigation();
                    }
                });
            });

        function syncScrollChrome() {
            const hasScrolled = window.scrollY > 140;
            const currentScrollY = window.scrollY || 0;
            const scrollDelta = currentScrollY - lastDockScrollY;
            const dockMotionDirection = scrollDelta > 0
                ? 'up'
                : scrollDelta < 0
                    ? 'down'
                    : null;
            const productGridRect = productGrid?.getBoundingClientRect();
            const productFixedTop =
                window.innerWidth <= 820
                    ? 18
                    : 20;
            const shouldFixCategories =
                Boolean(productGridRect)
                && productGridRect.top <= productFixedTop
                && productGridRect.bottom > productFixedTop + 80;

            document.body.classList.toggle(
                'storefront-scrolled',
                hasScrolled
            );

            categoryStickyShell?.classList.toggle(
                'is-product-fixed',
                shouldFixCategories
            );

            storeBottomDock?.classList.toggle(
                'show',
                hasScrolled
            );

            if (hasScrolled && storeBottomDock?.classList.contains('show')) {
                document.body.classList.add('store-dock-scrolling');
                document.body.classList.remove(
                    'store-dock-scroll-up',
                    'store-dock-scroll-down'
                );

                if (dockMotionDirection === 'up') {
                    document.body.classList.add(
                        'store-dock-scroll-up'
                    );
                } else if (dockMotionDirection === 'down') {
                    document.body.classList.add(
                        'store-dock-scroll-down'
                    );
                }

                if (dockScrollMotionTimer) {
                    window.clearTimeout(dockScrollMotionTimer);
                }

                dockScrollMotionTimer = window.setTimeout(
                    function () {
                        document.body.classList.remove('store-dock-scrolling');
                        document.body.classList.remove(
                            'store-dock-scroll-up',
                            'store-dock-scroll-down'
                        );
                    },
                    240
                );
            } else if (!hasScrolled) {
                document.body.classList.remove('store-dock-scrolling');
                document.body.classList.remove(
                    'store-dock-scroll-up',
                    'store-dock-scroll-down'
                );
            }

            lastDockScrollY = currentScrollY;

            if (
                !storeSearchOverlay?.classList.contains('open')
                && !cartDrawerWrapper?.classList.contains('open')
            ) {
                setActiveDockAction(persistentDockAction);
            }
        }

        syncScrollChrome();

        window.addEventListener(
            'pageshow',
            function () {
                persistentDockAction = 'home';
                lastDockScrollY = window.scrollY || 0;
                document.body.classList.remove(
                    'store-dock-scroll-up',
                    'store-dock-scroll-down'
                );

                if (
                    !storeSearchOverlay?.classList.contains('open')
                    && !cartDrawerWrapper?.classList.contains('open')
                ) {
                    setActiveDockAction('home');
                }
            }
        );

        window.addEventListener(
            'scroll',
            syncScrollChrome,
            { passive: true }
        );

        dockBackButton?.addEventListener(
            'click',
            function () {
                persistentDockAction = 'home';
                setActiveDockAction('home');

                const previousUrl = String(
                    dockBackButton.dataset.previousUrl || ''
                ).trim();

                if (previousUrl) {
                    window.location.href = previousUrl;
                    return;
                }

                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location.href = '{{ route('brand.show', $brand->slug) }}#products';
            }
        );

        document.addEventListener(
            'cart-drawer:open',
            function () {
                setActiveDockAction('cart');
            }
        );

        document.addEventListener(
            'cart-drawer:close',
            function () {
                persistentDockAction = 'home';
                setActiveDockAction('home');
            }
        );

        dockActions.forEach(function (action) {
            action.addEventListener('click', function () {
                const dockKey = action.dataset.dockKey;

                if (
                    dockKey
                    && !['cart', 'search', 'back'].includes(dockKey)
                ) {
                    persistentDockAction = dockKey;
                    setActiveDockAction(dockKey);
                }
            });
        });
    });
</script>
</body>
</html>
