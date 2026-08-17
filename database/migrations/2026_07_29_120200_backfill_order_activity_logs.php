<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_activity_logs')) {
            return;
        }

        $this->backfill(
            'confirmed_by_user_id',
            'confirmed_at',
            'order_confirmed',
            null,
            'Confirmed'
        );

        $this->backfill(
            'qc_by_user_id',
            'qc_checked_at',
            'qc_passed',
            null,
            'passed',
            ['qc_status' => 'passed']
        );

        $this->backfill(
            'qc_by_user_id',
            'qc_checked_at',
            'qc_issue',
            null,
            'issue',
            ['qc_status' => 'issue'],
            'qc_issue_note'
        );

        $this->backfill(
            'qc_resolved_by_user_id',
            'qc_resolved_at',
            'qc_resolved',
            'issue',
            'not_checked'
        );

        $this->backfill(
            'steadfast_sent_by_user_id',
            'sent_to_steadfast_at',
            'sent_steadfast',
            null,
            'sent'
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('order_activity_logs')) {
            return;
        }

        DB::table('order_activity_logs')
            ->where('meta->backfilled', true)
            ->delete();
    }

    private function backfill(
        string $userColumn,
        string $dateColumn,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null,
        array $where = [],
        ?string $noteColumn = null
    ): void {
        if (
            ! Schema::hasColumn('orders', $userColumn)
            || ! Schema::hasColumn('orders', $dateColumn)
        ) {
            return;
        }

        $query = DB::table('orders')
            ->select([
                'id',
                $userColumn,
                $dateColumn,
                $noteColumn ?: DB::raw('null as note_value'),
            ])
            ->whereNotNull($userColumn)
            ->whereNotNull($dateColumn);

        foreach ($where as $column => $value) {
            if (Schema::hasColumn('orders', $column)) {
                $query->where($column, $value);
            }
        }

        $query->orderBy('id')->chunkById(200, function ($orders) use (
            $userColumn,
            $dateColumn,
            $action,
            $oldValue,
            $newValue,
            $noteColumn
        ): void {
            $now = now();

            foreach ($orders as $order) {
                $exists = DB::table('order_activity_logs')
                    ->where('order_id', $order->id)
                    ->where('user_id', $order->{$userColumn})
                    ->where('action', $action)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('order_activity_logs')->insert([
                    'order_id' => $order->id,
                    'user_id' => $order->{$userColumn},
                    'action' => $action,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'note' => $noteColumn ? $order->{$noteColumn} : null,
                    'meta' => json_encode(['backfilled' => true]),
                    'created_at' => $order->{$dateColumn} ?: $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
};
