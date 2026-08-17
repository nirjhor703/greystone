@extends('admin.layouts.app')

@section('title', 'Settings | Grey Stone Admin')
@section('page-title', 'Settings')
@section('page-subtitle', 'Profile, security and admin account preferences')

@section('content')
@php
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp

<section class="brand-page-card settings-page-card settings-redesign">
    <div class="settings-hero">
        <div class="settings-identity">
            <span class="settings-avatar">
                {{ $initials ?: 'A' }}
            </span>

            <div>
                <span class="settings-kicker">Signed in as</span>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>

                <div class="settings-badges">
                    <span>
                        <i class="fa-solid fa-user-shield"></i>
                        {{ $user->roleLabel() }}
                    </span>

                    @if ($user->is_root_admin)
                        <span>
                            <i class="fa-solid fa-lock"></i>
                            Root Admin
                        </span>
                    @endif

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        {{ $user->is_active ? 'Active Login' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="settings-hero-meta">
            <span>User ID</span>
            <strong>#{{ $user->id }}</strong>
            <small>Joined {{ $user->created_at?->format('d M Y') }}</small>
        </div>
    </div>

    @if (session('settings_status'))
        <div class="settings-status-message">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('settings_status') }}
        </div>
    @endif

    <div class="settings-metric-grid">
        <article>
            <span>Handled Orders</span>
            <strong>{{ number_format($activity['handled_orders']) }}</strong>
            <small>Sent to Steadfast by this account</small>
        </article>

        <article>
            <span>Completed Orders</span>
            <strong>{{ number_format($activity['delivered_orders']) }}</strong>
            <small>Delivered from handled orders</small>
        </article>

        <article>
            <span>Products Handled</span>
            <strong>{{ number_format($activity['handled_products']) }}</strong>
            <small>Total item quantity in handled orders</small>
        </article>

        <article>
            <span>Handled Revenue</span>
            <strong>৳{{ number_format($activity['handled_revenue'], 2) }}</strong>
            <small>Delivered revenue handled by this account</small>
        </article>
    </div>

    <div class="settings-layout">
        <div class="settings-main-column">
            <article class="settings-panel">
                <div class="settings-panel-head">
                    <span>
                        <i class="fa-solid fa-user-pen"></i>
                    </span>

                    <div>
                        <h3>Profile</h3>
                        <p>Update the name and email shown across the admin panel.</p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.settings.profile') }}"
                    class="settings-form"
                >
                    @csrf
                    @method('PATCH')

                    <div class="brand-form-grid">
                        <div class="brand-form-field">
                            <label for="settingsName">
                                Name <span>*</span>
                            </label>

                            <input
                                id="settingsName"
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                autocomplete="name"
                                required
                            >

                            @error('name', 'settingsProfile')
                                <small class="brand-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="brand-form-field">
                            <label for="settingsEmail">
                                Email <span>*</span>
                            </label>

                            <input
                                id="settingsEmail"
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                autocomplete="email"
                                required
                            >

                            @error('email', 'settingsProfile')
                                <small class="brand-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="submit" class="brand-primary-button">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Profile
                        </button>
                    </div>
                </form>
            </article>

            <article class="settings-panel">
                <div class="settings-panel-head">
                    <span>
                        <i class="fa-solid fa-key"></i>
                    </span>

                    <div>
                        <h3>Password & Security</h3>
                        <p>Change your own password from here only.</p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.settings.password') }}"
                    class="settings-form"
                >
                    @csrf
                    @method('PUT')

                    <div class="brand-form-grid">
                        <div class="brand-form-field">
                            <label for="settingsCurrentPassword">
                                Current Password <span>*</span>
                            </label>

                            <input
                                id="settingsCurrentPassword"
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                required
                            >

                            @error('current_password', 'settingsPassword')
                                <small class="brand-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="brand-form-field">
                            <label for="settingsPassword">
                                New Password <span>*</span>
                            </label>

                            <input
                                id="settingsPassword"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                required
                            >

                            @error('password', 'settingsPassword')
                                <small class="brand-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="brand-form-field">
                            <label for="settingsPasswordConfirmation">
                                Confirm Password <span>*</span>
                            </label>

                            <input
                                id="settingsPasswordConfirmation"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                            >

                            @error('password_confirmation', 'settingsPassword')
                                <small class="brand-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="submit" class="brand-primary-button">
                            <i class="fa-solid fa-shield-halved"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </article>
        </div>

        <aside class="settings-side-column">
            <article class="settings-panel">
                <div class="settings-panel-head">
                    <span>
                        <i class="fa-solid fa-id-badge"></i>
                    </span>

                    <div>
                        <h3>Account Details</h3>
                        <p>Current admin identity and access state.</p>
                    </div>
                </div>

                <div class="settings-info-list">
                    <div>
                        <span>Email Verified</span>
                        <strong>{{ $user->email_verified_at ? 'Yes' : 'No' }}</strong>
                    </div>

                    <div>
                        <span>Role</span>
                        <strong>{{ $user->roleLabel() }}</strong>
                    </div>

                    <div>
                        <span>Root Access</span>
                        <strong>{{ $user->is_root_admin ? 'Yes' : 'No' }}</strong>
                    </div>

                    <div>
                        <span>Permissions</span>
                        <strong>{{ $permissionsCount }}</strong>
                    </div>

                    <div>
                        <span>Permission Updates</span>
                        <strong>{{ number_format($activity['permission_updates']) }}</strong>
                    </div>

                    <div>
                        <span>Last Updated</span>
                        <strong>{{ $user->updated_at?->diffForHumans() }}</strong>
                    </div>
                </div>
            </article>

            <article class="settings-panel">
                <div class="settings-panel-head">
                    <span>
                        <i class="fa-solid fa-server"></i>
                    </span>

                    <div>
                        <h3>System Snapshot</h3>
                        <p>Useful totals for daily admin work.</p>
                    </div>
                </div>

                <div class="settings-info-list">
                    <div>
                        <span>Brands</span>
                        <strong>{{ number_format($system['total_brands']) }}</strong>
                    </div>

                    <div>
                        <span>Categories</span>
                        <strong>{{ number_format($system['total_categories']) }}</strong>
                    </div>

                    <div>
                        <span>Coupons</span>
                        <strong>{{ number_format($system['total_coupons']) }}</strong>
                    </div>

                    <div>
                        <span>Timezone</span>
                        <strong>{{ $system['timezone'] }}</strong>
                    </div>

                    <div>
                        <span>Environment</span>
                        <strong>{{ ucfirst($system['environment']) }}</strong>
                    </div>
                </div>
            </article>
        </aside>
    </div>
</section>
@endsection
