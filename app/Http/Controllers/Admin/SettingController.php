<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $permissions = $user->permissions ?? [];
        $handledOrders = Order::query()
            ->where('steadfast_sent_by_user_id', $user->id);
        $deliveredOrders = (clone $handledOrders)
            ->where('status', Order::STATUS_DELIVERED);
        $handledOrderIds = (clone $handledOrders)->pluck('id');

        return view('admin.settings.index', [
            'user' => $user,
            'permissionsCount' => $user->is_root_admin
                ? 'Full'
                : number_format(count($permissions)),
            'activity' => [
                'handled_orders' => (clone $handledOrders)->count(),
                'delivered_orders' => (clone $deliveredOrders)->count(),
                'handled_products' => OrderItem::query()
                    ->whereIn('order_id', $handledOrderIds)
                    ->sum('quantity'),
                'handled_revenue' => (clone $deliveredOrders)
                    ->sum('grand_total'),
                'permission_updates' => AdminNotification::query()
                    ->where('type', AdminNotification::TYPE_PERMISSION_CHANGED)
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $user->id)
                    ->count(),
            ],
            'system' => [
                'timezone' => config('app.timezone'),
                'app_url' => config('app.url'),
                'environment' => app()->environment(),
                'total_brands' => Brand::query()->count(),
                'total_categories' => Category::query()->count(),
                'total_products' => Product::query()->count(),
                'total_coupons' => Coupon::query()->count(),
                'total_orders' => Order::query()->count(),
                'unread_notifications' => AdminNotification::query()
                    ->unread()
                    ->count(),
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('settingsProfile', [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($request->user()->id),
            ],
        ]);

        $user = $request->user();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('settings_status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('settingsPassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                Password::defaults(),
                'confirmed',
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('settings_status', 'Password updated successfully.');
    }
}
