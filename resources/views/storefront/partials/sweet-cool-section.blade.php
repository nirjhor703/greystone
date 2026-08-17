@php
    $sweetCoolProduct = $product ?? null;
    $sweetCoolPage = $sweetCoolProduct ? 'product' : 'storefront';
@endphp

<section
    class="sweet-cool-section"
    id="sweet-cool"
>
    <div class="store-container">
        <div class="sweet-cool-shell">
            <div class="sweet-cool-story-card">
                <span class="sweet-cool-eyebrow">
                    Factory Sweet Cool
                </span>

                <p class="sweet-cool-hook">
                    Excited to do business with us? Here is our factory.
                </p>

                <div class="sweet-cool-logo-stage">
                    <img
                        src="{{ asset('images/storefront/sweet-cool-logo.jpg') }}"
                        alt="Sweet Cool"
                        class="sweet-cool-logo-image"
                    >
                </div>

                <div class="sweet-cool-contact-list">
                    <a href="tel:01928883348" class="sweet-cool-contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <span>01928-883348</span>
                    </a>

                    <a href="mailto:sweetcool.online@gmail.com" class="sweet-cool-contact-item">
                        <i class="fa-regular fa-envelope"></i>
                        <span>Email</span>
                    </a>

                    <a
                        href="https://www.facebook.com/profile.php?id=61578844787879&mibextid=wwXIfr&rdid=KBIWk8vwgwRpRmhn&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1EACPGR4Fj%2F%3Fmibextid%3DwwXIfr%26ref%3D1#"
                        class="sweet-cool-contact-item"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <i class="fa-brands fa-facebook-f"></i>
                        <span>Facebook</span>
                    </a>

                    <div class="sweet-cool-contact-item static">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Location</span>
                    </div>
                </div>

                <div class="sweet-cool-form-card">
                    <div class="sweet-cool-form-head">
                        <h3>Bulk order or factory sourcing</h3>
                    </div>

                    @if (session('sweet_cool_success'))
                        <div class="sweet-cool-alert success">
                            {{ session('sweet_cool_success') }}
                        </div>
                    @endif

                    @if ($errors->has('sweet_cool'))
                        <div class="sweet-cool-alert error">
                            {{ $errors->first('sweet_cool') }}
                        </div>
                    @endif

                    <button
                        type="button"
                        class="sweet-cool-open-button"
                        id="openSweetCoolModal"
                    >
                        Message Us
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<div
    class="storefront storefront-{{ $brand->slug ?? 'grey-stone' }} store-modal sweet-cool-modal-wrapper {{ $errors->has('sweet_cool') ? 'open' : '' }}"
    id="sweetCoolModal"
    @if ($errors->has('sweet_cool'))
        style="display:flex;"
    @endif
