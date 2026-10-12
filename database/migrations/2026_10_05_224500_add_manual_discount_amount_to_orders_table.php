<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'manual_discount_amount')) {
                $table
                    ->decimal('manual_discount_amount', 12, 2)
                    ->default(0)
                    ->after('coupon_discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'manual_discount_amount')) {
                $table->dropColumn('manual_discount_amount');
            }
        });
    }
};
