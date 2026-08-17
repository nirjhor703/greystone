document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('productCrudPage');

    if (!page) {
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const addForm = document.getElementById('addProductForm');
    const editForm = document.getElementById('editProductForm');

    const addSubmit = document.getElementById(
        'addProductSubmitButton'
    );

    const editSubmit = document.getElementById(
        'editProductSubmitButton'
    );

    const deleteSubmit = document.getElementById(
        'confirmDeleteProductButton'
    );

    let deletedImageIds = [];
    let toastTimer = null;
    let activeVariantPickerCard = null;

    const productSizes = [
        '3XS',
        '2XS',
        'XS',
        'S',
        'M',
        'L',
        'XL',
        '2XL',
        '3XL',
        '4XL',
        '5XL',
        '6XL',
        '7XL',
    ];

    function normalizeHexColor(value) {
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

    function suggestedHexFromName(value) {
        const normalized = String(value || '')
            .trim()
            .toLocaleLowerCase();

        if (!normalized) {
            return '#111111';
        }

        const colorMap = [
            [/black|jet|charcoal/, '#111111'],
            [/white|ivory|cream/, '#F8FAFC'],
            [/grey|gray|ash|silver/, '#9CA3AF'],
            [/navy|midnight/, '#1E3A8A'],
            [/blue|ocean|denim|sky/, '#2563EB'],
            [/green|olive|mint/, '#16A34A'],
            [/yellow|gold|mustard/, '#FACC15'],
            [/orange|peach|coral/, '#F97316'],
            [/red|maroon|wine/, '#DC2626'],
            [/pink|rose|blush/, '#EC4899'],
            [/purple|violet|lavender/, '#7C3AED'],
            [/brown|chocolate|coffee|tan|beige/, '#8B5E3C'],
        ];

        const match = colorMap.find(function ([pattern]) {
            return pattern.test(normalized);
        });

        return match ? match[1] : '#64748B';
    }

    function variantGroupKey(group = {}) {
        const colorHex = normalizeHexColor(
            group?.color_hex || ''
        );

        if (colorHex) {
            return `hex:${colorHex}`;
        }

        return `name:${String(group?.color || '')
            .trim()
            .toLocaleLowerCase()}`;
    }

    function getVariantList(prefix) {
        return document.getElementById(
            `${prefix}_variant_list`
        );
    }
    
    function getVariantEmptyState(prefix) {
        return document.getElementById(
            `${prefix}_variant_empty`
        );
    }
    
    function normalizeVariantGroups(groups = []) {
        if (!Array.isArray(groups)) {
            return [];
        }
    
        return groups.map((group) => {
            const sizes = {};
    
            productSizes.forEach((size) => {
                sizes[size] = Math.max(
                    0,
                    Number(group?.sizes?.[size] || 0)
                );
            });
    
            return {
                color: String(group?.color || ''),
                color_hex:
                    normalizeHexColor(
                        group?.color_hex || ''
                    )
                    || suggestedHexFromName(
                        group?.color || ''
                    ),
                sizes,
            };
        });
    }
    
    function calculateVariantGroupTotal(group) {
        return productSizes.reduce((total, size) => {
            return total + Math.max(
                0,
                Number(group?.sizes?.[size] || 0)
            );
        }, 0);
    }

    function rgbToHex(red, green, blue) {
        return normalizeHexColor(
            `#${[red, green, blue]
                .map((value) =>
                    Math.max(0, Math.min(255, value))
                        .toString(16)
                        .padStart(2, '0')
                )
                .join('')}`
        );
    }

    function hexToRgb(hex) {
        const normalized = normalizeHexColor(hex);

        if (!normalized) {
            return { red: 17, green: 17, blue: 17 };
        }

        return {
            red: Number.parseInt(normalized.slice(1, 3), 16),
            green: Number.parseInt(normalized.slice(3, 5), 16),
            blue: Number.parseInt(normalized.slice(5, 7), 16),
        };
    }

    function rgbToHsv(red, green, blue) {
        const r = red / 255;
        const g = green / 255;
        const b = blue / 255;
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const delta = max - min;
        let hue = 0;

        if (delta !== 0) {
            if (max === r) {
                hue = ((g - b) / delta) % 6;
            } else if (max === g) {
                hue = (b - r) / delta + 2;
            } else {
                hue = (r - g) / delta + 4;
            }
        }

        hue = Math.round(hue * 60);

        if (hue < 0) {
            hue += 360;
        }

        return {
            h: hue,
            s: max === 0 ? 0 : delta / max,
            v: max,
        };
    }

    function hsvToRgb(hue, saturation, value) {
        const chroma = value * saturation;
        const segment = hue / 60;
        const x = chroma * (1 - Math.abs((segment % 2) - 1));
        let r = 0;
        let g = 0;
        let b = 0;

        if (segment >= 0 && segment < 1) {
            r = chroma; g = x; b = 0;
        } else if (segment < 2) {
            r = x; g = chroma; b = 0;
        } else if (segment < 3) {
            r = 0; g = chroma; b = x;
        } else if (segment < 4) {
            r = 0; g = x; b = chroma;
        } else if (segment < 5) {
            r = x; g = 0; b = chroma;
        } else {
            r = chroma; g = 0; b = x;
        }

        const match = value - chroma;

        return {
            red: Math.round((r + match) * 255),
            green: Math.round((g + match) * 255),
            blue: Math.round((b + match) * 255),
        };
    }
    
    function variantCardHtml(prefix, index, group = {}) {
        const color = escapeHtml(group.color || '');
        const colorHex =
            normalizeHexColor(
                group.color_hex || ''
            )
            || suggestedHexFromName(
                group.color || ''
            );

        const sizeInputs = productSizes
            .map((size, sizeIndex) => {
                const quantity = Math.max(
                    0,
                    Number(group?.sizes?.[size] || 0)
                );
    
                return `
                    <div class="product-color-size-item">
                        <label
                            for="${prefix}_variant_${index}_size_${sizeIndex}"
                        >
                            ${escapeHtml(size)}
                        </label>
    
                        <input
                            type="number"
                            id="${prefix}_variant_${index}_size_${sizeIndex}"
                            name="variants[${index}][sizes][${escapeHtml(size)}]"
                            class="product-variant-stock-input"
                            data-variant-index="${index}"
                            data-variant-size="${escapeHtml(size)}"
                            min="0"
                            step="1"
                            value="${quantity}"
                            placeholder="0"
                        >
                    </div>
                `;
            })
            .join('');
    
        const total = calculateVariantGroupTotal(group);
    
        return `
            <article
                class="product-color-variant-card"
                data-variant-card
                data-variant-index="${index}"
            >
                <div class="product-color-variant-card-header">
                    <div class="product-color-name-field">
                        <label
                            for="${prefix}_variant_${index}_color"
                        >
                            Color Name <span>*</span>
                        </label>
    
                        <input
                            type="text"
                            id="${prefix}_variant_${index}_color"
                            name="variants[${index}][color]"
                            class="product-variant-color-input"
                            data-variant-index="${index}"
                            value="${color}"
                            placeholder="Example: Black"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >

                        <div class="product-variant-color-tools">
                            <div class="product-variant-color-picker-shell">
                                <button
                                    type="button"
                                    class="product-variant-color-picker"
                                    data-open-advanced-picker="${index}"
                                >
                                    <span
                                        class="product-variant-color-preview"
                                        style="--variant-preview:${escapeHtml(colorHex || '#111111')};"
                                    ></span>

                                    <strong>Color</strong>
                                </button>

                                <input
                                    type="hidden"
                                    id="${prefix}_variant_${index}_color_hex"
                                    name="variants[${index}][color_hex]"
                                    data-variant-color-hex="${index}"
                                    value="${escapeHtml(colorHex || '#111111')}"
                                >

                                <button
                                    type="button"
                                    class="product-variant-eyedropper-button"
                                    data-variant-eyedropper="${index}"
                                >
                                    Pick from screen
                                </button>
                            </div>

                            <div
                                class="product-variant-advanced-picker"
                                data-advanced-picker-panel
                                hidden
                            >
                                <div class="product-variant-advanced-picker-head">
                                    <strong>Pick a Color</strong>

                                    <button
                                        type="button"
                                        data-close-advanced-picker
                                    >
                                        ×
                                    </button>
                                </div>

                                <div class="product-variant-advanced-picker-body">
                                    <div
                                        class="product-variant-spectrum"
                                        data-color-spectrum
                                    >
                                        <div class="product-variant-spectrum-white"></div>
                                        <div class="product-variant-spectrum-black"></div>
                                        <button
                                            type="button"
                                            class="product-variant-spectrum-handle"
                                            data-spectrum-handle
                                            aria-label="Move color handle"
                                        ></button>
                                    </div>

                                    <div
                                        class="product-variant-hue-slider"
                                        data-hue-slider
                                    >
                                        <button
                                            type="button"
                                            class="product-variant-hue-handle"
                                            data-hue-handle
                                            aria-label="Move hue handle"
                                        ></button>
                                    </div>

                                    <div class="product-variant-picker-side">
                                        <div
                                            class="product-variant-picker-preview"
                                            data-picker-preview
                                        ></div>

                                        <label class="product-variant-picker-hex">
                                            <span>#</span>
                                            <input
                                                type="text"
                                                maxlength="6"
                                                data-picker-hex-input
                                                value="${escapeHtml((colorHex || '#111111').replace('#', ''))}"
                                            >
                                        </label>

                                        <div class="product-variant-picker-actions">
                                            <button
                                                type="button"
                                                class="brand-secondary-button"
                                                data-close-advanced-picker
                                            >
                                                Cancel
                                            </button>

                                            <button
                                                type="button"
                                                class="brand-primary-button"
                                                data-apply-advanced-picker
                                            >
                                                OK
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <small
                            class="product-color-input-error"
                            data-color-error="${index}"
                        ></small>
                    </div>
    
                    <button
                        type="button"
                        class="product-remove-color-button"
                        data-remove-product-color="${prefix}"
                        data-variant-index="${index}"
                    >
                        Remove Color
                    </button>
                </div>
    
                <div class="product-color-variant-card-body">
                    <div class="product-color-size-heading">
                        <strong>Size-wise Stock</strong>
    
                        <span>
                            Empty or zero means unavailable
                        </span>
                    </div>
    
                    <div class="product-color-size-grid">
                        ${sizeInputs}
                    </div>
    
                    <div class="product-color-variant-summary">
                        Total stock for this color:
    
                        <strong
                            data-variant-total="${index}"
                        >
                            ${total}
                        </strong>
                    </div>
                </div>
            </article>
        `;
    }
    
    function renderVariantGroups(prefix, groups = []) {
        const list = getVariantList(prefix);
        const emptyState = getVariantEmptyState(prefix);
    
        if (!list) {
            return;
        }
    
        const normalizedGroups =
            normalizeVariantGroups(groups);
    
        list.innerHTML = '';
    
        normalizedGroups.forEach((group, index) => {
            list.insertAdjacentHTML(
                'beforeend',
                variantCardHtml(
                    prefix,
                    index,
                    group
                )
            );
        });
    
        emptyState?.classList.toggle(
            'hidden',
            normalizedGroups.length > 0
        );
    }
    
    function readVariantGroups(prefix) {
        const list = getVariantList(prefix);
    
        if (!list) {
            return [];
        }
    
        return Array.from(
            list.querySelectorAll('[data-variant-card]')
        ).map((card) => {
            const colorInput = card.querySelector(
                '.product-variant-color-input'
            );
    
            const sizes = {};
    
            productSizes.forEach((size) => {
                const input = card.querySelector(
                    `[data-variant-size="${CSS.escape(size)}"]`
                );
    
                sizes[size] = Math.max(
                    0,
                    Number(input?.value || 0)
                );
            });
    
            return {
                color: colorInput?.value?.trim() || '',
                color_hex: normalizeHexColor(
                    card.querySelector(
                        '[data-variant-color-hex]'
                    )?.value || ''
                ),
                sizes,
            };
        });
    }
    
    function addVariantGroup(prefix) {
        const groups = readVariantGroups(prefix);
    
        groups.push({
            color: '',
            color_hex: '',
            sizes: {},
        });
    
        renderVariantGroups(prefix, groups);
    
        const list = getVariantList(prefix);
    
        const latestColorInput = list?.querySelector(
            '.product-color-variant-card:last-child .product-variant-color-input'
        );
    
        latestColorInput?.focus();
    }
    
    function removeVariantGroup(prefix, index) {
        const groups = readVariantGroups(prefix);
    
        groups.splice(Number(index), 1);
    
        renderVariantGroups(prefix, groups);
    }
    
    function resetVariants(prefix) {
        renderVariantGroups(prefix, []);
    }
    
    function updateVariantCardTotal(input) {
        const card = input.closest(
            '[data-variant-card]'
        );
    
        if (!card) {
            return;
        }
    
        const index = card.dataset.variantIndex;
    
        const total = Array.from(
            card.querySelectorAll(
                '.product-variant-stock-input'
            )
        ).reduce((sum, stockInput) => {
            return sum + Math.max(
                0,
                Number(stockInput.value || 0)
            );
        }, 0);
    
        const totalOutput = card.querySelector(
            `[data-variant-total="${index}"]`
        );
    
        if (totalOutput) {
            totalOutput.textContent = total;
        }
    }
    
    function clearVariantErrors(prefix) {
        const list = getVariantList(prefix);
    
        list
            ?.querySelectorAll(
                '.product-color-input-error'
            )
            .forEach((error) => {
                error.textContent = '';
            });
    
        list
            ?.querySelectorAll(
                '.product-variant-color-input'
            )
            .forEach((input) => {
                input.classList.remove(
                    'brand-input-invalid'
                );
            });
    }
    
    function validateVariantForm(prefix) {
        const groups = readVariantGroups(prefix);
        const form = document.getElementById(
            `${prefix}ProductForm`
        );
    
        const generalError = form?.querySelector(
            '.variants_error'
        );
    
        clearVariantErrors(prefix);
    
        if (generalError) {
            generalError.textContent = '';
        }
    
        if (groups.length === 0) {
            if (generalError) {
                generalError.textContent =
                    'Please add at least one product color.';
            }
    
            return false;
        }
    
        const usedColors = new Set();
        let valid = true;
    
        groups.forEach((group, index) => {
            const normalizedColor =
                variantGroupKey(group);
    
            const input = document.getElementById(
                `${prefix}_variant_${index}_color`
            );
    
            const errorOutput = document.querySelector(
                `#${prefix}_variant_list [data-color-error="${index}"]`
            );
    
            if (!normalizedColor) {
                valid = false;
    
                input?.classList.add(
                    'brand-input-invalid'
                );
    
                if (errorOutput) {
                    errorOutput.textContent =
                        'Color name is required.';
                }
    
                return;
            }
    
            if (
                normalizedColor !== 'name:' &&
                usedColors.has(normalizedColor)
            ) {
                valid = false;
    
                input?.classList.add(
                    'brand-input-invalid'
                );
    
                if (errorOutput) {
                    errorOutput.textContent =
                        'This color was already added.';
                }
    
                return;
            }
    
            if (normalizedColor !== 'name:') {
                usedColors.add(normalizedColor);
            }
        });
    
        if (!valid && generalError) {
            generalError.textContent =
                'Please correct the product variant information.';
        }
    
        return valid;
    }

    function updateVariantSwatchState(card) {
        const colorHexInput = card?.querySelector(
            '[data-variant-color-hex]'
        );

        const colorHex = normalizeHexColor(
            colorHexInput?.value || ''
        );

        const preview = card?.querySelector(
            '.product-variant-color-preview'
        );

        const previewText = card?.querySelector(
            '.product-variant-color-picker strong'
        );

        if (preview) {
            preview.style.setProperty(
                '--variant-preview',
                colorHex || '#111111'
            );
        }

        if (previewText) {
            previewText.textContent =
                colorHex || '#111111';
        }

        card?.querySelectorAll('[data-variant-swatch]')
            .forEach((button) => {
                button.classList.toggle(
                    'active',
                    normalizeHexColor(
                        button.dataset.variantSwatch
                    ) === colorHex
                );
            });
    }

    function ensurePickerState(card) {
        if (card._advancedPickerState) {
            return card._advancedPickerState;
        }

        const rgb = hexToRgb(
            card.querySelector('[data-variant-color-hex]')
                ?.value || '#111111'
        );

        const hsv = rgbToHsv(
            rgb.red,
            rgb.green,
            rgb.blue
        );

        card._advancedPickerState = {
            h: hsv.h,
            s: hsv.s,
            v: hsv.v,
            draftHex: rgbToHex(
                rgb.red,
                rgb.green,
                rgb.blue
            ) || '#111111',
        };

        return card._advancedPickerState;
    }

    function syncAdvancedPickerUI(card) {
        const state = ensurePickerState(card);
        const panel = card.querySelector(
            '[data-advanced-picker-panel]'
        );

        if (!panel) {
            return;
        }

        const spectrum = panel.querySelector(
            '[data-color-spectrum]'
        );
        const spectrumHandle = panel.querySelector(
            '[data-spectrum-handle]'
        );
        const hueSlider = panel.querySelector(
            '[data-hue-slider]'
        );
        const hueHandle = panel.querySelector(
            '[data-hue-handle]'
        );
        const preview = panel.querySelector(
            '[data-picker-preview]'
        );
        const hexInput = panel.querySelector(
            '[data-picker-hex-input]'
        );

        const pureHue = hsvToRgb(state.h, 1, 1);
        const hueHex = rgbToHex(
            pureHue.red,
            pureHue.green,
            pureHue.blue
        );

        const finalRgb = hsvToRgb(
            state.h,
            state.s,
            state.v
        );

        state.draftHex = rgbToHex(
            finalRgb.red,
            finalRgb.green,
            finalRgb.blue
        ) || '#111111';

        spectrum?.style.setProperty(
            '--picker-hue',
            hueHex || '#FF0000'
        );

        if (spectrumHandle) {
            spectrumHandle.style.left = `${state.s * 100}%`;
            spectrumHandle.style.top = `${(1 - state.v) * 100}%`;
        }

        if (hueHandle) {
            hueHandle.style.top = `${(state.h / 360) * 100}%`;
        }

        if (preview) {
            preview.style.background =
                state.draftHex;
        }

        if (hexInput) {
            hexInput.value = state.draftHex.replace(
                '#',
                ''
            );
        }
    }

    function updatePickerFromSpectrum(card, event) {
        const panel = card.querySelector(
            '[data-advanced-picker-panel]'
        );
        const spectrum = panel?.querySelector(
            '[data-color-spectrum]'
        );

        if (!spectrum) {
            return;
        }

        const rect = spectrum.getBoundingClientRect();
        const state = ensurePickerState(card);
        const x = Math.max(
            0,
            Math.min(
                rect.width,
                event.clientX - rect.left
            )
        );
        const y = Math.max(
            0,
            Math.min(
                rect.height,
                event.clientY - rect.top
            )
        );

        state.s = rect.width === 0 ? 0 : x / rect.width;
        state.v = rect.height === 0 ? 0 : 1 - (y / rect.height);
        syncAdvancedPickerUI(card);
    }

    function updatePickerFromHue(card, event) {
        const panel = card.querySelector(
            '[data-advanced-picker-panel]'
        );
        const hueSlider = panel?.querySelector(
            '[data-hue-slider]'
        );

        if (!hueSlider) {
            return;
        }

        const rect = hueSlider.getBoundingClientRect();
        const y = Math.max(
            0,
            Math.min(
                rect.height,
                event.clientY - rect.top
            )
        );
        const state = ensurePickerState(card);

        state.h = rect.height === 0
            ? 0
            : Math.round((y / rect.height) * 360);

        syncAdvancedPickerUI(card);
    }

    function openAdvancedPicker(card) {
        const panel = card.querySelector(
            '[data-advanced-picker-panel]'
        );

        if (!panel) {
            return;
        }

        card.querySelectorAll('[data-advanced-picker-panel]')
            .forEach((item) => {
                item.hidden = true;
            });

        const colorHexInput = card.querySelector(
            '[data-variant-color-hex]'
        );
        const rgb = hexToRgb(
            colorHexInput?.value || '#111111'
        );
        const hsv = rgbToHsv(
            rgb.red,
            rgb.green,
            rgb.blue
        );

        card._advancedPickerState = {
            h: hsv.h,
            s: hsv.s,
            v: hsv.v,
            draftHex: rgbToHex(
                rgb.red,
                rgb.green,
                rgb.blue
            ) || '#111111',
        };

        panel.hidden = false;
        syncAdvancedPickerUI(card);
    }

    function closeAdvancedPicker(card) {
        card?.querySelectorAll('[data-advanced-picker-panel]')
            .forEach((item) => {
                item.hidden = true;
            });
    }

    function getVariantPickerScope(card) {
        return card?.closest('.product-modal-dialog');
    }

    function stopVariantPickerMode() {
        document
            .querySelectorAll('.product-modal-dialog.color-picking-mode')
            .forEach((dialog) => {
                dialog.classList.remove('color-picking-mode');
            });

        activeVariantPickerCard = null;
    }

    function startVariantPickerMode(card) {
        stopVariantPickerMode();

        const dialog = getVariantPickerScope(card);

        if (!dialog) {
            return;
        }

        activeVariantPickerCard = card;
        dialog.classList.add('color-picking-mode');

        showToast(
            'Now click any product image inside this modal to pick a color.'
        );
    }

    function setVariantColorHex(card, hex) {
        const colorHexInput = card?.querySelector(
            '[data-variant-color-hex]'
        );

        if (!colorHexInput) {
            return;
        }

        colorHexInput.value = normalizeHexColor(hex) || '#111111';
        updateVariantSwatchState(card);
    }

    function sampleColorFromImage(image, clientX, clientY) {
        const rect = image.getBoundingClientRect();

        if (!rect.width || !rect.height) {
            return '';
        }

        const scaleX = image.naturalWidth / rect.width;
        const scaleY = image.naturalHeight / rect.height;

        const x = Math.max(
            0,
            Math.min(
                image.naturalWidth - 1,
                Math.floor((clientX - rect.left) * scaleX)
            )
        );

        const y = Math.max(
            0,
            Math.min(
                image.naturalHeight - 1,
                Math.floor((clientY - rect.top) * scaleY)
            )
        );

        const canvas = document.createElement('canvas');
        canvas.width = image.naturalWidth;
        canvas.height = image.naturalHeight;

        const context = canvas.getContext('2d', {
            willReadFrequently: true,
        });

        if (!context) {
            return '';
        }

        context.drawImage(
            image,
            0,
            0,
            image.naturalWidth,
            image.naturalHeight
        );

        const [red, green, blue] = context.getImageData(
            x,
            y,
            1,
            1
        ).data;

        return normalizeHexColor(
            `#${[red, green, blue]
                .map((value) =>
                    Number(value)
                        .toString(16)
                        .padStart(2, '0')
                )
                .join('')}`
        );
    }

    function setBooleanToggle(inputId, enabled) {
        const input = document.getElementById(inputId);
    
        const button = document.querySelector(
            `[data-toggle-target="${inputId}"]`
        );
    
        if (!input || !button) {
            return;
        }
    
        input.value = enabled ? '1' : '0';
    
        button.classList.toggle(
            'active',
            enabled
        );
    
        button.setAttribute(
            'aria-pressed',
            enabled ? 'true' : 'false'
        );
    }
    
    function resetBooleanToggles(prefix) {
        setBooleanToggle(
            `${prefix}_is_featured`,
            false
        );
    
        setBooleanToggle(
            `${prefix}_is_new_arrival`,
            false
        );
    }

    const routeUrl = (template, id) =>
        template.replace('__ID__', id);

    function openModal(id) {
        const modal = document.getElementById(id);

        modal?.classList.add('open');
        modal?.setAttribute('aria-hidden', 'false');

        document.body.classList.add('brand-modal-open');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');
        stopVariantPickerMode();

        if (!document.querySelector('.brand-modal.open')) {
            document.body.classList.remove('brand-modal-open');
        }
    }

    function clearErrors(form) {
        form?.querySelectorAll('.brand-field-error').forEach((item) => {
            item.textContent = '';
        });

        form?.querySelectorAll(
            'input, select, textarea'
        ).forEach((item) => {
            item.classList.remove('brand-input-invalid');
        });
    }

    function displayErrors(form, errors) {
        Object.entries(errors).forEach(
            ([field, messages]) => {
                const message = Array.isArray(messages)
                    ? messages[0]
                    : messages;
    
                /*
                 * Variant errors
                 */
                if (field.startsWith('variants')) {
                    const generalError =
                        form.querySelector(
                            '.variants_error'
                        );
    
                    if (generalError) {
                        generalError.textContent =
                            message;
                    }
    
                    const colorMatch = field.match(
                        /^variants\.(\d+)\.color$/
                    );
    
                    if (colorMatch) {
                        const index = colorMatch[1];
    
                        const input = form.querySelector(
                            `[name="variants[${index}][color]"]`
                        );
    
                        const errorOutput =
                            form.querySelector(
                                `[data-color-error="${index}"]`
                            );
    
                        input?.classList.add(
                            'brand-input-invalid'
                        );
    
                        if (errorOutput) {
                            errorOutput.textContent =
                                message;
                        }
                    }
    
                    return;
                }
    
                const cleanField = field
                    .replace(/\.\d+$/, '');
    
                const errorElement = form.querySelector(
                    `.${cleanField}_error`
                );
    
                const input = form.querySelector(
                    `[name="${cleanField}"], ` +
                    `[name="${cleanField}[]"]`
                );
    
                if (errorElement) {
                    errorElement.textContent =
                        message;
                }
    
                input?.classList.add(
                    'brand-input-invalid'
                );
            }
        );
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('productToast');
        const title = document.getElementById('productToastTitle');
        const body = document.getElementById('productToastMessage');
        const icon = document.getElementById('productToastIcon');

        toast.classList.toggle('error', type === 'error');
        title.textContent = type === 'error' ? 'Error' : 'Success';
        body.textContent = message;
        icon.textContent = type === 'error' ? '!' : '✓';

        toast.classList.add('show');

        clearTimeout(toastTimer);

        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    function setLoading(button, state, text) {
        if (!button) {
            return;
        }

        if (state) {
            button.dataset.originalText = button.textContent;
            button.textContent = text;
            button.disabled = true;
            return;
        }

        button.textContent =
            button.dataset.originalText || button.textContent;

        button.disabled = false;
    }

    async function parseResponse(response) {
        const data = await response.json().catch(() => ({
            message: 'Invalid server response.',
        }));

        if (!response.ok) {
            throw {
                status: response.status,
                data,
            };
        }

        return data;
    }

    function escapeHtml(value = '') {
        const div = document.createElement('div');
        div.textContent = value ?? '';

        return div.innerHTML;
    }

    function filterCategories(prefix) {
        const brandSelect = document.getElementById(
            `${prefix}_brand_id`
        );

        const categorySelect = document.getElementById(
            `${prefix}_category_id`
        );

        const brandId = brandSelect?.value;

        categorySelect
            ?.querySelectorAll('option[data-brand-id]')
            .forEach((option) => {
                option.hidden =
                    Boolean(brandId) &&
                    option.dataset.brandId !== brandId;
            });

        const selected =
            categorySelect?.selectedOptions?.[0];

        if (
            selected?.dataset.brandId &&
            selected.dataset.brandId !== brandId
        ) {
            categorySelect.value = '';
        }
    }

    function previewNewImages(input, previewId) {
        const preview = document.getElementById(previewId);

        if (!preview) {
            return;
        }

        preview.innerHTML = '';

        const files = Array.from(input.files || []);

        files.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                preview.insertAdjacentHTML(
                    'beforeend',
                    `
                        <div class="product-preview-card">
                            <img
                                src="${event.target.result}"
                                alt="Preview ${index + 1}"
                            >

                            <span>
                                ${index === 0 ? 'Primary' : index + 1}
                            </span>
                        </div>
                    `
                );
            };

            reader.readAsDataURL(file);
        });
    }

    function renderExistingImages(images) {
        const container = document.getElementById(
            'edit_existing_images'
        );

        if (!container) {
            return;
        }

        container.innerHTML = '';

        images.forEach((image) => {
            container.insertAdjacentHTML(
                'beforeend',
                `
                    <div
                        class="product-existing-image-card ${
                            image.is_primary ? 'primary' : ''
                        }"
                        data-image-id="${image.id}"
                    >
                        <img
                            src="${escapeHtml(image.url)}"
                            alt="Product image"
                        >

                        <div class="product-image-card-actions">
                            <button
                                type="button"
                                class="setPrimaryProductImage"
                                data-id="${image.id}"
                            >
                                ${
                                    image.is_primary
                                        ? 'Primary'
                                        : 'Set Primary'
                                }
                            </button>

                            <button
                                type="button"
                                class="removeExistingProductImage"
                                data-id="${image.id}"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                `
            );
        });
    }

    function populateEditForm(product) {
        const textFields = [
            'brand_id',
            'category_id',
            'audience',
            'name',
            'slug',
            'product_code',
            'regular_price',
            'sale_price',
            'material',
            'short_description',
            'description',
            'care_instructions',
            'status',
        ];
    
        textFields.forEach((field) => {
            const input = document.getElementById(
                `edit_${field}`
            );
    
            if (input) {
                input.value = product[field] ?? '';
            }
        });
    
        renderVariantGroups(
            'edit',
            Array.isArray(product.variants)
                ? product.variants
                : []
        );
    
        const featuredEnabled =
            product.is_featured === true ||
            product.is_featured === 1 ||
            product.is_featured === '1';
    
        const newArrivalEnabled =
            product.is_new_arrival === true ||
            product.is_new_arrival === 1 ||
            product.is_new_arrival === '1';
    
        setBooleanToggle(
            'edit_is_featured',
            featuredEnabled
        );
    
        setBooleanToggle(
            'edit_is_new_arrival',
            newArrivalEnabled
        );
    
        const productIdInput =
            document.getElementById('edit_product_id');
    
        if (productIdInput) {
            productIdInput.value = product.id;
        }
    
        deletedImageIds = [];
    
        const deleteImagesInput =
            document.getElementById(
                'edit_delete_image_ids'
            );
    
        if (deleteImagesInput) {
            deleteImagesInput.value = '[]';
        }
    
        const images = Array.isArray(product.images)
            ? product.images
            : [];
    
        const primary = images.find(
            (image) => Boolean(image.is_primary)
        );
    
        const primaryInput =
            document.getElementById(
                'edit_primary_image_id'
            );
    
        if (primaryInput) {
            primaryInput.value = primary?.id || '';
        }
    
        filterCategories('edit');
        renderExistingImages(images);
    
        const newImagePreview =
            document.getElementById(
                'edit_new_image_preview'
            );
    
        if (newImagePreview) {
            newImagePreview.innerHTML = '';
        }
    
        const slugInput =
            document.getElementById('edit_slug');
    
        if (slugInput) {
            slugInput.dataset.manuallyEdited = 'true';
        }
    }

    function createRow(product) {
        const image = product.primary_image_url
            ? `
                <img
                    src="${escapeHtml(product.primary_image_url)}"
                    alt="${escapeHtml(product.name)}"
                >
            `
            : `
                <span>
                    ${escapeHtml(
                        product.name.charAt(0).toUpperCase()
                    )}
                </span>
            `;

        const price = product.sale_price
            ? `
                <strong>
                    ৳${Number(product.sale_price).toFixed(2)}
                </strong>

                <del>
                    ৳${Number(product.regular_price).toFixed(2)}
                </del>
            `
            : `
                <strong>
                    ৳${Number(product.regular_price).toFixed(2)}
                </strong>
            `;

        return `
            <tr id="productRow${product.id}">
                <td>
                    <span class="brand-id">
                        #${product.id}
                    </span>
                </td>

                <td>
                    <div class="brand-name-cell">
                        <div class="product-table-image">
                            ${image}
                        </div>

                        <div>
                            <strong>
                                ${escapeHtml(product.name)}
                            </strong>

                            <small>
                                /${escapeHtml(product.slug)}
                            </small>
                        </div>
                    </div>
                </td>

                <td>
                    ${escapeHtml(product.brand_name || '-')}
                </td>

                <td>
                    ${escapeHtml(product.category_name || '-')}
                </td>

                <td>
                    <code class="brand-slug">
                        ${escapeHtml(product.product_code)}
                    </code>
                </td>

                <td>
                    <div class="product-price-cell">
                        ${price}
                    </div>
                </td>

                <td>
                    <span class="brand-status-badge ${
                        product.stock_quantity > 0
                            ? 'active'
                            : 'inactive'
                    }">
                        ${product.stock_quantity}
                    </span>
                </td>

                <td>
                    <span class="brand-status-badge ${
                        product.is_featured
                            ? 'active'
                            : 'inactive'
                    }">
                        ${product.is_featured ? 'Yes' : 'No'}
                    </span>
                </td>

                <td>
                    <span class="brand-status-badge ${
                        product.status === 'Active'
                            ? 'active'
                            : 'inactive'
                    }">
                        ${escapeHtml(product.status)}
                    </span>
                </td>

                <td>
                    <div class="brand-table-actions">
                        <button
                            type="button"
                            class="brand-action-button edit editProductButton"
                            data-id="${product.id}"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="brand-action-button delete deleteProductButton"
                            data-id="${product.id}"
                            data-name="${escapeHtml(product.name)}"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function syncRow(product) {
        const row = document.getElementById(
            `productRow${product.id}`
        );

        const html = createRow(product);

        if (row) {
            row.outerHTML = html;
            return;
        }

        document.getElementById('emptyProductRow')?.remove();

        document
            .getElementById('productTableBody')
            ?.insertAdjacentHTML('afterbegin', html);
    }

    document
    .querySelectorAll('.product-boolean-toggle')
    .forEach((button) => {
        button.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                const inputId =
                    button.dataset.toggleTarget;

                const input =
                    document.getElementById(inputId);

                if (!input) {
                    return;
                }

                const currentlyEnabled =
                    input.value === '1';

                setBooleanToggle(
                    inputId,
                    !currentlyEnabled
                );
            }
        );
    });

    document
    .getElementById('openAddProductModal')
    ?.addEventListener('click', () => {
        addForm?.reset();
        clearErrors(addForm);

        resetBooleanToggles('add');

        resetVariants('add');

        /*
        * Add modal open হলে প্রথম color card
        * automatically দেখানো হবে।
        */
        addVariantGroup('add');

        const statusInput =
            document.getElementById('add_status');

        if (statusInput) {
            statusInput.value = 'Active';
        }

        const imagePreview =
            document.getElementById(
                'add_new_image_preview'
            );

        if (imagePreview) {
            imagePreview.innerHTML = '';
        }

        const slugInput =
            document.getElementById('add_slug');

        if (slugInput) {
            slugInput.dataset.manuallyEdited = '';
        }

        filterCategories('add');
        openModal('addProductModal');
    });

    page.addEventListener('click', (event) => {
        const openAdvancedPickerButton =
            event.target.closest(
                '[data-open-advanced-picker]'
            );

        if (openAdvancedPickerButton) {
            event.preventDefault();
            const card = openAdvancedPickerButton.closest(
                '[data-variant-card]'
            );

            if (card) {
                openAdvancedPicker(card);
            }

            return;
        }

        const closeAdvancedPickerButton =
            event.target.closest(
                '[data-close-advanced-picker]'
            );

        if (closeAdvancedPickerButton) {
            event.preventDefault();
            closeAdvancedPicker(
                closeAdvancedPickerButton.closest(
                    '[data-variant-card]'
                )
            );
            return;
        }

        const applyAdvancedPickerButton =
            event.target.closest(
                '[data-apply-advanced-picker]'
            );

        if (applyAdvancedPickerButton) {
            event.preventDefault();
            const card = applyAdvancedPickerButton.closest(
                '[data-variant-card]'
            );

            const state = card
                ? ensurePickerState(card)
                : null;

            if (card && state?.draftHex) {
                setVariantColorHex(card, state.draftHex);
                closeAdvancedPicker(card);
            }

            return;
        }

        if (activeVariantPickerCard) {
            const activeDialog = getVariantPickerScope(
                activeVariantPickerCard
            );

            const clickedImage = event.target.closest('img');

            if (
                clickedImage &&
                activeDialog?.contains(clickedImage)
            ) {
                event.preventDefault();

                try {
                    const pickedHex = sampleColorFromImage(
                        clickedImage,
                        event.clientX,
                        event.clientY
                    );

                    if (pickedHex) {
                        setVariantColorHex(
                            activeVariantPickerCard,
                            pickedHex
                        );

                        showToast(
                            'Color picked from image.'
                        );
                    } else {
                        showToast(
                            'Could not read that image color.',
                            'error'
                        );
                    }
                } catch (error) {
                    showToast(
                        'Could not read that image color.',
                        'error'
                    );
                }

                stopVariantPickerMode();
                return;
            }
        }

        const addColorButton = event.target.closest(
            '[data-add-product-color]'
        );
    
        if (addColorButton) {
            event.preventDefault();
    
            addVariantGroup(
                addColorButton.dataset.addProductColor
            );
    
            return;
        }
    
        const removeColorButton = event.target.closest(
            '[data-remove-product-color]'
        );
    
        if (removeColorButton) {
            event.preventDefault();
    
            removeVariantGroup(
                removeColorButton.dataset
                    .removeProductColor,
    
                removeColorButton.dataset
                    .variantIndex
            );

            return;
        }

        const swatchButton = event.target.closest(
            '[data-variant-swatch]'
        );

        if (swatchButton) {
            event.preventDefault();

            const card = swatchButton.closest(
                '[data-variant-card]'
            );

            const colorHexInput = card?.querySelector(
                '[data-variant-color-hex]'
            );

            if (colorHexInput) {
                colorHexInput.value = normalizeHexColor(
                    swatchButton.dataset.variantSwatch
                );
            }

            const colorNameInput = card?.querySelector(
                '.product-variant-color-input'
            );

            if (
                colorNameInput &&
                !String(colorNameInput.value || '').trim()
            ) {
                colorNameInput.value =
                    swatchButton.dataset
                        .variantSwatchName || '';
            }

            if (card) {
                updateVariantSwatchState(card);
            }

            return;
        }

        const eyedropperButton = event.target.closest(
            '[data-variant-eyedropper]'
        );

        if (eyedropperButton) {
            event.preventDefault();

            const card = eyedropperButton.closest(
                '[data-variant-card]'
            );

            if (card) {
                startVariantPickerMode(card);
            }

            return;
        }
    });

    page.addEventListener('input', (event) => {
        const stockInput = event.target.closest(
            '.product-variant-stock-input'
        );
    
        if (stockInput) {
            let quantity = Number(
                stockInput.value || 0
            );
    
            if (
                !Number.isInteger(quantity) ||
                quantity < 0
            ) {
                quantity = Math.max(
                    0,
                    Math.floor(quantity || 0)
                );
    
                stockInput.value = quantity;
            }
    
            updateVariantCardTotal(stockInput);
        }
    
        const colorInput = event.target.closest(
            '.product-variant-color-input'
        );
    
        if (colorInput) {
            colorInput.classList.remove(
                'brand-input-invalid'
            );
    
            const card = colorInput.closest(
                '[data-variant-card]'
            );
    
            const error = card?.querySelector(
                '.product-color-input-error'
            );
    
            if (error) {
                error.textContent = '';
            }
        }

        const colorHexInput = event.target.closest(
            '[data-variant-color-hex]'
        );

        if (colorHexInput) {
            colorHexInput.value =
                normalizeHexColor(
                    colorHexInput.value
                ) || '#111111';

            const card = colorHexInput.closest(
                '[data-variant-card]'
            );

            if (card) {
                updateVariantSwatchState(card);
            }
        }

        const pickerHexInput = event.target.closest(
            '[data-picker-hex-input]'
        );

        if (pickerHexInput) {
            const card = pickerHexInput.closest(
                '[data-variant-card]'
            );

            const nextHex = normalizeHexColor(
                pickerHexInput.value
            );

            if (card && nextHex) {
                const rgb = hexToRgb(nextHex);
                const hsv = rgbToHsv(
                    rgb.red,
                    rgb.green,
                    rgb.blue
                );

                card._advancedPickerState = {
                    h: hsv.h,
                    s: hsv.s,
                    v: hsv.v,
                    draftHex: nextHex,
                };

                syncAdvancedPickerUI(card);
            }
        }
    });

    page.addEventListener('pointerdown', (event) => {
        const spectrum = event.target.closest(
            '[data-color-spectrum]'
        );

        if (spectrum) {
            event.preventDefault();
            const card = spectrum.closest(
                '[data-variant-card]'
            );

            if (!card) {
                return;
            }

            updatePickerFromSpectrum(card, event);

            const handleMove = (moveEvent) => {
                updatePickerFromSpectrum(card, moveEvent);
            };

            const stopMove = () => {
                window.removeEventListener(
                    'pointermove',
                    handleMove
                );
                window.removeEventListener(
                    'pointerup',
                    stopMove
                );
            };

            window.addEventListener(
                'pointermove',
                handleMove
            );
            window.addEventListener(
                'pointerup',
                stopMove
            );

            return;
        }

        const hueSlider = event.target.closest(
            '[data-hue-slider]'
        );

        if (hueSlider) {
            event.preventDefault();
            const card = hueSlider.closest(
                '[data-variant-card]'
            );

            if (!card) {
                return;
            }

            updatePickerFromHue(card, event);

            const handleMove = (moveEvent) => {
                updatePickerFromHue(card, moveEvent);
            };

            const stopMove = () => {
                window.removeEventListener(
                    'pointermove',
                    handleMove
                );
                window.removeEventListener(
                    'pointerup',
                    stopMove
                );
            };

            window.addEventListener(
                'pointermove',
                handleMove
            );
            window.addEventListener(
                'pointerup',
                stopMove
            );
        }
    });

    document
        .querySelectorAll('[data-close-modal]')
        .forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(button.dataset.closeModal);
            });
        });

    document
        .getElementById('add_images')
        ?.addEventListener('change', (event) => {
            previewNewImages(
                event.target,
                'add_new_image_preview'
            );
        });

    document
        .getElementById('edit_images')
        ?.addEventListener('change', (event) => {
            previewNewImages(
                event.target,
                'edit_new_image_preview'
            );
        });

    ['add', 'edit'].forEach((prefix) => {
        document
            .getElementById(`${prefix}_brand_id`)
            ?.addEventListener('change', () => {
                filterCategories(prefix);
            });

        const nameInput = document.getElementById(
            `${prefix}_name`
        );

        const slugInput = document.getElementById(
            `${prefix}_slug`
        );

        nameInput?.addEventListener('input', (event) => {
            if (!slugInput.dataset.manuallyEdited) {
                slugInput.value = event.target.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        slugInput?.addEventListener('input', () => {
            slugInput.dataset.manuallyEdited = 'true';
        });
    });

    addForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
    
        clearErrors(addForm);
    
        if (!validateVariantForm('add')) {
            showToast(
                'Please complete the color-wise inventory.',
                'error'
            );
    
            return;
        }
    
        setLoading(addSubmit, true, 'Adding...');

        try {
            const response = await fetch(
                page.dataset.storeUrl,
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: new FormData(addForm),
                }
            );

            const data = await parseResponse(response);

            syncRow(data.product);
            closeModal('addProductModal');

            addForm.reset();
            resetVariants('add');
            resetBooleanToggles('add');

            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(
                    addForm,
                    error.data.errors || {}
                );
            } else {
                showToast(
                    error.data?.message ||
                        'Unable to add product.',
                    'error'
                );
            }
        } finally {
            setLoading(addSubmit, false);
        }
    });

    document
        .getElementById('productTableBody')
        ?.addEventListener('click', async (event) => {
            const editButton = event.target.closest(
                '.editProductButton'
            );

            const deleteButton = event.target.closest(
                '.deleteProductButton'
            );

            if (editButton) {
                try {
                    editButton.disabled = true;

                    const response = await fetch(
                        routeUrl(
                            page.dataset.showUrl,
                            editButton.dataset.id
                        ),
                        {
                            headers: {
                                'Accept': 'application/json',
                            },
                        }
                    );

                    const data = await parseResponse(response);

                    editForm?.reset();
                    clearErrors(editForm);
                    resetVariants('edit');

                    populateEditForm(data.product);

                    openModal('editProductModal');
                } catch (error) {
                    showToast(
                        error.data?.message ||
                            'Unable to load product.',
                        'error'
                    );
                } finally {
                    editButton.disabled = false;
                }
            }

            if (deleteButton) {
                document.getElementById(
                    'delete_product_id'
                ).value = deleteButton.dataset.id;

                document.getElementById(
                    'deleteProductName'
                ).textContent = deleteButton.dataset.name;

                openModal('deleteProductModal');
            }
        });

    document
        .getElementById('edit_existing_images')
        ?.addEventListener('click', (event) => {
            const removeButton = event.target.closest(
                '.removeExistingProductImage'
            );

            const primaryButton = event.target.closest(
                '.setPrimaryProductImage'
            );

            if (removeButton) {
                const imageId = Number(
                    removeButton.dataset.id
                );

                if (!deletedImageIds.includes(imageId)) {
                    deletedImageIds.push(imageId);
                }

                document.getElementById(
                    'edit_delete_image_ids'
                ).value = JSON.stringify(deletedImageIds);

                removeButton
                    .closest('.product-existing-image-card')
                    ?.remove();
            }

            if (primaryButton) {
                const imageId = primaryButton.dataset.id;

                document.getElementById(
                    'edit_primary_image_id'
                ).value = imageId;

                document
                    .querySelectorAll(
                        '.product-existing-image-card'
                    )
                    .forEach((card) => {
                        card.classList.remove('primary');

                        const button = card.querySelector(
                            '.setPrimaryProductImage'
                        );

                        if (button) {
                            button.textContent = 'Set Primary';
                        }
                    });

                const card = primaryButton.closest(
                    '.product-existing-image-card'
                );

                card?.classList.add('primary');
                primaryButton.textContent = 'Primary';
            }
        });

        editForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
        
            clearErrors(editForm);
        
            if (!validateVariantForm('edit')) {
                showToast(
                    'Please complete the color-wise inventory.',
                    'error'
                );
        
                return;
            }
        
            const id = document.getElementById(
                'edit_product_id'
            ).value;

        setLoading(editSubmit, true, 'Updating...');

        try {
            const response = await fetch(
                routeUrl(page.dataset.updateUrl, id),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: new FormData(editForm),
                }
            );

            const data = await parseResponse(response);

            syncRow(data.product);
            closeModal('editProductModal');

            showToast(data.message);
        } catch (error) {
            if (error.status === 422) {
                displayErrors(
                    editForm,
                    error.data.errors || {}
                );
            } else {
                showToast(
                    error.data?.message ||
                        'Unable to update product.',
                    'error'
                );
            }
        } finally {
            setLoading(editSubmit, false);
        }
    });

    deleteSubmit?.addEventListener('click', async () => {
        const id = document.getElementById(
            'delete_product_id'
        ).value;

        setLoading(deleteSubmit, true, 'Deleting...');

        try {
            const response = await fetch(
                routeUrl(page.dataset.deleteUrl, id),
                {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                }
            );

            const data = await parseResponse(response);

            document
                .getElementById(`productRow${id}`)
                ?.remove();

            closeModal('deleteProductModal');
            showToast(data.message);
        } catch (error) {
            showToast(
                error.data?.message ||
                    'Unable to delete product.',
                'error'
            );
        } finally {
            setLoading(deleteSubmit, false);
        }
    });

    document
        .getElementById('closeProductToast')
        ?.addEventListener('click', () => {
            document
                .getElementById('productToast')
                ?.classList.remove('show');
        });

    const focusProductId = new URLSearchParams(window.location.search)
        .get('focus_product');

    if (focusProductId) {
        const row = document.getElementById(`productRow${focusProductId}`);

        row?.classList.add('admin-row-focused');
        row?.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    }
});
