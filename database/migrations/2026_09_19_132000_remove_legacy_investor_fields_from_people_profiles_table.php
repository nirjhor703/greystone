<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'investor_enabled',
            'investment_amount',
            'investment_date',
            'investment_share_percent',
            'investment_monthly_return',
            'investment_note',
        ];

        $existing = array_filter($columns, fn (string $column): bool => Schema::hasColumn('people_profiles', $column));

        if ($existing === []) {
            return;
        }

        Schema::table('people_profiles', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }

    public function down(): void
    {
        Schema::table('people_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('people_profiles', 'investor_enabled')) {
                $table->boolean('investor_enabled')->default(false)->after('referral_enabled');
            }
            if (! Schema::hasColumn('people_profiles', 'investment_amount')) {
                $table->decimal('investment_amount', 14, 2)->default(0)->after('investor_enabled');
            }
            if (! Schema::hasColumn('people_profiles', 'investment_date')) {
                $table->date('investment_date')->nullable()->after('investment_amount');
            }
            if (! Schema::hasColumn('people_profiles', 'investment_share_percent')) {
                $table->decimal('investment_share_percent', 8, 4)->nullable()->after('investment_date');
            }
            if (! Schema::hasColumn('people_profiles', 'investment_monthly_return')) {
                $table->decimal('investment_monthly_return', 14, 2)->default(0)->after('investment_share_percent');
            }
            if (! Schema::hasColumn('people_profiles', 'investment_note')) {
                $table->text('investment_note')->nullable()->after('investment_monthly_return');
            }
        });
    }
};
