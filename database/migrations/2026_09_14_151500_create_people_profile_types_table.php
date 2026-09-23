<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people_profile_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $types = [
            'Marketer / Referrer',
            'Digital Marketer',
            'Developer',
            'Co-Founder / Vice President',
        ];

        foreach ($types as $index => $name) {
            DB::table('people_profile_types')->insert([
                'name' => $name,
                'slug' => Str::slug($name, '_'),
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('people_profiles')->where('profile_type', 'marketer')->update(['profile_type' => 'marketer_referrer']);
        DB::table('people_profiles')->where('profile_type', 'co_founder')->update(['profile_type' => 'co_founder_vice_president']);
    }

    public function down(): void
    {
        DB::table('people_profiles')->where('profile_type', 'marketer_referrer')->update(['profile_type' => 'marketer']);
        DB::table('people_profiles')->where('profile_type', 'co_founder_vice_president')->update(['profile_type' => 'co_founder']);

        Schema::dropIfExists('people_profile_types');
    }
};
