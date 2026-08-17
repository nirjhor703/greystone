<div
    class="new-customer-coupon-bar"
    id="newCustomerCouponBar"
    data-brand-id="{{ $brand->id ?? '' }}"
    hidden
>
    <div class="new-customer-coupon-bar-inner">
        <span
            class="new-customer-coupon-bar-text"
            id="newCustomerCouponBarText"
        ></span>

        <strong
            class="new-customer-coupon-bar-code"
            id="newCustomerCouponBarCode"
            hidden
        ></strong>

        <span
            class="new-customer-coupon-bar-timer"
            id="newCustomerCouponBarTimer"
            hidden
        ></span>

        <button
            type="button"
            class="new-customer-coupon-bar-apply"
            id="newCustomerCouponBarApply"
        >
            Apply code
        </button>
    </div>
</div>

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} new-customer-coupon-popup"
    id="newCustomerCouponPopup"
    data-brand-id="{{ $brand->id ?? '' }}"
    aria-hidden="true"
>
    <div
        class="new-customer-coupon-backdrop"
        data-close-new-customer-coupon
    ></div>

    <div
        class="new-customer-coupon-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="newCustomerCouponTitle"
    >
        <button
            type="button"
            class="new-customer-coupon-close"
            data-close-new-customer-coupon
            aria-label="Close offer"
        >
            ×
        </button>

        <span
            class="new-customer-coupon-badge"
            id="newCustomerCouponBadge"
        >
            New Customer Offer
        </span>

        <h2 id="newCustomerCouponTitle">
            Welcome Offer
        </h2>

        <p id="newCustomerCouponDescription">
            Use this coupon during checkout.
        </p>

        <div class="new-customer-coupon-code">
            <strong id="newCustomerCouponCode">
                WELCOME
            </strong>

            <button
                type="button"
                id="newCustomerCouponApply"
            >
                Apply
            </button>
        </div>

        <small
            class="new-customer-coupon-expiry"
            id="newCustomerCouponExpiry"
            hidden
        ></small>

        <button
            type="button"
            class="new-customer-coupon-shop"
            id="newCustomerCouponShop"
        >
            Shop the Collection
            <span>→</span>
        </button>
    </div>
</div>
