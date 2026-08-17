@extends('admin.layouts.app')

@section('title', 'Admins | Grey Stone Admin')
@section('page-title', 'Admin Users')
@section('page-subtitle', 'Manage secure admin access and permissions')

@section('content')
@php
    $currentAdmin = auth()->user();
    $canManageAdmins = $currentAdmin->hasAdminPermission('admin_users.manage_admins');
@endphp

<section
    class="brand-page-card admin-users-page"
    id="adminUsersPage"
    data-current-root="{{ $currentAdmin->is_root_admin ? '1' : '0' }}"
    data-root-passcode-verify-url="{{ route('admin.admin-users.verify-root-passcode') }}"
>
    <div class="brand-page-header">
        <div>
            <h2>Admins Table</h2>

            <p>
                Add admins, assign permissions and protect super admin access.
            </p>
        </div>

        @if (
            $currentAdmin->hasAdminPermission('admin_users.create')
            && $canManageAdmins
        )
            <button
                type="button"
                class="brand-primary-button"
                data-open-admin-modal="addAdminModal"
            >
                <span>＋</span>
                Add Admin
            </button>
        @endif
    </div>

    @if (session('status'))
        <div class="admin-inline-alert success">
            {{ session('status') }}
        </div>
    @endif

@if ($errors->any())
        <div class="admin-inline-alert error">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        class="admin-ajax-search"
        method="GET"
        action="{{ route('admin.admin-users.index') }}"
    >
        <div class="admin-search-grid">
            <div class="admin-search-field">
                <label>Search</label>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name, email or phone"
                >
            </div>

            <div class="admin-search-field">
                <label>Role</label>
                <select name="role">
                    <option value="">All Roles</option>
                    <option
                        value="super_admin"
                        @selected(request('role') === 'super_admin')
                    >
                        Super Admin
                    </option>
                    <option
                        value="admin"
                        @selected(request('role') === 'admin')
                    >
                        Admin
                    </option>
                </select>
            </div>

            <div class="admin-search-field">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option
                        value="active"
                        @selected(request('status') === 'active')
                    >
                        Active
                    </option>
                    <option
                        value="inactive"
                        @selected(request('status') === 'inactive')
                    >
                        Inactive
                    </option>
                </select>
            </div>

            <button type="submit" class="brand-secondary-button">
                Filter
            </button>
        </div>
    </form>

    <div class="brand-table-wrapper">
        <table class="brand-table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th class="brand-actions-heading">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $admin)
                    @php
                        $permissions = $admin->permissions ?? [];
                    @endphp

                    <tr>
                        <td>
                            <div class="brand-name-cell">
                                <span class="admin-avatar">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </span>

                                <div>
                                    <strong>{{ $admin->name }}</strong>
                                    <small>{{ $admin->email }}</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <strong>{{ $admin->personal_phone ?: '-' }}</strong>
                            @if ($admin->optional_phone)
                                <small class="order-table-muted">
                                    Optional: {{ $admin->optional_phone }}
                                </small>
                            @endif
                        </td>

                        <td>
                            <span class="admin-role-badge {{ $admin->role }}">
                                {{ $admin->roleLabel() }}
                            </span>

                            @if ($admin->is_root_admin)
                                <span class="admin-root-badge">
                                    Root
                                </span>
                            @endif
                        </td>

                        <td>
                            <span class="brand-status-badge {{ $admin->is_active ? 'active' : 'inactive' }}">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td>
                            @if ($admin->is_root_admin)
                                Full system access
                            @else
                                {{ count($permissions) }} permissions
                            @endif
                        </td>

                        <td>
                            {{ $admin->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <div class="brand-table-actions">
                                @if (
                                    $currentAdmin->hasAdminPermission('admin_users.update')
                                    && (
                                        $canManageAdmins
                                        || $admin->id === $currentAdmin->id
                                    )
                                    && (
                                        ! $admin->is_root_admin
                                        || $admin->id === $currentAdmin->id
                                    )
                                )
                                    <button
                                        type="button"
                                        class="brand-action-button edit editAdminButton"
                                        data-id="{{ $admin->id }}"
                                        data-name="{{ $admin->name }}"
                                        data-email="{{ $admin->email }}"
                                        data-personal-phone="{{ $admin->personal_phone }}"
                                        data-optional-phone="{{ $admin->optional_phone }}"
                                        data-role="{{ $admin->role }}"
                                        data-active="{{ $admin->is_active ? '1' : '0' }}"
                                        data-root="{{ $admin->is_root_admin ? '1' : '0' }}"
                                        data-self="{{ $admin->id === $currentAdmin->id ? '1' : '0' }}"
                                        data-lock-message="{{
                                            $admin->id === $currentAdmin->id && ! $admin->is_root_admin
                                                ? 'Your permissions are locked. Only another authorized super admin can change your access.'
                                                : (
                                                    $admin->permissions_updated_by
                                                        ? 'Permissions last changed by '.$admin->permissionUpdater?->name.' on '.$admin->permissions_updated_at?->format('d M Y h:i A').'.'
                                                        : ''
                                                )
                                        }}"
                                        data-permissions='@json($permissions)'
                                        data-update-url="{{ route('admin.admin-users.update', $admin) }}"
                                    >
                                        Edit
                                    </button>
                                @endif

                                @if (
                                    $currentAdmin->hasAdminPermission('admin_users.delete')
                                    && $canManageAdmins
                                    && (
                                        (
                                            ! $admin->is_root_admin
                                            && $admin->id !== $currentAdmin->id
                                        )
                                        || (
                                            $admin->is_root_admin
                                            && $admin->id === $currentAdmin->id
                                        )
                                    )
                                )
                                    <button
                                        type="button"
                                        class="brand-action-button delete deleteAdminButton"
                                        data-name="{{ $admin->name }}"
                                        data-root="{{ $admin->is_root_admin ? '1' : '0' }}"
                                        data-self="{{ $admin->id === $currentAdmin->id ? '1' : '0' }}"
                                        data-delete-url="{{ route('admin.admin-users.destroy', $admin) }}"
                                    >
                                        Delete
                                    </button>
                                @endif

                                @if ($admin->is_root_admin && $admin->id !== $currentAdmin->id)
                                    <span class="admin-root-locked-label">
                                        Root locked
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="brand-empty-state">
                                <strong>No admins found</strong>
                                <span>Add an admin to assign secure access.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@include('admin.admin-users.partials.modal', [
    'modalId' => 'addAdminModal',
    'formId' => 'addAdminForm',
    'title' => 'Add Admin',
    'subtitle' => 'Create a secure login account.',
    'action' => route('admin.admin-users.store'),
    'method' => 'POST',
    'submitText' => 'Add Admin',
    'modules' => $modules,
    'actions' => $actions,
    'sensitivePermissions' => $sensitivePermissions,
])

