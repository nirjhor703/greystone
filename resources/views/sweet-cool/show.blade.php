<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sweet Cool | {{ $brand->name }}</title>

    @if ($brand->favicon)
        <link rel="icon" href="{{ Storage::url($brand->favicon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap"
        rel="stylesheet"
    >
    <link
        href="https://fonts.bunny.net/css?family=anek-bangla:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        :root {
            --store-primary: {{ $brand->primary_color ?: '#333333' }};
            --store-secondary: {{ $brand->secondary_color ?: '#777777' }};
            --store-background: {{ $brand->background_color ?: '#ffffff' }};
            --store-button: {{ $brand->button_color ?: '#333333' }};
            --store-text: {{ $brand->text_color ?: '#171717' }};
        }

        body {
            font-family:
                {!! $brand->font_family
                    ? "'".e($brand->font_family)."', sans-serif"
                    : "'Figtree', sans-serif"
                !!};
        }
    </style>
</head>
<body>
@php
    $brandSlug = $brand->slug;
    $isPinkTouch = $brandSlug === 'pink-touch';
    $brandLogo = $brand->mobile_logo ?: $brand->logo;
    $storage = \Illuminate\Support\Facades\Storage::class;
    $logoUrl = $sweetCoolPage->logo_path
        ? $storage::url($sweetCoolPage->logo_path)
        : asset('storefront-backgrounds/sweetcool.png');
    $fullLogoUrl = asset('storefront-backgrounds/sweet-cool-full-logo.jpg');
    $heroUrl = $sweetCoolPage->hero_image_path
        ? $storage::url($sweetCoolPage->hero_image_path)
        : $logoUrl;
    $roleLabels = collect($sweetCoolRoles)
        ->mapWithKeys(fn (string $role) => [
            $role => str($role)->replace('-', ' ')->title()->toString(),
        ]);
    $gallerySections = [
        [
            'heading' => 'Factory Gallery',
            'copy' => 'Real production, workspace, and floor visuals from Sweet Cool.',
            'images' => $sweetCoolPage->factory_images ?? [],
        ],
        [
            'heading' => 'Product Gallery',
            'copy' => 'Products, samples, and sourcing-ready presentations in one stream.',
            'images' => $sweetCoolPage->product_images ?? [],
        ],
        [
            'heading' => 'More From Sweet Cool',
            'copy' => 'Campaign, mixed, and additional gallery visuals managed from your dashboard.',
            'images' => $sweetCoolPage->gallery_images ?? [],
        ],
    ];
@endphp

