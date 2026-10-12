<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_packaging_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('min_pieces');
            $table->unsignedInteger('max_pieces');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['product_id', 'is_active']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->foreignId('packaging_tier_id')->nullable()->after('packaging_type')->constrained('premium_packaging_tiers')->nullOnDelete();
            $table->string('packaging_tier_name', 100)->nullable()->after('packaging_tier_id');
            $table->unsignedInteger('packaging_min_pieces')->nullable()->after('packaging_tier_name');
            $table->unsignedInteger('packaging_max_pieces')->nullable()->after('packaging_min_pieces');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropForeign(['packaging_tier_id']);
            $table->dropColumn(['packaging_tier_id', 'packaging_tier_name', 'packaging_min_pieces', 'packaging_max_pieces']);
        });
        Schema::dropIfExists('premium_packaging_tiers');
    }
};
