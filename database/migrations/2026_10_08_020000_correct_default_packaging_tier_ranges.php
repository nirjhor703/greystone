<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            0 => ['name' => 'Small Package', 'min_pieces' => 1, 'max_pieces' => 3, 'price' => 30],
            1 => ['name' => 'Medium Package', 'min_pieces' => 4, 'max_pieces' => 7, 'price' => 50],
            2 => ['name' => 'Large Package', 'min_pieces' => 8, 'max_pieces' => 10, 'price' => 80],
        ];

        foreach ($defaults as $sortOrder => $values) {
            DB::table('premium_packaging_tiers')
                ->whereNull('product_id')
                ->where('sort_order', $sortOrder)
                ->update($values + ['updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Default values are intentionally not reverted automatically.
    }
};
