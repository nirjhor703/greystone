<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number', 40)->unique();
            $table->string('invoice_number', 40)->unique();

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('customer_name', 100);
            $table->string('phone', 20);
            $table->string('alternative_phone', 20)->nullable();
            $table->string('customer_email', 150)->nullable();

            $table->string('delivery_area', 30);
            $table->string('district', 100);
            $table->string('area_thana', 150);
            $table->string('road_no', 100);
            $table->string('house_no', 100);
            $table->string('full_address', 250);
            $table->text('order_note')->nullable();

            $table->string('payment_method', 50);

            $table->decimal('items_total', 12, 2);
            $table->decimal('delivery_charge', 12, 2);
            $table->decimal('grand_total', 12, 2);

            $table->string('status', 30)->default('Pending');
            $table->string('payment_status', 30)->default('Unpaid');

            $table->string('steadfast_consignment_id')->nullable();
            $table->string('courier_status')->nullable();
            $table->timestamp('sent_to_steadfast_at')->nullable();
            $table->foreignId('steadfast_sent_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->json('steadfast_response')->nullable();
            $table->text('steadfast_error')->nullable();

            $table->string('order_source', 30)->default('cart');
            $table->timestamps();

            $table->index(['brand_id', 'status']);
            $table->index(['courier_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
