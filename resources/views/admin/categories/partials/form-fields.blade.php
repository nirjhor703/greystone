<div class="brand-form-sections">
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Category Information</h4>
            <p>Create a simple category under a specific brand.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_brand_id">
                    Brand <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_brand_id"
                    name="brand_id"
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
                <label for="{{ $formPrefix }}_name">
                    Category Name <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_name"
                    type="text"
                    name="name"
                    placeholder="Example: Shirts"
                >

                <small class="brand-field-error name_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_slug">
                    Slug <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_slug"
                    type="text"
                    name="slug"
                    placeholder="Example: shirts"
                >

                <span class="brand-field-help">
                    Automatically generated from category name.
                </span>

                <small class="brand-field-error slug_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_prefix">
                    Prefix <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_prefix"
                    type="text"
                    name="prefix"
                    maxlength="5"
                    placeholder="Example: SH"
                >

                <span class="brand-field-help">
                    Use 2–5 letters only.
                </span>

                <small class="brand-field-error prefix_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_status">
                    Status <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_status"
                    name="status"
                >
                    <option value="">Select Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <small class="brand-field-error status_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_image">
                    Category Image
                </label>

                <input
                    id="{{ $formPrefix }}_image"
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small class="brand-field-error image_error"></small>

                <div
                    class="brand-current-image"
                    id="{{ $formPrefix }}_image_preview"
                ></div>
            </div>

            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_description">
                    Description
                </label>

                <textarea
                    id="{{ $formPrefix }}_description"
                    name="description"
                    placeholder="Optional category description"
                ></textarea>

                <small class="brand-field-error description_error"></small>
            </div>
        </div>
    </section>
</div>