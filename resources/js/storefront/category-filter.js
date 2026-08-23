document.addEventListener('DOMContentLoaded', function () {
    let selectedAudience = String(
        new URLSearchParams(window.location.search).get('audience')
        || 'men'
    ).toLowerCase();

    let selectedCategoryKey = String(
        new URLSearchParams(window.location.search).get('category')
        || ''
    ).toLowerCase();

    let activeRequestController = null;

    function getCategorySection() {
        return document.querySelector('.store-category-section');
    }

    function getProductSection() {
        return document.querySelector('.store-product-section');
    }

    function getSearchResults() {
        return document.getElementById('storeSearchResults');
    }

    function getSearchCount() {
        return document.getElementById('storeSearchCount');
    }

    function getSearchInput() {
        return document.getElementById('storeSearchInput');
    }

    function buildBrowseUrl(nextCategoryKey, nextAudience, nextPage = '') {
        const params = new URLSearchParams(window.location.search);

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

        if (nextPage) {
            params.set('page', nextPage);
        } else {
            params.delete('page');
        }

        const query = params.toString();

        return `${window.location.pathname}${query ? `?${query}` : ''}`;
    }

    function syncCrossBrandDivider() {
        const productGrid = document.getElementById('storeProductGrid');
        const crossBrandDivider = document.getElementById(
            'crossBrandProductsDivider'
        );

        if (!productGrid || !crossBrandDivider) {
            return;
        }

        const productCards = Array.from(
            productGrid.querySelectorAll(
                '.store-product-card[data-product-category-id]'
            )
        );

        const primaryProducts = productCards.filter(function (card) {
            return card.dataset.productBrandPriority === 'primary';
        });

        const secondaryProducts = productCards.filter(function (card) {
            return card.dataset.productBrandPriority === 'secondary';
        });

        if (primaryProducts.length > 0 && secondaryProducts.length > 0) {
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
        const productGrid = document.getElementById('storeProductGrid');
        const emptyMessage = document.getElementById('categoryProductEmpty');
        const emptyMessageText = document.getElementById(
            'categoryProductEmptyText'
        );

        if (!productGrid || !emptyMessage || !emptyMessageText) {
            return;
        }

        const productCards = Array.from(
            productGrid.querySelectorAll(
                '.store-product-card[data-product-category-id]'
            )
        );

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

    function refreshSearchFromDocument(parsedDocument) {
        const currentSearchResults = getSearchResults();
        const nextSearchResults = parsedDocument.getElementById(
            'storeSearchResults'
        );
        const currentSearchCount = getSearchCount();
        const nextSearchCount = parsedDocument.getElementById(
            'storeSearchCount'
        );

        if (currentSearchResults && nextSearchResults) {
            currentSearchResults.innerHTML = nextSearchResults.innerHTML;
        }

        if (currentSearchCount && nextSearchCount) {
            currentSearchCount.textContent = nextSearchCount.textContent;
        }

        const searchInput = getSearchInput();

        if (searchInput) {
            searchInput.dispatchEvent(
                new Event('input', { bubbles: true })
            );
        }
    }

    function replaceSection(currentSection, nextSection) {
        if (!(currentSection instanceof HTMLElement)) {
            return;
        }

        if (!(nextSection instanceof HTMLElement)) {
            return;
        }

        currentSection.replaceWith(nextSection);
    }

    function parseStateFromUrl(url) {
        const absoluteUrl = new URL(url, window.location.origin);

        selectedAudience = String(
            absoluteUrl.searchParams.get('audience') || 'men'
        ).toLowerCase();

        selectedCategoryKey = String(
            absoluteUrl.searchParams.get('category') || ''
        ).toLowerCase();
    }

    async function loadBrowseState(url, options = {}) {
        const {
            updateHistory = true,
            preserveScroll = true,
        } = options;

        if (activeRequestController) {
            activeRequestController.abort();
        }

        const currentCategorySection = getCategorySection();
        const currentProductSection = getProductSection();

        if (!currentCategorySection || !currentProductSection) {
            window.location.href = `${url}#products`;
            return;
        }

        const scrollTop = preserveScroll ? window.scrollY : null;

        currentCategorySection.classList.add('is-loading');
        currentProductSection.classList.add('is-loading');

        const controller = new AbortController();
        activeRequestController = controller;

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error('Failed to load products.');
            }

            const html = await response.text();
            const parsedDocument = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const nextCategorySection = parsedDocument.querySelector(
                '.store-category-section'
            );
            const nextProductSection = parsedDocument.querySelector(
                '.store-product-section'
            );

            if (!nextCategorySection || !nextProductSection) {
                throw new Error('Updated store layout could not be loaded.');
            }

            replaceSection(currentCategorySection, nextCategorySection);
            replaceSection(currentProductSection, nextProductSection);
            refreshSearchFromDocument(parsedDocument);
            parseStateFromUrl(url);
            syncCrossBrandDivider();
            syncEmptyState();

            if (updateHistory) {
                window.history.pushState({}, '', `${url}#products`);
            }

            if (typeof scrollTop === 'number') {
                window.scrollTo({
                    top: scrollTop,
                    behavior: 'auto',
                });
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            window.location.href = `${url}#products`;
        } finally {
            activeRequestController = null;
        }
    }

    function activateCategoryCard(card, event) {
        if (event) {
            event.preventDefault();
        }

        if (!(card instanceof HTMLElement)) {
            return false;
        }

        const nextCategoryKey = String(
            card.dataset.categoryKey || ''
        ).toLowerCase();

        loadBrowseState(
            buildBrowseUrl(nextCategoryKey, selectedAudience)
        );

        return false;
    }

    window.storefrontCategoryFilterActivate = activateCategoryCard;

    document.addEventListener('click', function (event) {
        const categoryCard = event.target.closest('.js-category-filter');

        if (categoryCard) {
            activateCategoryCard(categoryCard, event);
            return;
        }

        const audienceButton = event.target.closest('[data-audience-filter]');

        if (audienceButton) {
            event.preventDefault();

            const nextAudience = String(
                audienceButton.dataset.audienceFilter || 'men'
            ).toLowerCase();

            loadBrowseState(
                buildBrowseUrl(selectedCategoryKey, nextAudience)
            );
            return;
        }

        const paginationLink = event.target.closest(
            '#storePaginationWrap a[href]'
        );

        if (!paginationLink) {
            return;
        }

        const paginationUrl = paginationLink.getAttribute('href') || '';

        if (
            !paginationUrl
            || paginationUrl === '#'
            || paginationLink.matches('.is-disabled')
            || paginationLink.getAttribute('aria-disabled') === 'true'
        ) {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        const parsedUrl = new URL(
            paginationUrl,
            window.location.origin
        );

        loadBrowseState(
            `${parsedUrl.pathname}${parsedUrl.search}`
        );
    });

    window.addEventListener('popstate', function () {
        loadBrowseState(
            `${window.location.pathname}${window.location.search}`,
            {
                updateHistory: false,
                preserveScroll: false,
            }
        );
    });

    syncCrossBrandDivider();
    syncEmptyState();
});
