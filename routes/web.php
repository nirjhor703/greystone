<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SweetCoolInquiryController as AdminSweetCoolInquiryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SweetCoolInquiryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('brand.show', [
        'slug' => 'grey-stone',
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard redirect
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect()->to(auth()->user()->firstAllowedAdminUrl());
})
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->middleware('admin.active')
    ->group(function () {
        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )
            ->middleware('admin.permission:dashboard.view')
            ->name('dashboard');

        Route::get(
            '/employee-dashboard',
            [EmployeeDashboardController::class, 'index']
        )->name('employee-dashboard.index');

        Route::get(
            '/notifications',
            [AdminNotificationController::class, 'index']
        )
            ->middleware('admin.permission:notifications.view|stock_notifications.view')
            ->name('notifications.index');

        Route::post(
            '/notifications/mark-all-read',
            [AdminNotificationController::class, 'markAllRead']
        )
            ->middleware('admin.permission:notifications.update|stock_notifications.update')
            ->name('notifications.mark-all-read');

        Route::delete(
            '/notifications/bulk-delete',
            [AdminNotificationController::class, 'bulkDestroy']
        )
            ->middleware('admin.permission:notifications.delete|stock_notifications.delete')
            ->name('notifications.bulk-destroy');

        Route::get(
            '/notifications/{notification}/open',
            [AdminNotificationController::class, 'open']
        )
            ->whereNumber('notification')
            ->middleware('admin.permission:notifications.view|stock_notifications.view')
            ->name('notifications.open');

        Route::get(
            '/customers',
            [AdminCustomerController::class, 'index']
        )
            ->middleware('admin.permission:customers.view')
            ->name('customers.index');

        Route::get(
            '/sweet-cool',
            [AdminSweetCoolInquiryController::class, 'index']
        )
            ->middleware('admin.permission:sweet_cool.view')
            ->name('sweet-cool.index');

        Route::get(
            '/reports',
            [AdminReportController::class, 'index']
        )
            ->middleware('admin.permission:reports.view')
            ->name('reports.index');

        Route::get(
            '/reports/export',
            [AdminReportController::class, 'export']
        )
            ->middleware('admin.permission:reports.export')
            ->name('reports.export');

        Route::get(
            '/settings',
            [AdminSettingController::class, 'index']
        )
            ->middleware('admin.permission:settings.view')
            ->name('settings.index');

        Route::patch(
            '/settings/profile',
            [AdminSettingController::class, 'updateProfile']
        )
            ->middleware('admin.permission:settings.update')
            ->name('settings.profile');

        Route::put(
            '/settings/password',
            [AdminSettingController::class, 'updatePassword']
        )
            ->middleware('admin.permission:settings.update')
            ->name('settings.password');

        Route::get(
            '/admins',
            [AdminUserController::class, 'index']
        )
            ->middleware('admin.permission:admin_users.view')
            ->name('admin-users.index');

        Route::post(
            '/admins/verify-root-passcode',
            [AdminUserController::class, 'verifyRootPasscode']
        )
            ->middleware('admin.permission:admin_users.manage_admins')
            ->name('admin-users.verify-root-passcode');

        Route::post(
            '/admins',
            [AdminUserController::class, 'store']
        )
            ->middleware('admin.permission:admin_users.create')
            ->name('admin-users.store');

        Route::put(
            '/admins/{adminUser}',
            [AdminUserController::class, 'update']
        )
            ->whereNumber('adminUser')
            ->middleware('admin.permission:admin_users.update')
            ->name('admin-users.update');

        Route::delete(
            '/admins/{adminUser}',
            [AdminUserController::class, 'destroy']
        )
            ->whereNumber('adminUser')
            ->middleware('admin.permission:admin_users.delete')
            ->name('admin-users.destroy');

        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/brands',
            [AdminBrandController::class, 'index']
        )
            ->middleware('admin.permission:brands.view')
            ->name('brands.index');

        Route::post(
            '/brands',
            [AdminBrandController::class, 'store']
        )
            ->middleware('admin.permission:brands.create')
            ->name('brands.store');

        Route::get(
            '/brands/{brand}',
            [AdminBrandController::class, 'show']
        )
            ->whereNumber('brand')
            ->middleware('admin.permission:brands.view')
            ->name('brands.show');

        Route::post(
            '/brands/{brand}',
            [AdminBrandController::class, 'update']
        )
            ->whereNumber('brand')
            ->middleware('admin.permission:brands.update')
            ->name('brands.update');

        Route::delete(
            '/brands/{brand}',
            [AdminBrandController::class, 'destroy']
        )
            ->whereNumber('brand')
            ->middleware('admin.permission:brands.delete')
            ->name('brands.destroy');

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/categories',
            [AdminCategoryController::class, 'index']
        )
            ->middleware('admin.permission:categories.view')
            ->name('categories.index');

        Route::post(
            '/categories',
            [AdminCategoryController::class, 'store']
        )
            ->middleware('admin.permission:categories.create')
            ->name('categories.store');

        Route::get(
            '/categories/{category}',
            [AdminCategoryController::class, 'show']
        )
            ->whereNumber('category')
            ->middleware('admin.permission:categories.view')
            ->name('categories.show');

        Route::post(
            '/categories/{category}',
            [AdminCategoryController::class, 'update']
        )
            ->whereNumber('category')
            ->middleware('admin.permission:categories.update')
            ->name('categories.update');

        Route::delete(
            '/categories/{category}',
            [AdminCategoryController::class, 'destroy']
        )
            ->whereNumber('category')
            ->middleware('admin.permission:categories.delete')
            ->name('categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/products',
            [AdminProductController::class, 'index']
        )
            ->middleware('admin.permission:products.view')
            ->name('products.index');

        Route::post(
            '/products',
            [AdminProductController::class, 'store']
        )
            ->middleware('admin.permission:products.create')
            ->name('products.store');

        Route::get(
            '/products/{product}',
            [AdminProductController::class, 'show']
        )
            ->whereNumber('product')
            ->middleware('admin.permission:products.view')
            ->name('products.show');

        Route::post(
            '/products/{product}',
            [AdminProductController::class, 'update']
        )
            ->whereNumber('product')
            ->middleware('admin.permission:products.update')
            ->name('products.update');

        Route::delete(
            '/products/{product}',
            [AdminProductController::class, 'destroy']
        )
            ->whereNumber('product')
            ->middleware('admin.permission:products.delete')
            ->name('products.destroy');

        /*
        |--------------------------------------------------------------------------
        | Coupons
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/coupons',
            [AdminCouponController::class, 'index']
        )
            ->middleware('admin.permission:coupons.view')
            ->name('coupons.index');

        Route::post(
            '/coupons',
            [AdminCouponController::class, 'store']
        )
            ->middleware('admin.permission:coupons.create')
            ->name('coupons.store');

        Route::get(
            '/coupons/{coupon}',
            [AdminCouponController::class, 'show']
        )
            ->whereNumber('coupon')
            ->middleware('admin.permission:coupons.view')
            ->name('coupons.show');

        Route::post(
            '/coupons/{coupon}',
            [AdminCouponController::class, 'update']
        )
            ->whereNumber('coupon')
            ->middleware('admin.permission:coupons.update')
            ->name('coupons.update');

        Route::delete(
            '/coupons/{coupon}',
            [AdminCouponController::class, 'destroy']
        )
            ->whereNumber('coupon')
            ->middleware('admin.permission:coupons.delete')
            ->name('coupons.destroy');

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders',
            [AdminOrderController::class, 'index']
        )
            ->middleware('admin.permission:orders.view')
            ->name('orders.index');

        Route::post(
            '/orders',
            [AdminOrderController::class, 'store']
        )
            ->middleware('admin.permission:orders.create')
            ->name('orders.store');

        Route::get(
            '/orders/{order}',
            [AdminOrderController::class, 'show']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.view')
            ->name('orders.show');

        Route::post(
            '/orders/{order}',
            [AdminOrderController::class, 'update']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.update')
            ->name('orders.update');

        Route::delete(
            '/orders/{order}',
            [AdminOrderController::class, 'destroy']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.delete')
            ->name('orders.destroy');

        Route::post(
            '/orders/{order}/send-to-steadfast',
            [AdminOrderController::class, 'sendToSteadfast']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.send_steadfast')
            ->name('orders.send-to-steadfast');

        Route::post(
            '/orders/{order}/qc-passed',
            [AdminOrderController::class, 'qcPassed']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.update')
            ->name('orders.qc-passed');

        Route::post(
            '/orders/{order}/qc-issue',
            [AdminOrderController::class, 'qcIssue']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.update')
            ->name('orders.qc-issue');

        Route::post(
            '/orders/{order}/resolve-qc-issue',
            [AdminOrderController::class, 'resolveQcIssue']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.update')
            ->name('orders.resolve-qc-issue');

        Route::get(
            '/orders/{order}/steadfast',
            [AdminOrderController::class, 'steadfastDetails']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.view')
            ->name('orders.steadfast');

        Route::get(
            '/orders/{order}/invoice',
            [AdminOrderController::class, 'invoice']
        )
            ->whereNumber('order')
            ->middleware('admin.permission:orders.view')
            ->name('orders.invoice');
    });

/*
|--------------------------------------------------------------------------
| Profile routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Storefront cart
|--------------------------------------------------------------------------
*/

