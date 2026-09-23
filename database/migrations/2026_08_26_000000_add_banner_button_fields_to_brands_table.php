<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table): void {
            $table->string('signup_banner')
                ->nullable()
                ->after('offer_banners');

            $table->string('sales_banner')
                ->nullable()
                ->after('signup_banner');

            $table->string('coupons_banner')
                ->nullable()
                ->after('sales_banner');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table): void {
            $table->dropColumn([
                'signup_banner',
                'sales_banner',
                'coupons_banner',
            ]);
        });
    }
};
