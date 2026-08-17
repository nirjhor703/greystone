<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('categories');

        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name', 100);
            $table->string('slug', 120);
            $table->string('prefix', 10);

            $table->string('image')->nullable();

            $table->string('status', 20)
                ->default('Active');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(
                ['brand_id', 'name'],
                'categories_brand_name_unique'
            );

            $table->unique(
                ['brand_id', 'slug'],
                'categories_brand_slug_unique'
            );

            $table->unique(
                ['brand_id', 'prefix'],
                'categories_brand_prefix_unique'
            );

            $table->index('brand_id');
            $table->index(['brand_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};