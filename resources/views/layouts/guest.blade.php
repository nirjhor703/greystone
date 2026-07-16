<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Grey Stone')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body>
    <main class="auth-page">
        <section class="auth-showcase">
            <a href="{{ url('/') }}" class="auth-logo">
                <span class="auth-logo-mark">GS</span>

                <span class="auth-logo-content">
                    <strong>GREY STONE</strong>
                    <small>Management Portal</small>
                </span>
            </a>

            <div class="auth-showcase-content">
                <span class="auth-tagline">Multi-brand commerce</span>

                <h1>
                    One system.<br>
                    Three identities.
                </h1>

                <p>
                    Manage Grey Stone, Blue Shades and Pink Touch through one
                    secure and unified business dashboard.
                </p>
            </div>

            <p class="auth-copyright">
                © {{ date('Y') }} Grey Stone. All rights reserved.
            </p>
        </section>

        <section class="auth-form-section">
            <div class="auth-form-container">
                <a href="{{ url('/') }}" class="auth-mobile-logo">
                    <span class="auth-logo-mark">GS</span>

                    <span class="auth-logo-content">
                        <strong>GREY STONE</strong>
                        <small>Management Portal</small>
                    </span>
                </a>

                {{ $slot }}
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(
                    button.dataset.passwordToggle
                );

                if (!input) {
                    return;
                }

                const passwordHidden = input.type === 'password';

                input.type = passwordHidden ? 'text' : 'password';
                button.textContent = passwordHidden ? 'Hide' : 'Show';
            });
        });
    </script>
</body>
</html>