<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table
                ->string('role', 30)
                ->default('admin')
                ->after('password');

            $table
                ->json('permissions')
                ->nullable()
                ->after('role');

            $table
                ->boolean('is_active')
                ->default(true)
                ->after('permissions');

            $table
                ->boolean('is_root_admin')
                ->default(false)
                ->after('is_active');

            $table
                ->foreignId('created_by')
                ->nullable()
                ->after('is_root_admin')
                ->constrained('users')
                ->nullOnDelete();
        });

        $firstUserId = DB::table('users')
            ->orderBy('id')
            ->value('id');

        if ($firstUserId) {
            DB::table('users')
                ->where('id', '!=', $firstUserId)
                ->update([
                    'role' => 'admin',
                    'permissions' => json_encode([]),
                    'is_active' => false,
                    'is_root_admin' => false,
                ]);

            DB::table('users')
                ->where('id', $firstUserId)
                ->update([
                    'role' => 'super_admin',
                    'permissions' => null,
                    'is_active' => true,
                    'is_root_admin' => true,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');

            $table->dropColumn([
                'role',
                'permissions',
                'is_active',
                'is_root_admin',
            ]);
        });
    }
};
