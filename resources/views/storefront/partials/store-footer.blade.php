<footer class="store-footer" id="contact">
    <div class="store-container">
        <div class="store-footer-card store-footer-card-pro">
            <div class="store-footer-brand">
                <a
                    href="{{ route('brand.show', $brand->slug) }}"
                    class="store-footer-logo-link store-footer-logo-link-centered"
                    aria-label="{{ $brand->name }} home"
                >
                    @if ($brandLogo)
                        <img
                            src="{{ Storage::url($brandLogo) }}"
                            alt="{{ $brand->name }}"
                            class="store-footer-logo"
                        >
                    @else
                        <span class="store-footer-logo-fallback">
                            {{ $brand->name }}
                        </span>
                    @endif
                </a>

                <p>
                    {{ $brand->address ?: 'Trusted fashion and lifestyle shopping.' }}
                </p>
            </div>

            <div class="store-footer-columns">
                <div class="store-footer-column">
                    <span>Explore</span>
                    <a href="{{ route('brand.show', $brand->slug) }}">Home</a>
                    <a href="{{ route('brand.show', $brand->slug) }}#new-arrivals">New Arrival</a>
                    <a href="{{ route('brand.show', $brand->slug) }}#featured-products">Featured Product</a>
                </div>

                <div class="store-footer-column">
                    <span>Business</span>
                    <a href="#sweet-cool">Factory Sweet Cool</a>
                    <a href="{{ route('brand.show', $brand->slug) }}#categories">Browse Categories</a>
                    <a href="{{ route('brand.show', $brand->slug) }}#products">All Products</a>
                </div>

                <div class="store-footer-column">
                    <span>Contact</span>
                    @if ($brand->contact_number)
                        <a href="tel:{{ $brand->contact_number }}" class="store-footer-contact-link">
                            <i class="fa-solid fa-phone"></i>
                            <span>{{ $brand->contact_number }}</span>
                        </a>
                    @endif
                    @if ($brand->email)
                        <a href="mailto:{{ $brand->email }}" class="store-footer-contact-link">
                            <i class="fa-regular fa-envelope"></i>
                            <span>{{ $brand->email }}</span>
                        </a>
                    @endif
                    @if ($brand->whatsapp_link)
                        <a href="{{ $brand->whatsapp_link }}" target="_blank" rel="noopener noreferrer" class="store-footer-contact-link">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </a>
                    @endif
                    @if ($brand->address)
                        <div class="store-footer-contact-link is-static">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>{{ $brand->address }}</span>
                        </div>
                    @endif
                </div>

                <div class="store-footer-column">
                    <span>Social</span>
                    @if ($brand->facebook_link)
                        <a href="{{ $brand->facebook_link }}" target="_blank" rel="noopener noreferrer" class="store-footer-contact-link">
                            <i class="fa-brands fa-facebook-f"></i>
                            <span>Facebook</span>
                        </a>
                    @endif
                    @if ($brand->instagram_link)
                        <a href="{{ $brand->instagram_link }}" target="_blank" rel="noopener noreferrer" class="store-footer-contact-link">
                            <i class="fa-brands fa-instagram"></i>
                            <span>Instagram</span>
                        </a>
                    @endif
                    <a href="#contact" class="store-footer-contact-link">
                        <i class="fa-regular fa-life-ring"></i>
                        <span>Support Desk</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="store-footer-bottom">
            <span>
                © {{ date('Y') }} {{ $brand->name }}. All rights reserved.
            </span>

            <span>
                Powered by Grey Stone retail and factory sourcing.
            </span>
        </div>
    </div>
</footer>
