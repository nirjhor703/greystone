<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('is_root_admin', false)
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get(['id', 'permissions'])
            ->each(function ($user): void {
                $permissions = json_decode(
                    $user->permissions,
                    true
                ) ?: [];

                $alreadyTrusted = in_array(
                    'admin_users.assign_permissions',
                    $permissions,
                    true
                ) || in_array(
                    'admin_users.manage_super_admins',
                    $permissions,
                    true
                );

                if (
                    ! $alreadyTrusted
                    || in_array(
                        'admin_users.manage_admins',
                        $permissions,
                        true
                    )
                ) {
                    return;
                }

                $permissions[] = 'admin_users.manage_admins';

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'permissions' => json_encode(
                            array_values(array_unique($permissions))
                        ),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get(['id', 'permissions'])
            ->each(function ($user): void {
                $permissions = array_values(array_filter(
                    json_decode($user->permissions, true) ?: [],
                    fn ($permission) => $permission !== 'admin_users.manage_admins'
                ));

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                    ]);
            });
    }
};
