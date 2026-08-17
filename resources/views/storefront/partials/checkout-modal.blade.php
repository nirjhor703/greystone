{{-- =========================================================
     CUSTOMER INFORMATION / CHECKOUT MODAL
========================================================= --}}

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} store-modal checkout-modal-wrapper"
    id="checkoutModal"
    aria-hidden="true"
>
    <div
        class="store-modal-backdrop"
        data-close-checkout-modal
    ></div>

    <div
        class="store-modal-dialog checkout-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="checkoutModalTitle"
    >
        <button
            type="button"
            class="store-modal-close"
            data-close-checkout-modal
            aria-label="Close checkout"
        >
            ×
        </button>

        <div class="checkout-modal-header">
            <div class="checkout-modal-header-icon">
                1
            </div>

            <div>
                <span>Checkout</span>

                <h2 id="checkoutModalTitle">
                    Delivery Information
                </h2>

                <p>
                    Enter the information required to deliver
                    your order.
                </p>
            </div>
        </div>

        <form
            id="checkoutLeadForm"
            novalidate
        >
            <div class="checkout-modal-body">
                {{-- Customer information --}}
                <section class="checkout-form-section">
                    <div class="checkout-section-heading">
                        <div>
                            <strong>
                                Customer Information
                            </strong>

                            <small>
                                We will contact you using this
                                information.
                            </small>
                        </div>

                        <span>Required</span>
                    </div>

                    <div class="checkout-form-grid">
                        <div class="checkout-field full-width">
                            <label for="checkoutCustomerName">
                                Full Name
                                <em>*</em>
                            </label>

                            <input
                                type="text"
                                id="checkoutCustomerName"
                                name="customer_name"
                                maxlength="100"
                                autocomplete="name"
                                placeholder="Enter your full name"
                            >

                            <small
                                class="checkout-field-error"
                                data-checkout-error="customer_name"
                            ></small>
                        </div>

                        <div class="checkout-field">
                            <label for="checkoutPhone">
                                Phone Number
                                <em>*</em>
                            </label>

                            <input
                                type="tel"
                                id="checkoutPhone"
                                name="phone"
                                maxlength="20"
                                inputmode="tel"
                                autocomplete="tel"
                                placeholder="01XXXXXXXXX"
                            >

                            <small
                                class="checkout-field-error"
                                data-checkout-error="phone"
                            ></small>
                        </div>

                        <div class="checkout-field">
                            <label for="checkoutAlternativePhone">
                                Alternative Phone (Optional)
                            </label>

                            <input
                                type="tel"
                                id="checkoutAlternativePhone"
                                name="alternative_phone"
                                maxlength="20"
                                inputmode="tel"

                            >

                            <small
                                class="checkout-field-error"
                                data-checkout-error="alternative_phone"
                            ></small>
                        </div>

                        <div class="checkout-field full-width">
                            <label for="checkoutEmail">
                                Email Address (Optional)
                            </label>

                            <input
                                type="email"
                                id="checkoutEmail"
                                name="customer_email"
                                maxlength="150"
                                autocomplete="email"
                            >

                            <small
                                class="checkout-field-error"
                                data-checkout-error="customer_email"
                            ></small>
                        </div>
                    </div>
                </section>

                {{-- Delivery location --}}
                <section class="checkout-form-section">
                    <div class="checkout-section-heading">
                        <div>
                            <strong>
                                Delivery Location
                            </strong>

                            <small>
                                Delivery charge depends on the
                                selected area.
                            </small>
                        </div>
                    </div>

                    <div class="checkout-delivery-options">
                        <label class="checkout-delivery-option">
                            <input
                                type="radio"
                                name="delivery_area"
                                value="inside_dhaka"
                                data-delivery-charge="80"
                                checked
                            >

                            <span>
                                <strong>Inside Dhaka</strong>

                                <small>Delivery charge ৳80</small>
                            </span>
                        </label>

                        <label class="checkout-delivery-option">
                            <input
                                type="radio"
                                name="delivery_area"
                                value="outside_dhaka"
                                data-delivery-charge="130"
                            >

                            <span>
                                <strong>Outside Dhaka</strong>

                                <small>Delivery charge ৳130</small>
                            </span>
                        </label>
                    </div>

                    <small
                        class="checkout-field-error"
                        data-checkout-error="delivery_area"
                    ></small>

                    <div class="checkout-form-grid">
                        <div class="checkout-field">
                            <label for="checkoutDistrict">
                                District
                                <em>*</em>
                            </label>

                            <div class="searchable-district-shell">
                                <input
                                    type="text"
                                    id="checkoutDistrict"
                                    name="district"
                                    maxlength="100"
                                    placeholder="Search district"
                                    autocomplete="off"
                                >

                                <button
                                    type="button"
                                    class="district-clear-btn"
                                    id="districtClearBtn"
                                    aria-label="Clear district"
                                >
                                    ×
                                </button>

                                <div
                                    class="district-dropdown"
                                    id="districtDropdown"
                                ></div>
                            </div>

                            <small
                                class="checkout-field-error"
                                data-checkout-error="district"
                            ></small>
                        </div>

                        <div class="checkout-field">
                            <label for="checkoutAreaThana">
                                Area / Thana
                                <em>*</em>
                            </label>

                            <input
                                type="text"
                                id="checkoutAreaThana"
                                name="area_thana"
                                maxlength="150"
                                placeholder="Enter area or thana"
                            >

                            <small
                                class="checkout-field-error"
                                data-checkout-error="area_thana"
                            ></small>
                        </div>

                        <div class="checkout-field">
                            <label for="checkoutRoadNo">
                                Road Number
                                <em>*</em>
                            </label>

                            <input
                                type="text"
                                id="checkoutRoadNo"
                                name="road_no"
                                maxlength="100"
                                placeholder="Optional"
                            >
                        </div>

                        <div class="checkout-field">
                            <label for="checkoutHouseNo">
                                House Number
                                <em>*</em>
                            </label>

                            <input
                                type="text"
                                id="checkoutHouseNo"
                                name="house_no"
                                maxlength="100"
                                placeholder="Optional"
                            >
                        </div>

                        <div class="checkout-field full-width">
                            <label for="checkoutFullAddress">
                                Full Delivery Address
                                <em>*</em>
                            </label>

                            <textarea
                                id="checkoutFullAddress"
                                name="full_address"
                                rows="3"
                                placeholder="House, road, area and nearby landmark"
                                readonly
                                aria-readonly="true"
                            ></textarea>

                            <small
                                class="checkout-field-error"
                                data-checkout-error="full_address"
                            ></small>
                        </div>

                        <div class="checkout-field full-width">
                            <label for="checkoutOrderNote">
                                Order Note (Optional)
                            </label>

                            <textarea
                                id="checkoutOrderNote"
                                name="order_note"
                                rows="2"
                                placeholder="Any special delivery instruction"
                            ></textarea>
                        </div>
                    </div>
                </section>

                {{-- Payment --}}
                <section class="checkout-form-section">
                    <div class="checkout-section-heading">
                        <div>
                            <strong>Payment Method</strong>

                            <small>
                                Payment will be collected when
                                the order is delivered.
                            </small>
                        </div>
                    </div>

                    <label class="checkout-payment-option selected">
                        <input
                            type="radio"
                            name="payment_method"
                            value="cash_on_delivery"
                            checked
                        >

                        <span class="checkout-payment-icon">
                            ৳
                        </span>

                        <span>
                            <strong>Cash on Delivery</strong>

                            <small>
                                Pay after receiving your order
                            </small>
                        </span>

                        <b>✓</b>
                    </label>
                </section>

                {{-- Order summary --}}
                <section class="checkout-form-section checkout-cart-summary">
                    <div class="checkout-section-heading">
                        <div>
                            <strong>Order Summary</strong>

                            <small id="checkoutCartItemCount">
                                0 items
                            </small>
                        </div>
                    </div>

                    <div
                        class="checkout-cart-preview"
                        id="checkoutCartPreview"
                    ></div>

                    <div
                        class="checkout-voucher-panel"
                        id="checkoutVoucherPanel"
                        hidden
                    >
                        <div class="checkout-voucher-head">
                            <div>
                                <strong>Vouchers for your order</strong>
                                <small>
                                    Slide to choose the best active coupon.
                                </small>
                            </div>

                            <div class="checkout-voucher-controls">
                                <button
                                    type="button"
                                    id="voucherPrevButton"
                                    aria-label="Previous voucher"
                                >
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>

                                <button
                                    type="button"
                                    id="voucherNextButton"
                                    aria-label="Next voucher"
                                >
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <div
                            class="checkout-voucher-track"
                            id="checkoutVoucherTrack"
                            aria-live="polite"
                        ></div>
                    </div>

                    <div class="checkout-coupon-box">
                        <label for="checkoutCouponCode">
                            Coupon Code
                        </label>

                        <div class="checkout-coupon-actions">
                            <input
                                type="text"
                                id="checkoutCouponCode"
                                maxlength="60"
                                autocomplete="off"
                                placeholder="Enter coupon code"
                            >

                            <button
                                type="button"
                                id="applyCouponButton"
                            >
                                Apply
                            </button>

                            <button
                                type="button"
                                id="removeCouponButton"
                                hidden
                            >
                                Remove
                            </button>
                        </div>

                        <small
                            class="checkout-coupon-message"
                            id="checkoutCouponMessage"
                        ></small>
                    </div>

                    <div class="checkout-price-summary">
                        <div>
                            <span>Products Subtotal</span>

                            <strong id="checkoutItemsTotal">
                                ৳0
                            </strong>
                        </div>

                        <div>
                            <span>Delivery Charge</span>

                            <strong id="checkoutDeliveryCharge">
                                ৳0
                            </strong>
                        </div>

                        <div
                            class="checkout-discount-row"
                            id="checkoutDiscountRow"
                            hidden
                        >
                            <span>Coupon Discount</span>

                            <strong id="checkoutDiscountAmount">
                                -৳0
                            </strong>
                        </div>

                        <div class="grand-total">
                            <span>Grand Total</span>

                            <strong id="checkoutGrandTotal">
                                ৳0
                            </strong>
                        </div>
                    </div>
                </section>

                <small
                    class="checkout-general-error"
                    id="checkoutGeneralError"
                ></small>
            </div>

            <div class="checkout-modal-footer">
                <button
                    type="button"
                    class="checkout-cancel-button"
                    data-close-checkout-modal
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="checkout-review-button"
                    id="reviewOrderButton"
                >
                    Review Order
                    <span>→</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =========================================================
     ORDER CONFIRMATION MODAL
