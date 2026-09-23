<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sweet_cool_visit_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('email')->nullable();
            $table->string('phone', 30);
            $table->date('visit_date');
            $table->time('visit_time');
            $table->string('contact_reason')->nullable();
            $table->json('role_tags')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 30)->default('booked');
            $table->timestamps();

            $table->unique(['visit_date', 'visit_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sweet_cool_visit_bookings');
    }
};
