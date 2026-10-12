<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'delivery_charge_paid')) {
                $table
                    ->decimal('delivery_charge_paid', 12, 2)
                    ->default(0)
                    ->after('delivery_charge');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'delivery_charge_paid')) {
                $table->dropColumn('delivery_charge_paid');
            }
        });
    }
};
