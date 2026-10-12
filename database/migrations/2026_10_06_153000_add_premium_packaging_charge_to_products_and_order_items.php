<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'premium_packaging_charge')) {
                $table
                    ->decimal('premium_packaging_charge', 12, 2)
                    ->default(50)
                    ->after('packaging_type_enabled');
            }
        });

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'packaging_charge')) {
                $table
                    ->decimal('packaging_charge', 12, 2)
                    ->default(0)
                    ->after('packaging_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_items', 'packaging_charge')) {
                $table->dropColumn('packaging_charge');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'premium_packaging_charge')) {
                $table->dropColumn('premium_packaging_charge');
            }
        });
    }
};
