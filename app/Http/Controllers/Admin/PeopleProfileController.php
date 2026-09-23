<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PeopleProfile;
use App\Models\PeopleProfileType;
use App\Models\Referrer;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class PeopleProfileController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdminProfiles();

        $types = PeopleProfileType::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $typeOptions = $types
            ->where('is_active', true)
            ->pluck('name', 'slug')
            ->all();

        $profiles = PeopleProfile::query()
            ->with(['member', 'referrer', 'adminUser'])
            ->when($request->filled('type'), fn ($query) => $query->where('profile_type', $request->string('type')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));

                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('story', 'like', "%{$search}%"));
            })
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.people-profiles.index', [
            'profiles' => $profiles,
            'types' => $typeOptions,
            'profileTypes' => $types,
            'paymentTypes' => PeopleProfile::PAYMENT_TYPES,
            'salaryTypes' => ['monthly' => 'Monthly', 'fixed' => 'Fixed', 'commission' => 'Commission', 'gift' => 'Gift / Help', 'none' => 'No Salary'],
            'members' => Member::orderBy('name')->get(['id', 'name', 'email']),
            'referrers' => Referrer::orderBy('name')->get(['id', 'name', 'code']),
            'adminUsers' => User::orderBy('name')->get(['id', 'name', 'email', 'role', 'is_root_admin']),
            'modules' => config('admin_permissions.modules', []),
            'actions' => config('admin_permissions.actions', []),
            'sensitivePermissions' => config('admin_permissions.sensitive', []),
            'totalProfiles' => PeopleProfile::count(),
            'activeProfiles' => PeopleProfile::where('is_active', true)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = PeopleProfile::create($this->validated($request));
        $this->syncPhoto($request, $profile);
        $this->syncReferral($profile);
        $this->syncAdminAccess($request, $profile);

        return back()->with('status', 'People profile created.');
    }

    public function update(Request $request, PeopleProfile $peopleProfile): RedirectResponse
    {
        $peopleProfile->update($this->validated($request));
        $this->syncPhoto($request, $peopleProfile);
        $this->syncReferral($peopleProfile);
        $this->syncAdminAccess($request, $peopleProfile);

        return back()->with('status', 'People profile updated.');
    }

    public function destroy(PeopleProfile $peopleProfile): RedirectResponse
    {
        $peopleProfile->delete();

        return back()->with('status', 'People profile deleted.');
    }

    public function storeType(Request $request): RedirectResponse
    {
        $data = $this->validatedType($request);
        $data['slug'] = $this->uniqueTypeSlug($data['name']);
        $data['is_system'] = false;

        PeopleProfileType::create($data);

        return back()->with('status', 'Role permissions created.');
    }

    public function updateType(Request $request, PeopleProfileType $peopleProfileType): RedirectResponse
    {
        $data = $this->validatedType($request);
        $peopleProfileType->update($data);

        return back()->with('status', 'Role permissions updated.');
    }

    public function destroyType(PeopleProfileType $peopleProfileType): RedirectResponse
    {
        if (PeopleProfile::where('profile_type', $peopleProfileType->slug)->exists()) {
            return back()->withErrors('This profile type is used by a profile. Change those profiles first.');
        }

        $peopleProfileType->delete();

        return back()->with('status', 'Profile type deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'profile_type' => ['required', Rule::exists('people_profile_types', 'slug')->where('is_active', true)],
            'name' => ['required', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:160'],
            'email' => [
                Rule::requiredIf(fn () => in_array($request->input('profile_type'), [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true)),
                'nullable',
                'email',
                'max:160',
            ],
            'phone' => [
                Rule::requiredIf(fn () => in_array($request->input('profile_type'), [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true)),
                'nullable',
                'string',
                'max:20',
            ],
            'optional_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:5000'],
            'emergency_contact' => ['nullable', 'string', 'max:160'],
            'nid_or_document' => ['nullable', 'string', 'max:160'],
            'joining_date' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'member_id' => ['nullable', 'exists:members,id'],
            'referrer_id' => ['nullable', 'exists:referrers,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'payment_type' => ['required', Rule::in(array_keys(PeopleProfile::PAYMENT_TYPES))],
            'salary_amount' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'salary_type' => ['required', Rule::in(['monthly', 'fixed', 'commission', 'gift', 'none'])],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gift_balance' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'story' => ['nullable', 'string', 'max:5000'],
            'tax_note' => ['nullable', 'string', 'max:5000'],
            'admin_enabled' => ['nullable', 'boolean'],
            'admin_role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])],
            'admin_permissions' => ['nullable', 'array'],
            'admin_permissions.*' => ['string'],
            'permission_override_enabled' => ['nullable', 'boolean'],
            'admin_password' => [
                Rule::requiredIf(fn () => in_array($request->input('profile_type'), [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true) && ! $request->filled('user_id')),
                'nullable',
                'confirmed',
                Password::defaults(),
            ],
            'is_root_admin' => ['nullable', 'boolean'],
            'root_admin_passcode' => ['nullable', 'string', 'max:120'],
            'referral_enabled' => ['nullable', 'boolean'],
            'role_notes' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['admin_enabled'] = in_array($data['profile_type'], [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN], true);
        $data['admin_role'] = $data['profile_type'] === User::ROLE_SUPER_ADMIN
            ? User::ROLE_SUPER_ADMIN
            : User::ROLE_ADMIN;
        $data['referral_enabled'] = $request->boolean('referral_enabled');
        $data['permission_override_enabled'] = $request->boolean('permission_override_enabled');
        $data['admin_permissions'] = $data['permission_override_enabled']
            ? User::sanitizePermissions($request->input('admin_permissions', []))
            : null;
        $data['salary_amount'] = $data['salary_amount'] ?? 0;
        $data['gift_balance'] = $data['gift_balance'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['photo'], $data['admin_password'], $data['admin_password_confirmation'], $data['root_admin_passcode'], $data['is_root_admin']);

        return $data;
    }

    private function validatedType(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'default_permissions' => ['nullable', 'array'],
            'default_permissions.*' => ['string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['default_permissions'] = User::sanitizePermissions($request->input('default_permissions', []));
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueTypeSlug(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'profile_type';
        $slug = $base;
        $counter = 2;

        while (PeopleProfileType::where('slug', $slug)->exists()) {
            $slug = $base.'_'.$counter++;
        }

        return $slug;
    }

    private function syncPhoto(Request $request, PeopleProfile $profile): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        $directory = public_path('images/people-profiles');
        File::ensureDirectoryExists($directory);

        $file = $request->file('photo');
        $name = Str::slug($profile->name ?: 'profile').'-'.$profile->id.'-'.time().'.'.$file->extension();
        $file->move($directory, $name);

        $profile->update(['photo_path' => 'images/people-profiles/'.$name]);
    }

    private function syncReferral(PeopleProfile $profile): void
    {
        if (! $profile->referral_enabled) {
            $profile->referrer?->update(['is_active' => false]);
            return;
        }

        $referrer = $profile->referrer ?: Referrer::create([
            'member_id' => $profile->member_id,
            'name' => $profile->name,
            'code' => $this->uniqueReferralCode($profile->name),
            'commission_rate' => $profile->commission_rate ?? 1,
            'gift_balance' => $profile->gift_balance ?? 0,
            'is_active' => true,
        ]);

        $referrer->update([
            'member_id' => $profile->member_id,
            'name' => $profile->name,
            'commission_rate' => $profile->commission_rate ?? 1,
            'gift_balance' => $profile->gift_balance ?? 0,
            'is_active' => true,
        ]);

        if (! $profile->referrer_id) {
            $profile->update(['referrer_id' => $referrer->id]);
        }
    }

    private function syncAdminAccess(Request $request, PeopleProfile $profile): void
    {
        if (! $profile->admin_enabled) {
            $profile->adminUser?->update(['is_active' => false]);
            return;
        }

        $this->authorizeAdminSync($request, $profile);

        if (
            $profile->email
            && User::where('email', $profile->email)
                ->when($profile->user_id, fn ($query) => $query->where('id', '!=', $profile->user_id))
                ->exists()
        ) {
            abort(422, 'This email is already used by another admin.');
        }

        $isRoot = $request->boolean('is_root_admin');
        $role = $isRoot ? User::ROLE_SUPER_ADMIN : $profile->admin_role;
        $permissions = $profile->permission_override_enabled
            ? ($profile->admin_permissions ?? [])
            : ($this->rolePermissions($profile->profile_type));
        $payload = [
            'name' => $profile->name,
            'email' => $profile->email,
            'personal_phone' => $this->normalizePhone($profile->phone),
            'optional_phone' => $this->normalizePhone($profile->optional_phone),
            'role' => $role,
            'permissions' => $isRoot ? null : $permissions,
            'is_active' => $profile->is_active,
            'is_root_admin' => $isRoot,
            'permissions_updated_by' => $request->user()->id,
            'permissions_updated_at' => now(),
        ];

        $user = $profile->adminUser ?: ($profile->user_id ? User::find($profile->user_id) : null);

        if (! $user) {
            $payload['password'] = $request->input('admin_password');
            $payload['created_by'] = $request->user()->id;
            $user = User::create($payload);
            $profile->update(['user_id' => $user->id]);
            return;
        }

        if ($request->filled('admin_password')) {
            $payload['password'] = $request->input('admin_password');
        }

        $user->update($payload);
    }

    private function authorizeAdminSync(Request $request, PeopleProfile $profile): void
    {
        if (! $request->user()?->hasAdminPermission('admin_users.manage_admins')) {
            abort(403, 'You cannot manage admin login from HR.');
        }

        if ($profile->permission_override_enabled && ($profile->admin_permissions ?? []) && ! $request->user()?->hasAdminPermission('admin_users.assign_permissions')) {
            abort(403, 'You cannot assign permissions from HR.');
        }

        if ($profile->admin_role === User::ROLE_SUPER_ADMIN && ! $request->user()?->hasAdminPermission('admin_users.manage_super_admins')) {
            abort(403, 'You cannot manage super admin access from HR.');
        }

        if (! $request->boolean('is_root_admin')) {
            return;
        }

        $expectedPasscode = (string) config('admin_permissions.root_promotion_passcode');

        if (
            ! $request->user()?->is_root_admin
            || $expectedPasscode === ''
            || ! hash_equals($expectedPasscode, (string) $request->input('root_admin_passcode'))
        ) {
            abort(403, 'Root admin passcode is incorrect.');
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        return $phone !== '' ? $phone : null;
    }

    private function uniqueReferralCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, ''));
        $base = $base !== '' ? Str::limit($base, 28, '') : 'REF';
        $code = $base.'100';
        $counter = 2;

        while (Referrer::where('code', $code)->exists()) {
            $code = $base.$counter++;
        }

        return $code;
    }

    private function rolePermissions(string $role): array
    {
        $profileType = PeopleProfileType::where('slug', $role)->first();

        return User::sanitizePermissions($profileType?->default_permissions ?? []);
    }

    private function ensureAdminProfiles(): void
    {
        User::query()->get()->each(function (User $user): void {
            if (PeopleProfile::where('user_id', $user->id)->exists()) {
                return;
            }

            PeopleProfile::create([
                'user_id' => $user->id,
                'profile_type' => $user->is_root_admin || $user->role === User::ROLE_SUPER_ADMIN ? User::ROLE_SUPER_ADMIN : User::ROLE_ADMIN,
                'name' => $user->name,
                'title' => $user->is_root_admin ? 'Root Admin' : $user->roleLabel(),
                'email' => $user->email,
                'phone' => $user->personal_phone,
                'optional_phone' => $user->optional_phone,
                'payment_type' => 'salary',
                'salary_type' => 'monthly',
                'admin_enabled' => true,
                'admin_role' => $user->role,
                'admin_permissions' => $user->permissions,
                'permission_override_enabled' => (bool) ($user->permissions ?? []),
                'is_active' => $user->is_active,
            ]);
        });
    }
}
