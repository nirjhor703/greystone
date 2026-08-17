<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('brand_id')
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
        
            $table->string('name');
            $table->string('slug');
            $table->string('category_code', 20)->nullable();
        
            $table->string('image')->nullable();
            $table->string('banner')->nullable();
        
            $table->text('description')->nullable();
        
            $table->unsignedInteger('sort_order')->default(0);
        
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
        
            $table->timestamps();
        
            $table->unique(
                ['brand_id', 'slug'],
                'categories_brand_slug_unique'
            );
        
            $table->unique(
                ['brand_id', 'name'],
                'categories_brand_name_unique'
            );
        
            $table->index(['brand_id', 'is_active']);
            $table->index(['brand_id', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
