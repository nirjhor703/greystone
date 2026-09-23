<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_investors', function (Blueprint $table): void {
            $table->string('lifecycle_status', 30)->default('active')->after('is_active')->index();
            $table->timestamp('closed_at')->nullable()->after('lifecycle_status');
            $table->decimal('due_capital', 14, 2)->default(0)->after('closed_at');
            $table->decimal('due_profit', 14, 2)->default(0)->after('due_capital');
            $table->decimal('due_total', 14, 2)->default(0)->after('due_profit');
        });
    }

    public function down(): void
    {
        Schema::table('investment_investors', function (Blueprint $table): void {
            $table->dropColumn(['lifecycle_status', 'closed_at', 'due_capital', 'due_profit', 'due_total']);
        });
    }
};
