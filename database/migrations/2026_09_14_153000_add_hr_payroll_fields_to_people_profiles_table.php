<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people_profiles', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('referrer_id')->constrained('users')->nullOnDelete();
            $table->string('photo_path')->nullable()->after('title');
            $table->string('email')->nullable()->after('photo_path');
            $table->string('phone')->nullable()->after('email');
            $table->string('optional_phone')->nullable()->after('phone');
            $table->text('address')->nullable()->after('optional_phone');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('nid_or_document')->nullable()->after('emergency_contact');
            $table->date('joining_date')->nullable()->after('nid_or_document');
            $table->decimal('salary_amount', 12, 2)->default(0)->after('payment_type');
            $table->string('salary_type', 40)->default('monthly')->after('salary_amount');
            $table->boolean('admin_enabled')->default(false)->after('tax_note');
            $table->string('admin_role', 30)->default('admin')->after('admin_enabled');
            $table->json('admin_permissions')->nullable()->after('admin_role');
            $table->boolean('referral_enabled')->default(false)->after('admin_permissions');
            $table->text('role_notes')->nullable()->after('referral_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('people_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'photo_path',
                'email',
                'phone',
                'optional_phone',
                'address',
                'emergency_contact',
                'nid_or_document',
                'joining_date',
                'salary_amount',
                'salary_type',
                'admin_enabled',
                'admin_role',
                'admin_permissions',
                'referral_enabled',
                'role_notes',
            ]);
        });
    }
};
