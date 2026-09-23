<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweet_cool_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('teaser_kicker')->nullable();
            $table->string('teaser_title')->nullable();
            $table->text('teaser_description')->nullable();
            $table->string('teaser_button_text')->nullable();
            $table->string('hero_kicker')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_button_text')->nullable();
            $table->string('intro_heading')->nullable();
            $table->text('intro_description')->nullable();
            $table->string('section_one_title')->nullable();
            $table->text('section_one_body')->nullable();
            $table->string('section_two_title')->nullable();
            $table->text('section_two_body')->nullable();
            $table->string('section_three_title')->nullable();
            $table->text('section_three_body')->nullable();
            $table->json('promo_badges')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('factory_images')->nullable();
            $table->json('product_images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweet_cool_pages');
    }
};
