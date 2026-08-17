<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table
                ->boolean('new_customer_only')
                ->default(false)
                ->after('status');

            $table
                ->boolean('show_as_popup')
                ->default(false)
                ->after('new_customer_only');

            $table
                ->string('popup_badge', 100)
                ->nullable()
                ->after('show_as_popup');

            $table
                ->string('popup_title', 160)
                ->nullable()
                ->after('popup_badge');

            $table
                ->text('popup_description')
                ->nullable()
                ->after('popup_title');

            $table
                ->string('popup_button_text', 100)
                ->nullable()
                ->after('popup_description');

            $table
                ->unsignedInteger('popup_scroll_pixels')
                ->default(120)
                ->after('popup_button_text');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'new_customer_only',
                'show_as_popup',
                'popup_badge',
                'popup_title',
                'popup_description',
                'popup_button_text',
                'popup_scroll_pixels',
            ]);
        });
    }
};