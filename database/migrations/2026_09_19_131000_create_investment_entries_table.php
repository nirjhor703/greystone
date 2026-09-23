<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('investment_investor_id')->nullable()->constrained('investment_investors')->nullOnDelete();
            $table->string('entry_type', 40)->default('investor_investment')->index();
            $table->date('entry_date')->index();
            $table->date('maturity_date')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('purpose')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_entries');
    }
};
