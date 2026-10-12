<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('premium_packaging_tiers')) {
            return;
        }

        Schema::table('premium_packaging_tiers', function (Blueprint $table): void {
            // MySQL requires the foreign key to be removed before changing nullability.
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Shared tiers require NULL product_id values, so this is intentionally irreversible.
    }
};
