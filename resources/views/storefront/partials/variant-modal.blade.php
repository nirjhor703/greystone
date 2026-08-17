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
        </div>

        <div class="product-option-footer">
            <div class="variant-modal-summary">
                <div>
                    <span>Total Quantity</span>

                    <strong id="variantTotalQuantity">
                        0
                    </strong>
                </div>

                <div>
                    <span>Total Price</span>

                    <strong id="variantTotalPrice">
                        ৳0
                    </strong>
                </div>
            </div>

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
