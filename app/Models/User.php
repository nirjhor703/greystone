<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'personal_phone',
        'optional_phone',
        'password',
        'role',
        'permissions',
        'is_active',
        'is_root_admin',
        'created_by',
        'permissions_updated_by',
        'permissions_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'is_root_admin' => 'boolean',
            'permissions_updated_at' => 'datetime',
        ];
    }

    public function permissionUpdater(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'permissions_updated_by'
        );
    }

    public static function permissionKeys(): array
    {
        $modules = config('admin_permissions.modules', []);
        $actions = array_keys(config('admin_permissions.actions', []));
        $keys = [];

        foreach (array_keys($modules) as $module) {
            foreach ($actions as $action) {
                $keys[] = "{$module}.{$action}";
            }
        }

        return array_values(array_unique([
            ...$keys,
            ...array_keys(config('admin_permissions.sensitive', [])),
        ]));
    }

    public static function sanitizePermissions(array $permissions): array
    {
        $allowed = array_flip(self::permissionKeys());

        return array_values(array_unique(array_filter(
            $permissions,
            fn ($permission) => isset($allowed[$permission])
        )));
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->is_root_admin) {
            return true;
        }

        return in_array(
            $permission,
            $this->permissions ?? [],
            true
        );
    }

    public function canGrantPermission(string $permission): bool
    {
        return $this->is_root_admin
            || $this->hasAdminPermission($permission);
    }

    public function canAccessAdminModule(string $module): bool
    {
        return $this->hasAdminPermission("{$module}.view");
    }

    public function firstAllowedAdminRoute(): string
    {
        foreach (config('admin_permissions.modules', []) as $module => $meta) {
            if ($this->canAccessAdminModule($module)) {
                return $meta['route'];
            }
        }

        return 'admin.settings.index';
    }

    public function firstAllowedAdminUrl(): string
    {
        foreach (config('admin_permissions.modules', []) as $module => $meta) {
            if ($this->canAccessAdminModule($module)) {
                return route(
                    $meta['route'],
                    $meta['params'] ?? [],
                    false
                );
            }
        }

        return route('admin.settings.index', [], false);
    }

    public function roleLabel(): string
    {
        return $this->isSuperAdmin()
            ? 'Super Admin'
            : 'Admin';
    }
}
