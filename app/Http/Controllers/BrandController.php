<?php

namespace App\Http\Controllers;

use App\Models\Brand;

class BrandController extends Controller
{
    public function show(string $slug)
    {
        $brand = Brand::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $brands = Brand::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('brands.show', compact('brand', 'brands'));
    }
}