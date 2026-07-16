<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $brand->meta_title ?? $brand->name }}</title>

    <style>
        :root {
            --primary-color: {{ $brand->primary_color }};
            --secondary-color: {{ $brand->secondary_color }};
            --background-color: {{ $brand->background_color }};
            --button-color: {{ $brand->button_color }};
            --text-color: {{ $brand->text_color }};
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: {{ $brand->font_family ?? 'Arial, sans-serif' }};
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .brand-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            text-align: center;
        }

        .brand-card {
            width: 100%;
            max-width: 650px;
            padding: 60px 30px;
            border: 1px solid var(--secondary-color);
            border-radius: 20px;
        }

        .brand-name {
            margin: 0 0 15px;
            font-size: 48px;
            color: var(--primary-color);
        }

        .brand-text {
            margin-bottom: 30px;
            font-size: 18px;
        }

        .brand-button {
            display: inline-block;
            padding: 14px 28px;
            color: #ffffff;
            text-decoration: none;
            background-color: var(--button-color);
            border-radius: 8px;
        }

        .brand-switcher {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 30px;
        }

        .switch-button {
            padding: 10px 18px;
            color: var(--text-color);
            text-decoration: none;
            border: 1px solid var(--secondary-color);
            border-radius: 8px;
        }

        .switch-button:hover,
        .switch-button.active {
            color: #ffffff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>

<body>
    <main class="brand-page">
        <section class="brand-card">
            <h1 class="brand-name">{{ $brand->name }}</h1>

            <p class="brand-text">
                Welcome to the {{ $brand->name }} storefront.
            </p>

            <a href="#" class="brand-button">
                Explore Products
            </a>

            <div class="brand-switcher">
                @foreach ($brands as $switchBrand)
                    <a
                        href="{{ route('brand.show', $switchBrand->slug) }}"
                        class="switch-button {{ $switchBrand->id === $brand->id ? 'active' : '' }}"
                    >
                        {{ $switchBrand->name }}
                    </a>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>