<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people_profile_types', function (Blueprint $table): void {
            $table->json('default_permissions')->nullable()->after('slug');
            $table->boolean('is_system')->default(true)->after('default_permissions');
        });

        Schema::table('people_profiles', function (Blueprint $table): void {
            $table->boolean('permission_override_enabled')->default(false)->after('admin_permissions');
        });

        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'sort_order' => 1],
            ['name' => 'Admin', 'slug' => 'admin', 'sort_order' => 2],
            ['name' => 'Marketer', 'slug' => 'marketer', 'sort_order' => 3],
            ['name' => 'Digital Marketer', 'slug' => 'digital_marketer', 'sort_order' => 4],
            ['name' => 'Developer', 'slug' => 'developer', 'sort_order' => 5],
        ];

        foreach ($roles as $role) {
            DB::table('people_profile_types')->updateOrInsert(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'sort_order' => $role['sort_order'],
                    'is_active' => true,
                    'is_system' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        DB::table('people_profile_types')
            ->whereNotIn('slug', collect($roles)->pluck('slug')->all())
            ->update(['is_active' => false]);

        DB::table('people_profiles')->where('profile_type', 'marketer_referrer')->update(['profile_type' => 'marketer']);
        DB::table('people_profiles')->where('profile_type', 'co_founder_vice_president')->update(['profile_type' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('people_profiles', function (Blueprint $table): void {
            $table->dropColumn('permission_override_enabled');
        });

        Schema::table('people_profile_types', function (Blueprint $table): void {
            $table->dropColumn(['default_permissions', 'is_system']);
        });
    }
};
