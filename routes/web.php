<?php

use App\Http\Controllers\BrandController;

Route::get('/', function () {
    return redirect('/grey-stone');
});

Route::get('/{slug}', [BrandController::class, 'show'])
    ->whereIn('slug', ['grey-stone', 'blue-shades', 'pink-touch'])
    ->name('brand.show');
