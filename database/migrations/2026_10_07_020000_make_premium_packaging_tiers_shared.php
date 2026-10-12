<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('premium_packaging_tiers', function (Blueprint $table): void {
            $table->foreignId('product_id')->nullable()->change();
        });

        $productId = DB::table('premium_packaging_tiers')->min('product_id');
        if ($productId) {
            DB::table('premium_packaging_tiers')
                ->where('product_id', $productId)
                ->update(['product_id' => null]);
            DB::table('premium_packaging_tiers')
                ->whereNotNull('product_id')
                ->delete();
        }
    }

    public function down(): void
    {
        // Shared tiers cannot be safely reassigned to a product automatically.
    }
};
