document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | DOM Elements
    |--------------------------------------------------------------------------
    */

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const variantModal = document.getElementById(
        'productVariantModal'
    );

    const variantImage = document.getElementById(
        'variantProductImage'
    );

    const variantImageFallback = document.getElementById(
        'variantImageFallback'
    );

    const variantTitle = document.getElementById(
        'variantModalTitle'
    );

    const variantCode = document.getElementById(
        'variantProductCode'
    );

    const variantPrice = document.getElementById(
        'variantProductPrice'
    );

    const variantTotalPrice = document.getElementById(
        'variantTotalPrice'
    );

    const variantTotalQuantity = document.getElementById(
        'variantTotalQuantity'
    );

    const colorSection = document.getElementById(
        'variantColorSection'
    );

    const colorOptions = document.getElementById(
        'variantColorOptions'
    );

    const selectedColorOutput = document.getElementById(
        'variantSelectedColor'
    );

    const colorError = document.getElementById(
        'variantColorError'
    );

    const sizeSection = document.getElementById(
        'variantSizeSection'
    );

    const sizeOptions = document.getElementById(
        'variantSizeOptions'
    );

    const selectedSizeOutput = document.getElementById(
        'variantSelectedSize'
    );

    const sizeError = document.getElementById(
        'variantSizeError'
    );

    const generalError = document.getElementById(
        'variantGeneralError'
    );

    const confirmButton = document.getElementById(
        'confirmVariantSelection'
    );

    const variantSelectionList = document.getElementById(
        'variantSelectionList'
    );

    const variantSelectionEmpty = document.getElementById(
        'variantSelectionEmpty'
    );

    const variantSummaryCount = document.getElementById(
        'variantSummaryCount'
    );

    const cartDrawerWrapper = document.getElementById(
        'cartDrawerWrapper'
    );

    const cartDrawerItems = document.getElementById(
        'cartDrawerItems'
    );

    const cartDrawerCount = document.getElementById(
        'cartDrawerCount'
    );

    const cartDrawerSubtotal = document.getElementById(
        'cartDrawerSubtotal'
    );

    const cartDrawerFooter = document.getElementById(
        'cartDrawerFooter'
    );

    const floatingCartButton = document.getElementById(
        'floatingCartButton'
    );

    const floatingCartCount = document.getElementById(
        'floatingCartCount'
    );

    const headerCartCount = document.getElementById(
        'storeHeaderCartCount'
    );

    const openCartCheckoutButton = document.getElementById(
        'openCartCheckout'
    );

    const toast = document.getElementById(
        'storeToast'
    );

    const wishlistModalWrapper = document.getElementById(
        'wishlistModalWrapper'
    );

    const wishlistModalItems = document.getElementById(
        'wishlistModalItems'
    );

    const wishlistModalCount = document.getElementById(
        'wishlistModalCount'
    );

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    let selectedProduct = null;
    let selectedAction = 'cart';
    let selectedColor = null;
    let selectedColorHex = '';
    let selectedSizesByColor = new Map();
    let toastTimer = null;
    let wishlistItems = [];
    let currentCart = {
        items: [],
        count: 0,
        items_total: 0,
    };

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function normalizeColorHex(value) {
        const raw = String(value || '')
            .trim()
            .toUpperCase();

        if (!raw) {
            return '';
        }

        if (/^#([A-F0-9]{6})$/.test(raw)) {
            return raw;
        }

        if (/^([A-F0-9]{6})$/.test(raw)) {
            return `#${raw}`;
        }

        return '';
    }

    function fallbackColorFromName(name) {
        const source = String(name || '').trim();

        if (!source) {
            return '#CBD5E1';
        }

        let hash = 0;

        for (let index = 0; index < source.length; index += 1) {
            hash = source.charCodeAt(index) + ((hash << 5) - hash);
        }

        const hue = Math.abs(hash) % 360;

        return `hsl(${hue} 68% 58%)`;
    }

    function swatchColor(colorName, colorHex) {
        return normalizeColorHex(colorHex) || fallbackColorFromName(colorName);
    }

    function colorSelectionKey(colorName, colorHex) {
        const hex = normalizeColorHex(colorHex);

        if (hex) {
            return `hex:${hex}`;
        }

        return `name:${String(colorName || '')
            .trim()
            .toLowerCase()}`;
    }

    function getActiveColorSelection() {
        if (!selectedColor) {
            return null;
        }

        const key = colorSelectionKey(
            selectedColor,
            selectedColorHex
        );

        if (!selectedSizesByColor.has(key)) {
            selectedSizesByColor.set(key, {
                color: selectedColor,
                colorHex: selectedColorHex,
                sizes: new Map(),
            });
        }

        return selectedSizesByColor.get(key);
    }

    function colorSwatchHtml(colorName, colorHex, extraClass = '') {
        const className = [
            'store-color-swatch',
            extraClass,
        ]
            .filter(Boolean)
            .join(' ');

        return `
            <span
                class="${className}"
                style="--swatch-color:${escapeHtml(
                    swatchColor(colorName, colorHex)
                )};"
                aria-hidden="true"
            ></span>
        `;
    }

    function colorLabelHtml(colorName, colorHex) {
        return `
            <span class="store-color-chip">
                ${colorSwatchHtml(colorName, colorHex)}
                <b>${escapeHtml(colorName || 'Color')}</b>
            </span>
        `;
    }

    function readProductPayload(productButton) {
        const productCard = productButton.closest(
            '.store-product-card'
        );

        if (!productCard) {
            throw new Error(
                'Product card was not found.'
            );
        }

        const payloadElement = productCard.querySelector(
            '[data-product-payload]'
        );

        if (!payloadElement) {
            throw new Error(
                'Product payload element was not found.'
            );
        }

        const payloadText =
            payloadElement.textContent?.trim();

        if (!payloadText) {
            throw new Error(
                'Product payload is empty.'
            );
        }

        const product = JSON.parse(payloadText);

        if (
            !product ||
            !product.product_id ||
            !Array.isArray(product.variants)
        ) {
            throw new Error(
                'Product payload is invalid.'
            );
        }

        return product;
    }

    function money(value) {
        return `৳${Number(value || 0).toLocaleString(
            'en-BD',
            {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            }
        )}`;
    }

    function showToast(
        message,
        type = 'success'
    ) {
        if (!toast) {
            return;
        }

        clearTimeout(toastTimer);

        toast.textContent = message;
        toast.className =
            `store-toast visible ${type}`;

        toastTimer = setTimeout(function () {
            toast.classList.remove('visible');
        }, 3000);
    }

    /*
    |--------------------------------------------------------------------------
    | Wishlist
    |--------------------------------------------------------------------------
    */

    const wishlistStorageKey = 'greystone_storefront_wishlist';

    function readWishlist() {
        try {
            const parsed = JSON.parse(
                window.localStorage.getItem(
                    wishlistStorageKey
                ) || '[]'
            );

            return Array.isArray(parsed)
                ? parsed
                : [];
        } catch (error) {
            return [];
        }
    }

    function saveWishlist() {
        window.localStorage.setItem(
            wishlistStorageKey,
            JSON.stringify(wishlistItems)
        );
    }

    function normalizeWishlistProduct(button) {
        const parentCard = button.closest(
            '[data-product-brand-name], [data-search-brand-name]'
        );

        return {
            id: String(button.dataset.productId || ''),
            name: button.dataset.productName || 'Product',
            url: button.dataset.productUrl || '#',
            image: button.dataset.productImage || '',
            category: button.dataset.productCategory || 'Product',
            price: Number(button.dataset.productPrice || 0),
            brand_name:
                button.dataset.productBrandName ||
                parentCard?.dataset.productBrandName ||
                parentCard?.dataset.searchBrandName ||
                '',
            brand_slug:
                button.dataset.productBrandSlug ||
                parentCard?.dataset.productBrandSlug ||
                parentCard?.dataset.searchBrandSlug ||
                '',
        };
    }

    function wishlistHas(productId) {
        return wishlistItems.some(function (item) {
            return String(item.id) === String(productId);
        });
    }

    function syncWishlistButtons() {
        const count = wishlistItems.length;

        document
            .querySelectorAll('.store-wishlist-count')
            .forEach(function (badge) {
                badge.textContent = String(count);
                badge.hidden = count <= 0;
            });

        document
            .querySelectorAll('[data-wishlist-button]')
            .forEach(function (button) {
                const active = wishlistHas(
                    button.dataset.productId
                );

                button.classList.toggle('active', active);
                button.setAttribute(
                    'aria-pressed',
                    active ? 'true' : 'false'
                );

                const icon = button.querySelector('i');

                if (icon) {
                    icon.className = active
                        ? 'fa-solid fa-heart'
                        : 'fa-regular fa-heart';
                }
            });
    }

    function renderWishlist() {
        if (wishlistModalCount) {
            wishlistModalCount.textContent =
                `${wishlistItems.length} ${
                    wishlistItems.length === 1
                        ? 'item'
                        : 'items'
                }`;
        }

        if (!wishlistModalItems) {
            return;
        }

        if (!wishlistItems.length) {
            wishlistModalItems.innerHTML = `
                <div class="wishlist-empty-state">
                    <strong>No saved products yet</strong>
                    <p>Tap a heart on any product to save it here.</p>
                </div>
            `;

            return;
        }

        wishlistModalItems.innerHTML = '';

        wishlistItems.forEach(function (item) {
            const row = document.createElement('article');

            row.className = 'wishlist-modal-item';

            const name = escapeHtml(item.name || 'Product');
            const category = escapeHtml(item.category || 'Product');
            const brandName = escapeHtml(
                item.brand_name || ''
            );
            const brandSlug = escapeHtml(
                item.brand_slug || ''
            );
            const image = item.image
                ? escapeHtml(item.image)
                : '';
            const url = item.url
                ? escapeHtml(item.url)
                : '#';
            const id = escapeHtml(item.id || '');

            row.innerHTML = `
                <a href="${url}" class="wishlist-item-image">
                    ${
                        image
                            ? `<img src="${image}" alt="${name}">`
                            : `<span>${name.charAt(0).toUpperCase()}</span>`
                    }
                </a>

                <div class="wishlist-item-info">
                    <div class="wishlist-item-meta">
                        <small>${category}</small>
                        ${
                            brandName
                                ? `<em class="wishlist-item-brand-badge" data-brand-slug="${brandSlug}">${brandName}</em>`
                                : ''
                        }
                    </div>
                    <a href="${url}">${name}</a>
                    <strong>${money(item.price)}</strong>
                </div>

                <button
                    type="button"
                    class="wishlist-item-remove"
                    data-wishlist-remove="${id}"
                    aria-label="Remove ${name} from wishlist"
                >
                    ×
                </button>
            `;

            wishlistModalItems.appendChild(row);
        });
    }

    function openWishlistModal() {
        renderWishlist();

        wishlistModalWrapper?.classList.add('open');
        wishlistModalWrapper?.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'store-overlay-open'
        );
    }

    function closeWishlistModal() {
        wishlistModalWrapper?.classList.remove('open');
        wishlistModalWrapper?.setAttribute(
            'aria-hidden',
            'true'
        );

        removeBodyOverlayIfUnused();
    }

    function toggleWishlist(button) {
        const product = normalizeWishlistProduct(button);

        if (!product.id) {
            return;
        }

        if (wishlistHas(product.id)) {
            wishlistItems = wishlistItems.filter(function (item) {
                return String(item.id) !== String(product.id);
            });

            showToast('Removed from wishlist.', 'success');
        } else {
            wishlistItems.unshift(product);
            showToast('Added to wishlist.', 'success');
        }

        saveWishlist();
        syncWishlistButtons();
        renderWishlist();
    }

    async function readJsonResponse(response) {
        const contentType =
            response.headers.get('content-type') || '';

        if (
            contentType.includes(
                'application/json'
            )
        ) {
            return response.json();
        }

        const text = await response.text();

        throw new Error(
            text ||
            'The server returned an invalid response.'
        );
    }

    function getGroupedSelections() {
        return Array.from(
            selectedSizesByColor.values()
        )
            .map(function (group) {
                const items = Array.from(
                    group.sizes.values()
                )
                    .filter(function (item) {
                        return Number(item.quantity) > 0;
                    })
                    .map(function (item) {
                        return {
                            size: item.size,
                            quantity: Number(item.quantity),
                            stock: Number(item.stock || 0),
                        };
                    });

                return {
                    key: colorSelectionKey(
                        group.color,
                        group.colorHex
                    ),
                    color: group.color,
                    colorHex: normalizeColorHex(
                        group.colorHex
                    ),
                    items,
                };
            })
            .filter(function (group) {
                return group.items.length > 0;
            });
    }

    function getSelectedItems() {
        return getGroupedSelections().flatMap(
            function (group) {
                return group.items.map(function (item) {
                    return {
                        color: group.color,
                        colorHex: group.colorHex,
                        size: item.size,
                        quantity: item.quantity,
                        stock: item.stock,
                    };
                });
            }
        );
    }

    function getSelectedTotalQuantity() {
        return getSelectedItems().reduce(
            function (total, item) {
                return total + Number(item.quantity || 0);
            },
            0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Variant Modal
    |--------------------------------------------------------------------------
    */

    function openVariantModal(
        product,
        action
    ) {
        selectedProduct = product;
        selectedAction = action || 'cart';
        selectedColor = null;
        selectedColorHex = '';
        selectedSizesByColor = new Map();

        if (colorError) {
            colorError.textContent = '';
        }

        if (sizeError) {
            sizeError.textContent = '';
        }

        if (generalError) {
            generalError.textContent = '';
        }

        if (variantTitle) {
            variantTitle.textContent =
                product.name ||
                'Choose Product Options';
        }

        if (variantCode) {
            variantCode.textContent =
                product.product_code ||
                'Product';
        }

        if (variantPrice) {
            variantPrice.textContent = money(
                product.price
            );
        }

        if (variantTotalPrice) {
            variantTotalPrice.textContent =
                money(0);
        }

        if (variantTotalQuantity) {
            variantTotalQuantity.textContent =
                '0';
        }

        if (selectedColorOutput) {
            selectedColorOutput.textContent =
                'Required';
        }

        if (selectedSizeOutput) {
            selectedSizeOutput.textContent =
                '0 items selected';
        }

        if (variantSelectionList) {
            variantSelectionList.innerHTML = '';
        }

        if (variantSelectionEmpty) {
            variantSelectionEmpty.hidden = false;
        }

        if (variantSummaryCount) {
            variantSummaryCount.textContent =
                '0 items';
        }

        if (confirmButton) {
            confirmButton.disabled = false;

            confirmButton.textContent =
                selectedAction === 'buy_now'
                    ? 'Proceed to Checkout'
                    : 'Add Selected Items to Cart';
        }

        if (product.image_url) {
            if (variantImage) {
                variantImage.src =
                    product.image_url;

                variantImage.alt =
                    product.name || 'Product';

                variantImage.hidden = false;
            }

            if (variantImageFallback) {
                variantImageFallback.hidden = true;
            }
        } else {
            if (variantImage) {
                variantImage.removeAttribute('src');
                variantImage.alt = '';
                variantImage.hidden = true;
            }

            if (variantImageFallback) {
                variantImageFallback.hidden = false;

                variantImageFallback.textContent =
                    product.name
                        ?.charAt(0)
                        ?.toUpperCase() || 'P';
            }
        }

        renderColors(
            product.variants || []
        );

        if (sizeOptions) {
            sizeOptions.innerHTML = '';
        }

        if (sizeSection) {
            sizeSection.hidden = true;
        }

        variantModal?.classList.add('open');

        variantModal?.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'store-overlay-open'
        );
    }

    function closeVariantModal() {
        variantModal?.classList.remove('open');

        variantModal?.setAttribute(
            'aria-hidden',
            'true'
        );

        selectedProduct = null;
        selectedAction = 'cart';
        selectedColor = null;
        selectedColorHex = '';
        selectedSizesByColor = new Map();

        removeBodyOverlayIfUnused();
    }

    function renderColors(variantGroups) {
        if (!colorOptions) {
            return;
        }

        colorOptions.innerHTML = '';

        if (
            !Array.isArray(variantGroups) ||
            variantGroups.length === 0
        ) {
            if (colorSection) {
                colorSection.hidden = false;
            }

            colorOptions.innerHTML = `
                <div class="variant-empty-message">
                    No product colors are currently available.
                </div>
            `;

            if (generalError) {
                generalError.textContent =
                    'This product does not have any available variants.';
            }

            if (confirmButton) {
                confirmButton.disabled = true;
            }

            return;
        }

        if (colorSection) {
            colorSection.hidden = false;
        }

        variantGroups.forEach(function (group) {
            const color = String(
                group.color || ''
            ).trim();
            const colorHex = normalizeColorHex(
                group.color_hex || ''
            );

            const totalStock = Number(
                group.total_stock || 0
            );

            const available = totalStock > 0;

            const button =
                document.createElement('button');

            button.type = 'button';

            button.className =
                'variant-color-button';

            button.dataset.color = color;
            button.dataset.colorHex = colorHex;

            button.innerHTML = `
                <span class="variant-color-button-top">
                    ${colorSwatchHtml(
                        color,
                        colorHex,
                        'large'
                    )}
                </span>
                <strong>${escapeHtml(color)}</strong>
            `;

            if (!available) {
                button.disabled = true;

                button.classList.add(
                    'out-of-stock'
                );
            }

            button.addEventListener(
                'click',
                function () {
                    if (!available) {
                        return;
                    }

                    colorOptions
                        .querySelectorAll(
                            '.variant-color-button'
                        )
                        .forEach(function (
                            colorButton
                        ) {
                            colorButton.classList
                                .remove('selected');
                        });

                    button.classList.add(
                        'selected'
                    );

                    selectedColor = color;
                    selectedColorHex = colorHex;
                    if (selectedColorOutput) {
                        selectedColorOutput.textContent =
                            `${color} selected`;
                    }

                    if (colorError) {
                        colorError.textContent = '';
                    }

                    if (sizeError) {
                        sizeError.textContent = '';
                    }

                    if (generalError) {
                        generalError.textContent = '';
                    }

                    renderSizesForColor(
                        Array.isArray(group.sizes)
                            ? group.sizes
                            : []
                    );
                }
            );

            colorOptions.appendChild(button);
        });
    }

    function renderSizesForColor(sizes) {
        if (!sizeOptions) {
            return;
        }
    
        sizeOptions.innerHTML = '';

        if (sizeSection) {
            sizeSection.hidden = false;
        }

        const activeSelection =
            getActiveColorSelection();

        const sizeSelectionMap =
            activeSelection?.sizes
            || new Map();
    
        const availableSizes = Array.isArray(sizes)
            ? sizes.filter(function (item) {
                return Number(item.stock || 0) > 0;
            })
            : [];
    
        if (!availableSizes.length) {
            sizeOptions.innerHTML = `
                <div class="variant-empty-message">
                    No sizes are currently available for this color.
                </div>
            `;
    
            updateSelectedSizeSummary();
            updateVariantTotal();
    
            return;
        }
    
        availableSizes.forEach(function (item) {
            const size = String(
                item.size || ''
            ).trim();
    
            const stock = Math.max(
                0,
                Number(item.stock || 0)
            );
    
            const card =
                document.createElement('div');
    
            card.className =
                'variant-size-card';
    
            card.dataset.size = size;
    
            card.innerHTML = `
                <div class="variant-size-card-top">
                    <div>
                        <strong>
                            ${escapeHtml(size)}
                        </strong>
    
                        <span>
                            Choose quantity
                        </span>
                    </div>
    
                    <span class="variant-size-selected-badge">
                        Selected
                    </span>
                </div>
    
                <div class="variant-size-stepper">
                    <button
                        type="button"
                        data-size-decrease="${escapeHtml(size)}"
                        data-size-stock="${stock}"
                        aria-label="Decrease ${escapeHtml(size)} quantity"
                    >
                        −
                    </button>
    
                    <span
                        data-size-quantity="${escapeHtml(size)}"
                    >
                        ${Number(
                            sizeSelectionMap.get(size)
                                ?.quantity || 0
                        )}
                    </span>
    
                    <button
                        type="button"
                        data-size-increase="${escapeHtml(size)}"
                        data-size-stock="${stock}"
                        aria-label="Increase ${escapeHtml(size)} quantity"
                    >
                        +
                    </button>
                </div>
            `;
    
            card.classList.toggle(
                'selected',
                Number(
                    sizeSelectionMap.get(size)
                        ?.quantity || 0
                ) > 0
            );

            sizeOptions.appendChild(card);
        });
    
        updateSelectedSizeSummary();
        updateVariantTotal();
    }

    function getSelectedSizeQuantity(size) {
        const activeSelection =
            getActiveColorSelection();

        return Number(
            activeSelection?.sizes.get(size)
                ?.quantity || 0
        );
    }

    function updateSizeQuantity(
        size,
        quantity,
        stock
    ) {
        const numericStock = Math.max(
            0,
            Number(stock || 0)
        );

        const safeQuantity = Math.max(
            0,
            Math.min(
                Number(quantity || 0),
                numericStock
            )
        );

        const activeSelection =
            getActiveColorSelection();

        if (!activeSelection) {
            return;
        }

        if (safeQuantity === 0) {
            activeSelection.sizes.delete(size);
        } else {
            activeSelection.sizes.set(size, {
                size,
                quantity: safeQuantity,
                stock: numericStock,
            });
        }

        const output = sizeOptions?.querySelector(
            `[data-size-quantity="${CSS.escape(size)}"]`
        );

        if (output) {
            output.textContent =
                safeQuantity;
        }

        const row = output?.closest(
            '.variant-size-card'
        );

        row?.classList.toggle(
            'selected',
            safeQuantity > 0
        );

        if (sizeError) {
            sizeError.textContent = '';
        }

        if (generalError) {
            generalError.textContent = '';
        }

        updateSelectedSizeSummary();
        updateVariantTotal();
    }

    function updateSelectedSizeSummary() {
        const groupedSelections =
            getGroupedSelections();

        const items = groupedSelections.flatMap(
            function (group) {
                return group.items.map(function (item) {
                    return {
                        color: group.color,
                        colorHex: group.colorHex,
                        size: item.size,
                        quantity: item.quantity,
                    };
                });
            }
        );

        const totalQuantity = items.reduce(
            function (sum, item) {
                return sum + Number(
                    item.quantity || 0
                );
            },
            0
        );

        if (selectedSizeOutput) {
            selectedSizeOutput.textContent =
                totalQuantity > 0
                    ? `${items.length} size(s), ${totalQuantity} item(s)`
                    : '0 items selected';
        }

        if (variantSummaryCount) {
            variantSummaryCount.textContent =
                `${totalQuantity} ${
                    totalQuantity === 1
                        ? 'item'
                        : 'items'
                }`;
        }

        if (variantTotalQuantity) {
            variantTotalQuantity.textContent =
                totalQuantity;
        }

        if (!variantSelectionList) {
            return;
        }

        variantSelectionList.innerHTML = '';

        if (
            items.length === 0
        ) {
            if (variantSelectionEmpty) {
                variantSelectionEmpty.hidden = false;
            }

            return;
        }

        if (variantSelectionEmpty) {
            variantSelectionEmpty.hidden = true;
        }

        items.forEach(function (item) {
            const row =
                document.createElement('div');

            row.className =
                'variant-selection-item';

            row.innerHTML = `
                <div>
                    <strong>
                        ${colorLabelHtml(
                            item.color,
                            item.colorHex
                        )}
                        <span class="variant-selection-size-separator">/</span>
                        ${escapeHtml(item.size)}
                    </strong>

                    <span>
                        ${item.quantity}
                        ×
                        ${money(
                            selectedProduct?.price || 0
                        )}
                    </span>
                </div>

                <strong>
                    ${money(
                        Number(
                            selectedProduct?.price || 0
                        ) * Number(item.quantity)
                    )}
                </strong>
            `;

            variantSelectionList.appendChild(row);
        });
    }

    function updateVariantTotal() {
        if (!variantTotalPrice) {
            return;
        }

        const totalQuantity =
            getSelectedTotalQuantity();

        const unitPrice = Number(
            selectedProduct?.price || 0
        );

        variantTotalPrice.textContent = money(
            unitPrice * totalQuantity
        );
    }

    function validateVariantSelection() {
        let valid = true;

        if (colorError) {
            colorError.textContent = '';
        }

        if (sizeError) {
            sizeError.textContent = '';
        }

        if (generalError) {
            generalError.textContent = '';
        }

        if (!selectedProduct) {
            if (generalError) {
                generalError.textContent =
                    'Product information is unavailable.';
            }

            return false;
        }

        if (!selectedColor) {
            if (colorError) {
                colorError.textContent =
                    'Please select a color first.';
            }

            valid = false;
        }

        const groupedSelections =
            getGroupedSelections();

        if (groupedSelections.length === 0) {
            if (sizeError) {
                sizeError.textContent =
                    'Please select at least one size.';
            }

            valid = false;
        }

        groupedSelections.forEach(function (group) {
            group.items.forEach(function (item) {
                const quantity = Number(
                    item.quantity
                );

                const stock = Number(
                    item.stock
                );

                if (
                    quantity < 1 ||
                    quantity > stock
                ) {
                    if (generalError) {
                        generalError.textContent =
                            `Invalid quantity selected for ${group.color} / ${item.size}.`;
                    }

                    valid = false;
                }
            });
        });

        return valid;
    }

    /*
    |--------------------------------------------------------------------------
    | Add to Cart / Buy Now
    |--------------------------------------------------------------------------
    */

    async function addSelectedProductToCart() {
        if (!validateVariantSelection()) {
            return;
        }

        const productSnapshot = selectedProduct;
        const actionSnapshot = selectedAction || 'cart';
        const groupedSelections =
            getGroupedSelections();

        if (
            !productSnapshot ||
            !productSnapshot.product_id
        ) {
            const message =
                'Product information is unavailable.';
    
            if (generalError) {
                generalError.textContent = message;
            }
    
            showToast(message, 'error');
    
            return;
        }
    
        if (!groupedSelections.length) {
            const message =
                'Please select at least one size.';

            if (sizeError) {
                sizeError.textContent = message;
            }

            showToast(message, 'error');

            return;
        }

        if (generalError) {
            generalError.textContent = '';
        }

        if (colorError) {
            colorError.textContent = '';
        }

        if (sizeError) {
            sizeError.textContent = '';
        }

        if (confirmButton) {
            confirmButton.disabled = true;

            confirmButton.textContent =
                actionSnapshot === 'buy_now'
                    ? 'Preparing Checkout...'
                    : 'Adding Selected Items...';
        }

        try {
            let latestCart = currentCart;
            let latestMessage = '';

            for (const group of groupedSelections) {
                const response = await fetch('/cart', {
                    method: 'POST',

                    credentials: 'same-origin',

                    headers: {
                        Accept: 'application/json',

                        'Content-Type':
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',

                        'X-CSRF-TOKEN':
                            csrfToken || '',
                    },

                    body: JSON.stringify({
                        brand_id: Number(
                            productSnapshot.brand_id
                        ),

                        product_id: Number(
                            productSnapshot.product_id
                        ),

                        color: group.color,
                        color_hex: group.colorHex,

                        items: group.items.map(
                            function (item) {
                                return {
                                    size: String(
                                        item.size || ''
                                    ).trim(),

                                    quantity: Number(
                                        item.quantity || 0
                                    ),
                                };
                            }
                        ),
                    }),
                });

                if (response.status === 419) {
                    throw new Error(
                        'Your session has expired. Please refresh the page and try again.'
                    );
                }

                const data =
                    await readJsonResponse(response);

                if (!response.ok) {
                    const firstValidationError =
                        data?.errors
                            ? Object.values(
                                data.errors
                            )
                                .flat()
                                .find(Boolean)
                            : null;

                    throw new Error(
                        firstValidationError ||
                        data?.message ||
                        'Could not add product to cart.'
                    );
                }

                if (!data?.cart) {
                    throw new Error(
                        'Cart information was not returned by the server.'
                    );
                }

                latestCart = data.cart;
                latestMessage = String(
                    data?.message || ''
                ).trim();
            }

            renderCart(latestCart);

            closeVariantModal();

            /*
            |--------------------------------------------------------------------------
            | Buy Now
            |--------------------------------------------------------------------------
            */

            if (actionSnapshot === 'buy_now') {
                showToast(
                    latestMessage
                    || 'Selected items ready for checkout.'
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'storefront:buy-now',
                        {
                            detail: {
                                source: 'buy_now',

                                product:
                                    productSnapshot,

                                selections:
                                    groupedSelections,

                                cart:
                                    latestCart,
                            },
                        }
                    )
                );

                const checkoutModal =
                    document.getElementById(
                        'checkoutModal'
                    );
    
                if (!checkoutModal) {
                    openCartDrawer();
                }

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Normal Add to Cart
            |--------------------------------------------------------------------------
            */

            showToast(
                latestMessage
                || 'Selected items added to cart.'
            );

            openCartDrawer();
        } catch (error) {
            const message =
                error instanceof Error
                    ? error.message
                    : 'Could not add product to cart.';

            console.error(
                'Add to cart failed:',
                error
            );

            if (generalError) {
                generalError.textContent = message;
            }

            showToast(
                message,
                'error'
            );
        } finally {
            if (confirmButton) {
                confirmButton.disabled = false;

                confirmButton.textContent =
                    actionSnapshot === 'buy_now'
                        ? 'Proceed to Checkout'
                        : 'Add Selected Items to Cart';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cart Drawer
    |--------------------------------------------------------------------------
    */

    function openCartDrawer() {
        cartDrawerWrapper?.classList.add(
            'open'
        );

        cartDrawerWrapper?.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'store-overlay-open'
        );

        document.dispatchEvent(
            new CustomEvent('cart-drawer:open')
        );
    }

    function closeCartDrawer() {
        cartDrawerWrapper?.classList.remove(
            'open'
        );

        cartDrawerWrapper?.setAttribute(
            'aria-hidden',
            'true'
        );

        removeBodyOverlayIfUnused();

        document.dispatchEvent(
            new CustomEvent('cart-drawer:close')
        );
    }

    function removeBodyOverlayIfUnused() {
        const variantIsOpen =
            variantModal?.classList.contains(
                'open'
            );

        const cartIsOpen =
            cartDrawerWrapper?.classList.contains(
                'open'
            );

        const wishlistIsOpen =
            wishlistModalWrapper?.classList.contains(
                'open'
            );

        if (!variantIsOpen && !cartIsOpen && !wishlistIsOpen) {
            document.body.classList.remove(
                'store-overlay-open'
            );
        }
    }

    function renderCart(cart) {
        currentCart = {
            items: Array.isArray(cart?.items)
                ? cart.items
                : [],
    
            count: Number(
                cart?.count || 0
            ),
    
            items_total: Number(
                cart?.items_total || 0
            ),
        };
    
        const items = currentCart.items;
        const count = currentCart.count;
        const itemsTotal = currentCart.items_total;
    
        if (floatingCartCount) {
            floatingCartCount.textContent =
                String(count);
        }

        if (headerCartCount) {
            headerCartCount.textContent =
                String(count);
        }
    
        if (cartDrawerCount) {
            cartDrawerCount.textContent =
                `${count} ${
                    count === 1
                        ? 'item'
                        : 'items'
                }`;
        }
    
        if (cartDrawerSubtotal) {
            cartDrawerSubtotal.textContent =
                money(itemsTotal);
        }
    
        if (!cartDrawerItems) {
            return;
        }
    
        if (!items.length) {
            cartDrawerItems.innerHTML = `
                <div class="cart-empty-state">
                    <strong>
                        Your cart is empty
                    </strong>
    
                    <p>
                        Add products to continue shopping.
                    </p>
                </div>
            `;
    
            if (cartDrawerFooter) {
                cartDrawerFooter.hidden = true;
            }
    
            return;
        }
    
        if (cartDrawerFooter) {
            cartDrawerFooter.hidden = false;
        }
    
        cartDrawerItems.innerHTML = '';
    
        items.forEach(function (item) {
            const row =
                document.createElement('article');
    
            row.className =
                'cart-drawer-item';
    
            const productName =
                escapeHtml(
                    item.product_name || 'Product'
                );
    
            const productCode =
                escapeHtml(
                    item.product_code || ''
                );

            const brandName =
                escapeHtml(
                    item.brand_name || ''
                );

            const brandSlug = escapeHtml(
                String(item.brand_name || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
            );

            const imageUrl =
                item.image_url
                    ? escapeHtml(item.image_url)
                    : '';
    
            const size =
                escapeHtml(item.size || '');
    
            const color =
                escapeHtml(item.color || '');

            const colorHex = normalizeColorHex(
                item.color_hex || ''
            );
    
            const cartKey =
                escapeHtml(item.key || '');
    
            const quantity = Math.max(
                1,
                Number(item.quantity || 1)
            );
    
            const lineTotal = Number(
                item.line_total || 0
            );
    
            const fallbackLetter =
                productName
                    .charAt(0)
                    .toUpperCase() || 'P';
    
            row.innerHTML = `
                <div class="cart-item-image">
                    ${
                        imageUrl
                            ? `
                                <img
                                    src="${imageUrl}"
                                    alt="${productName}"
                                >
                            `
                            : `
                                <span>
                                    ${fallbackLetter}
                                </span>
                            `
                    }
                </div>
    
                <div class="cart-item-info">
                    <div class="cart-item-top">
                        <div>
                            ${
                                productCode
                                    ? `
                                        <span>
                                            ${productCode}
                                        </span>
                                    `
                                    : ''
                            }

                            ${
                                brandName
                                    ? `
                                        <em
                                            class="cart-item-brand-badge"
                                            data-brand-slug="${brandSlug}"
                                        >
                                            ${brandName}
                                        </em>
                                    `
                                    : ''
                            }

                            <h3>
                                ${productName}
                            </h3>
                        </div>
    
                        <button
                            type="button"
                            class="cart-item-remove"
                            data-cart-remove="${cartKey}"
                            aria-label="Remove product"
                        >
                            ×
                        </button>
                    </div>
    
                    <div class="cart-item-variants">
                        ${
                            color
                                ? `
                                    ${colorLabelHtml(
                                        color,
                                        colorHex
                                    )}
                                `
                                : ''
                        }
    
                        ${
                            size
                                ? `
                                    <span>
                                        Size: ${size}
                                    </span>
                                `
                                : ''
                        }
                    </div>
    
                    <div class="cart-item-bottom">
                        <div class="cart-item-quantity">
                            <button
                                type="button"
                                data-cart-decrease="${cartKey}"
                                data-current-quantity="${quantity}"
                                aria-label="Decrease quantity"
                            >
                                −
                            </button>
    
                            <span>
                                ${quantity}
                            </span>
    
                            <button
                                type="button"
                                data-cart-increase="${cartKey}"
                                data-current-quantity="${quantity}"
                                aria-label="Increase quantity"
                            >
                                +
                            </button>
                        </div>
    
                        <strong>
                            ${money(lineTotal)}
                        </strong>
                    </div>
                </div>
            `;
    
            cartDrawerItems.appendChild(row);
        });
    }

    async function loadCart(
        openDrawer = false
    ) {
        try {
            const response = await fetch(
                '/cart',
                {
                    method: 'GET',

                    headers: {
                        Accept:
                            'application/json',
                    },
                }
            );

            const data =
                await readJsonResponse(
                    response
                );

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    'Could not load cart.'
                );
            }

            renderCart(data.cart);

            if (openDrawer) {
                openCartDrawer();
            }
        } catch (error) {
            console.error(
                'Cart load failed:',
                error
            );

            if (openDrawer) {
                showToast(
                    error.message,
                    'error'
                );
            }
        }
    }

    async function updateCartQuantity(
        cartKey,
        quantity
    ) {
        if (
            !cartKey ||
            Number(quantity) < 1
        ) {
            return;
        }

        try {
            const response = await fetch(
                `/cart/${encodeURIComponent(cartKey)}`,
                {
                    method: 'PATCH',

                    headers: {
                        Accept:
                            'application/json',

                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken || '',
                    },

                    body: JSON.stringify({
                        quantity:
                            Number(quantity),
                    }),
                }
            );

            const data =
                await readJsonResponse(
                    response
                );

            if (!response.ok) {
                const firstValidationError =
                    data?.errors
                        ? Object.values(
                            data.errors
                        ).flat()[0]
                        : null;

                throw new Error(
                    firstValidationError ||
                    data.message ||
                    'Could not update cart.'
                );
            }

            renderCart(data.cart);

            showToast(
                data.message ||
                'Cart updated.'
            );
        } catch (error) {
            showToast(
                error.message,
                'error'
            );
        }
    }

    async function removeCartItem(
        cartKey
    ) {
        if (!cartKey) {
            return;
        }

        try {
            const response = await fetch(
                `/cart/${encodeURIComponent(cartKey)}`,
                {
                    method: 'DELETE',

                    headers: {
                        Accept:
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken || '',
                    },
                }
            );

            const data =
                await readJsonResponse(
                    response
                );

            if (!response.ok) {
                throw new Error(
                    data.message ||
                    'Could not remove product.'
                );
            }

            renderCart(data.cart);

            showToast(
                data.message ||
                'Product removed from cart.'
            );
        } catch (error) {
            showToast(
                error.message,
                'error'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delegated Click Handler
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {
            const productButton =
                event.target.closest(
                    '.js-product-action'
                );

            if (productButton) {
                event.preventDefault();

                try {
                    const product =
                        readProductPayload(
                            productButton
                        );

                    openVariantModal(
                        product,
                        productButton.dataset
                            .action || 'cart'
                    );
                } catch (error) {
                    console.error(
                        'Product payload error:',
                        error
                    );

                    showToast(
                        error.message ||
                        'Product information could not be loaded.',
                        'error'
                    );
                }

                return;
            }

            const sizeIncreaseButton =
                event.target.closest(
                    '[data-size-increase]'
                );

            if (sizeIncreaseButton) {
                event.preventDefault();

                const size =
                    sizeIncreaseButton.dataset
                        .sizeIncrease;

                const stock = Number(
                    sizeIncreaseButton.dataset
                        .sizeStock || 0
                );

                const currentQuantity =
                    getSelectedSizeQuantity(
                        size
                    );

                if (
                    currentQuantity >= stock
                ) {
                    showToast(
                        `Only ${stock} item(s) available for ${size}.`,
                        'error'
                    );

                    return;
                }

                updateSizeQuantity(
                    size,
                    currentQuantity + 1,
                    stock
                );

                return;
            }

            const sizeDecreaseButton =
                event.target.closest(
                    '[data-size-decrease]'
                );

            if (sizeDecreaseButton) {
                event.preventDefault();

                const size =
                    sizeDecreaseButton.dataset
                        .sizeDecrease;

                const stock = Number(
                    sizeDecreaseButton.dataset
                        .sizeStock || 0
                );

                const currentQuantity =
                    getSelectedSizeQuantity(
                        size
                    );

                updateSizeQuantity(
                    size,
                    currentQuantity - 1,
                    stock
                );

                return;
            }

            const removeButton =
                event.target.closest(
                    '[data-cart-remove]'
                );

            if (removeButton) {
                event.preventDefault();

                removeCartItem(
                    removeButton.dataset
                        .cartRemove
                );

                return;
            }

            const cartIncreaseButton =
                event.target.closest(
                    '[data-cart-increase]'
                );

            if (cartIncreaseButton) {
                event.preventDefault();

                const currentQuantity =
                    Number(
                        cartIncreaseButton
                            .dataset
                            .currentQuantity || 1
                    );

                updateCartQuantity(
                    cartIncreaseButton.dataset
                        .cartIncrease,

                    currentQuantity + 1
                );

                return;
            }

            const cartDecreaseButton =
                event.target.closest(
                    '[data-cart-decrease]'
                );

            if (cartDecreaseButton) {
                event.preventDefault();

                const currentQuantity =
                    Number(
                        cartDecreaseButton
                            .dataset
                            .currentQuantity || 1
                    );

                const nextQuantity =
                    currentQuantity - 1;

                if (nextQuantity < 1) {
                    return;
                }

                updateCartQuantity(
                    cartDecreaseButton.dataset
                        .cartDecrease,

                    nextQuantity
                );
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Direct Events
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[data-close-variant-modal]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeVariantModal
            );
        });

    document
        .querySelectorAll(
            '[data-close-cart-drawer]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeCartDrawer
            );
        });

    document
        .querySelectorAll('[data-open-wishlist]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                openWishlistModal
            );
        });

    document
        .querySelectorAll('[data-close-wishlist]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                closeWishlistModal
            );
        });

    document.addEventListener(
        'click',
        function (event) {
            const wishlistButton = event.target.closest(
                '[data-wishlist-button]'
            );

            if (wishlistButton) {
                event.preventDefault();
                event.stopPropagation();

                toggleWishlist(wishlistButton);

                return;
            }

            const removeButton = event.target.closest(
                '[data-wishlist-remove]'
            );

            if (!removeButton) {
                return;
            }

            wishlistItems = wishlistItems.filter(function (item) {
                return String(item.id) !== String(
                    removeButton.dataset.wishlistRemove
                );
            });

            saveWishlist();
            syncWishlistButtons();
            renderWishlist();
            showToast('Removed from wishlist.', 'success');
        }
    );

    confirmButton?.addEventListener(
        'click',
        addSelectedProductToCart
    );

    floatingCartButton?.addEventListener(
        'click',
        function () {
            loadCart(true);
        }
    );

    openCartCheckoutButton
        ?.addEventListener(
            'click',
            function () {
                window.dispatchEvent(
                    new CustomEvent(
                        'storefront:open-checkout',
                        {
                            detail: {
                                source: 'cart',
                            },
                        }
                    )
                );
            }
        );

    window.addEventListener(
        'storefront:order-created',
        function () {
            loadCart(false);
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (
                variantModal?.classList
                    .contains('open')
            ) {
                closeVariantModal();
                return;
            }

            if (
                cartDrawerWrapper?.classList
                    .contains('open')
            ) {
                closeCartDrawer();
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Initial Load
    |--------------------------------------------------------------------------
    */

    wishlistItems = readWishlist();
    syncWishlistButtons();
    renderWishlist();

    loadCart();
});
