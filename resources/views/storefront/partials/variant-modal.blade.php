<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} store-modal"
    id="productVariantModal"
    aria-hidden="true"
>
    <div
        class="store-modal-backdrop"
        data-close-variant-modal
    ></div>

    <div
        class="store-modal-dialog product-option-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="variantModalTitle"
    >
        <button
            type="button"
            class="store-modal-close"
            data-close-variant-modal
            aria-label="Close modal"
        >
            ×
        </button>

        {{-- Product information --}}
        <div class="product-option-header">
            <div class="product-option-image">
                <img
                    src=""
                    alt=""
                    id="variantProductImage"
                    hidden
                >

                <div
                    class="variant-image-fallback"
                    id="variantImageFallback"
                >
                    P
                </div>
            </div>

            <div class="product-option-copy">
                <span id="variantProductCode">
                    Product
                </span>

                <h2 id="variantModalTitle">
                    Select Product Options
                </h2>

                <strong id="variantProductPrice">
                    ৳0
                </strong>
            </div>
        </div>

        <div class="product-option-body">
            {{-- Color selection --}}
            <section
                class="product-option-section"
                id="variantColorSection"
            >
                <div class="product-option-section-heading">
                    <div>
                        <h3>Select Color</h3>

                        <p>
                            Choose your preferred color.
                        </p>
                    </div>

                    <span id="variantSelectedColor">
                        Required
                    </span>
                </div>

                <div
                    class="variant-color-grid"
                    id="variantColorOptions"
                ></div>

                <small
                    class="variant-field-error"
                    id="variantColorError"
                ></small>
            </section>

            {{-- Size and quantity selection --}}
            <section
                class="product-option-section"
                id="variantSizeSection"
                hidden
            >
                <div class="product-option-section-heading">
                    <div>
                        <h3>Select Size & Quantity</h3>

                        <p>
                            Choose one or multiple sizes.
                        </p>
                    </div>

                    <span id="variantSelectedSize">
                        0 items selected
                    </span>
                </div>

                <div
                    class="variant-multi-size-list"
                    id="variantSizeOptions"
                ></div>

                <small
                    class="variant-field-error"
                    id="variantSizeError"
                ></small>
            </section>

            {{-- Selected items summary --}}
            <section class="variant-selection-summary">
                <div class="variant-selection-summary-heading">
                    <div>
                        <strong>Your Selection</strong>

                        <small>
                            Review selected color, sizes and quantities
                        </small>
                    </div>

                    <span id="variantSummaryCount">
                        0 items
                    </span>
                </div>

                <div
                    class="variant-selection-empty"
                    id="variantSelectionEmpty"
                >
                    Select a color, then add quantity beside your chosen size.
                </div>

                <div
                    class="variant-selection-list"
                    id="variantSelectionList"
                ></div>
            </section>

            <section
                class="packaging-option-section"
                id="variantPackagingSection"
                hidden
            >
                <div class="packaging-section-heading">
                    <div>
                        <h3>Packaging</h3>
                        <p>Choose how you'd like your order packaged.</p>
                    </div>
                </div>

                <div class="packaging-choice-row">
                    <button type="button" class="packaging-choice-button selected" data-packaging-type="normal">
                        <strong>Normal Packaging</strong>
                        <small class="packaging-free-label">FREE</small>
                        <small>Included with your order</small>
                    </button>

                    <button type="button" class="packaging-choice-button premium" data-packaging-type="premium">
                        <strong>Premium Packaging</strong>
                        <small class="packaging-premium-price" data-packaging-premium-price></small>
                        <small>Upgrade your packaging</small>
                        <small class="packaging-selected-tier" data-packaging-selected-tier hidden></small>
                    </button>
                </div>

                <div class="premium-packaging-tier-chooser" id="premiumPackagingTierChooser" hidden>
                    <small class="packaging-selected-tier" data-packaging-selected-tier hidden></small>
                </div>
            </section>

            <div class="variant-modal-summary">
                <div>
                    <span>Quantity</span>
                    <strong id="variantTotalQuantity">0</strong>
                </div>
                <div>
                    <span>Total Price</span>
                    <strong id="variantTotalPrice">৳0</strong>
                </div>
            </div>
        </div>

        <div class="product-option-footer">
            <small
                class="variant-general-error"
                id="variantGeneralError"
            ></small>

            <button
                type="button"
                class="variant-confirm-button"
                id="confirmVariantSelection"
            >
                Add Selected Items to Cart
            </button>
        </div>
    </div>
</div>

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} store-modal packaging-info-modal"
    id="packagingInfoModal"
    aria-hidden="true"
>
    <div
        class="store-modal-backdrop"
        data-close-packaging-info
    ></div>

    <div
        class="store-modal-dialog packaging-info-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="packagingInfoTitle"
    >
        <button
            type="button"
            class="store-modal-close"
            data-close-packaging-info
            aria-label="Close packaging information"
        >
            ×
        </button>

        <h2 id="packagingInfoTitle">
            <span data-packaging-info-context>Premium Packaging</span>
        </h2>

        <img
            src="{{ asset('images/storefront/packaging-comparison.jpg') }}"
            alt="Normal and premium packaging comparison"
        >
        <p class="packaging-modal-order-label">Your Order</p>
        <p class="packaging-modal-order" data-packaging-modal-order></p>
        <h3 class="packaging-modal-tier-heading">Choose Your Premium Packaging</h3>
        <div class="packaging-tier-options" data-packaging-tier-options></div>
        <div class="packaging-modal-tier-content" data-packaging-modal-tiers hidden></div>
        <div class="packaging-modal-breakdown" data-packaging-breakdown hidden></div>
        <button type="button" class="packaging-confirm-button" data-confirm-packaging>Confirm Premium Packaging</button>
    </div>
</div>
