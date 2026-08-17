<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    public function checkLowStockProducts(): array
    {
        $created = 0;
        $checked = 0;

        Product::query()
            ->with('brand')
            ->where('status', Product::STATUS_ACTIVE)
            ->where('stock_quantity', '<', 10)
            ->orderBy('id')
            ->chunkById(100, function ($products) use (
                &$created,
                &$checked
            ): void {
                foreach ($products as $product) {
                    $checked++;

                    if ($this->lowStockReminder($product)) {
                        $created++;
                    }
                }
            });

        return [
            'checked' => $checked,
            'created' => $created,
        ];
    }

    public function newOrder(Order $order): AdminNotification
    {
        return AdminNotification::create([
            'category' => AdminNotification::CATEGORY_MAIN,
            'type' => AdminNotification::TYPE_NEW_ORDER,
            'title' => 'New order received',
            'message' => "{$order->order_number} has been placed by {$order->customer_name}.",
            'link_url' => route('admin.orders.index', [
                'focus_order' => $order->id,
            ], false),
            'notifiable_type' => Order::class,
            'notifiable_id' => $order->id,
            'meta' => [
                'invoice_number' => $order->invoice_number,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    public function courierStatusChanged(
        Order $order,
        ?string $oldStatus,
        string $newStatus
    ): ?AdminNotification {
        if ($oldStatus === $newStatus) {
            return null;
        }

        return AdminNotification::create([
            'category' => AdminNotification::CATEGORY_MAIN,
            'type' => AdminNotification::TYPE_COURIER_STATUS,
            'title' => 'Steadfast status updated',
            'message' => "{$order->order_number} is {$newStatus} by Steadfast.",
            'link_url' => route('admin.orders.index', [
                'focus_order' => $order->id,
            ], false),
            'notifiable_type' => Order::class,
            'notifiable_id' => $order->id,
            'meta' => [
                'invoice_number' => $order->invoice_number,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);
    }

    public function lowStockReminder(Product $product): ?AdminNotification
    {
        $totalStock = (int) $product->stock_quantity;

        if ($totalStock >= 10) {
            return null;
        }

        return DB::transaction(function () use (
            $product,
            $totalStock
        ): ?AdminNotification {
            $alreadyExists = AdminNotification::query()
                ->where('type', AdminNotification::TYPE_LOW_STOCK)
                ->where('notifiable_type', Product::class)
                ->where('notifiable_id', $product->id)
                ->whereDate('reminder_date', today())
                ->lockForUpdate()
                ->exists();

            if ($alreadyExists) {
                return null;
            }

            return AdminNotification::create([
                'category' => AdminNotification::CATEGORY_STOCK,
                'type' => AdminNotification::TYPE_LOW_STOCK,
                'title' => 'Low stock reminder',
                'message' =>
                    "Product {$product->name}({$product->product_code}) from {$product->brand?->name} is less than 10, currently {$totalStock} in stock. Please restock.",
                'link_url' => route('admin.products.index', [
                    'focus_product' => $product->id,
                ], false),
                'notifiable_type' => Product::class,
                'notifiable_id' => $product->id,
                'meta' => [
                    'product_name' => $product->name,
                    'product_code' => $product->product_code,
                    'brand_name' => $product->brand?->name,
                    'stock_quantity' => $totalStock,
                ],
                'reminder_date' => today(),
            ]);
        });
    }

    public function adminPermissionsChanged(
        User $adminUser,
        User $changedBy,
        array $oldPermissions,
        array $newPermissions,
        string $oldRole,
        string $newRole,
        bool $oldRoot,
        bool $newRoot
    ): ?AdminNotification {
        $oldPermissions = User::sanitizePermissions($oldPermissions);
        $newPermissions = User::sanitizePermissions($newPermissions);

        sort($oldPermissions);
        sort($newPermissions);

        $added = array_values(array_diff($newPermissions, $oldPermissions));
        $removed = array_values(array_diff($oldPermissions, $newPermissions));

        $roleChanged = $oldRole !== $newRole;
        $rootChanged = $oldRoot !== $newRoot;

        if (
            $added === []
            && $removed === []
            && ! $roleChanged
            && ! $rootChanged
        ) {
            return null;
        }

        $parts = [];

        if ($added !== []) {
            $parts[] = 'given: '.implode(', ', $added);
        }

        if ($removed !== []) {
            $parts[] = 'removed: '.implode(', ', $removed);
        }

        if ($roleChanged) {
            $parts[] = "role: {$oldRole} to {$newRole}";
        }

        if ($rootChanged) {
            $parts[] = $newRoot
                ? 'root access enabled'
                : 'root access disabled';
        }

        return AdminNotification::create([
            'category' => AdminNotification::CATEGORY_MAIN,
            'type' => AdminNotification::TYPE_PERMISSION_CHANGED,
            'title' => 'Admin permission changed',
            'message' =>
                "{$changedBy->name} changed {$adminUser->name}'s permissions: "
                .implode('; ', $parts).'.',
            'link_url' => route('admin.admin-users.index', [
                'focus_admin' => $adminUser->id,
            ], false),
            'notifiable_type' => User::class,
            'notifiable_id' => $adminUser->id,
            'meta' => [
                'admin_user_id' => $adminUser->id,
                'admin_user_name' => $adminUser->name,
                'changed_by_id' => $changedBy->id,
                'changed_by_name' => $changedBy->name,
                'added_permissions' => $added,
                'removed_permissions' => $removed,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'old_root' => $oldRoot,
                'new_root' => $newRoot,
            ],
        ]);
    }
}
