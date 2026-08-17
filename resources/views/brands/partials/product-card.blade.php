@php
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

    $currentBrand = $brand;
    $productBrand = $product->brand ?? $currentBrand;

    $productImage = $product->primaryImage
        ?? $product->images->first();

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

    $productUrl = route('products.show', [
        'brandSlug' => $productBrand->slug,
        'productSlug' => $product->slug,
    ]);

    $productCategoryKey = \Illuminate\Support\Str::slug(
        $product->category?->slug
        ?: $product->category?->name
        ?: 'product'
    );

    /*
    |--------------------------------------------------------------------------
    | Group active variants by color
    |--------------------------------------------------------------------------
    */

    $variantsByColor = $product->variants
        ->filter(function ($variant) {
            return (bool) $variant->status;
        })
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
                ) use ($colorVariants): array {
                    $variant = $colorVariants
                        ->first(function ($item) use ($size) {
                            return (string) $item->size
                                === $size;
                        });

                    return [
                        'size' => $size,

                        'stock' => (int) (
                            $variant?->stock_quantity
                            ?? 0
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

    /*
    |--------------------------------------------------------------------------
    | Available size preview
    |--------------------------------------------------------------------------
    */

    $availableSizeNames = $product->variants
        ->filter(function ($variant) {
            return (bool) $variant->status
                && (int) $variant->stock_quantity > 0;
        })
        ->pluck('size')
        ->filter()
        ->unique()
        ->sortBy(function ($size) {
            $position = array_search(
                $size,
                \App\Models\Product::AVAILABLE_SIZES,
                true
            );

            return $position === false
                ? 999
                : $position;
        })
        ->take(4)
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Product payload
    |--------------------------------------------------------------------------
    */

    $productPayload = [
        'brand_id' => (int) $productBrand->id,
        'product_id' => (int) $product->id,

        'category_id' => (int) $product->category_id,

        'name' => (string) $product->name,

        'product_code' => $product->product_code
            ? (string) $product->product_code
            : null,

        'price' => (float) $unitPrice,

        'regular_price' => (float) $product
            ->regular_price,

        'sale_price' => !is_null(
            $product->sale_price
        )
            ? (float) $product->sale_price
            : null,

        'stock_quantity' => (int) $product
            ->stock_quantity,

        'image_url' => $productImage
            ? Storage::url($productImage->image)
            : null,

        'variants' => $variantsByColor,
    ];
@endphp

<article
    class="store-product-card"
    data-product-category-id="{{ $product->category_id }}"
    data-product-category-key="{{ $productCategoryKey }}"
    data-product-audience="{{ $product->audience ?: 'both' }}"
    data-product-id="{{ $product->id }}"
    data-product-brand-id="{{ $productBrand->id }}"
    data-product-brand-name="{{ $productBrand->name }}"
    data-product-brand-slug="{{ $productBrand->slug }}"
    data-product-brand-priority="{{ (int) $productBrand->id === (int) $currentBrand->id ? 'primary' : 'secondary' }}"
>
    <script
        type="application/json"
        data-product-payload
    >{!! json_encode(
        $productPayload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) !!}</script>

    <a
        href="{{ $productUrl }}"
        class="store-product-image-wrapper"
    >
        <div class="store-product-top-strip">
            <div class="store-product-fire-mark">
                <img
                    src="{{ asset('images/storefront/fire-neon.png') }}"
                    alt=""
                    aria-hidden="true"
                >
            </div>

            <span class="store-product-brand-label">
                {{ $productBrand->name }}
            </span>
        </div>

        @if ($productImage)
            <img
                src="{{ Storage::url($productImage->image) }}"
                alt="{{ $product->name }}"
                class="store-product-image"
                loading="lazy"
            >
        @else
            <div class="store-product-image-fallback">
                {{
                    mb_strtoupper(
                        mb_substr(
                            $product->name,
                            0,
                            1
                        )
                    )
                }}
            </div>
        @endif

        <button
            type="button"
            class="store-wishlist-button product"
            data-wishlist-button
            data-product-id="{{ $product->id }}"
            data-product-name="{{ $product->name }}"
            data-product-url="{{ $productUrl }}"
            data-product-image="{{ $productImage ? Storage::url($productImage->image) : '' }}"
            data-product-category="{{ $product->category?->name ?? 'Product' }}"
            data-product-price="{{ $unitPrice }}"
            data-product-brand-name="{{ $productBrand->name }}"
            data-product-brand-slug="{{ $productBrand->slug }}"
            aria-label="Add {{ $product->name }} to wishlist"
        >
            <i class="fa-regular fa-heart"></i>
        </button>

        @if ((int) $product->stock_quantity <= 0)
            <div class="store-product-stock-overlay">
                Out of Stock
            </div>
        @endif

        @if ($hasSale)
            <span class="store-product-discount-notch">
                <svg
                    class="store-product-edge-frame"
                    viewBox="0 0 640 150"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                >
                    <path d="M6 56 C6 25 32 7 72 7 H568 C608 7 634 25 634 56 V66 H572 C556 66 548 80 543 101 C536 130 508 146 468 146 H172 C132 146 104 130 97 101 C92 80 84 66 68 66 H6 Z" />
                </svg>

                <span>{{ $discountPercentage }}% OFF</span>
            </span>
        @endif

    </a>

    @if ($product->is_new_arrival)
    <span class="store-product-highlight-tab is-new-arrival">
        <svg class="store-product-highlight-frame" viewBox="0 0 640 150" preserveAspectRatio="none" aria-hidden="true">
            <path d="M6 56 C6 25 32 7 72 7 H568 C608 7 634 25 634 56 V66 H572 C556 66 548 80 543 101 C536 130 508 146 468 146 H172 C132 146 104 130 97 101 C92 80 84 66 68 66 H6 Z" />
        </svg>
        <span>TRENDING</span>
    </span>
@endif

    <div class="store-product-content">
        <span class="store-product-edge-stock {{ (int) $product->stock_quantity > 0 ? 'is-in' : 'is-out' }}">
            <svg
                class="store-product-edge-frame"
                viewBox="0 0 640 150"
                preserveAspectRatio="none"
                aria-hidden="true"
            >
                <path d="M6 56 C6 25 32 7 72 7 H568 C608 7 634 25 634 56 V66 H572 C556 66 548 80 543 101 C536 130 508 146 468 146 H172 C132 146 104 130 97 101 C92 80 84 66 68 66 H6 Z" />
            </svg>

            <span>{{ (int) $product->stock_quantity > 0 ? 'In Stock' : 'Sold Out' }}</span>
        </span>

        <div class="store-product-meta-row">
            <span class="store-product-category">
                {{ $product->category?->name ?? 'Product' }}
            </span>
        </div>

        <h3>
            <a href="{{ $productUrl }}">
                {{ $product->name }}
            </a>
        </h3>

        <div class="store-product-bottom">
            <div class="store-product-price">
                @if ($hasSale)
                    <strong>
                        ৳{{ number_format(
                            (float) $product->sale_price,
                            0
                        ) }}
                    </strong>

                    <del>
                        ৳{{ number_format(
                            (float) $product->regular_price,
                            0
                        ) }}
                    </del>
                @else
                    <strong>
                        ৳{{ number_format(
                            (float) $product->regular_price,
                            0
                        ) }}
                    </strong>
                @endif
            </div>
        </div>

        <div class="store-product-action-grid">
            <button
                type="button"
                class="store-product-cart-button store-product-cart-button--full js-product-action"
                data-action="cart"
                @disabled(
                    (int) $product->stock_quantity <= 0
                    || empty($variantsByColor)
                )
            >
                @if ((int) $product->stock_quantity > 0 && !empty($variantsByColor))
                    Add to Cart
                @else
                    Out of Stock
                @endif
            </button>

            <button
                type="button"
                class="store-product-buy-button js-product-action"
                data-action="buy"
                @disabled(
                    (int) $product->stock_quantity <= 0
                    || empty($variantsByColor)
                )
            >
                Buy Now
            </button>
        </div>
    </div>
</article>
