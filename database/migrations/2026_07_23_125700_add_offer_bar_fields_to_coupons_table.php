<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table
                ->string('topbar_text', 180)
                ->nullable()
                ->after('popup_scroll_pixels');

            $table
                ->string('topbar_applied_text', 180)
                ->nullable()
                ->after('topbar_text');

            $table
                ->string('topbar_button_text', 80)
                ->nullable()
                ->after('topbar_applied_text');

            $table
                ->string('popup_apply_loading_text', 80)
                ->nullable()
                ->after('topbar_button_text');

            $table
                ->string('popup_applied_text', 80)
                ->nullable()
                ->after('popup_apply_loading_text');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropColumn([
                'topbar_text',
                'topbar_applied_text',
                'topbar_button_text',
                'popup_apply_loading_text',
                'popup_applied_text',
            ]);
        });
    }
};
