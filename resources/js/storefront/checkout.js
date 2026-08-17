document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    const checkoutModal = document.getElementById('checkoutModal');
    const checkoutForm = document.getElementById('checkoutLeadForm');
    const checkoutCartPreview = document.getElementById('checkoutCartPreview');
    const checkoutCartItemCount = document.getElementById('checkoutCartItemCount');
    const checkoutItemsTotal = document.getElementById('checkoutItemsTotal');
    const checkoutDeliveryCharge = document.getElementById('checkoutDeliveryCharge');
    const checkoutDiscountRow = document.getElementById('checkoutDiscountRow');
    const checkoutDiscountAmount = document.getElementById('checkoutDiscountAmount');
    const checkoutGrandTotal = document.getElementById('checkoutGrandTotal');
    const checkoutGeneralError = document.getElementById('checkoutGeneralError');
    const checkoutCouponCode = document.getElementById('checkoutCouponCode');
    const applyCouponButton = document.getElementById('applyCouponButton');
    const removeCouponButton = document.getElementById('removeCouponButton');
    const checkoutCouponMessage = document.getElementById('checkoutCouponMessage');
    const checkoutVoucherPanel = document.getElementById('checkoutVoucherPanel');
    const checkoutVoucherTrack = document.getElementById('checkoutVoucherTrack');
    const voucherPrevButton = document.getElementById('voucherPrevButton');
    const voucherNextButton = document.getElementById('voucherNextButton');

    const orderConfirmModal = document.getElementById('orderConfirmModal');
    const confirmItems = document.getElementById('confirmItems');
    const confirmName = document.getElementById('confirmName');
    const confirmPhone = document.getElementById('confirmPhone');
    const confirmDeliveryArea = document.getElementById('confirmDeliveryArea');
    const confirmDistrict = document.getElementById('confirmDistrict');
    const confirmAddress = document.getElementById('confirmAddress');
    const confirmPayment = document.getElementById('confirmPayment');
    const confirmItemsTotal = document.getElementById('confirmItemsTotal');
    const confirmDeliveryCharge = document.getElementById('confirmDeliveryCharge');
    const confirmDiscountRow = document.getElementById('confirmDiscountRow');
    const confirmDiscountAmount = document.getElementById('confirmDiscountAmount');
    const confirmGrandTotal = document.getElementById('confirmGrandTotal');
    const confirmGeneralError = document.getElementById('confirmGeneralError');
    const confirmFinalOrderBtn = document.getElementById('confirmFinalOrderBtn');
    const cancelFinalOrderBtn = document.getElementById('cancelFinalOrderBtn');

    const thankYouModal = document.getElementById('thankYouModal');
    const thankYouOrderCode = document.getElementById('thankYouOrderCode');
    const thankYouInvoiceButton = document.getElementById('thankYouInvoiceButton');

    const districtInput = document.getElementById('checkoutDistrict');
    const districtShell = document.querySelector('.searchable-district-shell');
    const districtDropdown = document.getElementById('districtDropdown');
    const districtClearBtn = document.getElementById('districtClearBtn');

    const areaInput = document.getElementById('checkoutAreaThana');
    const roadInput = document.getElementById('checkoutRoadNo');
    const houseInput = document.getElementById('checkoutHouseNo');
    const addressInput = document.getElementById('checkoutFullAddress');
    const phoneInput = document.getElementById('checkoutPhone');
    const alternativePhoneInput = document.getElementById('checkoutAlternativePhone');

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

        return `hsl(${Math.abs(hash) % 360} 68% 58%)`;
    }

    function swatchColor(name, hex) {
        return normalizeColorHex(hex) || fallbackColorFromName(name);
    }

    function colorChipHtml(name, hex) {
        return `
            <span class="store-color-chip">
                <span
                    class="store-color-swatch"
                    style="--swatch-color:${escapeHtml(
                        swatchColor(name, hex)
                    )};"
                    aria-hidden="true"
                ></span>
                <b>${escapeHtml(name || 'Color')}</b>
            </span>
        `;
    }

    const districtOptions = [
        {
            name: 'Dhaka',
            keywords: [
                'dhaka',
                'daka',
                'dhaka city',
                'ঢাকা',
            ],
        },
        {
            name: 'Faridpur',
            keywords: [
                'faridpur',
                'foridpur',
                'faridpoor',
                'ফরিদপুর',
            ],
        },
        {
            name: 'Gazipur',
            keywords: [
                'gazipur',
                'gajipur',
                'gazipor',
                'গাজীপুর',
            ],
        },
        {
            name: 'Gopalganj',
            keywords: [
                'gopalganj',
                'gopalgonj',
                'gopal gonj',
                'গোপালগঞ্জ',
            ],
        },
        {
            name: 'Kishoreganj',
            keywords: [
                'kishoreganj',
                'kishorgonj',
                'kishoregonj',
                'কিশোরগঞ্জ',
            ],
        },
        {
            name: 'Madaripur',
            keywords: [
                'madaripur',
                'madaripor',
                'মাদারীপুর',
            ],
        },
        {
            name: 'Manikganj',
            keywords: [
                'manikganj',
                'manikgonj',
                'মানিকগঞ্জ',
            ],
        },
        {
            name: 'Munshiganj',
            keywords: [
                'munshiganj',
                'munshigonj',
                'munsiganj',
                'মুন্সিগঞ্জ',
            ],
        },
        {
            name: 'Narayanganj',
            keywords: [
                'narayanganj',
                'narayangonj',
                'নারায়ণগঞ্জ',
                'নারায়ণগঞ্জ',
            ],
        },
        {
            name: 'Narsingdi',
            keywords: [
                'narsingdi',
                'norsingdi',
                'narshingdi',
                'নরসিংদী',
            ],
        },
        {
            name: 'Rajbari',
            keywords: [
                'rajbari',
                'raj bari',
                'রাজবাড়ী',
                'রাজবাড়ী',
            ],
        },
        {
            name: 'Shariatpur',
            keywords: [
                'shariatpur',
                'shoriotpur',
                'sariatpur',
                'শরীয়তপুর',
                'শরিয়তপুর',
            ],
        },
        {
            name: 'Tangail',
            keywords: [
                'tangail',
                'tangayl',
                'tangile',
                'টাঙ্গাইল',
            ],
        },
        {
            name: 'Bagerhat',
            keywords: [
                'bagerhat',
                'bagherhat',
                'বাগেরহাট',
            ],
        },
        {
            name: 'Chuadanga',
            keywords: [
                'chuadanga',
                'chuwadanga',
                'চুয়াডাঙ্গা',
                'চুয়াডাঙ্গা',
            ],
        },
        {
            name: 'Jessore',
            keywords: [
                'jessore',
                'jessor',
                'jashore',
                'joshor',
                'যশোর',
            ],
        },
        {
            name: 'Jhenaidah',
            keywords: [
                'jhenaidah',
                'jhenidah',
                'ঝিনাইদহ',
            ],
        },
        {
            name: 'Khulna',
            keywords: [
                'khulna',
                'kulna',
                'খুলনা',
            ],
        },
        {
            name: 'Kushtia',
            keywords: [
                'kushtia',
                'kustia',
                'kushtiya',
                'কুষ্টিয়া',
                'কুষ্টিয়া',
            ],
        },
        {
            name: 'Magura',
            keywords: [
                'magura',
                'maguara',
                'মাগুরা',
            ],
        },
        {
            name: 'Meherpur',
            keywords: [
                'meherpur',
                'meherpoor',
                'মেহেরপুর',
            ],
        },
        {
            name: 'Narail',
            keywords: [
                'narail',
                'norail',
                'নড়াইল',
                'নড়াইল',
            ],
        },
        {
            name: 'Satkhira',
            keywords: [
                'satkhira',
                'shatkhira',
                'satkira',
                'সাতক্ষীরা',
            ],
        },
        {
            name: 'Bogra',
            keywords: [
                'bogra',
                'bogura',
                'বগুড়া',
                'বগুড়া',
            ],
        },
        {
            name: 'Joypurhat',
            keywords: [
                'joypurhat',
                'jaipurhat',
                'জয়পুরহাট',
                'জয়পুরহাট',
            ],
        },
        {
            name: 'Naogaon',
            keywords: [
                'naogaon',
                'nowgaon',
                'naoga',
                'নওগাঁ',
            ],
        },
        {
            name: 'Natore',
            keywords: [
                'natore',
                'nator',
                'নাটোর',
            ],
        },
        {
            name: 'Chapainawabganj',
            keywords: [
                'chapainawabganj',
                'chapai nawabganj',
                'chapai',
                'nawabganj',
                'চাঁপাইনবাবগঞ্জ',
            ],
        },
        {
            name: 'Pabna',
            keywords: [
                'pabna',
                'pabna district',
                'পাবনা',
            ],
        },
        {
            name: 'Rajshahi',
            keywords: [
                'rajshahi',
                'rajshai',
                'রাজশাহী',
            ],
        },
        {
            name: 'Sirajganj',
            keywords: [
                'sirajganj',
                'sirajgonj',
                'শিরাজগঞ্জ',
                'সিরাজগঞ্জ',
            ],
        },
        {
            name: 'Dinajpur',
            keywords: [
                'dinajpur',
                'dinajpoor',
                'দিনাজপুর',
            ],
        },
        {
            name: 'Gaibandha',
            keywords: [
                'gaibandha',
                'gaybandha',
                'গাইবান্ধা',
            ],
        },
        {
            name: 'Kurigram',
            keywords: [
                'kurigram',
                'kurigam',
                'কুড়িগ্রাম',
                'কুড়িগ্রাম',
            ],
        },
        {
            name: 'Lalmonirhat',
            keywords: [
                'lalmonirhat',
                'lalmonir hat',
                'লালমনিরহাট',
            ],
        },
        {
            name: 'Nilphamari',
            keywords: [
                'nilphamari',
                'nilfamari',
                'নীলফামারী',
            ],
        },
        {
            name: 'Panchagarh',
            keywords: [
                'panchagarh',
                'ponchogor',
                'panchagar',
                'পঞ্চগড়',
                'পঞ্চগড়',
            ],
        },
        {
            name: 'Rangpur',
            keywords: [
                'rangpur',
                'rongpur',
                'রংপুর',
            ],
        },
        {
            name: 'Thakurgaon',
            keywords: [
                'thakurgaon',
                'thakur gaon',
                'ঠাকুরগাঁও',
            ],
        },
        {
            name: 'Barguna',
            keywords: [
                'barguna',
                'borguna',
                'বরগুনা',
            ],
        },
        {
            name: 'Barisal',
            keywords: [
                'barisal',
                'barishal',
                'বরিশাল',
            ],
        },
        {
            name: 'Bhola',
            keywords: [
                'bhola',
                'vola',
                'ভোলা',
            ],
        },
        {
            name: 'Jhalokati',
            keywords: [
                'jhalokati',
                'jhalokathi',
                'ঝালকাঠি',
            ],
        },
        {
            name: 'Patuakhali',
            keywords: [
                'patuakhali',
                'potuakhali',
                'পটুয়াখালী',
                'পটুয়াখালী',
            ],
        },
        {
            name: 'Pirojpur',
            keywords: [
                'pirojpur',
                'pirojpoor',
                'পিরোজপুর',
            ],
        },
        {
            name: 'Bandarban',
            keywords: [
                'bandarban',
                'bandorban',
                'বান্দরবান',
            ],
        },
        {
            name: 'Brahmanbaria',
            keywords: [
                'brahmanbaria',
                'brahmanbaria',
                'b baria',
                'bbaria',
                'ব্রাহ্মণবাড়িয়া',
                'ব্রাহ্মণবাড়িয়া',
            ],
        },
        {
            name: 'Chandpur',
            keywords: [
                'chandpur',
                'chandpoor',
                'চাঁদপুর',
            ],
        },
        {
            name: 'Chittagong',
            keywords: [
                'chittagong',
                'chattogram',
                'chattagram',
                'ctg',
                'চট্টগ্রাম',
            ],
        },
        {
            name: 'Comilla',
            keywords: [
                'comilla',
                'cumilla',
                'kumilla',
                'comila',
                'কুমিল্লা',
            ],
        },
        {
            name: "Cox's Bazar",
            keywords: [
                "cox's bazar",
                'coxs bazar',
                'cox bazar',
                'coxsbazar',
                'কক্সবাজার',
            ],
        },
        {
            name: 'Feni',
            keywords: [
                'feni',
                'ফেনী',
            ],
        },
        {
            name: 'Khagrachari',
            keywords: [
                'khagrachari',
                'khagrachori',
                'খাগড়াছড়ি',
                'খাগড়াছড়ি',
            ],
        },
        {
            name: 'Lakshmipur',
            keywords: [
                'lakshmipur',
                'laxmipur',
                'lokkhipur',
                'লক্ষ্মীপুর',
            ],
        },
        {
            name: 'Noakhali',
            keywords: [
                'noakhali',
                'nowakhali',
                'নোয়াখালী',
                'নোয়াখালী',
            ],
        },
        {
            name: 'Rangamati',
            keywords: [
                'rangamati',
                'rangamathi',
                'রাঙামাটি',
            ],
        },
        {
            name: 'Habiganj',
            keywords: [
                'habiganj',
                'hobigonj',
                'হবিগঞ্জ',
            ],
        },
        {
            name: 'Maulvibazar',
            keywords: [
                'maulvibazar',
                'moulvibazar',
                'moulovi bazar',
                'মৌলভীবাজার',
            ],
        },
        {
            name: 'Sunamganj',
            keywords: [
                'sunamganj',
                'sunamgonj',
                'সুনামগঞ্জ',
            ],
        },
        {
            name: 'Sylhet',
            keywords: [
                'sylhet',
                'silet',
                'silhet',
                'সিলেট',
            ],
        },
        {
            name: 'Jamalpur',
            keywords: [
                'jamalpur',
                'jamalpoor',
                'জামালপুর',
            ],
        },
        {
            name: 'Mymensingh',
            keywords: [
                'mymensingh',
                'mymensing',
                'mymenshingh',
                'ময়মনসিংহ',
                'ময়মনসিংহ',
            ],
        },
        {
            name: 'Netrokona',
            keywords: [
                'netrokona',
                'netrakona',
                'নেত্রকোনা',
            ],
        },
        {
            name: 'Sherpur',
            keywords: [
                'sherpur',
                'serpur',
                'শেরপুর',
            ],
        },
    ];
    

    function normalizeDistrictSearch(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[’‘`]/g, "'")
            .replace(
                /[^a-z0-9\u0980-\u09FF]+/g,
                ''
            );
    }
    
    function getMatchingDistricts(
        keyword = ''
    ) {
        const normalizedKeyword =
            normalizeDistrictSearch(keyword);
    
        return districtOptions.filter(
            function (district) {
                /*
                | Outside Dhaka selected থাকলে
                | Dhaka district দেখানো হবে না।
                */
    
                if (
                    selectedDeliveryArea()
                        === 'outside_dhaka'
                    &&
                    district.name === 'Dhaka'
                ) {
                    return false;
                }
    
                if (!normalizedKeyword) {
                    return true;
                }
    
                const searchableValues = [
                    district.name,
                    ...(district.keywords || []),
                ];
    
                return searchableValues.some(
                    function (value) {
                        const normalizedValue =
                            normalizeDistrictSearch(
                                value
                            );
    
                        return (
                            normalizedValue.includes(
                                normalizedKeyword
                            )
                            ||
                            normalizedKeyword.includes(
                                normalizedValue
                            )
                        );
                    }
                );
            }
        );
    }
    
    function getExactDistrictMatch(
        keyword = ''
    ) {
        const normalizedKeyword =
            normalizeDistrictSearch(keyword);
    
        if (!normalizedKeyword) {
            return null;
        }
    
        return (
            getMatchingDistricts(
                keyword
            ).find(
                function (district) {
                    const searchableValues = [
                        district.name,
                        ...(district.keywords || []),
                    ];
    
                    return searchableValues.some(
                        function (value) {
                            return (
                                normalizeDistrictSearch(
                                    value
                                )
                                === normalizedKeyword
                            );
                        }
                    );
                }
            )
            || null
        );
    }

    let checkoutCart = {
        items: [],
        count: 0,
        items_total: 0,
        coupon: null,
    };

    let pendingOrderPayload = null;
    let appliedCoupon = null;
    let availableCoupons = [];
    let shouldRefreshAfterOrder = false;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function money(value) {
        return `৳${Number(value || 0).toLocaleString('en-BD', {
            maximumFractionDigits: 2,
        })}`;
    }

    function plainMoney(value) {
        return Number(value || 0).toLocaleString('en-BD', {
            maximumFractionDigits: 2,
        });
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

    async function loadCheckoutCart() {
        const response = await fetch('/cart', {
            headers: {
                Accept: 'application/json',
            },
        });

        const data = await parseResponse(response);

        checkoutCart = data.cart || {
            items: [],
            count: 0,
            items_total: 0,
            coupon: null,
        };

        appliedCoupon = checkoutCart.coupon || null;

        if (!checkoutCart.items?.length) {
            throw new Error('Your cart is empty.');
        }

        renderCheckoutCart();
        await loadAvailableCoupons();
        updateCheckoutTotals();
    }

    function selectedDeliveryArea() {
        return checkoutForm?.querySelector(
            'input[name="delivery_area"]:checked'
        )?.value || 'inside_dhaka';
    }

    function getDeliveryCharge() {
        return selectedDeliveryArea() === 'inside_dhaka'
            ? 80
            : 130;
    }

    function deliveryAreaLabel() {
        return selectedDeliveryArea() === 'inside_dhaka'
            ? 'Inside Dhaka'
            : 'Outside Dhaka';
    }

    function updateCheckoutTotals() {
        const itemsTotal = Number(checkoutCart.items_total || 0);
        const deliveryCharge = getDeliveryCharge();
        const discountAmount = Number(
            appliedCoupon?.discount_amount || 0
        );
        const grandTotal = Math.max(
            itemsTotal + deliveryCharge - discountAmount,
            0
        );

        if (checkoutItemsTotal) {
            checkoutItemsTotal.textContent = money(itemsTotal);
        }

        if (checkoutDeliveryCharge) {
            checkoutDeliveryCharge.textContent = money(deliveryCharge);
        }

        if (checkoutDiscountRow) {
            checkoutDiscountRow.hidden = discountAmount <= 0;
        }

        if (checkoutDiscountAmount) {
            checkoutDiscountAmount.textContent = `-${money(discountAmount)}`;
        }

        if (checkoutGrandTotal) {
            checkoutGrandTotal.textContent = money(grandTotal);
        }

        syncCouponUi();
        renderAvailableCoupons();
    }

    function setCouponMessage(message = '', type = '') {
        if (!checkoutCouponMessage) {
            return;
        }

        checkoutCouponMessage.textContent = message;
        checkoutCouponMessage.className =
            `checkout-coupon-message ${type}`.trim();
    }

    function syncCouponUi() {
        if (checkoutCouponCode && appliedCoupon?.code) {
            checkoutCouponCode.value = appliedCoupon.code;
        }

        if (removeCouponButton) {
            removeCouponButton.hidden = !appliedCoupon;
        }

        if (applyCouponButton) {
            applyCouponButton.textContent = appliedCoupon
                ? 'Update'
                : 'Apply';
        }
    }

    async function loadAvailableCoupons() {
        try {
            const response = await fetch('/coupons/available', {
                headers: {
                    Accept: 'application/json',
                },
            });

            const data = await parseResponse(response);

            availableCoupons = data.coupons || [];
        } catch (error) {
            availableCoupons = [];
        }
    }

    function renderAvailableCoupons() {
        if (!checkoutVoucherPanel || !checkoutVoucherTrack) {
            return;
        }

        checkoutVoucherPanel.hidden = availableCoupons.length === 0;
        checkoutVoucherTrack.innerHTML = '';

        if (availableCoupons.length === 0) {
            return;
        }

        const itemsTotal = Number(checkoutCart.items_total || 0);
        const phone = phoneInput?.value.trim() || '';
        const hasValidPhone = /^01[3-9]\d{8}$/.test(phone);

        availableCoupons.forEach((coupon) => {
            const minOrder = Number(coupon.min_order_amount || 0);
            const remaining = Math.max(minOrder - itemsTotal, 0);
            const progress = minOrder > 0
                ? Math.min((itemsTotal / minOrder) * 100, 100)
                : 100;
            const hasMinimumAmount = remaining <= 0;
            const phoneReady =
                !coupon.new_customer_only || hasValidPhone;
            const eligible = hasMinimumAmount && phoneReady;
            const isApplied =
                appliedCoupon?.code === coupon.code;
            const card = document.createElement('article');

            card.className =
                `checkout-voucher-card ${eligible ? 'eligible' : 'locked'} ${isApplied ? 'applied' : ''}`;

            card.innerHTML = `
                <div class="checkout-voucher-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>

                <div class="checkout-voucher-content">
                    <div class="checkout-voucher-title-row">
                        <strong>${escapeHtml(coupon.title || coupon.discount_label || coupon.code)}</strong>

                        <button
                            type="button"
                            data-apply-voucher="${escapeHtml(coupon.code)}"
                            ${eligible && !isApplied ? '' : 'disabled'}
                        >
                            ${isApplied ? 'Applied' : 'Apply'}
                        </button>
                    </div>

                    <div class="checkout-voucher-code-row">
                        <span>${escapeHtml(coupon.discount_label || '')}</span>
                        <code>${escapeHtml(coupon.code)}</code>
                        ${coupon.new_customer_only ? '<em>New customer</em>' : ''}
                    </div>

                    <div class="checkout-voucher-progress">
                        <span style="width: ${progress}%"></span>
                    </div>

                    <div class="checkout-voucher-meta">
                        <span>Min. order ${money(minOrder)}</span>
                        <span>
                            ${
                                !hasMinimumAmount
                                    ? `${money(remaining)} more to unlock`
                                    : !phoneReady
                                        ? 'Enter phone to verify'
                                        : 'Ready to apply'
                            }
                        </span>
                        ${
                            coupon.expires_at
                                ? `<span>Use by ${escapeHtml(coupon.expires_at)}</span>`
                                : ''
                        }
                    </div>
                </div>
            `;

            checkoutVoucherTrack.appendChild(card);
        });
    }

    async function applyCoupon(selectedCode = null) {
        const code = (selectedCode || checkoutCouponCode?.value || '').trim();
        const phone = phoneInput?.value.trim() || '';

        if (!code) {
            setCouponMessage('Enter a coupon code first.', 'error');
            return;
        }

        if (
            phone
            && !/^01[3-9]\d{8}$/.test(phone)
        ) {
            setCouponMessage(
                'Enter a valid phone number or leave it empty.',
                'error'
            );
        
            phoneInput?.focus();
        
            return;
        }

        applyCouponButton.disabled = true;
        setCouponMessage('Checking coupon...', '');

        try {
            const response = await fetch('/coupons/apply', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    code,
                    phone,
                }),
            });

            const data = await parseResponse(response);

            appliedCoupon = data.coupon;
            if (checkoutCouponCode) {
                checkoutCouponCode.value = data.coupon?.code || code;
            }
            setCouponMessage(data.message, 'success');
            updateCheckoutTotals();
        } catch (error) {
            appliedCoupon = null;
            setCouponMessage(
                error.data?.message || 'Unable to apply coupon.',
                'error'
            );
            updateCheckoutTotals();
        } finally {
            applyCouponButton.disabled = false;
        }

        
    }

    async function removeCoupon() {
        removeCouponButton.disabled = true;

        try {
            await fetch('/coupons/remove', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            });

            appliedCoupon = null;

            if (checkoutCouponCode) {
                checkoutCouponCode.value = '';
            }

            setCouponMessage('Coupon removed.', '');
            updateCheckoutTotals();
        } finally {
            removeCouponButton.disabled = false;
        }
    }

    function renderCheckoutCart() {
        if (!checkoutCartPreview) {
            return;
        }

        checkoutCartPreview.innerHTML = '';

        if (checkoutCartItemCount) {
            checkoutCartItemCount.textContent = `${checkoutCart.count || 0} items`;
        }

        checkoutCart.items.forEach(function (item) {
            const row = document.createElement('article');

            row.className = 'checkout-preview-item';
            row.innerHTML = itemHtml(item, true);

            checkoutCartPreview.appendChild(row);
        });
    }

    function itemHtml(item, compact = false) {
        const image = item.image_url
            ? `
                <img
                    src="${escapeHtml(item.image_url)}"
                    alt="${escapeHtml(item.product_name)}"
                >
            `
            : `
                <div class="cart-item-image">
                    <span>${escapeHtml((item.product_name || 'P').charAt(0).toUpperCase())}</span>
                </div>
            `;

        return `
            ${image}
            <div>
                <h4>${escapeHtml(item.product_name)}</h4>
                <p>
                    ${colorChipHtml(
                        item.color || '',
                        item.color_hex || ''
                    )}
                    <span class="checkout-item-separator">/</span>
                    ${escapeHtml(item.size || '-')}
                    ${compact ? '·' : '<br>'}
                    Qty ${Number(item.quantity)}
                    ×
                    ${money(item.unit_price)}
                </p>
            </div>
            <strong>${money(item.line_total)}</strong>
        `;
    }

    function openModal(modal) {
        modal?.classList.add('open');
        modal?.setAttribute('aria-hidden', 'false');
        document.body.classList.add('store-overlay-open');
    }

    function closeModal(modal) {
        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.store-modal.open')) {
            document.body.classList.remove('store-overlay-open');
        }
    }

    function closeThankYouModal() {
        closeModal(thankYouModal);

        if (shouldRefreshAfterOrder) {
            window.location.reload();
            return;
        }

        document.getElementById('products')?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });
    }

    function clearErrors() {
        if (checkoutGeneralError) {
            checkoutGeneralError.textContent = '';
        }

        if (confirmGeneralError) {
            confirmGeneralError.textContent = '';
        }

        document
            .querySelectorAll('[data-checkout-error]')
            .forEach(function (element) {
                element.textContent = '';
                element
                    .closest('.checkout-field')
                    ?.classList.remove('has-error');
            });
    }

    function setFieldError(field, message) {
        const output = document.querySelector(
            `[data-checkout-error="${field}"]`
        );

        output?.closest('.checkout-field')?.classList.add('has-error');

        if (output) {
            output.textContent = message;
        }
    }

    function syncGeneratedAddress() {
        if (!addressInput) {
            return;
        }
    
        const parts = [];
    
        const house =
            houseInput?.value.trim();
    
        const road =
            roadInput?.value.trim();
    
        const area =
            areaInput?.value.trim();
    
        const district =
            districtInput?.value.trim();
    
        if (house) {
            parts.push(`House- ${house}`);
        }
    
        if (road) {
            parts.push(`Road- ${road}`);
        }
    
        if (area) {
            parts.push(area);
        }
    
        if (district) {
            parts.push(district);
        }
    
        addressInput.value =
            parts.join(', ');
    }

    const pendingCouponCode =
        localStorage.getItem(
            'pending_coupon_code'
        );

    if (
        pendingCouponCode
        && checkoutCouponCode
    ) {
        checkoutCouponCode.value =
            pendingCouponCode;
    }

    function handleDeliveryAreaChange() {
        const deliveryArea =
            selectedDeliveryArea();
    
        if (
            deliveryArea
            === 'inside_dhaka'
        ) {
            if (districtInput) {
                districtInput.value =
                    'Dhaka';
    
                districtInput.readOnly =
                    true;
            }
    
            closeDistrictDropdown();
        } else {
            if (districtInput) {
                if (
                    districtInput.value
                    === 'Dhaka'
                ) {
                    districtInput.value = '';
                }
    
                districtInput.readOnly =
                    false;
            }
    
            renderDistrictDropdown('');
        }
    
        districtShell?.classList.toggle(
            'locked',
            deliveryArea
                === 'inside_dhaka'
        );
    
        districtShell?.classList.toggle(
            'has-value',
            Boolean(
                districtInput?.value
            )
        );
    
        syncGeneratedAddress();
        updateCheckoutTotals();
    }

    function openDistrictDropdown() {
        districtShell?.classList.add('open');
    }

    function closeDistrictDropdown() {
        districtShell?.classList.remove('open');
    }

    function renderDistrictDropdown(
        keyword = ''
    ) {
        if (!districtDropdown) {
            return;
        }
    
        const districts =
            getMatchingDistricts(keyword);
    
        districtDropdown.innerHTML = '';
    
        if (!districts.length) {
            districtDropdown.innerHTML = `
                <div class="district-empty">
                    No matching district found
                </div>
            `;
    
            return;
        }
    
        districts.forEach(
            function (district) {
                const button =
                    document.createElement(
                        'button'
                    );
    
                button.type = 'button';
                button.className =
                    'district-option';
    
                if (
                    districtInput?.value
                    === district.name
                ) {
                    button.classList.add(
                        'active'
                    );
                }
    
                button.innerHTML = `
                    <strong>
                        ${escapeHtml(
                            district.name
                        )}
                    </strong>
                `;
    
                button.addEventListener(
                    'click',
                    function () {
                        selectDistrict(
                            district.name
                        );
                    }
                );
    
                districtDropdown.appendChild(
                    button
                );
            }
        );
    }

    function selectDistrict(
        districtName
    ) {
        if (!districtInput) {
            return;
        }
    
        if (
            selectedDeliveryArea()
                === 'outside_dhaka'
            &&
            districtName === 'Dhaka'
        ) {
            districtInput.value = '';
            districtInput.readOnly = false;
    
            setFieldError(
                'district',
                'Outside Dhaka cannot use Dhaka district.'
            );
    
            return;
        }
    
        districtInput.value =
            districtName;
    
        /*
        | District select হওয়ার পরে
        | canonical district name field-এ থাকবে।
        */
    
        districtInput.readOnly = true;
    
        districtShell?.classList.add(
            'has-value'
        );
    
        closeDistrictDropdown();
    
        const districtError =
            document.querySelector(
                '[data-checkout-error="district"]'
            );
    
        if (districtError) {
            districtError.textContent = '';
    
            districtError
                .closest('.checkout-field')
                ?.classList.remove(
                    'has-error'
                );
        }
    
        syncGeneratedAddress();
    }

    function validateCheckout() {
        clearErrors();
        syncGeneratedAddress();

        const formData = new FormData(checkoutForm);
        const data = Object.fromEntries(formData.entries());
        let valid = true;

        if (!data.customer_name?.trim()) {
            setFieldError('customer_name', 'Full name is required.');
            valid = false;
        }

        if (!/^01[3-9]\d{8}$/.test(data.phone?.trim() || '')) {
            setFieldError('phone', 'Enter a valid Bangladeshi phone number.');
            valid = false;
        }

        if (
            data.alternative_phone?.trim()
            && !/^01[3-9]\d{8}$/.test(data.alternative_phone.trim())
        ) {
            setFieldError('alternative_phone', 'Enter a valid phone number.');
            valid = false;
        }

        if (
            data.customer_email?.trim()
            && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.customer_email.trim())
        ) {
            setFieldError('customer_email', 'Enter a valid email address.');
            valid = false;
        }

        if (!data.district?.trim()) {
            setFieldError('district', 'District is required.');
            valid = false;
        }

        if (selectedDeliveryArea() === 'outside_dhaka' && data.district === 'Dhaka') {
            setFieldError('district', 'Outside Dhaka cannot use Dhaka district.');
            valid = false;
        }

        if (!data.area_thana?.trim()) {
            setFieldError('area_thana', 'Area or thana is required.');
            valid = false;
        }

        if (!data.road_no?.trim()) {
            setFieldError('road_no', 'Road number is required.');
            valid = false;
        }

        if (!data.house_no?.trim()) {
            setFieldError('house_no', 'House number is required.');
            valid = false;
        }

        if (!data.full_address?.trim()) {
            setFieldError('full_address', 'Full address is required.');
            valid = false;
        }

        if (!valid) {
            return null;
        }

        return {
            customer_name: data.customer_name.trim(),
            phone: data.phone.trim(),
            alternative_phone: data.alternative_phone?.trim() || null,
            customer_email: data.customer_email?.trim() || null,
            delivery_area: selectedDeliveryArea(),
            district: data.district.trim(),
            area_thana: data.area_thana.trim(),
            road_no: data.road_no.trim(),
            house_no: data.house_no.trim(),
            full_address: data.full_address.trim(),
            order_note: data.order_note?.trim() || null,
            coupon_code: appliedCoupon?.code || null,
        };
    }

    function renderConfirmation(payload) {
        if (!confirmItems) {
            return;
        }

        confirmItems.innerHTML = '';

        checkoutCart.items.forEach(function (item) {
            const row = document.createElement('article');
            row.className = 'confirm-item';
            row.innerHTML = itemHtml(item);
            confirmItems.appendChild(row);
        });

        const itemsTotal = Number(checkoutCart.items_total || 0);
        const deliveryCharge = getDeliveryCharge();
        const discountAmount = Number(
            appliedCoupon?.discount_amount || 0
        );
        const grandTotal = Math.max(
            itemsTotal + deliveryCharge - discountAmount,
            0
        );

        if (confirmName) confirmName.textContent = payload.customer_name;
        if (confirmPhone) confirmPhone.textContent = payload.phone;
        if (confirmDeliveryArea) confirmDeliveryArea.textContent = deliveryAreaLabel();
        if (confirmDistrict) confirmDistrict.textContent = payload.district;
        if (confirmAddress) confirmAddress.textContent = payload.full_address;
        if (confirmPayment) confirmPayment.textContent = 'Cash on Delivery';
        if (confirmItemsTotal) confirmItemsTotal.textContent = plainMoney(itemsTotal);
        if (confirmDeliveryCharge) confirmDeliveryCharge.textContent = plainMoney(deliveryCharge);
        if (confirmDiscountRow) confirmDiscountRow.hidden = discountAmount <= 0;
        if (confirmDiscountAmount) confirmDiscountAmount.textContent = plainMoney(discountAmount);
        if (confirmGrandTotal) confirmGrandTotal.textContent = plainMoney(grandTotal);
    }

    function showServerErrors(errors = {}) {
        Object.entries(errors).forEach(function ([field, messages]) {
            setFieldError(
                field,
                Array.isArray(messages) ? messages[0] : messages
            );
        });
    }

    async function submitFinalOrder() {
        if (!pendingOrderPayload || !confirmFinalOrderBtn) {
            return;
        }

        const originalHtml = confirmFinalOrderBtn.innerHTML;
        confirmFinalOrderBtn.disabled = true;
        confirmFinalOrderBtn.innerHTML = '<span>Submitting...</span>';

        try {
            const response = await fetch('/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify(pendingOrderPayload),
            });

            const data = await parseResponse(response);

            checkoutForm?.reset();
            pendingOrderPayload = null;
            appliedCoupon = null;

            localStorage.setItem(
                'storefront_customer_has_order',
                '1'
            );
            
            localStorage.removeItem(
                'pending_coupon_code'
            );

            if (checkoutCouponCode) {
                checkoutCouponCode.value = '';
            }

            setCouponMessage('');

            if (thankYouOrderCode) {
                thankYouOrderCode.textContent =
                    data.order_code || 'Pending';
            }

            if (thankYouInvoiceButton) {
                const invoiceUrl = data.order?.invoice_url || '';

                thankYouInvoiceButton.href = invoiceUrl || '#';
                thankYouInvoiceButton.hidden = !invoiceUrl;
            }

            closeModal(orderConfirmModal);
            closeModal(checkoutModal);
            shouldRefreshAfterOrder = true;
            openModal(thankYouModal);

            window.dispatchEvent(
                new CustomEvent('storefront:order-created', {
                    detail: data,
                })
            );
        } catch (error) {
            if (error.status === 422 && error.data?.errors) {
                closeModal(orderConfirmModal);
                openModal(checkoutModal);
                showServerErrors(error.data.errors);
                return;
            }

            if (confirmGeneralError) {
                confirmGeneralError.textContent =
                    error.data?.message || 'Unable to place order.';
            }
        } finally {
            confirmFinalOrderBtn.disabled = false;
            confirmFinalOrderBtn.innerHTML = originalHtml;
        }
    }

    window.addEventListener('storefront:open-checkout', async function () {
        try {
            await loadCheckoutCart();
            document.getElementById('cartDrawerWrapper')?.classList.remove('open');
            handleDeliveryAreaChange();
            openModal(checkoutModal);
        } catch (error) {
            if (checkoutGeneralError) {
                checkoutGeneralError.textContent = error.message;
            }
        }
    });

    window.addEventListener('storefront:buy-now', async function () {
        try {
            await loadCheckoutCart();
            handleDeliveryAreaChange();
            openModal(checkoutModal);
        } catch (error) {
            if (checkoutGeneralError) {
                checkoutGeneralError.textContent = error.message;
            }
        }
    });

    checkoutForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const payload = validateCheckout();

        if (!payload) {
            return;
        }

        pendingOrderPayload = payload;
        renderConfirmation(payload);
        closeModal(checkoutModal);
        openModal(orderConfirmModal);
    });

    checkoutForm
        ?.querySelectorAll('input[name="delivery_area"]')
        .forEach(function (input) {
            input.addEventListener('change', handleDeliveryAreaChange);
        });

    [areaInput, roadInput, houseInput].forEach(function (input) {
        input?.addEventListener('input', syncGeneratedAddress);
    });

    [phoneInput, alternativePhoneInput].forEach(function (input) {
        input?.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            renderAvailableCoupons();
        });
    });

    districtInput?.addEventListener(
        'click',
        function () {
            if (
                selectedDeliveryArea()
                    === 'inside_dhaka'
            ) {
                return;
            }
    
            /*
            | আগে select করা district আবার
            | edit/search করার সুযোগ দেবে।
            */
    
            this.readOnly = false;
    
            openDistrictDropdown();
    
            renderDistrictDropdown(
                this.value.trim()
            );
    
            requestAnimationFrame(
                function () {
                    districtInput.focus();
                    districtInput.select();
                }
            );
        }
    );

    districtInput?.addEventListener(
        'input',
        function () {
            const typedValue =
                this.value.trim();
    
            districtShell?.classList.toggle(
                'has-value',
                Boolean(typedValue)
            );
    
            renderDistrictDropdown(
                typedValue
            );
    
            openDistrictDropdown();
    
            const exactMatch =
                getExactDistrictMatch(
                    typedValue
                );
    
            /*
            | Exact official name বা alias পাওয়া গেলে
            | canonical district auto-select হবে।
            |
            | cumilla  -> Comilla
            | foridpur -> Faridpur
            | bogura   -> Bogra
            */
    
            if (
                exactMatch
                &&
                normalizeDistrictSearch(
                    typedValue
                ).length >= 3
            ) {
                selectDistrict(
                    exactMatch.name
                );
    
                return;
            }
    
            this.readOnly = false;
    
            syncGeneratedAddress();
        }
    );

    districtClearBtn?.addEventListener(
        'click',
        function () {
            if (
                selectedDeliveryArea()
                    === 'inside_dhaka'
            ) {
                return;
            }
    
            if (districtInput) {
                districtInput.value = '';
                districtInput.readOnly = false;
            }
    
            districtShell?.classList.remove(
                'has-value'
            );
    
            renderDistrictDropdown('');
    
            openDistrictDropdown();
    
            syncGeneratedAddress();
    
            requestAnimationFrame(
                function () {
                    districtInput?.focus();
                }
            );
        }
    );

    cancelFinalOrderBtn?.addEventListener('click', function () {
        closeModal(orderConfirmModal);
        openModal(checkoutModal);
    });

    confirmFinalOrderBtn?.addEventListener('click', submitFinalOrder);

    applyCouponButton?.addEventListener('click', function () {
        applyCoupon();
    });

    removeCouponButton?.addEventListener('click', removeCoupon);

    checkoutVoucherTrack?.addEventListener('click', function (event) {
        const button = event.target.closest('[data-apply-voucher]');

        if (!button || button.disabled) {
            return;
        }

        if (checkoutCouponCode) {
            checkoutCouponCode.value = button.dataset.applyVoucher;
        }

        applyCoupon(button.dataset.applyVoucher);
    });

    voucherPrevButton?.addEventListener('click', function () {
        checkoutVoucherTrack?.scrollBy({
            left: -260,
            behavior: 'smooth',
        });
    });

    voucherNextButton?.addEventListener('click', function () {
        checkoutVoucherTrack?.scrollBy({
            left: 260,
            behavior: 'smooth',
        });
    });

    checkoutCouponCode?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyCoupon();
        }
    });

    document
        .querySelectorAll('[data-close-checkout-modal]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(checkoutModal);
            });
        });

    document
        .querySelectorAll('[data-close-confirm-modal]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(orderConfirmModal);
            });
        });

    document
        .querySelectorAll('[data-close-thank-you-modal]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                closeThankYouModal();
            });
        });

    document.addEventListener('click', function (event) {
        if (districtShell && !districtShell.contains(event.target)) {
            closeDistrictDropdown();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal(orderConfirmModal);
            closeModal(checkoutModal);
            if (thankYouModal?.classList.contains('open')) {
                closeThankYouModal();
            }
        }
    });

    handleDeliveryAreaChange();
});
