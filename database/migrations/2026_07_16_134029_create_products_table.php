<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 150);
            $table->string('slug', 180);
            $table->string('product_code', 30);

            $table->decimal('regular_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();

            $table->unsignedInteger('stock_quantity')->default(0);

            $table->string('stock_status', 20)
                ->default('In Stock');

            $table->json('sizes')->nullable();
            $table->json('colors')->nullable();

            $table->string('material')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->text('care_instructions')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);

            $table->string('status', 20)
                ->default('Active');

            $table->timestamps();

            $table->unique(
                ['brand_id', 'slug'],
                'products_brand_slug_unique'
            );

            $table->unique(
                ['brand_id', 'product_code'],
                'products_brand_code_unique'
            );

            $table->index(['brand_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'is_featured']);
            $table->index(['brand_id', 'is_new_arrival']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};