<div class="storefront storefront-{{ $brandSlug }} sweet-cool-storefront">
    <main class="sweet-cool-page" id="sweet-cool">
        <div class="store-container sweet-cool-back-wrap">
            <a
                href="{{ route('brand.show', $brand->slug) }}"
                class="store-header-icon-button sweet-cool-back-button"
                aria-label="Back to {{ $brand->name }}"
            >
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>

        <section class="sweet-cool-page-hero">
            <div class="store-container sweet-cool-page-hero-grid">
                <div class="sweet-cool-page-copy">
                    <span class="sweet-cool-page-kicker">
                        {{ $sweetCoolPage->hero_kicker }}
                    </span>

                    <div class="sweet-cool-page-hero-logo-stack">
                        <div class="sweet-cool-page-hero-logo-wrap is-full">
                            <img
                                src="{{ $fullLogoUrl }}"
                                alt="Sweet Cool full logo"
                                class="sweet-cool-page-hero-logo-full"
                            >
                        </div>
                    </div>

                    <h1>{{ $sweetCoolPage->hero_title }}</h1>
                    <p>{{ $sweetCoolPage->hero_description }}</p>

                    <div class="sweet-cool-page-badges">
                        @foreach (($sweetCoolPage->promo_badges ?? []) as $badge)
                            @if (filled($badge))
                                <span>{{ $badge }}</span>
                            @endif
                        @endforeach
                    </div>

                    <a href="#sweet-cool-form" class="sweet-cool-page-primary-button">
                        {{ $sweetCoolPage->hero_button_text ?: "Let's Dive into SweetCool" }}
                    </a>
                </div>

            </div>
        </section>

        <section class="sweet-cool-page-intro">
            <div class="store-container">
                <div class="sweet-cool-page-section-heading">
                    <h2>{{ $sweetCoolPage->intro_heading }}</h2>
                    <p>{{ $sweetCoolPage->intro_description }}</p>
                </div>

                <div class="sweet-cool-page-feature-grid">
                    <article>
                        <strong>{{ $sweetCoolPage->section_one_title }}</strong>
                        <p>{{ $sweetCoolPage->section_one_body }}</p>
                    </article>
                    <article>
                        <strong>{{ $sweetCoolPage->section_two_title }}</strong>
                        <p>{{ $sweetCoolPage->section_two_body }}</p>
                    </article>
                    <article>
                        <strong>{{ $sweetCoolPage->section_three_title }}</strong>
                        <p>{{ $sweetCoolPage->section_three_body }}</p>
                    </article>
                </div>
            </div>
        </section>

        @foreach ($gallerySections as $section)
            @if (count($section['images']) > 0)
                <section class="sweet-cool-page-gallery-section">
                    <div class="store-container">
                        <div class="sweet-cool-page-gallery-head">
                            <h2>{{ $section['heading'] }}</h2>
                            <p>{{ $section['copy'] }}</p>
                        </div>

                        <div class="sweet-cool-page-gallery" data-sweet-gallery>
                            <button
                                type="button"
                                class="sweet-cool-page-gallery-arrow is-prev"
                                data-gallery-prev
                                aria-label="Previous images"
                            >
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>

                            <div class="sweet-cool-page-gallery-track" data-gallery-track>
                                @foreach ($section['images'] as $image)
                                    <article class="sweet-cool-page-gallery-slide">
                                        <img
                                            src="{{ $storage::url($image) }}"
                                            alt="{{ $section['heading'] }}"
                                            loading="lazy"
                                        >
                                    </article>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                class="sweet-cool-page-gallery-arrow is-next"
                                data-gallery-next
                                aria-label="Next images"
                            >
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>

                            <div class="sweet-cool-page-gallery-dots" data-gallery-dots></div>
                        </div>
                    </div>
                </section>
            @endif
        @endforeach

        <section class="sweet-cool-page-connect" id="sweet-cool-form">
            <div class="store-container">
                <div class="sweet-cool-page-form-shell">
                    <div class="sweet-cool-page-form-head">
                        <span>One Smart Form</span>
                        <h2>Talk to Sweet Cool</h2>
                        <p>
                            One compact form for factory connection, sourcing discussion, and a confirmed visit slot.
                        </p>
                    </div>

                    @if (session('sweet_cool_success'))
                        <div class="sweet-cool-page-alert success">
                            {{ session('sweet_cool_success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="sweet-cool-page-alert error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('sweet-cool.store') }}"
                        class="sweet-cool-page-form sweet-cool-page-form-unified"
                    >
                        @csrf
                        <input type="hidden" name="source_page" value="sweet-cool">
                        <input type="hidden" name="page_url" value="{{ url()->current() }}">
                        <input type="hidden" name="brand_id" value="{{ $brand->id }}">

                        <div class="sweet-cool-page-form-two">
                            <label>
                                <span>Name</span>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Your full name" required>
                            </label>
                            <label>
                                <span>Phone</span>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone or WhatsApp" required>
                            </label>
                            <label>
                                <span>Email (Optional)</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com">
                            </label>
                            <label>
                                <span>Company (Optional)</span>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Company or shop name">
                            </label>
                        </div>

                        <div class="sweet-cool-page-form-three">
                            <label>
                                <span>Requirement</span>
                                <select name="interest_type" required>
                                    <option value="">Choose requirement</option>
                                    <option value="bulk-order" @selected(old('interest_type') === 'bulk-order')>Bulk order</option>
                                    <option value="factory-sourcing" @selected(old('interest_type') === 'factory-sourcing')>Factory sourcing</option>
                                    <option value="custom-production" @selected(old('interest_type') === 'custom-production')>Custom production</option>
                                    <option value="wholesale-partnership" @selected(old('interest_type') === 'wholesale-partnership')>Wholesale partnership</option>
                                    <option value="factory-visit" @selected(old('interest_type') === 'factory-visit')>Factory visit</option>
                                    <option value="buyer-meeting" @selected(old('interest_type') === 'buyer-meeting')>Buyer meeting</option>
                                </select>
                            </label>
                            <label>
                                <span>Preferred Contact (Optional)</span>
                                <select name="preferred_contact">
                                    <option value="">Choose contact method</option>
                                    <option value="phone" @selected(old('preferred_contact') === 'phone')>Phone</option>
                                    <option value="whatsapp" @selected(old('preferred_contact') === 'whatsapp')>WhatsApp</option>
                                    <option value="email" @selected(old('preferred_contact') === 'email')>Email</option>
                                </select>
                            </label>
                        </div>

                        <div class="sweet-cool-page-booking-box">
                            <div class="sweet-cool-page-booking-head">
                                <strong>Factory Visit Booking (Optional)</strong>
                            </div>

                            <div class="sweet-cool-page-form-two">
                                <label>
                                    <span>Visit Date</span>
                                    <input
                                        type="date"
                                        name="visit_date"
                                        id="sweetCoolVisitDate"
                                        min="{{ now()->toDateString() }}"
                                        value="{{ old('visit_date') }}"
                                    >
                                </label>
                                <label>
                                    <span>Visit Time</span>
                                    <input
                                        type="time"
                                        name="visit_time"
                                        id="sweetCoolVisitTime"
                                        value="{{ old('visit_time') }}"
                                    >
                                </label>
                            </div>

                            <div class="sweet-cool-page-slot-status" id="sweetCoolSlotStatus">
                                Pick a date and time if you want us to reserve a Sweet Cool visit slot for you.
                            </div>
                        </div>

                        <div class="sweet-cool-page-role-wrap">
                            <span class="sweet-cool-page-role-title">Who are you? <em>Required</em></span>
                            <div class="sweet-cool-page-role-grid">
                                @foreach ($roleLabels as $roleValue => $roleLabel)
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="role_tags[]"
                                            value="{{ $roleValue }}"
                                            @checked(collect(old('role_tags', []))->contains($roleValue))
                                        >
                                        <span>{{ $roleLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <label>
                            <span>Message</span>
                            <textarea
                                name="message"
                                rows="4"
                                placeholder="Tell us which product, requirement, sourcing plan, or business goal you want to discuss."
                                required
                            >{{ old('message') }}</textarea>
                        </label>

                        <button type="submit" class="sweet-cool-page-submit">
                            Send to Sweet Cool
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="sweet-cool-page-contact">
            <div class="store-container">
                <div class="sweet-cool-page-contact-shell">
                    <div class="sweet-cool-page-contact-head">
                        <span>Sweet Cool</span>
                        <h2>Feel free to contact us</h2>
                    </div>

                    <div class="sweet-cool-page-contact-list">
                        <a href="tel:01928883348" class="sweet-cool-page-contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <span>01928-883348</span>
                        </a>

                        <a href="mailto:sweetcool.online@gmail.com" class="sweet-cool-page-contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <span>sweetcool.online@gmail.com</span>
                        </a>

                        <a
                            href="https://www.facebook.com/profile.php?id=61578844787879&mibextid=wwXIfr&rdid=jhJ5LvxFngYWuftL&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1EACPGR4Fj%2F%3Fmibextid%3DwwXIfr%26ref%3D1#"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="sweet-cool-page-contact-item"
                        >
                            <i class="fa-brands fa-facebook-f"></i>
                            <span>Facebook</span>
                        </a>
                    </div>

                    <a href="#sweet-cool-form" class="sweet-cool-page-contact-button">
                        Feel Free to Contact Us
                    </a>
                </div>
            </div>
        </section>
    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-sweet-gallery]').forEach(function (gallery) {
        const track = gallery.querySelector('[data-gallery-track]');
        const slides = Array.from(gallery.querySelectorAll('.sweet-cool-page-gallery-slide'));
        const prev = gallery.querySelector('[data-gallery-prev]');
        const next = gallery.querySelector('[data-gallery-next]');
        const dotsHost = gallery.querySelector('[data-gallery-dots]');
        let index = 0;

        if (!track || slides.length === 0) {
            return;
        }

        const dots = slides.map(function (_, slideIndex) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'sweet-cool-page-dot';
            dot.setAttribute('aria-label', 'Go to slide ' + (slideIndex + 1));
            dot.addEventListener('click', function () {
                index = slideIndex;
                render();
            });
            dotsHost?.appendChild(dot);
            return dot;
        });

        function render() {
            track.style.transform = 'translateX(-' + (index * 100) + '%)';
            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('active', dotIndex === index);
            });
        }

        prev?.addEventListener('click', function () {
            index = index === 0 ? slides.length - 1 : index - 1;
            render();
        });

        next?.addEventListener('click', function () {
            index = index === slides.length - 1 ? 0 : index + 1;
            render();
        });

        render();
    });

    const dateInput = document.getElementById('sweetCoolVisitDate');
    const timeInput = document.getElementById('sweetCoolVisitTime');
    const statusBox = document.getElementById('sweetCoolSlotStatus');
    let requestTimer = null;

    function checkAvailability() {
        if (!dateInput?.value || !timeInput?.value || !statusBox) {
            return;
        }

        statusBox.textContent = 'Checking this slot...';
        statusBox.classList.remove('is-available', 'is-booked');

        const query = new URLSearchParams({
            visit_date: dateInput.value,
            visit_time: timeInput.value,
        });

        window.fetch('{{ route('sweet-cool.bookings.availability') }}?' + query.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                statusBox.textContent = payload.message || 'Availability updated.';
                statusBox.classList.toggle('is-available', Boolean(payload.available));
                statusBox.classList.toggle('is-booked', !payload.available);
            })
            .catch(function () {
                statusBox.textContent = 'We could not check this slot right now. Please try again.';
                statusBox.classList.remove('is-available');
                statusBox.classList.add('is-booked');
            });
    }

    [dateInput, timeInput].forEach(function (input) {
        input?.addEventListener('input', function () {
            if (requestTimer) {
                window.clearTimeout(requestTimer);
            }

            requestTimer = window.setTimeout(checkAvailability, 220);
        });
    });

    if (dateInput?.value && timeInput?.value) {
        checkAvailability();
    }
});
</script>
</body>
</html>
