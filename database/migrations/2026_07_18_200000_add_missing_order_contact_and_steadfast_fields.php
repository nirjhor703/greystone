<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'alternative_phone')) {
                $table->string('alternative_phone', 20)
                    ->nullable()
                    ->after('phone');
            }

            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email', 150)
                    ->nullable()
                    ->after('alternative_phone');
            }

            if (!Schema::hasColumn('orders', 'steadfast_sent_by_user_id')) {
                $table->foreignId('steadfast_sent_by_user_id')
                    ->nullable()
                    ->after('sent_to_steadfast_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'steadfast_response')) {
                $table->json('steadfast_response')
                    ->nullable()
                    ->after('steadfast_sent_by_user_id');
            }

            if (!Schema::hasColumn('orders', 'steadfast_error')) {
                $table->text('steadfast_error')
                    ->nullable()
                    ->after('steadfast_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'steadfast_sent_by_user_id')) {
                $table->dropForeign([
                    'steadfast_sent_by_user_id',
                ]);
            }

            $columns = [
                'steadfast_error',
                'steadfast_response',
                'steadfast_sent_by_user_id',
                'customer_email',
                'alternative_phone',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
