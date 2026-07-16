<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('brand.show', [
        'slug' => 'grey-stone',
    ]);
});

Route::get('/{slug}', [BrandController::class, 'show'])
    ->whereIn('slug', [
        'grey-stone',
        'blue-shades',
        'pink-touch',
    ])
    ->name('brand.show');

/*
|--------------------------------------------------------------------------
| Dashboard redirect
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Profile routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';