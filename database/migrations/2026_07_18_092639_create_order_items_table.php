<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product snapshot
            |--------------------------------------------------------------------------
            |
            | Product পরে edit/delete হলেও order history ঠিক থাকবে।
            |
            */

            $table->string('product_name', 150);
            $table->string('product_code', 50)->nullable();
            $table->string('product_image')->nullable();

            $table->string('size', 20)->nullable();
            $table->string('color', 100)->nullable();

            $table->unsignedInteger('quantity');

            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);

            $table->timestamps();

            $table->index([
                'order_id',
                'product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};