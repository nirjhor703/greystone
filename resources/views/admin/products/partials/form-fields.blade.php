<div class="product-form-sections">
    {{-- Basic Information --}}
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Basic Information</h4>
            <p>Select the brand, category and product identity.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_brand_id">
                    Brand <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_brand_id"
                    name="brand_id"
                    required
                >
                    <option value="">Select Brand</option>

                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>

                <small class="brand-field-error brand_id_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_category_id">
                    Category <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_category_id"
                    name="category_id"
                    required
                >
                    <option value="">Select Category</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            data-brand-id="{{ $category->brand_id }}"
                        >
                            {{ $category->brand?->name }}
                            —
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <small class="brand-field-error category_id_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_name">
                    Product Name <span>*</span>
                </label>

                <input
                    type="text"
                    id="{{ $formPrefix }}_name"
                    name="name"
                    placeholder="Example: Premium Oversized Shirt"
                    required
                >

                <small class="brand-field-error name_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_slug">
                    Slug <span>*</span>
                </label>

                <input
                    type="text"
                    id="{{ $formPrefix }}_slug"
                    name="slug"
                    placeholder="premium-oversized-shirt"
                    required
                >

                <small class="brand-field-error slug_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_product_code">
                    Product Code
                </label>

                <input
                    type="text"
                    id="{{ $formPrefix }}_product_code"
                    name="product_code"
                    placeholder="Automatically generated"
                >

                <small class="brand-field-error product_code_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_material">
                    Material
                </label>

                <input
                    type="text"
                    id="{{ $formPrefix }}_material"
                    name="material"
                    placeholder="Example: 100% Cotton"
                >

                <small class="brand-field-error material_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_audience">
                    Audience <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_audience"
                    name="audience"
                    required
                >
                    <option value="both">Men & Women</option>
                    <option value="men">Men</option>
                    <option value="women">Women</option>
                </select>

                <small class="brand-field-error audience_error"></small>
            </div>
        </div>
    </section>

    {{-- Price and Status --}}
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Price and Status</h4>
            <p>Set product pricing and storefront availability.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_regular_price">
                    Regular Price <span>*</span>
                </label>

                <input
                    type="number"
                    id="{{ $formPrefix }}_regular_price"
                    name="regular_price"
                    min="0"
                    step="0.01"
                    placeholder="1500"
                    required
                >

                <small class="brand-field-error regular_price_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_sale_price">
                    Sale Price
                </label>

                <input
                    type="number"
                    id="{{ $formPrefix }}_sale_price"
                    name="sale_price"
                    min="0"
                    step="0.01"
                    placeholder="1290"
                >

                <small class="brand-field-error sale_price_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_status">
                    Product Status <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_status"
                    name="status"
                    required
                >
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <small class="brand-field-error status_error"></small>
            </div>
        </div>
    </section>

    {{-- Color-wise Variant Inventory --}}
<section class="brand-form-section">
    <div class="brand-form-section-title product-variant-section-title">
        <div>
            <h4>Color-wise Variant Inventory</h4>

            <p>
                First add a color, then enter stock for each size
                available in that color.
            </p>
        </div>

        <button
            type="button"
            class="brand-secondary-button product-add-color-button"
            data-add-product-color="{{ $formPrefix }}"
        >
            ＋ Add Color
        </button>
    </div>

    <div class="product-variant-help-box">
        <strong>How it works</strong>

        <p>
            Example: Black / M = 5, Black / XL = 3,
            White / M = 2. Empty size fields will be saved as 0.
        </p>
    </div>

    <div
        class="product-color-variant-list"
        id="{{ $formPrefix }}_variant_list"
        data-form-prefix="{{ $formPrefix }}"
    ></div>

    <div
        class="product-variant-empty-state"
        id="{{ $formPrefix }}_variant_empty"
    >
        <strong>No colors added yet</strong>

        <p>
            Click “Add Color” to create the first color-wise
            stock group.
        </p>
    </div>

    <small
        class="brand-field-error variants_error"
    ></small>
</section>

    {{-- Product Images --}}
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Product Images</h4>
            <p>Upload up to 10 images. The first image will be primary.</p>
        </div>

        <div class="brand-form-field brand-full-field">
            <label for="{{ $formPrefix }}_images">
                Product Images

                @if ($formPrefix === 'add')
                    <span>*</span>
                @endif
            </label>

            <input
                type="file"
                id="{{ $formPrefix }}_images"
                name="images[]"
                accept=".jpg,.jpeg,.png,.webp"
                multiple
            >

            <small class="brand-field-error images_error"></small>

            <div
                class="product-image-preview-grid"
                id="{{ $formPrefix }}_new_image_preview"
            ></div>

            @if ($formPrefix === 'edit')
                <div
                    class="product-existing-images"
                    id="edit_existing_images"
                ></div>

                <input
                    type="hidden"
                    name="delete_image_ids"
                    id="edit_delete_image_ids"
                    value="[]"
                >

                <input
                    type="hidden"
                    name="primary_image_id"
                    id="edit_primary_image_id"
                >
            @endif
        </div>
    </section>

    {{-- Descriptions --}}
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Descriptions</h4>
            <p>Add storefront and product details page content.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_short_description">
                    Short Description
                </label>

                <textarea
                    id="{{ $formPrefix }}_short_description"
                    name="short_description"
                    rows="3"
                    placeholder="A short summary shown near the product title."
                ></textarea>

                <small class="brand-field-error short_description_error"></small>
            </div>

            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_description">
                    Full Description
                </label>

                <textarea
                    id="{{ $formPrefix }}_description"
                    name="description"
                    rows="7"
                    placeholder="Write the complete product description."
                ></textarea>

                <small class="brand-field-error description_error"></small>
            </div>

            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_care_instructions">
                    Care Instructions
                </label>

                <textarea
                    id="{{ $formPrefix }}_care_instructions"
                    name="care_instructions"
                    rows="4"
                    placeholder="Hand wash, do not bleach..."
                ></textarea>

                <small class="brand-field-error care_instructions_error"></small>
            </div>
        </div>
    </section>

    {{-- Landing Page Control --}}
    <section class="brand-form-section">
    <div class="brand-form-section-title">
        <h4>Landing Page Control</h4>
        <p>Choose where the product should be highlighted.</p>
    </div>

    <div class="product-toggle-grid">
        <div class="product-toggle-card">
            <input
                type="hidden"
                id="{{ $formPrefix }}_is_featured"
                name="is_featured"
                value="0"
            >

            <button
                type="button"
                class="product-boolean-toggle"
                data-toggle-target="{{ $formPrefix }}_is_featured"
                aria-pressed="false"
            >
                <span class="product-toggle-switch">
                    <span class="product-toggle-knob"></span>
                </span>

                <span class="product-toggle-copy">
                    <strong>Featured Product</strong>
                    <small>
                        Show this product in featured sections.
                    </small>
                </span>
            </button>
        </div>

        <div class="product-toggle-card">
            <input
                type="hidden"
                id="{{ $formPrefix }}_is_new_arrival"
                name="is_new_arrival"
                value="0"
            >

            <button
                type="button"
                class="product-boolean-toggle"
                data-toggle-target="{{ $formPrefix }}_is_new_arrival"
                aria-pressed="false"
            >
                <span class="product-toggle-switch">
                    <span class="product-toggle-knob"></span>
                </span>

                <span class="product-toggle-copy">
                    <strong>New Arrival</strong>
                    <small>
                        Mark this product as a new arrival.
                    </small>
                </span>
            </button>
        </div>
    </div>
</section>
</div>