>
    <div
        class="store-modal-backdrop"
        data-close-sweet-cool-modal
    ></div>

    <div
        class="store-modal-dialog sweet-cool-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sweetCoolModalTitle"
    >
        <button
            type="button"
            class="store-modal-close"
            data-close-sweet-cool-modal
            aria-label="Close Sweet Cool form"
        >
            ×
        </button>

        <div class="sweet-cool-mini-brand sweet-cool-mini-brand-modal">
            <img
                src="{{ asset('images/storefront/sweet-cool-logo.jpg') }}"
                alt="Sweet Cool"
                class="sweet-cool-mini-logo"
            >
            <strong>Sweet Cool</strong>
        </div>

        <div class="sweet-cool-form-head">
            <h3 id="sweetCoolModalTitle">Talk to Sweet Cool</h3>
            <p>Tell us what you need and we will reach out.</p>
        </div>

        @if ($errors->has('sweet_cool'))
            <div class="sweet-cool-alert error">
                {{ $errors->first('sweet_cool') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('sweet-cool.store') }}"
            class="sweet-cool-form"
        >
            @csrf

            <input
                type="hidden"
                name="brand_id"
                value="{{ $brand->id }}"
            >

            @if ($sweetCoolProduct)
                <input
                    type="hidden"
                    name="product_id"
                    value="{{ $sweetCoolProduct->id }}"
                >
            @endif

            <input
                type="hidden"
                name="source_page"
                value="{{ $sweetCoolPage }}"
            >

            <input
                type="hidden"
                name="page_url"
                value="{{ url()->current() }}"
            >

            <div class="sweet-cool-form-grid">
                <label>
                    <span>Name</span>
                    <input
                        type="text"
                        name="customer_name"
                        value="{{ old('customer_name') }}"
                        placeholder="Your name"
                        required
                    >
                    @error('customer_name')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Phone</span>
                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Phone or WhatsApp"
                        required
                    >
                    @error('phone')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Email</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                    >
                    @error('email')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Company</span>
                    <input
                        type="text"
                        name="company_name"
                        value="{{ old('company_name') }}"
                        placeholder="Company or shop name"
                    >
                    @error('company_name')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Need</span>
                    <select
                        name="interest_type"
                        required
                    >
                        <option value="">Select requirement</option>
                        <option value="bulk-order" @selected(old('interest_type') === 'bulk-order')>Bulk order</option>
                        <option value="factory-sourcing" @selected(old('interest_type') === 'factory-sourcing')>Factory sourcing</option>
                        <option value="custom-production" @selected(old('interest_type') === 'custom-production')>Custom production</option>
                        <option value="wholesale-partnership" @selected(old('interest_type') === 'wholesale-partnership')>Wholesale partnership</option>
                    </select>
                    @error('interest_type')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label>
                    <span>Quantity</span>
                    <input
                        type="text"
                        name="quantity_note"
                        value="{{ old('quantity_note') }}"
                        placeholder="e.g. 300 pcs / monthly"
                    >
                    @error('quantity_note')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>

            <label>
                <span>Preferred Contact</span>
                <div class="sweet-cool-choice-row">
                    <label>
                        <input
                            type="radio"
                            name="preferred_contact"
                            value="phone"
                            @checked(old('preferred_contact', 'phone') === 'phone')
                        >
                        <span>Phone</span>
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="preferred_contact"
                            value="whatsapp"
                            @checked(old('preferred_contact') === 'whatsapp')
                        >
                        <span>WhatsApp</span>
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="preferred_contact"
                            value="email"
                            @checked(old('preferred_contact') === 'email')
                        >
                        <span>Email</span>
                    </label>
                </div>
                @error('preferred_contact')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <label>
                <span>Message</span>
                <textarea
                    name="message"
                    rows="5"
                    placeholder="Tell us which product, quantity, target price, or factory requirement you want to discuss."
                    required
                >{{ old('message') }}</textarea>
                @error('message')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <button
                type="submit"
                class="sweet-cool-submit"
            >
                Send to Sweet Cool
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sweetCoolModal = document.getElementById('sweetCoolModal');
    const openSweetCoolModalButton = document.getElementById('openSweetCoolModal');

    function openSweetCoolModal() {
        if (!sweetCoolModal) {
            return;
        }

        sweetCoolModal.classList.add('open');
        sweetCoolModal.style.display = 'flex';
        document.body.classList.add('store-overlay-open');
    }

    function closeSweetCoolModal() {
        if (!sweetCoolModal) {
            return;
        }

        sweetCoolModal.classList.remove('open');
        sweetCoolModal.style.display = '';
        document.body.classList.remove('store-overlay-open');
    }

    openSweetCoolModalButton?.addEventListener('click', openSweetCoolModal);

    sweetCoolModal
        ?.querySelectorAll('[data-close-sweet-cool-modal]')
        .forEach(function (button) {
            button.addEventListener('click', closeSweetCoolModal);
        });

    if (sweetCoolModal?.classList.contains('open')) {
        document.body.classList.add('store-overlay-open');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && sweetCoolModal?.classList.contains('open')) {
            closeSweetCoolModal();
        }
    });
});
</script>
