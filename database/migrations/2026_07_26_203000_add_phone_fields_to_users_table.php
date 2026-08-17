<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'personal_phone')) {
                $table
                    ->string('personal_phone', 20)
                    ->nullable()
                    ->after('email');
            }

            if (! Schema::hasColumn('users', 'optional_phone')) {
                $table
                    ->string('optional_phone', 20)
                    ->nullable()
                    ->after('personal_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = [];

            if (Schema::hasColumn('users', 'optional_phone')) {
                $columns[] = 'optional_phone';
            }

            if (Schema::hasColumn('users', 'personal_phone')) {
                $columns[] = 'personal_phone';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