Route::prefix('cart')
    ->name('cart.')
    ->group(function () {
        Route::get(
            '/',
            [CartController::class, 'index']
        )->name('index');

        Route::post(
            '/',
            [CartController::class, 'store']
        )->name('store');

        Route::patch(
            '/{cartKey}',
            [CartController::class, 'update']
        )->name('update');

        Route::delete(
            '/{cartKey}',
            [CartController::class, 'destroy']
        )->name('destroy');

        Route::delete(
            '/',
            [CartController::class, 'clear']
        )->name('clear');
    });

Route::post(
    '/orders',
    [OrderController::class, 'store']
)->name('orders.store');

Route::get(
    '/orders/{order}/invoice',
    [OrderController::class, 'invoice']
)
    ->whereNumber('order')
    ->middleware('signed')
    ->name('orders.invoice');

Route::post(
    '/coupons/apply',
    [CouponController::class, 'apply']
)->name('coupons.apply');

Route::get(
    '/coupons/available',
    [CouponController::class, 'available']
)->name('coupons.available');

Route::get(
    '/coupons/popup',
    [CouponController::class, 'popup']
)->name('coupons.popup');

Route::delete(
    '/coupons/remove',
    [CouponController::class, 'remove']
)->name('coupons.remove');

Route::post(
    '/sweet-cool',
    [SweetCoolInquiryController::class, 'store']
)->name('sweet-cool.store');

/*
|--------------------------------------------------------------------------
| Storefront routes
|--------------------------------------------------------------------------
*/

Route::get(
    '/{brandSlug}/products/{productSlug}',
    [ProductController::class, 'show']
)
    ->whereIn('brandSlug', [
        'grey-stone',
        'blue-shades',
        'pink-touch',
    ])
    ->name('products.show');

Route::get(
    '/{slug}',
    [BrandController::class, 'show']
)
    ->whereIn('slug', [
        'grey-stone',
        'blue-shades',
        'pink-touch',
    ])
    ->name('brand.show');

/*
|--------------------------------------------------------------------------
| Authentication routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