========================================================= --}}

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} store-modal order-confirm-wrapper"
    id="orderConfirmModal"
    aria-hidden="true"
>
    <div
        class="store-modal-backdrop"
        data-close-confirm-modal
    ></div>

    <div
        class="store-modal-dialog order-confirm-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="orderConfirmTitle"
    >
        <button
            type="button"
            class="store-modal-close"
            id="closeConfirmBtn"
            data-close-confirm-modal
            aria-label="Close confirmation"
        >
            ×
        </button>

        <div class="confirm-head">
            <div class="confirm-icon">
                <span>▤</span>
            </div>

            <div>
                <h2 id="orderConfirmTitle">
                    Confirm Your Order
                </h2>

                <p>
                    Please check your products, address and payment details before final confirmation.
                </p>
            </div>
        </div>

        <div class="confirm-content">
            <section class="confirm-section">
                <h4>
                    <span>□</span>
                    Order Items
                </h4>
                <div
                    class="confirm-items"
                    id="confirmItems"
                ></div>
            </section>

            <section class="confirm-section">
                <h4>
                    <span>✓</span>
                    Customer Details
                </h4>

                <div class="confirm-info">
                    <div>
                        <span>Name</span>
                        <strong id="confirmName">-</strong>
                    </div>
                    <div>
                        <span>Phone</span>
                        <strong id="confirmPhone">-</strong>
                    </div>
                    <div>
                        <span>Delivery Area</span>
                        <strong id="confirmDeliveryArea">-</strong>
                    </div>
                    <div>
                        <span>District</span>
                        <strong id="confirmDistrict">-</strong>
                    </div>
                    <div class="confirm-address-row">
                        <span>Address</span>
                        <strong id="confirmAddress">-</strong>
                    </div>
                    <div>
                        <span>Payment</span>
                        <strong id="confirmPayment">Cash on Delivery</strong>
                    </div>
                </div>
            </section>

            <section class="confirm-section payment-summary-card">
                <h4>
                    <span>৳</span>
                    Payment Summary
                </h4>

                <div class="confirm-total-row">
                    <span>Items Total</span>
                    <strong>৳ <span id="confirmItemsTotal">0</span></strong>
                </div>

                <div class="confirm-total-row">
                    <span>Delivery Charge</span>
                    <strong>৳ <span id="confirmDeliveryCharge">0</span></strong>
                </div>

                <div
                    class="confirm-total-row"
                    id="confirmDiscountRow"
                    hidden
                >
                    <span>Coupon Discount</span>
                    <strong>-৳ <span id="confirmDiscountAmount">0</span></strong>
                </div>

                <div class="confirm-total-row grand">
                    <span>Grand Total</span>
                    <strong>৳ <span id="confirmGrandTotal">0</span></strong>
                </div>
            </section>
        </div>

        <small
            class="checkout-general-error confirm-general-error"
            id="confirmGeneralError"
        ></small>

        <div class="confirm-actions">
            <button
                type="button"
                class="confirm-no-btn"
                id="cancelFinalOrderBtn"
            >
                No, Edit
            </button>

            <button
                type="button"
                class="confirm-yes-btn animated-confirm-btn"
                id="confirmFinalOrderBtn"
            >
                <span>Yes, Confirm Order</span>
                <b>✓</b>
            </button>
        </div>
    </div>
</div>

{{-- =========================================================
     THANK YOU MODAL
========================================================= --}}

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} store-modal thank-you-wrapper"
    id="thankYouModal"
    aria-hidden="true"
>
    <div
        class="store-modal-backdrop"
        data-close-thank-you-modal
    ></div>

    <div
        class="store-modal-dialog thank-you-modal"
        role="dialog"
        aria-modal="true"
    >
        <div class="thank-you-icon">✓</div>

        <h2>Thanks for ordering!</h2>

        <p>
            Your order has been received successfully. Our team will contact you soon for confirmation.
        </p>

        <div class="thank-you-code">
            <span>Order Code</span>
            <strong id="thankYouOrderCode">Pending</strong>
        </div>

        <a
            class="thank-you-invoice-btn"
            id="thankYouInvoiceButton"
            href="#"
            target="_blank"
            rel="noopener"
            hidden
        >
            Download Invoice
        </a>

        <button
            class="thank-you-btn"
            id="closeThankYouBtn"
            type="button"
            data-close-thank-you-modal
        >
            Continue Shopping
        </button>
    </div>
</div>
