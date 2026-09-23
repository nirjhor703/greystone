<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sweet_cool_inquiries', function (Blueprint $table) {
            $table->string('contact_reason')->nullable()->after('company_name');
            $table->json('role_tags')->nullable()->after('preferred_contact');
        });
    }

    public function down(): void
    {
        Schema::table('sweet_cool_inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'contact_reason',
                'role_tags',
            ]);
        });
    }
};
