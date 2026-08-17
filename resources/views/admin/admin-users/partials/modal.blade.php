<div
    class="brand-modal"
    id="{{ $modalId }}"
    aria-hidden="true"
>
    <div
        class="brand-modal-backdrop"
        data-close-admin-modal="{{ $modalId }}"
    ></div>

    <div class="brand-modal-dialog brand-modal-large">
        <div class="brand-modal-header">
            <div>
                <h3>{{ $title }}</h3>
                <p>{{ $subtitle }}</p>
            </div>

            <button
                type="button"
                class="brand-modal-close"
                data-close-admin-modal="{{ $modalId }}"
            >
                ×
            </button>
        </div>

        <form
            method="POST"
            action="{{ $action }}"
            id="{{ $formId }}"
            data-admin-user-form
        >
            @csrf

            @if ($method !== 'POST')
                @method($method)
            @endif

            <input
                type="hidden"
                name="root_admin_passcode"
                value=""
                data-root-passcode-input
            >

            <div class="brand-modal-body">
                <div
                    class="admin-permission-lock-notice"
                    data-admin-lock-notice
                    hidden
                ></div>

                <section class="brand-form-section">
                    <div class="brand-form-section-title">
                        <h4>Account</h4>
                        <p>
                            {{ $method === 'POST'
                                ? 'Name, email, login password and role.'
                                : 'Name, email and account role. Password changes live in Settings.' }}
                        </p>
                    </div>

                    <div class="brand-form-grid">
                        <div class="brand-form-field">
                            <label>Name *</label>
                            <input
                                type="text"
                                name="name"
                                maxlength="120"
                                required
                            >
                        </div>

                        <div class="brand-form-field">
                            <label>Email *</label>
                            <input
                                type="email"
                                name="email"
                                maxlength="160"
                                required
                            >
                        </div>

                        <div class="brand-form-field">
                            <label>Personal Phone *</label>
                            <input
                                type="tel"
                                name="personal_phone"
                                maxlength="11"
                                pattern="01[3-9][0-9]{8}"
                                placeholder="01XXXXXXXXX"
                                required
                            >
                        </div>

                        <div class="brand-form-field">
                            <label>Optional Phone</label>
                            <input
                                type="tel"
                                name="optional_phone"
                                maxlength="11"
                                pattern="01[3-9][0-9]{8}"
                                placeholder="01XXXXXXXXX"
                            >
                        </div>

                        @if ($method === 'POST')
                            <div class="brand-form-field">
                                <label>Password *</label>
                                <input
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <div class="brand-form-field">
                                <label>Confirm Password *</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>
                        @endif

                        <div class="brand-form-field">
                            <label>Role *</label>
                            <select name="role" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>

                        <div class="brand-form-field">
                            <label class="brand-toggle-label">
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    checked
                                >

                                <span class="brand-toggle"></span>
                                <span>Active Login</span>
                            </label>
                        </div>

                        @if (auth()->user()->is_root_admin)
                            <div class="brand-form-field">
                                <label class="brand-toggle-label">
                                    <input
                                        type="hidden"
                                        name="is_root_admin"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="is_root_admin"
                                        value="1"
                                        data-admin-root-toggle
                                    >

                                    <span class="brand-toggle"></span>
                                    <span>Root Admin</span>
                                </label>

                                <small class="brand-field-help">
                                    Root admins have permanent full system access.
                                </small>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="brand-form-section">
                    <div class="brand-form-section-title">
                        <h4>Module Permissions</h4>
                        <p>Select exactly what this admin can view or change.</p>
                    </div>

                    <label class="admin-full-access-toggle">
                        <input
                            type="checkbox"
                            data-admin-full-access
                        >

                        <span>Give Full System Access</span>
                    </label>

                    <div class="admin-permission-grid">
                        @foreach ($modules as $module => $meta)
                            <div class="admin-permission-card">
                                <div class="admin-permission-head">
                                    <span>
                                        <i class="fa-solid {{ $meta['icon'] }}"></i>
                                        <strong>{{ $meta['label'] }}</strong>
                                    </span>

                                    <button
                                        type="button"
                                        data-admin-module-select-all="{{ $module }}"
                                    >
                                        Select All
                                    </button>
                                </div>

                                <div class="admin-permission-options">
                                    @foreach ($actions as $action => $label)
                                        <label>
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $module }}.{{ $action }}"
                                            >
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="brand-form-section">
                    <div class="brand-form-section-title">
                        <h4>Critical Permissions</h4>
                        <p>Give these only to trusted super admins.</p>
                    </div>

                    <div class="admin-sensitive-permissions">
                        @foreach ($sensitivePermissions as $permission => $label)
                            <label>
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission }}"
                                >
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="brand-modal-footer">
                <button
                    type="button"
                    class="brand-secondary-button"
                    data-close-admin-modal="{{ $modalId }}"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="brand-primary-button"
                >
                    {{ $submitText }}
                </button>
            </div>
        </form>
    </div>
</div>
