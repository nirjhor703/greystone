<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_size_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('size', 10);

            $table->unsignedInteger('stock_quantity')
                ->default(0);

            $table->timestamps();

            $table->unique(
                ['product_id', 'size'],
                'product_size_unique'
            );

            $table->index([
                'product_id',
                'stock_quantity',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_size_stocks');
    }
};