<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'packaging_total')) {
                $table->decimal('packaging_total', 12, 2)
                    ->default(0)
                    ->after('items_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'packaging_total')) {
                $table->dropColumn('packaging_total');
            }
        });
    }
};
