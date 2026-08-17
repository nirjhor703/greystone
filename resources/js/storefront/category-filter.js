document.addEventListener(
    'DOMContentLoaded',
    function () {
        const categoryCards = Array.from(
            document.querySelectorAll('.js-category-filter')
        );

        const productCards = Array.from(
            document.querySelectorAll(
                '.store-product-card[data-product-category-id]'
            )
        );

        const audienceToggle = document.getElementById(
            'storeAudienceToggle'
        );

        const audienceButtons = Array.from(
            document.querySelectorAll('[data-audience-filter]')
        );

        const productGrid = document.getElementById(
            'storeProductGrid'
        );

        const crossBrandDivider = document.getElementById(
            'crossBrandProductsDivider'
        );

        const emptyMessage = document.getElementById(
            'categoryProductEmpty'
        );

        const emptyMessageText = document.getElementById(
            'categoryProductEmptyText'
        );

        const searchParams = new URLSearchParams(
            window.location.search
        );

        const selectedAudience = String(
            searchParams.get('audience') || 'men'
        ).toLowerCase();

        const selectedCategoryKey = String(
            searchParams.get('category') || ''
        ).toLowerCase();

        function buildBrowseUrl(nextCategoryKey, nextAudience) {
            const params = new URLSearchParams(
                window.location.search
            );

            if (nextCategoryKey) {
                params.set('category', nextCategoryKey);
            } else {
                params.delete('category');
            }

            if (nextAudience) {
                params.set('audience', nextAudience);
            } else {
                params.delete('audience');
            }

            params.delete('page');

            const query = params.toString();

            return `${window.location.pathname}${query ? `?${query}` : ''}#products`;
        }

        function syncCategoryState() {
            categoryCards.forEach(function (card) {
                const active =
                    String(card.dataset.categoryKey || '').toLowerCase()
                    === selectedCategoryKey;

                card.classList.toggle('active', active);
                card.setAttribute(
                    'aria-pressed',
                    active ? 'true' : 'false'
                );
            });
        }

        function syncAudienceState() {
            if (audienceToggle) {
                audienceToggle.dataset.activeAudience = selectedAudience;
            }

            audienceButtons.forEach(function (button) {
                const active =
                    String(button.dataset.audienceFilter || '').toLowerCase()
                    === selectedAudience;

                button.classList.toggle('active', active);
                button.setAttribute(
                    'aria-pressed',
                    active ? 'true' : 'false'
                );
            });
        }

        function syncCrossBrandDivider() {
            if (!productGrid || !crossBrandDivider) {
                return;
            }

            const primaryProducts = productCards.filter(
                function (card) {
                    return (
                        card.dataset.productBrandPriority === 'primary'
                    );
                }
            );

            const secondaryProducts = productCards.filter(
                function (card) {
                    return (
                        card.dataset.productBrandPriority === 'secondary'
                    );
                }
            );

            if (
                primaryProducts.length > 0
                && secondaryProducts.length > 0
            ) {
                crossBrandDivider.hidden = false;
                productGrid.insertBefore(
                    crossBrandDivider,
                    secondaryProducts[0]
                );
                return;
            }

            crossBrandDivider.hidden = true;
            productGrid.appendChild(crossBrandDivider);
        }

        function syncEmptyState() {
            if (!emptyMessage || !emptyMessageText) {
                return;
            }

            const hasProducts = productCards.length > 0;

            emptyMessage.hidden = hasProducts;

            if (hasProducts) {
                return;
            }

            const audienceLabel = selectedAudience === 'women'
                ? 'women'
                : 'men';

            emptyMessageText.textContent = selectedCategoryKey
                ? `No ${audienceLabel} products are available in this category.`
                : `No ${audienceLabel} products are currently available.`;
        }

        window.storefrontCategoryFilterActivate = function (card, event) {
            if (event) {
                event.preventDefault();
            }

            if (!(card instanceof HTMLElement)) {
                return false;
            }

            window.location.href = buildBrowseUrl(
                String(card.dataset.categoryKey || '').toLowerCase(),
                selectedAudience
            );

            return false;
        };

        categoryCards.forEach(function (card) {
            card.addEventListener('click', function (event) {
                window.storefrontCategoryFilterActivate(card, event);
            });
        });

        audienceButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const nextAudience = String(
                    button.dataset.audienceFilter || 'men'
                ).toLowerCase();

                window.location.href = buildBrowseUrl(
                    selectedCategoryKey,
                    nextAudience
                );
            });
        });

        syncCategoryState();
        syncAudienceState();
        syncCrossBrandDivider();
        syncEmptyState();
    }
);
