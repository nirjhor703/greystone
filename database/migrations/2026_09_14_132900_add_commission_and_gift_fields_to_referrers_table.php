<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrers', function (Blueprint $table): void {
            if (! Schema::hasColumn('referrers', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)
                    ->default(1)
                    ->after('code');
            }

            if (! Schema::hasColumn('referrers', 'gift_balance')) {
                $table->decimal('gift_balance', 12, 2)
                    ->default(0)
                    ->after('balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referrers', function (Blueprint $table): void {
            if (Schema::hasColumn('referrers', 'gift_balance')) {
                $table->dropColumn('gift_balance');
            }

            if (Schema::hasColumn('referrers', 'commission_rate')) {
                $table->dropColumn('commission_rate');
            }
        });
    }
};
