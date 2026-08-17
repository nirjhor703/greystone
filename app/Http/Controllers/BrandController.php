<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $categories = Category::query()
            ->where('brand_id', $brand->id)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $selectedAudience = Str::lower(
            (string) $request->query('audience', Product::AUDIENCE_MEN)
        );

        if (!in_array($selectedAudience, [
            Product::AUDIENCE_MEN,
            Product::AUDIENCE_WOMEN,
        ], true)) {
            $selectedAudience = Product::AUDIENCE_MEN;
        }

        $selectedCategoryKey = Str::slug(
            (string) $request->query('category', '')
        );

        $selectedCategory = $categories->first(
            fn (Category $category) => Str::slug(
                $category->slug ?: $category->name
            ) === $selectedCategoryKey
        );

        $baseProductQuery = Product::query()
            ->with([
                'brand',
                'category',
                'primaryImage',
                'images',
                'variants',
            ])
            ->where('brand_id', $brand->id)
            ->where('status', Product::STATUS_ACTIVE)
            ->whereHas('category', function ($query) {
                $query->where('status', 'Active');
            });

        $allProductsQuery = Product::query()
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
            ->orderByDesc('id');

        $applyAudienceFilter = function ($query) use ($selectedAudience) {
            $query->where(function ($audienceQuery) use ($selectedAudience) {
                $audienceQuery
                    ->where('audience', $selectedAudience)
                    ->orWhere('audience', Product::AUDIENCE_BOTH)
                    ->orWhereNull('audience');
            });
        };

        $applyCategoryFilter = function ($query) use ($selectedCategoryKey) {
            if ($selectedCategoryKey === '') {
                return;
            }

            $query->whereHas('category', function ($categoryQuery) use ($selectedCategoryKey) {
                $categoryQuery->whereRaw(
                    'LOWER(COALESCE(slug, name)) = ?',
                    [Str::lower($selectedCategoryKey)]
                )->orWhereRaw(
                    'LOWER(REPLACE(COALESCE(slug, name), " ", "-")) = ?',
                    [Str::lower($selectedCategoryKey)]
                );
            });
        };

        $applyAudienceFilter($baseProductQuery);
        $applyAudienceFilter($allProductsQuery);
        $applyCategoryFilter($baseProductQuery);
        $applyCategoryFilter($allProductsQuery);

        $featuredProducts = (clone $baseProductQuery)
            ->where('is_featured', true)
            ->latest('id')
            ->limit(8)
            ->get();

        $newArrivalProducts = (clone $baseProductQuery)
            ->where('is_new_arrival', true)
            ->latest('id')
            ->limit(8)
            ->get();

        $searchProducts = (clone $allProductsQuery)
            ->get();

        $products = (clone $allProductsQuery)
            ->paginate(12)
            ->withQueryString();

        return view('brands.show', compact(
            'brand',
            'brands',
            'categories',
            'featuredProducts',
            'newArrivalProducts',
            'products',
            'searchProducts',
            'selectedAudience',
            'selectedCategory',
            'selectedCategoryKey'
        ));
    }
}
