<div class="brand-form-sections">
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Basic Information</h4>
            <p>Brand name, slug and visual assets.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_name">
                    Brand Name <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_name"
                    type="text"
                    name="name"
                    placeholder="Example: Grey Stone"
                >

                <small class="brand-field-error name_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_slug">
                    Brand Slug <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_slug"
                    type="text"
                    name="slug"
                    placeholder="Example: grey-stone"
                >

                <span class="brand-field-help">
                    Use lowercase letters, numbers and hyphens.
                </span>

                <small class="brand-field-error slug_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_logo">
                    Desktop Logo
                </label>

                <input
                    id="{{ $formPrefix }}_logo"
                    type="file"
                    name="logo"
                    accept=".jpg,.jpeg,.png,.webp,.svg"
                >

                <small class="brand-field-error logo_error"></small>

                <div
                    class="brand-current-image"
                    id="{{ $formPrefix }}_logo_preview"
                ></div>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_mobile_logo">
                    Mobile Logo
                </label>

                <input
                    id="{{ $formPrefix }}_mobile_logo"
                    type="file"
                    name="mobile_logo"
                    accept=".jpg,.jpeg,.png,.webp,.svg"
                >

                <small class="brand-field-error mobile_logo_error"></small>

                <div
                    class="brand-current-image"
                    id="{{ $formPrefix }}_mobile_logo_preview"
                ></div>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_favicon">
                    Favicon
                </label>

                <input
                    id="{{ $formPrefix }}_favicon"
                    type="file"
                    name="favicon"
                    accept=".jpg,.jpeg,.png,.webp,.ico"
                >

                <small class="brand-field-error favicon_error"></small>

                <div
                    class="brand-current-image"
                    id="{{ $formPrefix }}_favicon_preview"
                ></div>
            </div>

            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_offer_banners">
                    Offer Banners
                </label>

                <input
                    id="{{ $formPrefix }}_offer_banners"
                    type="file"
                    name="offer_banners[]"
                    accept=".jpg,.jpeg,.png,.webp"
                    multiple
                >

                <span class="brand-field-help">
                    Upload Canva banners for the storefront carousel. Best ratio: 16:7, recommended 1600 x 700 px.
                </span>

                <small class="brand-field-error offer_banners_error"></small>

                <div
                    class="brand-current-image brand-current-banner-list"
                    id="{{ $formPrefix }}_offer_banners_preview"
                ></div>
            </div>

            <div class="brand-form-field brand-checkbox-field">
                <label class="brand-toggle-label">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        id="{{ $formPrefix }}_is_active"
                        checked
                    >

                    <span class="brand-toggle"></span>
                    Active Storefront
                </label>

                <small class="brand-field-error is_active_error"></small>
            </div>
        </div>
    </section>

    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Theme Settings</h4>
            <p>Control the storefront colors and typography.</p>
        </div>

        <div class="brand-color-grid">
            @foreach ([
                'primary_color' => ['Primary Color', '#3a3a3a'],
                'secondary_color' => ['Secondary Color', '#777777'],
                'background_color' => ['Background Color', '#ffffff'],
                'button_color' => ['Button Color', '#2f2f2f'],
                'text_color' => ['Text Color', '#111111'],
            ] as $field => [$label, $default])
                <div class="brand-form-field">
                    <label for="{{ $formPrefix }}_{{ $field }}">
                        {{ $label }} <span>*</span>
                    </label>

                    <div class="brand-color-input">
                        <input
                            id="{{ $formPrefix }}_{{ $field }}"
                            type="color"
                            name="{{ $field }}"
                            value="{{ $default }}"
                        >

                        <input
                            type="text"
                            class="brand-color-text"
                            data-color-target="{{ $formPrefix }}_{{ $field }}"
                            value="{{ $default }}"
                            maxlength="7"
                        >
                    </div>

                    <small class="brand-field-error {{ $field }}_error"></small>
                </div>
            @endforeach

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_font_family">
                    Font Family
                </label>

                <input
                    id="{{ $formPrefix }}_font_family"
                    type="text"
                    name="font_family"
                    placeholder="Example: Figtree, sans-serif"
                >

                <small class="brand-field-error font_family_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_header_style">
                    Header Style
                </label>

                <select
                    id="{{ $formPrefix }}_header_style"
                    name="header_style"
                >
                    <option value="">Select Header Style</option>
                    <option value="default">Default</option>
                    <option value="minimal">Minimal</option>
                    <option value="centered">Centered</option>
                </select>

                <small class="brand-field-error header_style_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_footer_style">
                    Footer Style
                </label>

                <select
                    id="{{ $formPrefix }}_footer_style"
                    name="footer_style"
                >
                    <option value="">Select Footer Style</option>
                    <option value="default">Default</option>
                    <option value="minimal">Minimal</option>
                    <option value="expanded">Expanded</option>
                </select>

                <small class="brand-field-error footer_style_error"></small>
            </div>
        </div>
    </section>

    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Contact Information</h4>
            <p>Customer support and social media details.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_contact_number">
                    Contact Number
                </label>

                <input
                    id="{{ $formPrefix }}_contact_number"
                    type="text"
                    name="contact_number"
                    placeholder="Example: 01XXXXXXXXX"
                >

                <small class="brand-field-error contact_number_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_email">
                    Email Address
                </label>

                <input
                    id="{{ $formPrefix }}_email"
                    type="email"
                    name="email"
                    placeholder="brand@example.com"
                >

                <small class="brand-field-error email_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_facebook_link">
                    Facebook Link
                </label>

                <input
                    id="{{ $formPrefix }}_facebook_link"
                    type="url"
                    name="facebook_link"
                    placeholder="https://facebook.com/..."
                >

                <small class="brand-field-error facebook_link_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_instagram_link">
                    Instagram Link
                </label>

                <input
                    id="{{ $formPrefix }}_instagram_link"
                    type="url"
                    name="instagram_link"
                    placeholder="https://instagram.com/..."
                >

                <small class="brand-field-error instagram_link_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_whatsapp_link">
                    WhatsApp Link
                </label>

                <input
                    id="{{ $formPrefix }}_whatsapp_link"
                    type="url"
                    name="whatsapp_link"
                    placeholder="https://wa.me/..."
                >

                <small class="brand-field-error whatsapp_link_error"></small>
            </div>

            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_address">
                    Address
                </label>

                <textarea
                    id="{{ $formPrefix }}_address"
                    name="address"
                    placeholder="Enter business address"
                ></textarea>

                <small class="brand-field-error address_error"></small>
            </div>
        </div>
    </section>

    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>SEO Settings</h4>
            <p>Search engine title and description.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_meta_title">
                    Meta Title
                </label>

                <input
                    id="{{ $formPrefix }}_meta_title"
                    type="text"
                    name="meta_title"
                    placeholder="Storefront page title"
                >

                <small class="brand-field-error meta_title_error"></small>
            </div>

            <div class="brand-form-field brand-full-field">
                <label for="{{ $formPrefix }}_meta_description">
                    Meta Description
                </label>

                <textarea
                    id="{{ $formPrefix }}_meta_description"
                    name="meta_description"
                    placeholder="Storefront meta description"
                ></textarea>

                <small class="brand-field-error meta_description_error"></small>
            </div>
        </div>
    </section>
</div>
