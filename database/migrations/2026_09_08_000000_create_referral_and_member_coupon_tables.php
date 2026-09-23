<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('reward_amount', 12, 2)->default(50);
            $table->timestamps();
        });

        Schema::create('referrers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->nullable()->unique()->constrained('members')->nullOnDelete();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->decimal('balance', 12, 2)->default(0);
            $table->unsignedInteger('successful_referrals')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('members', function (Blueprint $table): void {
            $table->foreignId('referred_by_id')->nullable()->after('id')->constrained('referrers')->nullOnDelete();
        });

        Schema::create('member_coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->string('source', 30)->default('referral');
            $table->string('status', 20)->default('available');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->unique(['member_id', 'coupon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_coupons');
        Schema::table('members', fn (Blueprint $table) => $table->dropConstrainedForeignId('referred_by_id'));
        Schema::dropIfExists('referrers');
        Schema::dropIfExists('referral_settings');
    }
};
