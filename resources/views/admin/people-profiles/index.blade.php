@extends('admin.layouts.app')

@section('title', 'HR & Payroll | Grey Stone Admin')
@section('page-title', 'HR & Payroll')
@section('page-subtitle', 'Manage employees, admin access, referral access, payroll and notes')

@section('content')
@php
    $canEditInfo = auth()->user()?->hasAdminPermission('info.editing');
    $peopleHelpTexts = [
        'total_profiles' => 'System-e total employee/profile count ekhane dekhay.',
        'active_profiles' => 'Active employee/profile count. Inactive hole table-e thakbe but normally kaj korbe na.',
        'roles' => 'Admin, marketer, developer er moto role/type count ekhane dekhay.',
        'main_authority' => 'Business-er main authority/VP type high-trust profile ready ache kina tar quick reminder.',
        'search' => 'Name, title, story ba note diye employee quickly find kora jay.',
        'type_filter' => 'Specific role/type select kore table filter kora jay.',
        'person' => 'Employee/person-er name, designation and contact summary.',
        'profile_type' => 'Ei person kon role/type-e kaj korbe.',
        'access' => 'Admin access on/off, role and permissions summary.',
        'payroll' => 'Salary, payment type, commission/gift related summary.',
        'referral' => 'Ei person customer refer korte parbe kina and referral code/commission.',
        'linked' => 'Member/referrer/admin account-er sathe related link summary.',
        'story_tax' => 'Payment, contribution, receipt/tax explanation rakhar note.',
        'status' => 'Profile active kina. Inactive hole operator normally use korbe na.',
        'role_setup' => 'Role add/edit kore default permissions set kora jay.',
        'role_name' => 'Role-er display name, jemon Super Admin, Marketer, Developer.',
        'sort' => 'Role list-e order control korar number.',
        'default_permissions' => 'Ei role pawa person-ra default kon permissions pabe.',
        'select_all' => 'Ei role-er shob permission ek click-e select/unselect.',
        'saved_roles' => 'Already created role-gula ekhane edit/delete kora jay.',
        'photo' => 'Employee/profile image upload korar field.',
        'name' => 'Employee/person-er full name.',
        'designation' => 'Business title/designation, jemon Developer, Vice President.',
        'email' => 'Login/contact email.',
        'phone' => 'Primary phone number.',
        'optional_phone' => 'Extra backup phone number.',
        'emergency_contact' => 'Emergency contact person/phone.',
        'nid_document' => 'NID/document reference note.',
        'joining_date' => 'Ei person kobe theke kaj shuru koreche.',
        'address' => 'Personal/contact address.',
        'custom_permissions' => 'Role default permission-er baire individual permission customize korte eta on korun.',
        'password' => 'New admin login create/update korle password.',
        'root_admin' => 'Root admin full control pabe. Very sensitive.',
        'root_passcode' => 'Root admin korte security passcode required.',
        'referral_enabled' => 'On korle ei person referral code diye customer refer korte parbe.',
        'linked_member' => 'Existing member-er sathe profile connect korte hole select korun.',
        'linked_referrer' => 'Existing referrer code select ba referral on korle auto create hobe.',
        'payment_type' => 'Payment salary, commission, mixed etc. kon type seta.',
        'salary_type' => 'Monthly/contract/commission basis salary rule.',
        'salary_amount' => 'Salary amount taka.',
        'commission_rate' => 'Referral/commission percentage.',
        'gift_balance' => 'Extra gift/bonus/help amount track korar balance.',
        'role_notes' => 'Duty, responsibility and operator instruction note.',
        'story' => 'Keno payment/commission/gift pabe tar story/contribution.',
        'tax_note' => 'Receipt, tax or payment explanation note.',
    ];
    $infoHelp = function (string $key) use ($peopleHelpTexts, $canEditInfo) {
        static $helpCounts = [];
        $helpCounts[$key] = ($helpCounts[$key] ?? 0) + 1;
        $id = 'peopleHelp'.str_replace(' ', '', ucwords(str_replace('_', ' ', $key))).$helpCounts[$key];
        $editButton = $canEditInfo ? '<button type="button" class="investment-help-edit-button">Edit</button>' : '';

        return new \Illuminate\Support\HtmlString(
            '<button type="button" class="investment-info-button" onclick="const box=document.getElementById(\''.$id.'\'); if(box){ box.hidden = !box.hidden; }" aria-label="Help">i</button><small id="'.$id.'" class="investment-help-text" data-investment-editable-help="peopleHelpText_'.$key.'" hidden><span data-investment-help-copy>'.e($peopleHelpTexts[$key] ?? '').'</span>'.$editButton.'</small>'
        );
    };
