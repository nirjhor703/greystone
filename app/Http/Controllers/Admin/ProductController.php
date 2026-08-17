<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Product list
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->with('brand')
            ->where('status', 'Active')
            ->orderBy('brand_id')
            ->orderBy('name')
            ->get();

        $products = $this->productQuery(request())
            ->with([
                'brand',
                'category',
                'primaryImage',
                'images',
                'variants',
            ])
            ->latest('id')
            ->get();

        if (request()->ajax()) {
            return view(
                'admin.products.partials.table-rows',
                compact('products')
            );
        }

        return view('admin.products.index', compact(
            'brands',
            'categories',
            'products'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Return one product for edit modal
    |--------------------------------------------------------------------------
    */

    public function show(Product $product): JsonResponse
{
    $product->load([
        'brand',
        'category',

        'images' => function ($query) {
            $query
                ->orderBy('sort_order')
                ->orderBy('id');
        },

        'variants' => function ($query) {
            $query
                ->orderBy('color')
                ->orderBy('id');
        },
    ]);

    $variants = $product->variants
        ->groupBy(function ($variant) {
            return mb_strtolower(trim($variant->color));
        })
        ->map(function ($colorVariants) {
            $firstVariant = $colorVariants->first();

            $sizes = collect(Product::AVAILABLE_SIZES)
                ->mapWithKeys(function (
                    string $size
                ) use ($colorVariants): array {
                    $variant = $colorVariants
                        ->firstWhere('size', $size);

                    return [
                        $size => (int) (
                            $variant?->stock_quantity ?? 0
                        ),
                    ];
                });

            return [
                'color' => $firstVariant->color,
                'color_hex' => $firstVariant->normalized_color_hex,
                'sizes' => $sizes,
            ];
        })
        ->values();

    return response()->json([
        'status' => 'success',

        'product' => [
            'id' => $product->id,
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
            'audience' => $product->audience
                ?: Product::AUDIENCE_BOTH,

            'name' => $product->name,
            'slug' => $product->slug,
            'product_code' => $product->product_code,

            'regular_price' => $product->regular_price,
            'sale_price' => $product->sale_price,

            'stock_quantity' => (int) $product->stock_quantity,
            'stock_status' => $product->stock_status,

            'variants' => $variants,

            'material' => $product->material,
            'short_description' =>
                $product->short_description,
            'description' => $product->description,
            'care_instructions' =>
                $product->care_instructions,

            'is_featured' =>
                (bool) $product->is_featured,

            'is_new_arrival' =>
                (bool) $product->is_new_arrival,

            'status' => $product->status,

            'images' => $product->images
                ->map(
                    fn (ProductImage $image): array => [
                        'id' => $image->id,
                        'url' => Storage::url(
                            $image->image
                        ),
                        'sort_order' =>
                            (int) $image->sort_order,
                        'is_primary' =>
                            (bool) $image->is_primary,
                    ]
                )
                ->values(),
        ],
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Store product
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateProduct($request);

        try {
            return DB::transaction(function () use (
                $request,
                $validated
            ): JsonResponse {
                $product = Product::create(
                    $this->prepareProductData(
                        $request,
                        $validated
                    )
                );

                $this->syncVariants(
                    $product,
                    $validated['variants']
                );

                // Only one image-upload call
                $this->storeNewImages(
                    $request,
                    $product
                );

                $this->ensurePrimaryImage($product);

                $product->refresh();

                $product->load([
                    'brand',
                    'category',
                    'primaryImage',
                    'images',
                    'variants',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Product added successfully!',
                    'product' => $this->tableData($product),
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update product
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ): JsonResponse {
        $validated = $this->validateProduct(
            $request,
            $product
        );

        try {
            return DB::transaction(function () use (
                $request,
                $validated,
                $product
            ): JsonResponse {
                $product->update(
                    $this->prepareProductData(
                        $request,
                        $validated
                    )
                );

                $this->syncVariants(
                    $product,
                    $validated['variants']
                );

                $deleteImageIds = $this->parseDeleteImageIds(
                    $request->input(
                        'delete_image_ids',
                        '[]'
                    )
                );

                $this->deleteSelectedImages(
                    $product,
                    $deleteImageIds
                );

                $this->storeNewImages(
                    $request,
                    $product
                );

                $this->setPrimaryImage(
                    $product,
                    $request->input('primary_image_id')
                );

                $this->ensurePrimaryImage($product);

                $product->refresh();

                $product->load([
                    'brand',
                    'category',
                    'primaryImage',
                    'images',
                    'variants',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Product updated successfully!',
                    'product' => $this->tableData($product),
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete product
    |--------------------------------------------------------------------------
    */

    public function destroy(Product $product): JsonResponse
    {
        try {
            DB::transaction(function () use ($product): void {
                $product->load('images');

                foreach ($product->images as $image) {
                    Storage::disk('public')->delete(
                        $image->image
                    );
                }

                Storage::disk('public')->deleteDirectory(
                    'products/'.$product->id
                );

                $product->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully!',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        $productId = $product?->id;
        $brandId = $request->input('brand_id');

        return $request->validate([
            'brand_id' => [
                'required',
                'integer',
                'exists:brands,id',
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) use ($brandId): void {
                    $belongsToBrand = Category::query()
                        ->whereKey($value)
                        ->where('brand_id', $brandId)
                        ->exists();

                    if (!$belongsToBrand) {
                        $fail(
                            'Selected category does not belong to this brand.'
                        );
                    }
                },
            ],

            'audience' => [
                'required',
                Rule::in([
                    Product::AUDIENCE_MEN,
                    Product::AUDIENCE_WOMEN,
                    Product::AUDIENCE_BOTH,
                ]),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'required',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

                Rule::unique('products', 'slug')
                    ->where(
                        fn ($query) => $query->where(
                            'brand_id',
                            $brandId
                        )
                    )
                    ->ignore($productId),
            ],

            'product_code' => [
                'nullable',
                'string',
                'max:30',

                Rule::unique('products', 'product_code')
                    ->where(
                        fn ($query) => $query->where(
                            'brand_id',
                            $brandId
                        )
                    )
                    ->ignore($productId),
            ],

            'regular_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                'lt:regular_price',
            ],

            /*
             * stock_quantity is intentionally omitted.
             * Total stock is calculated from size_stocks.
             */

            /*
|--------------------------------------------------------------------------
| Color-wise size variants
|--------------------------------------------------------------------------
*/

        'variants' => [
            'required',
            'array',
            'min:1',

            function (
                string $attribute,
                mixed $value,
                Closure $fail
            ): void {
                if (!is_array($value)) {
                    return;
                }

                $normalizedColors = [];

                foreach ($value as $variantGroup) {
                    $color = trim(
                        (string) (
                            $variantGroup['color'] ?? ''
                        )
                    );

                    $colorHex = ProductVariant::normalizeColorHex(
                        $variantGroup['color_hex'] ?? null
                    );

                    if ($color === '') {
                        continue;
                    }

                    $normalizedColor = $colorHex
                        ?: mb_strtolower($color);

                    if (
                        in_array(
                            $normalizedColor,
                            $normalizedColors,
                            true
                        )
                    ) {
                        $fail(
                            "The color '{$color}' was added more than once."
                        );

                        return;
                    }

                    $normalizedColors[] = $normalizedColor;
                }
            },
        ],

        'variants.*.color' => [
            'required',
            'string',
            'max:100',
        ],

        'variants.*.color_hex' => [
            'nullable',
            'string',
            'regex:/^#?[A-Fa-f0-9]{6}$/',
        ],

        'variants.*.sizes' => [
            'required',
            'array',

            function (
                string $attribute,
                mixed $value,
                Closure $fail
            ): void {
                if (!is_array($value)) {
                    return;
                }

                foreach (array_keys($value) as $size) {
                    if (
                        !in_array(
                            $size,
                            Product::AVAILABLE_SIZES,
                            true
                        )
                    ) {
                        $fail(
                            "The size '{$size}' is not valid."
                        );

                        return;
                    }
                }
            },
        ],

        'variants.*.sizes.*' => [
            'nullable',
            'integer',
            'min:0',
        ],

            'material' => [
                'nullable',
                'string',
                'max:255',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'care_instructions' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_featured' => [
                'nullable',
                'boolean',
            ],

            'is_new_arrival' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'required',
                Rule::in([
                    Product::STATUS_ACTIVE,
                    Product::STATUS_INACTIVE,
                ]),
            ],

            'images' => [
                $product ? 'nullable' : 'required',
                'array',
                'max:10',
                ...($product ? [] : ['min:1']),
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'primary_image_id' => [
                'nullable',
                'integer',
            ],

            'delete_image_ids' => [
                'nullable',
                'json',
            ],
        ]);
    }

    private function productQuery(Request $request)
    {
        return Product::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('stock_status', 'like', "%{$search}%")
                        ->orWhereHas('brand', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('category', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('brand_id'), function ($query) use ($request): void {
                $query->where('brand_id', $request->input('brand_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('stock'), function ($query) use ($request): void {
                if ($request->input('stock') === 'low') {
                    $query->where('stock_quantity', '<', 10);
                }

                if ($request->input('stock') === 'out') {
                    $query->where('stock_quantity', '<=', 0);
                }
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare product data
    |--------------------------------------------------------------------------
    */

    private function prepareProductData(
        Request $request,
        array $validated
    ): array {
        $category = Category::query()
            ->findOrFail($validated['category_id']);

        $productCode = trim(
            $validated['product_code'] ?? ''
        );

        if ($productCode === '') {
            $productCode = $this->generateProductCode(
                $category
            );
        }

        $submittedVariants = $validated['variants'];

        $totalStock = collect($submittedVariants)
            ->sum(function (array $variantGroup): int {
                $sizes = $variantGroup['sizes'] ?? [];

                return collect(Product::AVAILABLE_SIZES)
                    ->sum(function (
                        string $size
                    ) use ($sizes): int {
                        return max(
                            0,
                            (int) ($sizes[$size] ?? 0)
                        );
                    });
            });

        $colors = collect($submittedVariants)
            ->pluck('color')
            ->map(
                fn ($color) => trim((string) $color)
            )
            ->filter()
            ->unique(
                fn ($color) => mb_strtolower($color)
            )
            ->values()
            ->all();

        $salePrice = $validated['sale_price'] ?? null;

        if ($salePrice === '') {
            $salePrice = null;
        }

        return [
            'brand_id' => (int) $validated['brand_id'],
            'category_id' => (int) $validated['category_id'],
            'audience' => $validated['audience']
                ?? Product::AUDIENCE_BOTH,

            'name' => trim($validated['name']),
            'slug' => trim($validated['slug']),

            'product_code' => strtoupper($productCode),

            'regular_price' => $validated['regular_price'],
            'sale_price' => $salePrice,

            'stock_quantity' => $totalStock,

            'stock_status' => $totalStock > 0
                ? Product::STOCK_IN
                : Product::STOCK_OUT,

            /*
            * Compatibility copy.
            * Actual stock source is product_variants.
            */
            'colors' => $colors ?: null,

            'material' => $this->nullableTrimmedString(
                $validated['material'] ?? null
            ),

            'short_description' =>
                $this->nullableTrimmedString(
                    $validated['short_description'] ?? null
                ),

            'description' => $this->nullableTrimmedString(
                $validated['description'] ?? null
            ),

            'care_instructions' =>
                $this->nullableTrimmedString(
                    $validated['care_instructions'] ?? null
                ),

            'is_featured' => $request->boolean(
                'is_featured'
            ),

            'is_new_arrival' => $request->boolean(
                'is_new_arrival'
            ),

            'status' => $validated['status'],
        ];
    }

    /*
|--------------------------------------------------------------------------
| Color and size variants
|--------------------------------------------------------------------------
*/

private function syncVariants(
    Product $product,
    array $submittedVariants
): void {
    /*
     * Product form is the source of truth.
     * Existing variants are rebuilt during update.
     */
    $product->variants()->delete();

    foreach ($submittedVariants as $variantGroup) {
        $color = trim(
            (string) ($variantGroup['color'] ?? '')
        );

        $colorHex = ProductVariant::normalizeColorHex(
            $variantGroup['color_hex'] ?? null
        );

        if ($color === '') {
            continue;
        }

        $sizes = $variantGroup['sizes'] ?? [];

        foreach (Product::AVAILABLE_SIZES as $size) {
            $quantity = max(
                0,
                (int) ($sizes[$size] ?? 0)
            );

            $product->variants()->create([
                'color' => $color,
                'color_hex' => $colorHex,
                'size' => $size,
                'stock_quantity' => $quantity,

                'variant_sku' =>
                    $this->generateVariantSku(
                        $product,
                        $color,
                        $size
                    ),

                'status' => true,
            ]);
        }
    }

    $totalStock = (int) $product
        ->variants()
        ->where('status', true)
        ->sum('stock_quantity');

    $product->update([
        'stock_quantity' => $totalStock,

        'stock_status' => $totalStock > 0
            ? Product::STOCK_IN
            : Product::STOCK_OUT,

        'colors' => collect($submittedVariants)
            ->pluck('color')
            ->map(
                fn ($color) => trim(
                    (string) $color
                )
            )
            ->filter()
            ->unique(
                fn ($color) =>
                    mb_strtolower($color)
            )
            ->values()
            ->all(),
    ]);
}

private function generateVariantSku(
    Product $product,
    string $color,
    string $size
): string {
    $colorCode = strtoupper(
        preg_replace(
            '/[^A-Za-z0-9]+/',
            '-',
            trim($color)
        )
    );

    $sizeCode = strtoupper(
        preg_replace(
            '/[^A-Za-z0-9]+/',
            '',
            trim($size)
        )
    );

    return implode('-', [
        strtoupper($product->product_code),
        trim($colorCode, '-'),
        $sizeCode,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Product code
    |--------------------------------------------------------------------------
    */

    private function generateProductCode(
        Category $category
    ): string {
        $prefix = strtoupper(
            trim($category->prefix ?: 'PRD')
        );

        $lastProduct = Product::query()
            ->where('brand_id', $category->brand_id)
            ->where('category_id', $category->id)
            ->where(
                'product_code',
                'like',
                $prefix.'-%'
            )
            ->latest('id')
            ->first();

        $nextNumber = 1;

        if ($lastProduct) {
            $parts = explode(
                '-',
                $lastProduct->product_code
            );

            $lastNumber = (int) end($parts);

            $nextNumber = $lastNumber + 1;
        }

        do {
            $code = sprintf(
                '%s-%06d',
                $prefix,
                $nextNumber
            );

            $exists = Product::query()
                ->where('brand_id', $category->brand_id)
                ->where('product_code', $code)
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | Image handling
    |--------------------------------------------------------------------------
    */

    private function storeNewImages(
        Request $request,
        Product $product
    ): void {
        if (!$request->hasFile('images')) {
            return;
        }

        $currentMaximum = $product
            ->images()
            ->max('sort_order');

        $currentMaximum = $currentMaximum === null
            ? -1
            : (int) $currentMaximum;

        $hasPrimaryImage = $product
            ->images()
            ->where('is_primary', true)
            ->exists();

        foreach (
            $request->file('images', []) as $index => $file
        ) {
            ProductImage::create([
                'product_id' => $product->id,

                'image' => $file->store(
                    'products/'.$product->id,
                    'public'
                ),

                'sort_order' =>
                    $currentMaximum + $index + 1,

                'is_primary' =>
                    !$hasPrimaryImage && $index === 0,
            ]);
        }
    }

    private function deleteSelectedImages(
        Product $product,
        array $imageIds
    ): void {
        $imageIds = collect($imageIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($imageIds)) {
            return;
        }

        $images = $product
            ->images()
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete(
                $image->image
            );

            $image->delete();
        }
    }

    private function setPrimaryImage(
        Product $product,
        mixed $imageId
    ): void {
        if (!$imageId) {
            return;
        }

        $image = $product
            ->images()
            ->whereKey((int) $imageId)
            ->first();

        if (!$image) {
            return;
        }

        $product->images()->update([
            'is_primary' => false,
        ]);

        $image->update([
            'is_primary' => true,
        ]);
    }

    private function ensurePrimaryImage(
        Product $product
    ): void {
        $hasPrimaryImage = $product
            ->images()
            ->where('is_primary', true)
            ->exists();

        if ($hasPrimaryImage) {
            return;
        }

        $firstImage = $product
            ->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($firstImage) {
            $firstImage->update([
                'is_primary' => true,
            ]);
        }
    }

    private function parseDeleteImageIds(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            ? $decoded
            : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

   

    private function nullableTrimmedString(
        mixed $value
    ): ?string {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === ''
            ? null
            : $value;
    }

    private function tableData(
        Product $product
    ): array {
        $primaryImage = $product->primaryImage
            ?? $product->images->first();

        return [
            'id' => $product->id,
            'brand_id' => $product->brand_id,
            'brand_name' => $product->brand?->name,

            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'audience' => $product->audience
                ?: Product::AUDIENCE_BOTH,

            'name' => $product->name,
            'slug' => $product->slug,
            'product_code' => $product->product_code,

            'regular_price' => $product->regular_price,
            'sale_price' => $product->sale_price,

            'stock_quantity' => (int) $product->stock_quantity,
            'stock_status' => $product->stock_status,

            'is_featured' => (bool) $product->is_featured,
            'is_new_arrival' => (bool) $product->is_new_arrival,
            'status' => $product->status,

            'primary_image_url' => $primaryImage
                ? Storage::url($primaryImage->image)
                : null,
        ];
    }
}
