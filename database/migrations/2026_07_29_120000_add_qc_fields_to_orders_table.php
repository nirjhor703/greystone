<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'confirmed_by_user_id')) {
                $table
                    ->foreignId('confirmed_by_user_id')
                    ->nullable()
                    ->after('payment_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'confirmed_at')) {
                $table
                    ->timestamp('confirmed_at')
                    ->nullable()
                    ->after('confirmed_by_user_id');
            }

            if (! Schema::hasColumn('orders', 'qc_status')) {
                $table
                    ->string('qc_status', 30)
                    ->default('not_checked')
                    ->after('confirmed_at');
            }

            if (! Schema::hasColumn('orders', 'qc_by_user_id')) {
                $table
                    ->foreignId('qc_by_user_id')
                    ->nullable()
                    ->after('qc_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'qc_checked_at')) {
                $table
                    ->timestamp('qc_checked_at')
                    ->nullable()
                    ->after('qc_by_user_id');
            }

            if (! Schema::hasColumn('orders', 'qc_issue_note')) {
                $table
                    ->text('qc_issue_note')
                    ->nullable()
                    ->after('qc_checked_at');
            }

            if (! Schema::hasColumn('orders', 'qc_resolved_by_user_id')) {
                $table
                    ->foreignId('qc_resolved_by_user_id')
                    ->nullable()
                    ->after('qc_issue_note')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'qc_resolved_at')) {
                $table
                    ->timestamp('qc_resolved_at')
                    ->nullable()
                    ->after('qc_resolved_by_user_id');
            }
        });

        DB::table('orders')
            ->whereNull('qc_status')
            ->update([
                'qc_status' => 'not_checked',
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach ([
                'qc_resolved_by_user_id',
                'qc_by_user_id',
                'confirmed_by_user_id',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('orders', 'confirmed_at') ? 'confirmed_at' : null,
                Schema::hasColumn('orders', 'qc_status') ? 'qc_status' : null,
                Schema::hasColumn('orders', 'qc_checked_at') ? 'qc_checked_at' : null,
                Schema::hasColumn('orders', 'qc_issue_note') ? 'qc_issue_note' : null,
                Schema::hasColumn('orders', 'qc_resolved_at') ? 'qc_resolved_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
