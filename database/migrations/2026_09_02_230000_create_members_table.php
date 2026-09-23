<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('gender', 10)->index();
            $table->string('mobile', 30)->index();
            $table->string('mobile_2', 30)->nullable();
            $table->text('address');
            $table->date('date_of_birth')->index();
            $table->string('division', 30)->index();
            $table->string('google_id')->nullable()->unique();
            $table->text('avatar_url')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
