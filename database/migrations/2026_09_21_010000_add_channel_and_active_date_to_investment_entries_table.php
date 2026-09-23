<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_entries', function (Blueprint $table): void {
            $table->string('investment_channel', 20)->default('online')->after('entry_type')->index();
            $table->date('active_date')->nullable()->after('entry_date')->index();
        });
    }

    public function down(): void
    {
        Schema::table('investment_entries', function (Blueprint $table): void {
            $table->dropColumn(['investment_channel', 'active_date']);
        });
    }
};
