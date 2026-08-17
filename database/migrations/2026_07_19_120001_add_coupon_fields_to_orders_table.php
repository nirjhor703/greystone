<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')
                    ->nullable()
                    ->after('payment_method')
                    ->constrained('coupons')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code', 60)
                    ->nullable()
                    ->after('coupon_id');
            }

            if (!Schema::hasColumn('orders', 'coupon_discount_amount')) {
                $table->decimal('coupon_discount_amount', 12, 2)
                    ->default(0)
                    ->after('coupon_code');
            }

            if (!Schema::hasColumn('orders', 'coupon_snapshot')) {
                $table->json('coupon_snapshot')
                    ->nullable()
                    ->after('coupon_discount_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn([
                'coupon_code',
                'coupon_discount_amount',
                'coupon_snapshot',
            ]);
        });
    }
};
