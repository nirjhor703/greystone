<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AdminPermissionDeniedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('permissionUpdater')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('personal_phone', 'like', "%{$search}%")
                        ->orWhere('optional_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request): void {
                $query->where('role', $request->input('role'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('is_active', $request->input('status') === 'active');
            })
            ->latest('id')
            ->get();

        return view('admin.admin-users.index', [
            'users' => $users,
            'modules' => config('admin_permissions.modules', []),
            'actions' => config('admin_permissions.actions', []),
            'sensitivePermissions' => config('admin_permissions.sensitive', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validatedData($request);

            $this->authorizeAdminManagement();
            $isRootAdmin = $this->resolveRootAdminFlag($request);
            $role = $isRootAdmin
                ? User::ROLE_SUPER_ADMIN
                : $validated['role'];

            $this->authorizeRoleWrite($role);
            $permissions = $this->validatedPermissions($request);

            $createdUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'personal_phone' => $this->normalizePhone(
                    $validated['personal_phone']
                ),
                'optional_phone' => $this->normalizePhone(
                    $validated['optional_phone'] ?? null
                ),
                'password' => $validated['password'],
                'role' => $role,
                'permissions' => $isRootAdmin ? null : $permissions,
                'is_active' => $isRootAdmin || $request->boolean('is_active'),
                'is_root_admin' => $isRootAdmin,
                'created_by' => $request->user()->id,
                'permissions_updated_by' => $request->user()->id,
                'permissions_updated_at' => now(),
            ]);

            app(AdminNotificationService::class)
                ->adminPermissionsChanged(
                    $createdUser,
                    $request->user(),
                    [],
                    $createdUser->permissions ?? [],
                    'none',
                    $createdUser->role,
                    false,
                    (bool) $createdUser->is_root_admin
                );
        } catch (AdminPermissionDeniedException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'admin_permission' => $exception->getMessage(),
                ]);
        }

        return back()->with('status', 'Admin user added successfully.');
    }

    public function update(Request $request, User $adminUser): RedirectResponse
    {
        try {
            $validated = $this->validatedData($request, $adminUser);
            $isSelfUpdate = (int) $adminUser->id === (int) $request->user()->id;
            $oldPermissions = $adminUser->permissions ?? [];
            $oldRole = $adminUser->role;
            $oldRoot = (bool) $adminUser->is_root_admin;

            $this->guardRootTarget($request, $adminUser);
            $this->verifyRootSelfAction($request, $adminUser);

            if (! $isSelfUpdate) {
                $this->authorizeAdminManagement();
                $this->authorizeRoleWrite($adminUser->role);
                $this->authorizeRoleWrite($validated['role']);
            }

            $permissions = $this->validatedPermissions($request);
            $isRootAdmin = $adminUser->is_root_admin
                || $this->resolveRootAdminFlag($request);

            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'personal_phone' => $this->normalizePhone(
                    $validated['personal_phone']
                ),
                'optional_phone' => $this->normalizePhone(
                    $validated['optional_phone'] ?? null
                ),
                'role' => $validated['role'],
                'permissions' => $permissions,
                'is_active' => $request->boolean('is_active'),
            ];

            if ($adminUser->is_root_admin) {
                $payload['role'] = User::ROLE_SUPER_ADMIN;
                $payload['permissions'] = null;
                $payload['is_active'] = true;
            }

            if (
                ! $adminUser->is_root_admin
                && $isRootAdmin
            ) {
                $payload['role'] = User::ROLE_SUPER_ADMIN;
                $payload['permissions'] = null;
                $payload['is_active'] = true;
                $payload['is_root_admin'] = true;
            }

            if ($isSelfUpdate && ! $adminUser->is_root_admin) {
                $payload['role'] = $adminUser->role;
                $payload['permissions'] = $adminUser->permissions ?? [];
                $payload['is_active'] = true;
            } elseif (! $adminUser->is_root_admin) {
                $payload['permissions_updated_by'] = $request->user()->id;
                $payload['permissions_updated_at'] = now();
            }

            if (! empty($validated['password'])) {
                $payload['password'] = $validated['password'];
            }

            $adminUser->update($payload);

            $adminUser->refresh();

            app(AdminNotificationService::class)
                ->adminPermissionsChanged(
                    $adminUser,
                    $request->user(),
                    $oldPermissions,
                    $adminUser->permissions ?? [],
                    $oldRole,
                    $adminUser->role,
                    $oldRoot,
                    (bool) $adminUser->is_root_admin
                );
        } catch (AdminPermissionDeniedException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'admin_permission' => $exception->getMessage(),
                ]);
        }

        return back()->with('status', 'Admin user updated successfully.');
    }

    public function destroy(Request $request, User $adminUser): RedirectResponse
    {
        try {
            $this->guardRootTarget($request, $adminUser);
            $isSelfDelete = (int) $adminUser->id === (int) $request->user()->id;

            if ($isSelfDelete && ! $adminUser->is_root_admin) {
                throw new AdminPermissionDeniedException(
                    'You cannot delete your own account.'
                );
            }

            $this->verifyRootSelfAction($request, $adminUser);
            $this->authorizeAdminManagement();

            $permission = $adminUser->isSuperAdmin()
                ? 'admin_users.delete_super_admins'
                : 'admin_users.delete';

            if (! $request->user()->hasAdminPermission($permission)) {
                throw new AdminPermissionDeniedException(
                    'You cannot delete this admin. Delete access is blocked for your account.'
                );
            }

            $adminUser->delete();

            if ($isSelfDelete) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('status', 'Root account deleted successfully.');
            }
        } catch (AdminPermissionDeniedException $exception) {
            return back()->withErrors([
                'admin_permission' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Admin user deleted successfully.');
    }

    public function verifyRootPasscode(Request $request): JsonResponse
    {
        if (! $request->user()?->is_root_admin) {
            return response()->json([
                'message' => 'Only a root admin can give root access.',
            ], 403);
        }

        $validated = $request->validate([
            'root_admin_passcode' => ['required', 'string', 'max:120'],
        ]);

        $expectedPasscode = (string) config(
            'admin_permissions.root_promotion_passcode'
        );

        if (
            $expectedPasscode === ''
            || ! hash_equals(
                $expectedPasscode,
                (string) $validated['root_admin_passcode']
            )
        ) {
            return response()->json([
                'message' => 'Root admin passcode is incorrect.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
        ]);
    }

    private function validatedData(
        Request $request,
        ?User $adminUser = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:160',
                Rule::unique('users', 'email')->ignore($adminUser?->id),
            ],
            'personal_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^01[3-9]\d{8}$/',
            ],
            'optional_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^01[3-9]\d{8}$/',
                'different:personal_phone',
            ],
            'password' => [
                $adminUser ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],
            'role' => [
                'required',
                Rule::in([
                    User::ROLE_SUPER_ADMIN,
                    User::ROLE_ADMIN,
                ]),
            ],
            'is_active' => ['nullable', 'boolean'],
            'is_root_admin' => ['nullable', 'boolean'],
            'root_admin_passcode' => ['nullable', 'string', 'max:120'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        return $phone !== '' ? $phone : null;
    }

    private function resolveRootAdminFlag(Request $request): bool
    {
        if (! $request->boolean('is_root_admin')) {
            return false;
        }

        if ($request->user()?->is_root_admin) {
            $expectedPasscode = (string) config(
                'admin_permissions.root_promotion_passcode'
            );

            $givenPasscode = (string) $request->input(
                'root_admin_passcode'
            );

            if (
                $expectedPasscode === ''
                || ! hash_equals(
                    $expectedPasscode,
                    $givenPasscode
                )
            ) {
                throw new AdminPermissionDeniedException(
                    'Root admin passcode is incorrect. Root access was not changed.'
                );
            }

            return true;
        }

        throw new AdminPermissionDeniedException(
            'Only a root admin can create or promote another root admin.'
        );
    }

    private function verifyRootSelfAction(
        Request $request,
        User $adminUser
    ): void {
        if (
            ! $adminUser->is_root_admin
            || (int) $adminUser->id !== (int) $request->user()->id
        ) {
            return;
        }

        $expectedPasscode = (string) config(
            'admin_permissions.root_promotion_passcode'
        );

        $givenPasscode = (string) $request->input(
            'root_admin_passcode'
        );

        if (
            $expectedPasscode === ''
            || ! hash_equals($expectedPasscode, $givenPasscode)
        ) {
            throw new AdminPermissionDeniedException(
                'Root admin passcode is required before editing or deleting your root account.'
            );
        }
    }

    private function validatedPermissions(Request $request): array
    {
        $permissions = User::sanitizePermissions(
            $request->input('permissions', [])
        );

        if (
            $permissions
            && ! $request->user()->hasAdminPermission('admin_users.assign_permissions')
        ) {
            throw new AdminPermissionDeniedException(
                'You cannot assign admin permissions. Another authorized super admin must give you this access first.'
            );
        }

        if ($request->user()->is_root_admin) {
            return $permissions;
        }

        foreach ($permissions as $permission) {
            if (! $request->user()->canGrantPermission($permission)) {
                throw new AdminPermissionDeniedException(
                    "You cannot change {$permission}. This permission is blocked for your account by another super admin."
                );
            }
        }

        return $permissions;
    }

    private function authorizeRoleWrite(string $role): void
    {
        if ($role !== User::ROLE_SUPER_ADMIN) {
            return;
        }

        if (! auth()->user()?->hasAdminPermission('admin_users.manage_super_admins')) {
            throw new AdminPermissionDeniedException(
                'You cannot manage super admins. This access is blocked for your account.'
            );
        }
    }

    private function authorizeAdminManagement(): void
    {
        if (auth()->user()?->hasAdminPermission('admin_users.manage_admins')) {
            return;
        }

        throw new AdminPermissionDeniedException(
            'You cannot add, update or delete admin accounts. Manage Admins access is blocked for your account.'
        );
    }

    private function guardRootTarget(Request $request, User $adminUser): void
    {
        if (! $adminUser->is_root_admin) {
            return;
        }

        if ((int) $request->user()->id === (int) $adminUser->id) {
            return;
        }

        throw new AdminPermissionDeniedException(
            'Root admins are locked. One root account cannot edit or delete another root account.'
        );
    }
}
