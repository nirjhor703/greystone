<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Member | Grey Stone')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=anek-bangla:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="member-body @yield('body-class')">
    <div class="member-language-switch" aria-label="Language selector">
        <button type="button" data-language-toggle="en">EN</button>
        <button type="button" data-language-toggle="bn">বাংলা</button>
    </div>
    @yield('content')
</body>
</html>
