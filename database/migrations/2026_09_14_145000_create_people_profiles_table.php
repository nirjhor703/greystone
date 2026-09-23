<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('profile_type', 40)->default('marketer');
            $table->string('name')->default('New Profile');
            $table->string('title')->nullable();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referrer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_type', 40)->default('mixed');
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->decimal('gift_balance', 12, 2)->default(0);
            $table->text('story')->nullable();
            $table->text('tax_note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('people_profiles')->insert([
            [
                'profile_type' => 'marketer',
                'name' => 'Marketer / Referrer',
                'title' => 'Referral Marketer',
                'payment_type' => 'commission',
                'story' => 'People who bring customers through referral codes.',
                'tax_note' => 'Commission based payment. Keep receipt linked with order/referral records.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'profile_type' => 'digital_marketer',
                'name' => 'Digital Marketer',
                'title' => 'Digital Marketer',
                'payment_type' => 'mixed',
                'story' => 'People responsible for online promotion, ads, content and campaigns.',
                'tax_note' => 'Payment can be commission, contract or gift depending on agreement.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'profile_type' => 'developer',
                'name' => 'Developer',
                'title' => 'Developer',
                'payment_type' => 'mixed',
                'story' => 'Development contribution, technical support and ongoing system work.',
                'tax_note' => 'Use payment tags and notes so every receipt explains the work relationship.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'profile_type' => 'co_founder',
                'name' => 'Co-Founder',
                'title' => 'Co-Founder & Vice President',
                'payment_type' => 'gift',
                'story' => 'Second authority and long-term business partner profile.',
                'tax_note' => 'Keep payments documented clearly as business share, support, gift or agreed role payment.',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('people_profiles');
    }
};
