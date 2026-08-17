<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} cart-drawer-wrapper"
    id="cartDrawerWrapper"
    aria-hidden="true"
>
    <div
        class="cart-drawer-backdrop"
        data-close-cart-drawer
    ></div>

    <aside
        class="cart-drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cartDrawerTitle"
    >
        <div class="cart-drawer-header">
            <div>
                <span>Your Shopping Cart</span>

                <h2 id="cartDrawerTitle">
                    Cart
                    <small id="cartDrawerCount">
                        0 items
                    </small>
                </h2>
            </div>

            <button
                type="button"
                class="cart-drawer-close"
                data-close-cart-drawer
                aria-label="Close cart"
            >
                ×
            </button>
        </div>

        <div
            class="cart-drawer-items"
            id="cartDrawerItems"
        >
            <div class="cart-drawer-loading">
                Loading cart...
            </div>
        </div>

        <div
            class="cart-drawer-footer"
            id="cartDrawerFooter"
        >
            <div class="cart-drawer-subtotal">
                <span>Subtotal</span>

                <strong id="cartDrawerSubtotal">
                    ৳0
                </strong>
            </div>

            <p>
                Delivery charge will be calculated at checkout.
            </p>

            <button
                type="button"
                class="cart-checkout-button"
                id="openCartCheckout"
            >
                Proceed to Checkout
            </button>

            <button
                type="button"
                class="cart-continue-button"
                data-close-cart-drawer
            >
                Continue Shopping
            </button>
        </div>
    </aside>
</div>

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} wishlist-modal-wrapper"
    id="wishlistModalWrapper"
    aria-hidden="true"
>
    <div
        class="wishlist-modal-backdrop"
        data-close-wishlist
    ></div>

    <section
        class="wishlist-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="wishlistModalTitle"
    >
        <div class="wishlist-modal-header">
            <div>
                <span>Saved Products</span>

                <h2 id="wishlistModalTitle">
                    Wishlist
                    <small id="wishlistModalCount">
                        0 items
                    </small>
                </h2>
            </div>

            <button
                type="button"
                class="wishlist-modal-close"
                data-close-wishlist
                aria-label="Close wishlist"
            >
                ×
            </button>
        </div>

        <div
            class="wishlist-modal-items"
            id="wishlistModalItems"
        ></div>
    </section>
</div>

<button
    type="button"
    class="floating-cart-button"
    id="floatingCartButton"
    aria-label="Open cart"
>
    <span>Cart</span>

    <strong id="floatingCartCount">
        0
    </strong>
</button>

<div
    class="store-toast"
    id="storeToast"
    aria-live="polite"
></div>
