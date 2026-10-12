<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'packaging_type_enabled')) {
                $table
                    ->boolean('packaging_type_enabled')
                    ->default(false)
                    ->after('is_sales_badge');
            }
        });

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'packaging_type')) {
                $table
                    ->string('packaging_type', 20)
                    ->default('normal')
                    ->after('color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_items', 'packaging_type')) {
                $table->dropColumn('packaging_type');
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'packaging_type_enabled')) {
                $table->dropColumn('packaging_type_enabled');
            }
        });
    }
};
