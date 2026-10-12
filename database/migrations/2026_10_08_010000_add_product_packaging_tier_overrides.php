<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('premium_packaging_tier_override')->default(false)->after('packaging_type_enabled');
        });

        if (! DB::table('premium_packaging_tiers')->whereNull('product_id')->exists()) {
            DB::table('premium_packaging_tiers')->insert([
                ['product_id' => null, 'name' => 'Small Package', 'min_pieces' => 1, 'max_pieces' => 3, 'price' => 30, 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['product_id' => null, 'name' => 'Medium Package', 'min_pieces' => 4, 'max_pieces' => 6, 'price' => 50, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['product_id' => null, 'name' => 'Large Package', 'min_pieces' => 7, 'max_pieces' => 10, 'price' => 80, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('premium_packaging_tier_override');
        });
    }
};
