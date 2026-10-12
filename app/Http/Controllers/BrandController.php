<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\PremiumPackagingTier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $overrideProductIds = $newArrivalProducts
            ->filter(fn (Product $product): bool => (bool) $product->premium_packaging_tier_override)
            ->pluck('id');

        $packagingTiers = PremiumPackagingTier::query()
            ->where('is_active', true)
            ->where(function ($query) use ($overrideProductIds): void {
                $query->whereNull('product_id');

                if ($overrideProductIds->isNotEmpty()) {
                    $query->orWhereIn('product_id', $overrideProductIds);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (PremiumPackagingTier $tier): string => $tier->product_id === null
                ? 'shared'
                : (string) $tier->product_id);

        $sharedPackagingTiers = $packagingTiers->get('shared', collect());
        $newArrivalPackagingTiers = $newArrivalProducts->mapWithKeys(
            function (Product $product) use ($packagingTiers, $sharedPackagingTiers): array {
                $tiers = $product->premium_packaging_tier_override
                    ? $packagingTiers->get((string) $product->id, collect())
                    : $sharedPackagingTiers;

                return [$product->id => $tiers->map(fn (PremiumPackagingTier $tier): array => [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'min_pieces' => $tier->min_pieces,
                    'max_pieces' => $tier->max_pieces,
                    'price' => (float) $tier->price,
                ])->values()];
            }
        );

        $products = (clone $allProductsQuery)
            ->paginate(12)
            ->withQueryString();

        $searchMaxPrice = max(
            1000,
            (int) ceil(
                (clone $allProductsQuery)->reorder()->max(DB::raw('COALESCE(sale_price, regular_price)')) ?: 1000
            )
        );

        $brandProductCount = (clone $baseProductQuery)->count();

        $activeCoupons = Coupon::query()
            ->with('brand')
            ->active()
            ->where(function ($query) use ($brand): void {
                $query->whereNull('brand_id')
                    ->orWhere('brand_id', $brand->id);
            })
            ->latest('id')
            ->get()
            ->filter(
                fn (Coupon $coupon): bool => $coupon->isUsableNow()
            )
            ->values();

        return view('brands.show', compact(
            'brand',
            'brands',
            'categories',
            'featuredProducts',
            'newArrivalProducts',
            'newArrivalPackagingTiers',
            'products',
            'searchMaxPrice',
            'brandProductCount',
            'activeCoupons',
            'selectedAudience',
            'selectedCategory',
            'selectedCategoryKey'
        ));
    }

    public function search(Request $request, string $slug): JsonResponse
    {
        $brand = Brand::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $query = Product::query()
            ->with(['brand', 'category', 'primaryImage', 'images'])
            ->where('status', Product::STATUS_ACTIVE)
            ->whereHas('brand', fn ($brandQuery) => $brandQuery->where('is_active', true))
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('status', 'Active'));

        $term = trim((string) $request->query('q', ''));
        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(function ($searchQuery) use ($like): void {
                $searchQuery
                    ->where('name', 'like', $like)
                    ->orWhere('product_code', 'like', $like)
                    ->orWhereHas('category', function ($categoryQuery) use ($like): void {
                        $categoryQuery->where('name', 'like', $like)
                            ->orWhere('slug', 'like', $like);
                    });
            });
        }

        $audience = Str::lower((string) $request->query('audience', 'all'));
        if (in_array($audience, [Product::AUDIENCE_MEN, Product::AUDIENCE_WOMEN], true)) {
            $query->where(function ($audienceQuery) use ($audience): void {
                $audienceQuery->where('audience', $audience)
                    ->orWhere('audience', Product::AUDIENCE_BOTH)
                    ->orWhereNull('audience');
            });
        }

        $category = trim((string) $request->query('category', 'all'));
        if ($category !== '' && $category !== 'all') {
            $query->whereHas('category', function ($categoryQuery) use ($category): void {
                $categoryQuery->where('slug', $category)
                    ->orWhereRaw('LOWER(REPLACE(name, " ", "-")) = ?', [Str::lower($category)]);
            });
        }

        $tag = trim((string) $request->query('tag', 'all'));
        if ($tag === 'sale') {
            $query->where('is_sales_badge', true);
        } elseif ($tag === 'new') {
            $query->where('is_new_arrival', true);
        } elseif ($tag === 'featured') {
            $query->where('is_featured', true);
        }

        $minPrice = max(0, (float) $request->query('min_price', 0));
        $maxPrice = (float) $request->query('max_price', 0);
        if ($minPrice > 0 || $maxPrice > 0) {
            $query->whereRaw(
                'COALESCE(sale_price, regular_price) BETWEEN ? AND ?',
                [$minPrice, $maxPrice > 0 ? $maxPrice : PHP_FLOAT_MAX]
            );
        }

        $limit = min(24, max(1, (int) $request->query('limit', 24)));
        $products = $query
            ->orderByRaw('CASE WHEN brand_id = ? THEN 0 ELSE 1 END', [$brand->id])
            ->orderBy('brand_id')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $products->map(function (Product $product) use ($brand): array {
                $image = $product->primaryImage ?? $product->images->first();
                $productBrand = $product->brand ?? $brand;
                $price = ! is_null($product->sale_price)
                    ? (float) $product->sale_price
                    : (float) $product->regular_price;

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'url' => route('products.show', [
                        'brandSlug' => $productBrand->slug,
                        'productSlug' => $product->slug,
                    ]),
                    'image_url' => $image ? Storage::url($image->image) : null,
                    'category_name' => (string) ($product->category?->name ?? 'Product'),
                    'category_slug' => (string) ($product->category?->slug ?? ''),
                    'audience' => (string) ($product->audience ?: 'both'),
                    'price' => $price,
                    'regular_price' => (float) $product->regular_price,
                    'sale' => (bool) $product->is_sales_badge,
                    'new' => (bool) $product->is_new_arrival,
                    'featured' => (bool) $product->is_featured,
                    'brand_id' => (int) $productBrand->id,
                    'brand_name' => (string) $productBrand->name,
                    'brand_slug' => (string) $productBrand->slug,
                    'brand_priority' => (int) $productBrand->id === (int) $brand->id ? 'primary' : 'secondary',
                ];
            })->values(),
            'meta' => ['max_price' => $maxPrice],
        ]);
    }
}
