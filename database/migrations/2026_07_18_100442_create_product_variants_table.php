<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('color', 100);
            $table->string('size', 20);

            $table->unsignedInteger('stock_quantity')
                ->default(0);

            $table->string('variant_sku', 100)
                ->nullable();

            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Same product cannot have duplicate color + size combination
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'product_id',
                    'color',
                    'size',
                ],
                'product_color_size_unique'
            );

            $table->index([
                'product_id',
                'color',
                'status',
            ]);

            $table->index([
                'product_id',
                'stock_quantity',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};