<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereIn('status', ['Processing', 'Shipped'])
            ->update([
                'status' => 'Confirmed',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
