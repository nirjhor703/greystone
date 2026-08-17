<div class="brand-form-sections">
    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>Coupon Information</h4>
            <p>Set discount rules for checkout coupon codes.</p>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_brand_id">
                    Brand
                </label>

                <select
                    id="{{ $formPrefix }}_brand_id"
                    name="brand_id"
                >
                    <option value="">All Brands</option>

                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>

                <small class="brand-field-error brand_id_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_code">
                    Coupon Code <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_code"
                    type="text"
                    name="code"
                    maxlength="60"
                    placeholder="Example: SAVE10"
                >

                <small class="brand-field-error code_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_title">
                    Title
                </label>

                <input
                    id="{{ $formPrefix }}_title"
                    type="text"
                    name="title"
                    maxlength="120"
                    placeholder="Example: Eid offer"
                >

                <small class="brand-field-error title_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_discount_type">
                    Discount Type <span>*</span>
                </label>

                <select
                    id="{{ $formPrefix }}_discount_type"
                    name="discount_type"
                >
                    <option value="">Select Type</option>
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                </select>

                <small class="brand-field-error discount_type_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_discount_value">
                    Discount Value <span>*</span>
                </label>

                <input
                    id="{{ $formPrefix }}_discount_value"
                    type="number"
                    name="discount_value"
                    min="1"
                    step="0.01"
                    placeholder="Example: 100 or 10"
                >

                <small class="brand-field-error discount_value_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_max_discount_amount">
                    Max Discount
                </label>

                <input
                    id="{{ $formPrefix }}_max_discount_amount"
                    type="number"
                    name="max_discount_amount"
                    min="1"
                    step="0.01"
                    placeholder="For percentage coupons"
                >

                <small class="brand-field-error max_discount_amount_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_min_order_amount">
                    Minimum Order
                </label>

                <input
                    id="{{ $formPrefix }}_min_order_amount"
                    type="number"
                    name="min_order_amount"
                    min="0"
                    step="0.01"
                    placeholder="0"
                >

                <small class="brand-field-error min_order_amount_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_usage_limit">
                    Usage Limit
                </label>

                <input
                    id="{{ $formPrefix }}_usage_limit"
                    type="number"
                    name="usage_limit"
                    min="1"
                    step="1"
                    placeholder="Unlimited"
                >

                <small class="brand-field-error usage_limit_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_starts_at">
                    Starts At
                </label>

                <input
                    id="{{ $formPrefix }}_starts_at"
                    type="datetime-local"
                    name="starts_at"
                >

                <small class="brand-field-error starts_at_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_expires_at">
                    Expires At
                </label>

                <input
                    id="{{ $formPrefix }}_expires_at"
                    type="datetime-local"
                    name="expires_at"
                >

                <small class="brand-field-error expires_at_error"></small>
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
        </div>
    </section>

    <section class="brand-form-section">
        <div class="brand-form-section-title">
            <h4>
                New Customer Popup
            </h4>
    
            <p>
                Show this coupon after a new customer starts
                scrolling the website.
            </p>
        </div>
    
        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label class="brand-toggle-label">
                    <input
                        type="hidden"
                        name="new_customer_only"
                        value="0"
                    >
    
                    <input
                        type="checkbox"
                        name="new_customer_only"
                        value="1"
                        id="{{ $formPrefix }}_new_customer_only"
                    >
    
                    <span class="brand-toggle"></span>
    
                    <span>
                        New Customer Only
                    </span>
                </label>
    
                <small class="brand-field-help">
                    Customers with an existing non-cancelled
                    order cannot use this coupon.
                </small>
    
                <small
                    class="brand-field-error"
                    data-error="new_customer_only"
                ></small>
            </div>
    
            <div class="brand-form-field">
                <label class="brand-toggle-label">
                    <input
                        type="hidden"
                        name="show_as_popup"
                        value="0"
                    >
    
                    <input
                        type="checkbox"
                        name="show_as_popup"
                        value="1"
                        id="{{ $formPrefix }}_show_as_popup"
                    >
    
                    <span class="brand-toggle"></span>
    
                    <span>
                        Show as Website Popup
                    </span>
                </label>
    
                <small class="brand-field-help">
                    The popup will appear after the customer
                    scrolls the website.
                </small>
    
                <small
                    class="brand-field-error"
                    data-error="show_as_popup"
                ></small>
            </div>
    
            <div class="brand-form-field">
                <label>
                    Popup Badge
                </label>
    
                <input
                    type="text"
                    name="popup_badge"
                    id="{{ $formPrefix }}_popup_badge"
                    maxlength="100"
                    placeholder="New Customer Offer"
                >
    
                <small
                    class="brand-field-error"
                    data-error="popup_badge"
                ></small>
            </div>
    
            <div class="brand-form-field">
                <label>
                    Popup Title
                </label>
    
                <input
                    type="text"
                    name="popup_title"
                    id="{{ $formPrefix }}_popup_title"
                    maxlength="160"
                    placeholder="10% OFF your first order"
                >
    
                <small
                    class="brand-field-error"
                    data-error="popup_title"
                ></small>
            </div>
    
            <div class="brand-form-field brand-full-field">
                <label>
                    Popup Description
                </label>
    
                <textarea
                    name="popup_description"
                    id="{{ $formPrefix }}_popup_description"
                    rows="3"
                    maxlength="500"
                    placeholder="Use this welcome coupon during checkout."
                ></textarea>
    
                <small
                    class="brand-field-error"
                    data-error="popup_description"
                ></small>
            </div>
    
            <div class="brand-form-field">
                <label>
                    Popup Button Text
                </label>
    
                <input
                    type="text"
                    name="popup_button_text"
                    id="{{ $formPrefix }}_popup_button_text"
                    maxlength="100"
                    placeholder="Use This Coupon"
                >
    
                <small
                    class="brand-field-error"
                    data-error="popup_button_text"
                ></small>
            </div>

            <div class="brand-form-field">
                <label>
                    Applying Button Text
                </label>

                <input
                    type="text"
                    name="popup_apply_loading_text"
                    id="{{ $formPrefix }}_popup_apply_loading_text"
                    maxlength="80"
                    placeholder="Applying..."
                >

                <small
                    class="brand-field-error"
                    data-error="popup_apply_loading_text"
                ></small>
            </div>

            <div class="brand-form-field">
                <label>
                    Applied Button Text
                </label>

                <input
                    type="text"
                    name="popup_applied_text"
                    id="{{ $formPrefix }}_popup_applied_text"
                    maxlength="80"
                    placeholder="Applied"
                >

                <small
                    class="brand-field-error"
                    data-error="popup_applied_text"
                ></small>
            </div>

            <div class="brand-form-field brand-full-field">
                <label>
                    Top Bar Text
                </label>

                <input
                    type="text"
                    name="topbar_text"
                    id="{{ $formPrefix }}_topbar_text"
                    maxlength="180"
                    placeholder="You are new - enjoy {discount} your first order! Code {code}"
                >

                <small class="brand-field-help">
                    You can use {discount}, {code} and {time}.
                </small>

                <small
                    class="brand-field-error"
                    data-error="topbar_text"
                ></small>
            </div>

            <div class="brand-form-field brand-full-field">
                <label>
                    Top Bar Saved Text
                </label>

                <input
                    type="text"
                    name="topbar_applied_text"
                    id="{{ $formPrefix }}_topbar_applied_text"
                    maxlength="180"
                    placeholder="{discount} locked in - order before the timer ends."
                >

                <small class="brand-field-help">
                    This text appears after customer applies the offer.
                </small>

                <small
                    class="brand-field-error"
                    data-error="topbar_applied_text"
                ></small>
            </div>

            <div class="brand-form-field">
                <label>
                    Top Bar Button Text
                </label>

                <input
                    type="text"
                    name="topbar_button_text"
                    id="{{ $formPrefix }}_topbar_button_text"
                    maxlength="80"
                    placeholder="Apply code"
                >

                <small
                    class="brand-field-error"
                    data-error="topbar_button_text"
                ></small>
            </div>
    
            <div class="brand-form-field">
                <label>
                    Show After Scrolling
                </label>
    
                <input
                    type="number"
                    name="popup_scroll_pixels"
                    id="{{ $formPrefix }}_popup_scroll_pixels"
                    min="50"
                    max="5000"
                    value="120"
                >
    
                <small class="brand-field-help">
                    Scroll amount in pixels. Recommended: 120.
                </small>
    
                <small
                    class="brand-field-error"
                    data-error="popup_scroll_pixels"
                ></small>
            </div>
        </div>
    </section>
</div>