@include('admin.admin-users.partials.modal', [
    'modalId' => 'editAdminModal',
    'formId' => 'editAdminForm',
    'title' => 'Edit Admin',
    'subtitle' => 'Update account and permissions.',
    'action' => '#',
    'method' => 'PUT',
    'submitText' => 'Update Admin',
    'modules' => $modules,
    'actions' => $actions,
    'sensitivePermissions' => $sensitivePermissions,
])

<div
    class="brand-modal"
    id="deleteAdminModal"
    aria-hidden="true"
>
    <div
        class="brand-modal-backdrop"
        data-close-admin-modal="deleteAdminModal"
    ></div>

    <div class="brand-modal-dialog brand-delete-dialog">
        <div class="brand-delete-icon">
            !
        </div>

        <h3>Delete Admin?</h3>

        <p id="deleteAdminMessage">
            This admin account will lose dashboard access.
        </p>

        <span>
            This action cannot be undone.
        </span>

        <form
            method="POST"
            action="#"
            id="deleteAdminForm"
        >
            @csrf
            @method('DELETE')

            <input
                type="hidden"
                name="root_admin_passcode"
                value=""
                data-delete-root-passcode-input
            >

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-admin-modal="deleteAdminModal"
                >
                    No, Cancel
                </button>

                <button
                    type="submit"
                    class="brand-danger-button"
                >
                    Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>

@if ($currentAdmin->is_root_admin)
    <div
        class="brand-modal"
        id="rootPasscodeModal"
        aria-hidden="true"
    >
        <div
            class="brand-modal-backdrop"
            data-close-root-passcode-modal
        ></div>

        <div class="brand-modal-dialog brand-delete-dialog">
            <div class="brand-delete-icon">
                !
            </div>

            <h3 id="rootPasscodeTitle">Root Admin Passcode</h3>

            <p id="rootPasscodeDescription">
                Enter the root passcode before giving permanent root access.
            </p>

            <input
                type="password"
                class="admin-root-passcode-input"
                id="rootPasscodeInput"
                placeholder="Enter passcode"
                autocomplete="off"
            >

            <span id="rootPasscodeError" hidden>
                Passcode is required.
            </span>

            <div class="brand-delete-actions">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-root-passcode-modal
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="brand-danger-button"
                    id="confirmRootPasscodeButton"
                >
                    OK
                </button>
            </div>
        </div>
    </div>
@endif
@endsection
