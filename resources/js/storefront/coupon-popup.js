document.addEventListener(
    'DOMContentLoaded',
    function () {
        const popup =
            document.getElementById(
                'newCustomerCouponPopup'
            );

        const offerBar =
            document.getElementById(
                'newCustomerCouponBar'
            );

        if (!popup) {
            return;
        }

        const barText =
            document.getElementById(
                'newCustomerCouponBarText'
            );

        const barCode =
            document.getElementById(
                'newCustomerCouponBarCode'
            );

        const barTimer =
            document.getElementById(
                'newCustomerCouponBarTimer'
            );

        const barApplyButton =
            document.getElementById(
                'newCustomerCouponBarApply'
            );

        const storePromoStrip =
            document.getElementById('storePromoStrip');

        const storePromoText =
            document.getElementById('storePromoText');

        const storePromoCode =
            document.getElementById('storePromoCode');

        const storePromoTimer =
            document.getElementById('storePromoTimer');

        const storePromoApply =
            document.getElementById('storePromoApply');

        const storePromoPrev =
            document.querySelector('[data-store-promo-prev]');

        const storePromoNext =
            document.querySelector('[data-store-promo-next]');

        const badge =
            document.getElementById(
                'newCustomerCouponBadge'
            );

        const title =
            document.getElementById(
                'newCustomerCouponTitle'
            );

        const description =
            document.getElementById(
                'newCustomerCouponDescription'
            );

        const codeOutput =
            document.getElementById(
                'newCustomerCouponCode'
            );

        const applyButton =
            document.getElementById(
                'newCustomerCouponApply'
            );

        const shopButton =
            document.getElementById(
                'newCustomerCouponShop'
            );

        const expiryOutput =
            document.getElementById(
                'newCustomerCouponExpiry'
            );

        const brandId =
            popup.dataset.brandId || '';

        let coupon = null;
        let opened = false;
        let countdownTimer = null;
        let fallbackCountdownEndsAt = null;
        let storePromoActiveSlide = 0;

        const alreadyPurchased =
            localStorage.getItem(
                'storefront_customer_has_order'
            ) === '1';

        if (alreadyPurchased) {
            return;
        }

        function openPopup() {
            if (!coupon || opened) {
                return;
            }

            opened = true;

            popup.classList.add('open');
            popup.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.classList.add(
                'store-overlay-open'
            );
        }

        function closePopup() {
            popup.classList.remove('open');
            popup.setAttribute(
                'aria-hidden',
                'true'
            );

            document.body.classList.remove(
                'store-overlay-open'
            );

            if (coupon?.id) {
                sessionStorage.setItem(
                    `coupon_popup_seen_${coupon.id}`,
                    '1'
                );
            }
        }

        function remainingTimeText() {
            if (
                !coupon?.expires_at
                && !fallbackCountdownEndsAt
            ) {
                return '';
            }

            const diff =
                (
                    coupon?.expires_at
                        ? new Date(coupon.expires_at).getTime()
                        : fallbackCountdownEndsAt
                )
                - Date.now();

            if (diff <= 0) {
                return 'Expired';
            }

            const totalSeconds =
                Math.floor(diff / 1000);

            const days =
                Math.floor(totalSeconds / 86400);

            const hours =
                Math.floor((totalSeconds % 86400) / 3600);

            const minutes =
                Math.floor((totalSeconds % 3600) / 60);

            const seconds =
                totalSeconds % 60;

            if (days > 0) {
                return `${days}d ${hours}h ${minutes}m left`;
            }

            if (hours > 0) {
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')} left`;
            }

            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')} left`;
        }

        function formatTemplate(
            value
        ) {
            const timeText =
                remainingTimeText();

            return String(value || '')
                .replaceAll(
                    '{discount}',
                    coupon?.discount_label || ''
                )
                .replaceAll(
                    '{code}',
                    coupon?.code || ''
                )
                .replaceAll(
                    '{time}',
                    timeText
                );
        }

        function pendingCouponMatches() {
            return (
                coupon?.code
                && localStorage.getItem(
                    'pending_coupon_code'
                ) === coupon.code
            );
        }

        function setStorePromoSlide(index) {
            storePromoActiveSlide = index === 1 ? 1 : 0;

            storePromoStrip?.setAttribute(
                'data-active-slide',
                String(storePromoActiveSlide)
            );
        }

        function syncStorePromoControls(isApplied = false) {
            if (!storePromoStrip || !coupon) {
                return;
            }

            if (storePromoText) {
                storePromoText.textContent =
                    formatTemplate(
                        isApplied
                            ? coupon.topbar_applied_text
                            : coupon.topbar_text
                    ) || 'Free Shipping & More Offers';
            }

            if (storePromoCode) {
                storePromoCode.textContent = coupon.code;
                storePromoCode.hidden = isApplied;
            }

            if (storePromoApply) {
                storePromoApply.hidden = false;
                storePromoApply.disabled = isApplied;
                storePromoApply.textContent =
                    isApplied
                        ? `Saved ${coupon.discount_label || ''}`.trim()
                        : coupon.topbar_button_text
                            || 'Apply code';
            }
        }

        function setOfferBarState() {
            if (!offerBar || !coupon) {
                return;
            }

            const isApplied =
                pendingCouponMatches();

            offerBar.hidden = false;
            offerBar.classList.toggle(
                'is-applied',
                isApplied
            );

            document.body.classList.add(
                'coupon-offer-bar-visible'
            );

            syncStorePromoControls(isApplied);

            if (barText) {
                barText.textContent =
                    formatTemplate(
                        isApplied
                            ? coupon.topbar_applied_text
                            : coupon.topbar_text
                    );
            }

            if (barCode) {
                barCode.textContent =
                    coupon.code;

                barCode.hidden =
                    isApplied;
            }

            if (barApplyButton) {
                barApplyButton.disabled =
                    isApplied;

                barApplyButton.textContent =
                    isApplied
                        ? `Saved ${coupon.discount_label || ''}`.trim()
                        : coupon.topbar_button_text
                            || 'Apply code';
            }
        }

        function updateCountdown() {
            const timeText =
                remainingTimeText();

            if (
                (
                    coupon?.expires_at
                    || fallbackCountdownEndsAt
                )
                && timeText
            ) {
                if (expiryOutput) {
                    expiryOutput.textContent =
                        `Offer ends in ${timeText}`;

                    expiryOutput.hidden = false;
                }

                if (barTimer) {
                    barTimer.textContent =
                        timeText;

                    barTimer.hidden = false;
                }

                if (storePromoTimer) {
                    storePromoTimer.textContent =
                        timeText;

                    storePromoTimer.hidden = false;
                }
            }

            setOfferBarState();
        }

        storePromoPrev?.addEventListener('click', () => {
            setStorePromoSlide(storePromoActiveSlide === 0 ? 1 : 0);
        });

        storePromoNext?.addEventListener('click', () => {
            setStorePromoSlide(storePromoActiveSlide === 0 ? 1 : 0);
        });

        storePromoApply?.addEventListener('click', () => {
            barApplyButton?.click();
        });

        window.setInterval(() => {
            if (!storePromoStrip) {
                return;
            }

            setStorePromoSlide(storePromoActiveSlide === 0 ? 1 : 0);
        }, 3000);

        function startCountdown() {
            if (countdownTimer) {
                clearInterval(
                    countdownTimer
                );
            }

            if (
                !coupon?.expires_at
                && coupon?.id
            ) {
                const fallbackKey =
                    `coupon_countdown_ends_${coupon.id}`;

                const storedEnd =
                    Number(
                        sessionStorage.getItem(
                            fallbackKey
                        )
                    );

                fallbackCountdownEndsAt =
                    storedEnd > Date.now()
                        ? storedEnd
                        : Date.now() + 15 * 60 * 1000;

                sessionStorage.setItem(
                    fallbackKey,
                    String(fallbackCountdownEndsAt)
                );
            }

            updateCountdown();

            countdownTimer = setInterval(
                updateCountdown,
                1000
            );
        }

        function populatePopup() {
            const colors = coupon.colors || {};

            if (colors.primary) {
                popup.style.setProperty(
                    '--coupon-popup-primary',
                    colors.primary
                );

                offerBar?.style.setProperty(
                    '--coupon-popup-primary',
                    colors.primary
                );
            }

            if (colors.button) {
                popup.style.setProperty(
                    '--coupon-popup-button',
                    colors.button
                );

                offerBar?.style.setProperty(
                    '--coupon-popup-button',
                    colors.button
                );
            }

            if (colors.background) {
                popup.style.setProperty(
                    '--coupon-popup-background',
                    colors.background
                );

                offerBar?.style.setProperty(
                    '--coupon-popup-background',
                    colors.background
                );
            }

            badge.textContent =
                coupon.badge;

            title.textContent =
                coupon.title;

            description.textContent =
                coupon.description;

            codeOutput.textContent =
                coupon.code;

            if (applyButton) {
                applyButton.disabled =
                    pendingCouponMatches();

                applyButton.textContent =
                    pendingCouponMatches()
                        ? coupon.applied_text
                            || 'Applied'
                        : coupon.button_text
                            || 'Use This Coupon';
            }

            setOfferBarState();
            startCountdown();
        }

        async function loadPopupCoupon() {
            try {
                const query = brandId
                    ? `?brand_id=${encodeURIComponent(
                        brandId
                    )}`
                    : '';

                const response = await fetch(
                    `/coupons/popup${query}`,
                    {
                        credentials:
                            'same-origin',

                        headers: {
                            Accept:
                                'application/json',
                        },
                    }
                );

                const data =
                    await response.json();

                if (
                    !response.ok
                    || !data?.coupon
                ) {
                    return;
                }

                coupon = data.coupon;

                populatePopup();

                const alreadySeen =
                    sessionStorage.getItem(
                        `coupon_popup_seen_${coupon.id}`
                    ) === '1'
                    || pendingCouponMatches();

                if (alreadySeen) {
                    return;
                }

                const scrollPixels =
                    Math.max(
                        Number(
                            coupon.scroll_pixels
                            || 120
                        ),
                        50
                    );

                function handleScroll() {
                    if (
                        window.scrollY
                        < scrollPixels
                    ) {
                        return;
                    }

                    window.removeEventListener(
                        'scroll',
                        handleScroll
                    );

                    openPopup();
                }

                window.addEventListener(
                    'scroll',
                    handleScroll,
                    {
                        passive: true,
                    }
                );
            } catch (error) {
                console.error(
                    'Coupon popup failed:',
                    error
                );
            }
        }

        applyButton?.addEventListener(
            'click',
            function () {
                saveCouponOffer(
                    applyButton
                );
            }
        );

        barApplyButton?.addEventListener(
            'click',
            function () {
                saveCouponOffer(
                    barApplyButton
                );
            }
        );

        shopButton?.addEventListener(
            'click',
            function () {
                closePopup();

                document
                    .getElementById(
                        'products'
                    )
                    ?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
            }
        );

        popup
            .querySelectorAll(
                '[data-close-new-customer-coupon]'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    closePopup
                );
            });

        loadPopupCoupon();

        function saveCouponOffer(
            sourceButton
        ) {
            if (!coupon?.code) {
                return;
            }

            if (sourceButton) {
                sourceButton.disabled = true;
                sourceButton.textContent =
                    coupon.apply_loading_text
                    || 'Applying...';
            }

            window.setTimeout(
                function () {
                    localStorage.setItem(
                        'pending_coupon_code',
                        coupon.code
                    );

                    sessionStorage.setItem(
                        `coupon_popup_seen_${coupon.id}`,
                        '1'
                    );

                    const checkoutCouponInput =
                        document.getElementById(
                            'checkoutCouponCode'
                        );

                    if (checkoutCouponInput) {
                        checkoutCouponInput.value =
                            coupon.code;
                    }

                    if (applyButton) {
                        applyButton.disabled = true;
                        applyButton.textContent =
                            coupon.applied_text
                            || 'Applied';
                    }

                    setOfferBarState();
                },
                450
            );
        }

        window.addEventListener(
            'storefront:order-created',
            function () {
                offerBar?.remove();
                document.body.classList.remove(
                    'coupon-offer-bar-visible'
                );
            }
        );
    }
);