@endphp
<div id="peopleProfileCrudPage"
    data-update-url="{{ route('admin.people-profiles.update', '__ID__') }}"
    data-role-store-url="{{ route('admin.people-profile-types.store') }}"
    data-role-update-url="{{ route('admin.people-profile-types.update', '__ID__') }}">
    <section class="brand-page-card people-profile-page">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="brand-page-header">
            <div>
                <h2>Employees Table</h2>
                <p>Manage employee profile, admin access, referral access, payroll and notes in one place.</p>
            </div>

            <div class="people-header-actions">
                <button type="button" class="brand-secondary-button" id="openPeopleTypeModal">
                    Permissions
                </button>
                <button type="button" class="brand-primary-button" id="openAddPeopleProfileModal">
                    <span>＋</span>
                    Add Employee
                </button>
            </div>
        </div>

        <div class="customer-stat-grid">
            <article><span>Total Profiles</span><strong>{{ number_format($totalProfiles) }}</strong></article>
            <article><span>Active Profiles</span><strong>{{ number_format($activeProfiles) }}</strong></article>
            <article><span>Roles</span><strong>{{ number_format($profileTypes->where('is_active', true)->count()) }}</strong></article>
            <article><span>Main Authority</span><strong>VP Ready</strong></article>
        </div>

        <form method="GET" class="admin-ajax-search">
            <div class="admin-search-grid">
                <div class="admin-search-field">
                    <label>Search</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, title or story">
                </div>

                <div class="admin-search-field">
                    <label>Type</label>
                    <select name="type">
                        <option value="">All types</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="brand-primary-button" type="submit">Filter</button>
                <a class="brand-secondary-button" href="{{ route('admin.people-profiles.index') }}">Reset</a>
            </div>
        </form>

        <div class="brand-table-wrapper">
            <table class="brand-table people-profile-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Person</th>
                        <th>Type</th>
                        <th>Access {!! $infoHelp('access') !!}</th>
                        <th>Payroll {!! $infoHelp('payroll') !!}</th>
                        <th>Referral {!! $infoHelp('referral') !!}</th>
                        <th>Linked</th>
                        <th>Story / Tax Note {!! $infoHelp('story_tax') !!}</th>
                        <th>Status</th>
                        <th class="brand-actions-heading">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        @php
                            $payload = [
                                'id' => $profile->id,
                                'profile_type' => $profile->profile_type,
                                'name' => $profile->name,
                                'title' => $profile->title,
                                'email' => $profile->email,
                                'phone' => $profile->phone,
                                'optional_phone' => $profile->optional_phone,
                                'address' => $profile->address,
                                'emergency_contact' => $profile->emergency_contact,
                                'nid_or_document' => $profile->nid_or_document,
                                'joining_date' => $profile->joining_date?->format('Y-m-d'),
                                'member_id' => $profile->member_id,
                                'referrer_id' => $profile->referrer_id,
                                'user_id' => $profile->user_id,
                                'payment_type' => $profile->payment_type,
                                'salary_amount' => $profile->salary_amount,
                                'salary_type' => $profile->salary_type,
                                'commission_rate' => $profile->commission_rate,
                                'gift_balance' => $profile->gift_balance,
                                'story' => $profile->story,
                                'tax_note' => $profile->tax_note,
                                'admin_enabled' => $profile->admin_enabled,
                                'admin_role' => $profile->admin_role,
                                'admin_permissions' => $profile->admin_permissions ?? [],
                                'permission_override_enabled' => $profile->permission_override_enabled,
                                'referral_enabled' => $profile->referral_enabled,
                                'role_notes' => $profile->role_notes,
                                'sort_order' => $profile->sort_order,
                                'is_active' => $profile->is_active,
                            ];
                        @endphp
                        <tr>
                            <td><span class="brand-id">#{{ $profile->id }}</span></td>
                            <td>
                                <div class="brand-name-cell">
                                    <div class="brand-table-logo">
                                        @if($profile->photo_path)
                                            <img src="{{ asset($profile->photo_path) }}" alt="{{ $profile->name }}">
                                        @else
                                            <span>{{ mb_strtoupper(mb_substr($profile->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $profile->name }}</strong>
                                        <small>{{ $profile->title ?: 'No title set' }}</small>
                                        <small>{{ $profile->phone ?: $profile->email ?: 'No contact added' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="people-profile-type">{{ $types[$profile->profile_type] ?? $profile->typeLabel() }}</span></td>
                            <td>
                                @if($profile->admin_enabled && $profile->adminUser)
                                    <strong>{{ $profile->adminUser->is_root_admin ? 'Root Admin' : $profile->adminUser->roleLabel() }}</strong>
                                    <small>{{ $profile->adminUser->is_root_admin ? 'Full access' : count($profile->adminUser->permissions ?? []).' permissions' }}</small>
                                @elseif($profile->admin_enabled)
                                    <strong>Admin On</strong><small>Not linked yet</small>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>
                                <strong>৳{{ number_format((float) $profile->salary_amount, 2) }}</strong>
                                <small>{{ $salaryTypes[$profile->salary_type] ?? ucfirst($profile->salary_type) }} · {{ $profile->paymentTypeLabel() }}</small>
                                <small>Gift ৳{{ number_format((float) $profile->gift_balance, 2) }}</small>
                            </td>
                            <td>
                                @if($profile->referral_enabled)
                                    <strong>Enabled</strong>
                                    <small>{{ $profile->referrer?->code ?: 'Code will auto create' }}</small>
                                    <small>{{ $profile->commission_rate !== null ? number_format((float) $profile->commission_rate, 2).'%' : '1.00%' }}</small>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>
                                @if($profile->member)
                                    <strong>{{ $profile->member->name }}</strong><small>Member</small>
                                @elseif($profile->referrer)
                                    <strong>{{ $profile->referrer->name }}</strong><small>{{ $profile->referrer->code }}</small>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ Str::limit($profile->story ?: 'No story added', 48) }}</strong>
                                <small>{{ Str::limit($profile->tax_note ?: 'No tax note added', 48) }}</small>
                            </td>
                            <td><span class="brand-status-badge {{ $profile->is_active ? 'active' : 'inactive' }}">{{ $profile->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="brand-table-actions">
                                    <button type="button" class="brand-action-button edit editPeopleProfileButton" data-profile-id="{{ $profile->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.people-profiles.destroy', $profile) }}" onsubmit="return confirm('Delete {{ $profile->name }} profile?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="brand-action-button delete">Delete</button>
                                    </form>
                                </div>
                                <script type="application/json" id="peopleProfileData{{ $profile->id }}">{!! json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="brand-empty-state"><strong>No people profiles found</strong><span>Add your first profile.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $profiles->links() }}
    </section>

    <div class="brand-modal" id="peopleTypeModal" aria-hidden="true">
        <div class="brand-modal-backdrop" data-close-modal="peopleTypeModal"></div>
        <div class="brand-modal-dialog brand-modal-large">
            <div class="brand-modal-header">
                <div>
                    <h3>Permissions</h3>
                    <p>Set default permissions for each role. Individual employee override stays inside Edit.</p>
                </div>
                <button type="button" class="brand-modal-close" data-close-modal="peopleTypeModal">×</button>
            </div>

            <div class="brand-modal-body">
                <div class="people-type-panel">
                    <form method="POST" action="{{ route('admin.people-profile-types.store') }}" id="peopleRoleBuilderForm">
                        @csrf
                        <input type="hidden" name="_method" id="peopleRoleBuilderMethod" value="" disabled>

                        <div class="people-role-builder">
                            <aside class="people-role-sidebar">
                                <span class="people-role-kicker">Role setup</span>
                                <h4>Add or edit one role {!! $infoHelp('role_setup') !!}</h4>
                                <p>Select a role, then set its default permissions on the right.</p>

                                <label>
                                    Role
                                    <select id="peopleRoleSelector">
                                        <option value="">＋ Add new role</option>
                                        @foreach($profileTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label>
                                    Role name
                                    <input type="text" name="name" id="peopleRoleName" placeholder="Example: Brand Ambassador" required>
                                </label>

                                <div class="people-role-mini-grid">
                                    <label>
                                        Sort
                                        <input type="number" name="sort_order" id="peopleRoleSortOrder" value="0" min="0" max="9999">
                                    </label>
                                    <label class="people-inline-check people-role-active-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" id="peopleRoleIsActive" value="1" checked>
                                        Active
                                    </label>
                                </div>

                                <div class="people-role-actions">
                                    <button type="button" class="brand-secondary-button" id="resetPeopleRoleBuilder">New Role</button>
                                    <button type="submit" class="brand-primary-button" id="savePeopleRoleButton">Add Role</button>
                                </div>
                            </aside>

                            <section class="people-role-permissions">
                                <div class="people-role-permission-head">
                                    <div>
                                        <span class="people-role-kicker">Default access</span>
                                        <h4>Permissions {!! $infoHelp('default_permissions') !!}</h4>
                                    </div>
                                    <label class="people-inline-check">
                                        <input type="checkbox" data-role-select-all>
                                        Select all
                                    </label>
                                </div>

                                <div class="people-role-module-grid">
                                    @foreach($modules as $module => $meta)
                                        <article class="people-role-module-card">
                                            <div class="people-role-module-title">
                                                <strong>{{ $meta['label'] }}</strong>
                                                <label class="people-inline-check">
                                                    <input type="checkbox" data-role-module-select="{{ $module }}">
                                                    All
                                                </label>
                                            </div>
                                            <div class="people-role-options">
                                                @foreach($actions as $action => $label)
                                                    @php
                                                        $permissionKey = $module.'.'.$action;
                                                    @endphp
                                                    <label>
                                                        <input type="checkbox" name="default_permissions[]" value="{{ $permissionKey }}" data-role-permission data-role-module="{{ $module }}">
                                                        {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </article>
                                    @endforeach

                                    <article class="people-role-module-card people-role-sensitive-card">
                                        <div class="people-role-module-title">
                                            <strong>Special permissions {!! $infoHelp('default_permissions') !!}</strong>
                                            <label class="people-inline-check">
                                                <input type="checkbox" data-role-module-select="sensitive">
                                                All
                                            </label>
                                        </div>
                                        <div class="people-role-options">
                                            @foreach($sensitivePermissions as $permission => $label)
                                                <label>
                                                    <input type="checkbox" name="default_permissions[]" value="{{ $permission }}" data-role-permission data-role-module="sensitive">
                                                    {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </article>
                                </div>
                            </section>
                        </div>
                    </form>

                    <div class="people-role-table-card">
                        <div class="people-role-table-head">
                            <div>
                                <h4>Saved roles</h4>
                                <p>Edit any role from here. System roles can be updated but not deleted.</p>
                            </div>
                        </div>

                        <div class="brand-table-wrapper">
                            <table class="brand-table people-role-table">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Permissions {!! $infoHelp('default_permissions') !!}</th>
                                        <th>Status</th>
                                        <th class="brand-actions-heading">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profileTypes as $type)
                                        @php
                                            $typePermissions = $type->default_permissions ?? [];
                                            $typePayload = [
                                                'id' => $type->id,
                                                'name' => $type->name,
                                                'slug' => $type->slug,
                                                'sort_order' => $type->sort_order,
                                                'is_active' => $type->is_active,
                                                'is_system' => $type->is_system,
                                                'default_permissions' => $typePermissions,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $type->name }}</strong>
                                                <small>{{ $type->slug }}{{ $type->is_system ? ' · system role' : '' }}</small>
                                            </td>
                                            <td>
                                                <span class="people-role-count">{{ count($typePermissions) }} selected</span>
                                            </td>
                                            <td>
                                                <span class="brand-status-badge {{ $type->is_active ? 'active' : 'inactive' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td>
                                                <div class="brand-table-actions">
                                                    <button type="button" class="brand-action-button edit editPeopleRoleButton" data-role-id="{{ $type->id }}">Edit</button>
                                                    @unless($type->is_system)
                                                        <form method="POST" action="{{ route('admin.people-profile-types.destroy', $type) }}" onsubmit="return confirm('Delete {{ $type->name }} role?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="brand-action-button delete">Delete</button>
                                                        </form>
                                                    @endunless
                                                </div>
                                                <script type="application/json" id="peopleRoleData{{ $type->id }}">{!! json_encode($typePayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach(['add' => 'Add New Profile', 'edit' => 'Edit Profile'] as $mode => $title)
        <div class="brand-modal" id="{{ $mode }}PeopleProfileModal" aria-hidden="true">
            <div class="brand-modal-backdrop" data-close-modal="{{ $mode }}PeopleProfileModal"></div>
            <div class="brand-modal-dialog brand-modal-large">
                <div class="brand-modal-header">
                    <div>
                        <h3>{{ $title }}</h3>
                    <p>{{ $mode === 'add' ? 'Create a new employee profile.' : 'Update employee profile, access and payroll.' }}</p>
                    </div>
                    <button type="button" class="brand-modal-close" data-close-modal="{{ $mode }}PeopleProfileModal">×</button>
                </div>

                <form method="POST" enctype="multipart/form-data" id="{{ $mode }}PeopleProfileForm" action="{{ $mode === 'add' ? route('admin.people-profiles.store') : route('admin.people-profiles.update', $profiles->first() ?? 1) }}">
                    @csrf
                    @if($mode === 'edit')
                        @method('PUT')
                    @endif
                    <div class="brand-modal-body">
                        @include('admin.people-profiles.partials.form-fields', ['profile' => null, 'formPrefix' => $mode.'_people_profile'])
                    </div>
                    <div class="brand-modal-footer">
                        <button type="button" class="brand-secondary-button" data-close-modal="{{ $mode }}PeopleProfileModal">Cancel</button>
                        <button type="submit" class="brand-primary-button">{{ $mode === 'add' ? 'Add Employee' : 'Save Changes' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
