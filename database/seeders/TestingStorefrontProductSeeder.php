<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestingStorefrontProductSeeder extends Seeder
{
    public function run(): void
    {
        $imagePool = ProductImage::query()
            ->pluck('image')
            ->filter()
            ->values();

        $fallbackImages = [
            'products/1/6jWb35n0BQxtVZLNjmaZ7dYEdyJH6KnWIjijqWNe.jpg',
            'products/2/UOcZztoVKjRkWLJQiNjcYWjZVGksZ6mDIb3AS0TJ.jpg',
            'products/4/MML6YY2kRznz8AvKHFLkhtX1ODQ6fJvtjaXeDqPD.jpg',
            'products/5/WIkfrkDpW9higmoIefTg91aVPX0E5JSyKPWZD7MM.jpg',
        ];

        $imagePaths = $imagePool->isNotEmpty()
            ? $imagePool->all()
            : $fallbackImages;

        $catalog = [
            'grey-stone' => [
                'categories' => [
                    ['name' => 'Shirts', 'prefix' => 'SH'],
                    ['name' => 'Pants', 'prefix' => 'PN'],
                    ['name' => 'Shoes', 'prefix' => 'SO'],
                    ['name' => 'Accessories', 'prefix' => 'AC'],
                ],
                'products' => [
                    ['name' => 'Mercury Oxford Shirt', 'category' => 'Shirts', 'code' => 'GS-DEMO-001', 'regular' => 1650, 'sale' => 1390, 'featured' => true, 'new' => true, 'audience' => Product::AUDIENCE_MEN],
                    ['name' => 'Charcoal Utility Pants', 'category' => 'Pants', 'code' => 'GS-DEMO-002', 'regular' => 2150, 'sale' => 1890, 'featured' => false, 'new' => true, 'audience' => Product::AUDIENCE_MEN],
                    ['name' => 'Stone Runner Sneakers', 'category' => 'Shoes', 'code' => 'GS-DEMO-003', 'regular' => 2990, 'sale' => 2590, 'featured' => true, 'new' => false, 'audience' => Product::AUDIENCE_MEN],
                    ['name' => 'Graphite Travel Cap', 'category' => 'Accessories', 'code' => 'GS-DEMO-004', 'regular' => 850, 'sale' => 690, 'featured' => false, 'new' => true, 'audience' => Product::AUDIENCE_BOTH],
                    ['name' => 'Steel Knit Polo', 'category' => 'Shirts', 'code' => 'GS-DEMO-005', 'regular' => 1450, 'sale' => null, 'featured' => true, 'new' => false, 'audience' => Product::AUDIENCE_MEN],
                ],
            ],
            'blue-shades' => [
                'categories' => [
                    ['name' => 'Pants', 'prefix' => 'PN'],
                    ['name' => 'Shoes', 'prefix' => 'SO'],
                    ['name' => 'Shirts', 'prefix' => 'SH'],
                    ['name' => 'Glasses', 'prefix' => 'GL'],
                ],
                'products' => [
                    ['name' => 'Ocean Blue Denim', 'category' => 'Pants', 'code' => 'BS-DEMO-001', 'regular' => 1890, 'sale' => 1590, 'featured' => true, 'new' => true, 'audience' => Product::AUDIENCE_MEN],
                    ['name' => 'Skyline Casual Shirt', 'category' => 'Shirts', 'code' => 'BS-DEMO-002', 'regular' => 1350, 'sale' => 1190, 'featured' => false, 'new' => true, 'audience' => Product::AUDIENCE_MEN],
                    ['name' => 'Cobalt Street Sneakers', 'category' => 'Shoes', 'code' => 'BS-DEMO-003', 'regular' => 2750, 'sale' => 2390, 'featured' => true, 'new' => false, 'audience' => Product::AUDIENCE_MEN],
                    ['name' => 'Aqua Shade Sunglasses', 'category' => 'Glasses', 'code' => 'BS-DEMO-004', 'regular' => 950, 'sale' => 790, 'featured' => true, 'new' => true, 'audience' => Product::AUDIENCE_BOTH],
                    ['name' => 'Navy Cargo Jogger', 'category' => 'Pants', 'code' => 'BS-DEMO-005', 'regular' => 2250, 'sale' => null, 'featured' => false, 'new' => false, 'audience' => Product::AUDIENCE_MEN],
                ],
            ],
            'pink-touch' => [
                'categories' => [
                    ['name' => 'Tops', 'prefix' => 'TP'],
                    ['name' => 'Pants', 'prefix' => 'PN'],
                    ['name' => 'Shoes', 'prefix' => 'SO'],
                    ['name' => 'Bags', 'prefix' => 'BG'],
                    ['name' => 'Glasses', 'prefix' => 'GL'],
                ],
                'products' => [
                    ['name' => 'Rose Cloud Top', 'category' => 'Tops', 'code' => 'PT-DEMO-001', 'regular' => 1250, 'sale' => 990, 'featured' => true, 'new' => true, 'audience' => Product::AUDIENCE_WOMEN],
                    ['name' => 'Blush Wide Pants', 'category' => 'Pants', 'code' => 'PT-DEMO-002', 'regular' => 1750, 'sale' => 1490, 'featured' => false, 'new' => true, 'audience' => Product::AUDIENCE_WOMEN],
                    ['name' => 'Petal Soft Sneakers', 'category' => 'Shoes', 'code' => 'PT-DEMO-003', 'regular' => 2450, 'sale' => 2190, 'featured' => true, 'new' => false, 'audience' => Product::AUDIENCE_WOMEN],
                    ['name' => 'Candy Mini Bag', 'category' => 'Bags', 'code' => 'PT-DEMO-004', 'regular' => 1150, 'sale' => 890, 'featured' => true, 'new' => true, 'audience' => Product::AUDIENCE_WOMEN],
                    ['name' => 'Pink Aura Sunglasses', 'category' => 'Glasses', 'code' => 'PT-DEMO-005', 'regular' => 950, 'sale' => null, 'featured' => false, 'new' => false, 'audience' => Product::AUDIENCE_BOTH],
                ],
            ],
        ];

        DB::transaction(function () use ($catalog, $imagePaths): void {
            $imageIndex = 0;

            foreach ($catalog as $brandSlug => $brandCatalog) {
                $brand = Brand::query()
                    ->where('slug', $brandSlug)
                    ->firstOrFail();

                $categories = [];

                foreach ($brandCatalog['categories'] as $categoryData) {
                    $category = Category::query()->updateOrCreate(
                        [
                            'brand_id' => $brand->id,
                            'slug' => Str::slug($categoryData['name']),
                        ],
                        [
                            'name' => $categoryData['name'],
                            'prefix' => $categoryData['prefix'],
                            'status' => 'Active',
                            'description' => $categoryData['name'].' collection for '.$brand->name.'.',
                        ]
                    );

                    $categories[$categoryData['name']] = $category;
                }

                foreach ($brandCatalog['products'] as $productData) {
                    $category = $categories[$productData['category']];
                    $colors = $this->colorsForBrand($brandSlug);
                    $stockSeed = 8 + $imageIndex;

                    $product = Product::query()->updateOrCreate(
                        [
                            'brand_id' => $brand->id,
                            'slug' => Str::slug($productData['name']),
                        ],
                        [
                            'category_id' => $category->id,
                            'audience' => $productData['audience']
                                ?? Product::AUDIENCE_BOTH,
                            'name' => $productData['name'],
                            'product_code' => $productData['code'],
                            'regular_price' => $productData['regular'],
                            'sale_price' => $productData['sale'],
                            'stock_quantity' => $stockSeed,
                            'stock_status' => Product::STOCK_IN,
                            'colors' => $colors,
                            'material' => 'Premium blended fabric',
                            'short_description' => 'Demo storefront product for testing category, new arrival and featured layouts.',
                            'description' => 'A polished demo item added for storefront testing across category filtering, checkout and product cards.',
                            'care_instructions' => 'Gentle wash recommended.',
                            'is_featured' => $productData['featured'],
                            'is_new_arrival' => $productData['new'],
                            'status' => Product::STATUS_ACTIVE,
                        ]
                    );

                    ProductImage::query()->updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'sort_order' => 0,
                        ],
                        [
                            'image' => $imagePaths[$imageIndex % count($imagePaths)],
                            'is_primary' => true,
                        ]
                    );

                    $this->syncVariants($product, $productData['code'], $colors, $stockSeed);
                    $product->syncStockFromVariants();

                    $imageIndex++;
                }
            }
        });
    }

    private function colorsForBrand(string $brandSlug): array
    {
        return match ($brandSlug) {
            'blue-shades' => ['Blue', 'Navy'],
            'pink-touch' => ['Pink', 'White'],
            default => ['Grey', 'Black'],
        };
    }

    private function syncVariants(
        Product $product,
        string $baseSku,
        array $colors,
        int $stockSeed
    ): void {
        $sizes = ['S', 'M', 'L'];

        foreach ($colors as $colorIndex => $color) {
            foreach ($sizes as $sizeIndex => $size) {
                ProductVariant::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color' => $color,
                        'size' => $size,
                    ],
                    [
                        'stock_quantity' => max(
                            2,
                            $stockSeed - $colorIndex - $sizeIndex
                        ),
                        'variant_sku' => sprintf(
                            '%s-%s-%s',
                            $baseSku,
                            strtoupper(substr($color, 0, 2)),
                            $size
                        ),
                        'status' => true,
                    ]
                );
            }
        }
    }
}
