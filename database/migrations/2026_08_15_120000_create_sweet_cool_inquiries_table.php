<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweet_cool_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('source_page', 40);
            $table->string('customer_name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('interest_type', 40);
            $table->string('quantity_note')->nullable();
            $table->string('preferred_contact', 20)->nullable();
            $table->text('message');
            $table->string('page_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweet_cool_inquiries');
    }
};
