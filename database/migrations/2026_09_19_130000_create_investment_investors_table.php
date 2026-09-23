<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_investors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('people_profile_id')->nullable()->constrained('people_profiles')->nullOnDelete();
            $table->string('name');
            $table->string('type', 30)->default('external')->index();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('term_months')->default(12);
            $table->date('agreement_date')->nullable();
            $table->text('agreement_note')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_investors');
    }
};
