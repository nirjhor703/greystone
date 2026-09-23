<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_milestones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('step')->unique();
            $table->string('title')->default('New reward');
            $table->string('description')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('purchase_milestones')->insert(collect(range(1, 10))->map(fn (int $step): array => [
            'step' => $step,
            'title' => $step === 1 ? 'Journey begins' : 'Reward '.$step,
            'description' => $step === 1 ? 'Your first purchase starts the Grey Stone journey.' : 'A new surprise is waiting at this step.',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_milestones');
    }
};
