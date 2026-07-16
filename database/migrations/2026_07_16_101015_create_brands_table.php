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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
        
            $table->string('name');
            $table->string('slug')->unique();
        
            $table->string('logo')->nullable();
            $table->string('mobile_logo')->nullable();
            $table->string('favicon')->nullable();
        
            $table->string('primary_color')->default('#2f2f2f');
            $table->string('secondary_color')->default('#6b6b6b');
            $table->string('background_color')->default('#ffffff');
            $table->string('button_color')->default('#2f2f2f');
            $table->string('text_color')->default('#111111');
            $table->string('font_family')->nullable();
        
            $table->string('header_style')->nullable();
            $table->string('footer_style')->nullable();
        
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('whatsapp_link')->nullable();
            $table->text('address')->nullable();
        
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
        
            $table->boolean('is_active')->default(true);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
