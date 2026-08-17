<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(
        string $brandSlug,
        string $productSlug
    ) {
        $brand = Brand::query()
            ->where('slug', $brandSlug)
            ->where('is_active', true)
            ->firstOrFail();
    
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->where('brand_id', $brand->id)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();
    
        $product = Product::query()
            ->with([
                'category',
                'primaryImage',
                'images',
                'variants',
            ])
            ->where('brand_id', $brand->id)
            ->where('slug', $productSlug)
            ->where('status', Product::STATUS_ACTIVE)
            ->whereHas('category', function ($query) {
                $query->where('status', 'Active');
            })
            ->firstOrFail();
    
        $relatedProducts = Product::query()
            ->with([
                'category',
                'primaryImage',
                'images',
                'variants',
            ])
            ->where('brand_id', $brand->id)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->latest()
            ->take(4)
            ->get();

        $featuredProducts = Product::query()
            ->where('brand_id', $brand->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->where('is_featured', true)
            ->limit(1)
            ->get();

        $newArrivalProducts = Product::query()
            ->where('brand_id', $brand->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->where('is_new_arrival', true)
            ->limit(1)
            ->get();

        $searchProducts = Product::query()
            ->with([
                'brand',
                'category',
                'primaryImage',
                'images',
                'variants',
            ])
            ->where('status', Product::STATUS_ACTIVE)
            ->whereHas('brand', function ($query) {
                $query->where('is_active', true);
            })
            ->whereHas('category', function ($query) {
                $query->where('status', 'Active');
            })
            ->orderByRaw(
                'CASE WHEN brand_id = ? THEN 0 ELSE 1 END',
                [$brand->id]
            )
            ->orderBy('brand_id')
            ->orderByDesc('id')
            ->get();

        return view('products.show', [
            'brand' => $brand,
            'brands' => $brands,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newArrivalProducts' => $newArrivalProducts,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'searchProducts' => $searchProducts,
        ]);
    }
}
