<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Grey Stone Admin')</title>

    <script>
        (function () {
            const savedTheme = localStorage.getItem('greystone-admin-theme');
            const prefersDark = window.matchMedia
                && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');

            document.documentElement.dataset.adminTheme = theme;
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>
@php
    $adminUser = auth()->user();
    $canAccess = fn (string $module): bool =>
        $adminUser?->canAccessAdminModule($module) ?? false;
    $adminUserName = $adminUser?->name ?? 'Admin';
    $adminUserRole = $adminUser?->roleLabel() ?? 'Guest';
    $adminUserInitial = strtoupper(substr($adminUserName, 0, 1));

    $mainNotificationCount = class_exists(\App\Models\AdminNotification::class)
        ? \App\Models\AdminNotification::query()
            ->category(\App\Models\AdminNotification::CATEGORY_MAIN)
            ->unread()
            ->count()
        : 0;

    $stockNotificationCount = class_exists(\App\Models\AdminNotification::class)
        ? \App\Models\AdminNotification::query()
            ->category(\App\Models\AdminNotification::CATEGORY_STOCK)
            ->unread()
            ->count()
        : 0;
@endphp
<div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <span class="admin-brand-mark">GS</span>

            <span class="admin-brand-text">
                <strong>GREY STONE</strong>
                <small>Admin Portal</small>
            </span>
        </a>

        <nav class="admin-nav">
            <div class="admin-nav-title">Overview</div>

            @if ($canAccess('dashboard'))
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </span>
                    Dashboard
                </a>
            @endif

            <a
                href="{{ route('admin.employee-dashboard.index') }}"
                class="admin-nav-link {{ request()->routeIs('admin.employee-dashboard.*') ? 'active' : '' }}"
            >
                <span class="admin-nav-icon">
                    <i class="fa-solid fa-ranking-star"></i>
                </span>
                Employee Dashboard
            </a>

            <div class="admin-nav-title">Commerce</div>

            @if ($canAccess('brands'))
                <a
                    href="{{ route('admin.brands.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-store"></i>
                    </span>
                    Brands
                </a>
            @endif

            @if ($canAccess('categories'))
                <a
                    href="{{ route('admin.categories.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                    Categories
                </a>
            @endif

            @if ($canAccess('products'))
                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-shirt"></i>
                    </span>
                    Products
                </a>
            @endif

            @if ($canAccess('orders'))
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    Orders
                </a>
            @endif

            

            @if ($canAccess('coupons'))
                <a
                    href="{{ route('admin.coupons.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-ticket"></i>
                    </span>
                    Coupons
                </a>
            @endif

            

            @if ($canAccess('notifications'))
                <a
                    href="{{ route('admin.notifications.index', ['category' => 'main']) }}"
                    class="admin-nav-link {{ request()->routeIs('admin.notifications.*') && request('category', 'main') === 'main' ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-bell"></i>
                    </span>
                    Notifications

                    @if ($mainNotificationCount > 0)
                        <span class="admin-nav-count">
                            {{ $mainNotificationCount }}
                        </span>
                    @endif
                </a>
            @endif

            @if ($canAccess('stock_notifications'))
                <a
                    href="{{ route('admin.notifications.index', ['category' => 'stock']) }}"
                    class="admin-nav-link {{ request()->routeIs('admin.notifications.*') && request('category') === 'stock' ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-box-open"></i>
                    </span>
                    Stock Notifications

                    @if ($stockNotificationCount > 0)
                        <span class="admin-nav-count">
                            {{ $stockNotificationCount }}
                        </span>
                    @endif
                </a>
            @endif

            <div class="admin-nav-title">Management</div>

            @if ($canAccess('customers'))
                <a
                    href="{{ route('admin.customers.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    Customers
                </a>
            @endif

            @if ($canAccess('sweet_cool'))
                <a
                    href="{{ route('admin.sweet-cool.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.sweet-cool.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-industry"></i>
                    </span>
                    Sweet Cool
                </a>
            @endif

            @if ($canAccess('reports'))
                <a
                    href="{{ route('admin.reports.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </span>
                    Reports
                </a>
            @endif

            @if ($canAccess('admin_users'))
                <a
                    href="{{ route('admin.admin-users.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>
                    Admins
                </a>
            @endif

            @if ($canAccess('settings'))
                <a
                    href="{{ route('admin.settings.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                >
                    <span class="admin-nav-icon">
                        <i class="fa-solid fa-gear"></i>
                    </span>
                    Settings
                </a>
            @endif
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-user-mini">
                <span class="admin-avatar">
                    {{ $adminUserInitial }}
                </span>

                <div>
                    <strong>{{ $adminUserName }}</strong>
                    <small>{{ $adminUserRole }}</small>
                </div>
            </div>
        </div>
    </aside>

    <div class="admin-overlay" id="adminOverlay"></div>

    <section class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button
                    type="button"
                    class="admin-menu-button"
                    id="adminMenuButton"
                    aria-label="Open menu"
                >
                    ☰
                </button>

                <div class="admin-page-heading">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p>@yield('page-subtitle', 'Manage your business')</p>
                </div>
            </div>

            <div class="admin-topbar-actions">
                <button
                    type="button"
                    class="admin-theme-toggle"
                    id="adminThemeToggle"
                    aria-label="Toggle admin theme"
                    aria-pressed="false"
                >
                    <i class="fa-solid fa-moon"></i>
                    <span>Dark</span>
                </button>

                <a
                    href="{{ route('brand.show', ['slug' => 'grey-stone']) }}"
                    target="_blank"
                    class="admin-store-link"
                >
                    View Store
                </a>

                <span>{{ $adminUserName }}</span>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    id="adminLogoutForm"
                >
                    @csrf

                    <button
                        type="button"
                        class="admin-logout-button"
                        id="openLogoutModal"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            @yield('content')
        </main>
    </section>
</div>

@if ($errors->has('admin_permission'))
    <div
        class="brand-modal open"
        id="adminPermissionMessageModal"
        aria-hidden="false"
    >
        <div
            class="brand-modal-backdrop"
            data-close-admin-permission-modal
        ></div>

        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">
                !
            </div>

            <h3>Access Blocked</h3>

            <p>
                {{ $errors->first('admin_permission') }}
            </p>

            <span>
                Ask the root super admin or an authorized super admin to change this access.
            </span>

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-admin-permission-modal
                >
                    Okay
                </button>
            </div>
        </div>
    </div>
@endif

<div
    class="brand-modal"
    id="logoutConfirmModal"
    aria-hidden="true"
>
    <div
        class="brand-modal-backdrop"
        data-close-logout-modal
    ></div>

    <div class="brand-modal-dialog brand-delete-dialog">
        <div class="brand-delete-icon">
            !
        </div>

        <h3>Logout?</h3>

        <p>
            Are you sure you want to logout from
            <strong>{{ $adminUserName }}</strong>?
        </p>

        <span>
            You will need to sign in again to access the admin panel.
        </span>

        <div class="brand-delete-actions">
            <button
                type="button"
                class="brand-secondary-button"
                data-close-logout-modal
            >
                No, Cancel
            </button>

            <button
                type="button"
                class="brand-danger-button"
                id="confirmLogoutButton"
            >
                Yes, Logout
            </button>
        </div>
    </div>
</div>

<script>
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const menuButton = document.getElementById('adminMenuButton');

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    }

    menuButton?.addEventListener('click', function () {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });

    overlay?.addEventListener('click', closeSidebar);

    const themeToggle = document.getElementById('adminThemeToggle');
    const themeToggleIcon = themeToggle?.querySelector('i');
    const themeToggleText = themeToggle?.querySelector('span');

    function applyAdminTheme(theme) {
        const nextTheme = theme === 'dark' ? 'dark' : 'light';

        document.documentElement.dataset.adminTheme = nextTheme;
        localStorage.setItem('greystone-admin-theme', nextTheme);

        if (themeToggle) {
            const isDark = nextTheme === 'dark';

            themeToggle.setAttribute('aria-pressed', String(isDark));
            themeToggleIcon.className = isDark
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';
            themeToggleText.textContent = isDark ? 'Light' : 'Dark';
        }
    }

    applyAdminTheme(document.documentElement.dataset.adminTheme || 'light');

    themeToggle?.addEventListener('click', function () {
        const currentTheme = document.documentElement.dataset.adminTheme;

        applyAdminTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });

    const logoutModal = document.getElementById('logoutConfirmModal');
    const openLogoutModal = document.getElementById('openLogoutModal');
    const confirmLogoutButton = document.getElementById('confirmLogoutButton');
    const adminLogoutForm = document.getElementById('adminLogoutForm');

    function showLogoutModal() {
        logoutModal?.classList.add('open');
        logoutModal?.setAttribute('aria-hidden', 'false');
        document.body.classList.add('brand-modal-open');
    }

    function hideLogoutModal() {
        logoutModal?.classList.remove('open');
        logoutModal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('brand-modal-open');
    }

    openLogoutModal?.addEventListener('click', showLogoutModal);

    logoutModal
        ?.querySelectorAll('[data-close-logout-modal]')
        .forEach(function (button) {
            button.addEventListener('click', hideLogoutModal);
        });

    confirmLogoutButton?.addEventListener('click', function () {
        adminLogoutForm?.submit();
    });

    if (document.getElementById('adminPermissionMessageModal')) {
        document.body.classList.add('brand-modal-open');
    }

    document
        .querySelectorAll('[data-close-admin-permission-modal]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const modal = document.getElementById(
                    'adminPermissionMessageModal'
                );

                modal?.classList.remove('open');
                modal?.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('brand-modal-open');
            });
        });
</script>

@stack('scripts')
</body>
</html>
