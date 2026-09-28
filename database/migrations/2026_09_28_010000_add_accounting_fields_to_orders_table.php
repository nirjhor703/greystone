<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'order_channel')) {
                $table->string('order_channel', 20)->default('online')->after('order_source')->index();
            }

            if (! Schema::hasColumn('orders', 'source_note')) {
                $table->string('source_note')->nullable()->after('order_channel');
            }

            if (! Schema::hasColumn('orders', 'added_by_user_id')) {
                $table->foreignId('added_by_user_id')->nullable()->after('source_note')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'vat_enabled')) {
                $table->boolean('vat_enabled')->default(false)->after('coupon_snapshot');
            }

            if (! Schema::hasColumn('orders', 'vat_percent')) {
                $table->decimal('vat_percent', 5, 2)->default(0)->after('vat_enabled');
            }

            if (! Schema::hasColumn('orders', 'vat_amount')) {
                $table->decimal('vat_amount', 12, 2)->default(0)->after('vat_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['vat_amount', 'vat_percent', 'vat_enabled', 'added_by_user_id', 'source_note', 'order_channel'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
