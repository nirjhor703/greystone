<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_entries', function (Blueprint $table): void {
            if (! Schema::hasColumn('investment_entries', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->after('investment_investor_id')->constrained('brands')->nullOnDelete();
            }

            if (! Schema::hasColumn('investment_entries', 'cost_category')) {
                $table->string('cost_category', 80)->nullable()->after('investment_channel')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('investment_entries', function (Blueprint $table): void {
            if (Schema::hasColumn('investment_entries', 'brand_id')) {
                $table->dropConstrainedForeignId('brand_id');
            }

            if (Schema::hasColumn('investment_entries', 'cost_category')) {
                $table->dropColumn('cost_category');
            }
        });
    }
};
