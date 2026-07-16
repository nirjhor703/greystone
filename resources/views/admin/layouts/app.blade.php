<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin Dashboard')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f5f7;
            color: #1f2937;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            padding: 24px 18px;
            background: #1f1f1f;
            color: #ffffff;
        }

        .sidebar-logo {
            margin-bottom: 35px;
            font-size: 24px;
            font-weight: 700;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-link {
            display: block;
            padding: 12px 14px;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 8px;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            color: #ffffff;
            background: #343434;
        }

        .main-area {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 70px;
            padding: 15px 28px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .topbar-title {
            margin: 0;
            font-size: 20px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-button {
            padding: 9px 15px;
            cursor: pointer;
            background: #1f1f1f;
            color: #ffffff;
            border: 0;
            border-radius: 7px;
        }

        .content {
            padding: 28px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                padding: 18px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">
            GreyStone Admin
        </div>

        <nav class="sidebar-menu">
            <a
                href="{{ route('admin.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            >
                Dashboard
            </a>

            <a href="#" class="sidebar-link">
                Brands
            </a>

            <a href="#" class="sidebar-link">
                Products
            </a>

            <a href="#" class="sidebar-link">
                Orders
            </a>

            <a href="#" class="sidebar-link">
                Customers
            </a>

            <a href="#" class="sidebar-link">
                Reports
            </a>

            <a href="#" class="sidebar-link">
                Settings
            </a>
        </nav>
    </aside>

    <section class="main-area">
        <header class="topbar">
            <h1 class="topbar-title">
                @yield('page-title', 'Dashboard')
            </h1>

            <div class="topbar-user">
                <span>{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="logout-button">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </section>
</div>

@stack('scripts')
</body>
</html>