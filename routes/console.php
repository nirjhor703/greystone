<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Services\SteadfastCourierService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dashboard:demo-orders', function () {
    $brands = Brand::query()
        ->where('is_active', true)
        ->orderBy('id')
        ->get();

    if ($brands->isEmpty()) {
        $this->warn('No active brands found.');

        return;
    }

    $plans = [
        [1, 2, 1, 3, 2, 4, 2, 3, 4, 5, 3, 4, 6, 5],
        [2, 1, 3, 2, 4, 3, 5, 4, 3, 6, 5, 7, 6, 8],
        [1, 1, 2, 3, 2, 3, 4, 3, 5, 4, 6, 5, 7, 6],
    ];

    $created = 0;
    $updated = 0;

    DB::transaction(function () use ($brands, $plans, &$created, &$updated): void {
        foreach ($brands->values() as $brandIndex => $brand) {
            $product = Product::query()
                ->where('brand_id', $brand->id)
                ->orderBy('id')
                ->first();

            $plan = $plans[$brandIndex] ?? $plans[array_key_last($plans)];

            foreach ($plan as $dayIndex => $orderCount) {
                $date = now()
                    ->subDays(13 - $dayIndex)
                    ->setTime(10 + ($dayIndex % 8), 15 + ($brandIndex * 8));

                for ($orderIndex = 1; $orderIndex <= $orderCount; $orderIndex++) {
                    $invoiceNumber = 'DEMO'.$brand->id.$date->format('ymd').str_pad((string) $orderIndex, 2, '0', STR_PAD_LEFT);

                    $existingOrder = Order::query()
                        ->where('invoice_number', $invoiceNumber)
                        ->first();

                    if ($existingOrder) {
                        $existingOrder
                            ->forceFill([
                                'created_at' => $date,
                                'updated_at' => $date,
                            ])
                            ->saveQuietly();

                        $existingOrder->items()
                            ->update([
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);

                        $updated++;

                        continue;
                    }

                    $quantity = (($dayIndex + $orderIndex + $brandIndex) % 3) + 1;
                    $unitPrice = (float) (
                        $product?->sale_price
                        ?: $product?->regular_price
                        ?: (850 + ($brandIndex * 180))
                    );
                    $itemsTotal = $quantity * $unitPrice;
                    $deliveryCharge = $brandIndex === 0 ? 80 : 120;

                    $order = Order::query()->create([
                        'order_number' => 'ORD'.$brand->id.$date->format('ymdHis').$orderIndex,
                        'invoice_number' => $invoiceNumber,
                        'brand_id' => $brand->id,
                        'customer_name' => 'Demo Customer '.$orderIndex,
                        'phone' => '019'.str_pad((string) ($brand->id * 10000000 + $dayIndex * 1000 + $orderIndex), 8, '0', STR_PAD_LEFT),
                        'customer_email' => 'demo'.$brand->id.$dayIndex.$orderIndex.'@example.com',
                        'delivery_area' => $brandIndex === 0 ? 'Inside Dhaka' : 'Outside Dhaka',
                        'district' => $brandIndex === 0 ? 'Dhaka' : ['Chattogram', 'Sylhet', 'Khulna'][$brandIndex % 3],
                        'area_thana' => $brandIndex === 0 ? 'Dhanmondi' : 'Sadar',
                        'road_no' => 'Road '.(($dayIndex % 9) + 1),
                        'house_no' => 'House '.(($orderIndex % 7) + 10),
                        'full_address' => 'Demo address for dashboard trend',
                        'order_note' => '[Dashboard demo trend data]',
                        'payment_method' => Order::PAYMENT_COD,
                        'items_total' => $itemsTotal,
                        'delivery_charge' => $deliveryCharge,
                        'grand_total' => $itemsTotal + $deliveryCharge,
                        'status' => match ($dayIndex % 4) {
                            0 => Order::STATUS_DELIVERED,
                            1 => Order::STATUS_CONFIRMED,
                            2 => Order::STATUS_CANCELLED,
                            default => Order::STATUS_PENDING,
                        },
                        'payment_status' => Order::PAYMENT_UNPAID,
                        'order_source' => Order::SOURCE_CART,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product?->id,
                        'product_name' => $product?->name ?: $brand->name.' Demo Item',
                        'product_code' => $product?->product_code ?: 'DEMO-'.$brand->id,
                        'product_image' => null,
                        'size' => ['M', 'L', 'XL'][($dayIndex + $orderIndex) % 3],
                        'color' => ['Black', 'Blue', 'Pink'][($brandIndex + $orderIndex) % 3],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $itemsTotal,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $order
                        ->forceFill([
                            'created_at' => $date,
                            'updated_at' => $date,
                        ])
                        ->saveQuietly();

                    $order->items()
                        ->update([
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);

                    $created++;
                }
            }
        }
    });

    $this->info("Dashboard demo orders created: {$created}; updated: {$updated}");
})->purpose('Create realistic demo orders for dashboard charts.');

Artisan::command('stock:check-low', function (
    AdminNotificationService $notifications
) {
    $result = $notifications->checkLowStockProducts();

    $this->info("Low stock products checked: {$result['checked']}");
    $this->info("Low stock notifications created: {$result['created']}");
})->purpose('Create daily low-stock admin notifications.');

Schedule::command('stock:check-low')
    ->timezone(config('app.timezone', 'Asia/Dhaka'))
    ->dailyAt('08:00')
    ->withoutOverlapping();

Artisan::command('products:sync-stock', function () {
    $synced = 0;

    Product::query()
        ->with('variants')
        ->orderBy('id')
        ->chunkById(100, function ($products) use (&$synced): void {
            foreach ($products as $product) {
                $product->syncStockFromVariants();
                $synced++;
            }
        });

    $this->info("Products stock synced: {$synced}");
})->purpose('Recalculate product stock totals from active variants.');

Artisan::command('employee-dashboard:demo-data', function () {
    $users = User::query()
        ->where('is_active', true)
        ->orderBy('id')
        ->get();

    if ($users->isEmpty()) {
        $this->warn('No active users found.');

        return;
    }

    $orders = Order::query()
        ->where('invoice_number', 'like', 'DEMO%')
        ->where('status', '!=', Order::STATUS_CANCELLED)
        ->orderBy('created_at')
        ->orderBy('id')
        ->get();

    if ($orders->isEmpty()) {
        $this->warn('No non-cancelled demo orders found. Run dashboard:demo-orders first.');

        return;
    }

    $updated = 0;

    DB::transaction(function () use ($users, $orders, &$updated): void {
        foreach ($orders->values() as $index => $order) {
            $user = $users[$index % $users->count()];
            $sentAt = $order->created_at
                ? $order->created_at->copy()->addHours(($index % 9) + 1)
                : now()->subDays($orders->count() - $index);

            $order
                ->forceFill([
                    'steadfast_sent_by_user_id' => $user->id,
                    'sent_to_steadfast_at' => $sentAt,
                    'steadfast_consignment_id' =>
                        'DEMO-CONS-'.$order->id,
                    'courier_status' => match ($index % 4) {
                        0 => 'delivered',
                        1 => 'in_transit',
                        2 => 'in_review',
                        default => 'pickup_pending',
                    },
                    'steadfast_response' => [
                        'demo' => true,
                        'assigned_to' => $user->name,
                        'assigned_at' => $sentAt->format('Y-m-d H:i:s'),
                    ],
                    'steadfast_error' => null,
                ])
                ->save();

            $updated++;
        }
    });

    $this->info("Employee dashboard demo orders assigned: {$updated}");
})->purpose('Assign demo order handling data across active users.');

Artisan::command('steadfast:sync-statuses', function (
    SteadfastCourierService $steadfast,
    AdminNotificationService $notifications
) {
    $checked = 0;
    $changed = 0;

    Order::query()
        ->whereNotNull('sent_to_steadfast_at')
        ->whereNotIn('status', [
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ])
        ->orderBy('id')
        ->chunkById(50, function ($orders) use (
            $steadfast,
            $notifications,
            &$checked,
            &$changed
        ): void {
            foreach ($orders as $order) {
                $checked++;

                try {
                    $oldStatus = $order->courier_status;
                    $response = $steadfast->deliveryStatus($order);
                    $deliveryStatus = data_get(
                        $response,
                        'delivery_status'
                    );

                    if (!$deliveryStatus) {
                        continue;
                    }

                    $orderStatus = match ($deliveryStatus) {
                        'delivered',
                        'delivered_approval_pending' =>
                            Order::STATUS_DELIVERED,

                        'cancelled',
                        'cancelled_approval_pending' =>
                            Order::STATUS_CANCELLED,

                        default => $order->status,
                    };

                    $steadfastResponse = $order->steadfast_response ?: [];

                    if (!is_array($steadfastResponse)) {
                        $steadfastResponse = [];
                    }

                    $order->update([
                        'courier_status' => $deliveryStatus,
                        'status' => $orderStatus,
                        'steadfast_response' => [
                            ...$steadfastResponse,
                            'latest_status_response' => $response,
                            'latest_status_checked_at' => now()
                                ->format('Y-m-d H:i:s'),
                        ],
                        'steadfast_error' => null,
                    ]);

                    $order->refresh();

                    if ($oldStatus !== $deliveryStatus) {
                        $changed++;

                        $notifications->courierStatusChanged(
                            $order,
                            $oldStatus,
                            $deliveryStatus
                        );
                    }
                } catch (Throwable $exception) {
                    report($exception);

                    $order->update([
                        'steadfast_error' => $exception->getMessage(),
                    ]);
                }
            }
        });

    $this->info(
        "Steadfast orders checked: {$checked}; changed: {$changed}"
    );
})->purpose('Sync sent order statuses from Steadfast.');

Schedule::command('steadfast:sync-statuses')
    ->everyFiveMinutes();
