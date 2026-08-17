<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Category List
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = $this
            ->categoryQuery(request())
            ->latest('id')
            ->get();

        if (request()->ajax()) {
            return view(
                'admin.categories.partials.table-rows',
                compact('categories')
            );
        }

        return view(
            'admin.categories.index',
            compact('brands', 'categories')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Single Category
    |--------------------------------------------------------------------------
    */

    public function show(
        Category $category
    ): JsonResponse {
        $category->load('brand');

        return response()->json([
            'status' => 'success',

            'category' => [
                'id' => $category->id,
                'brand_id' => $category->brand_id,
                'brand_name' => $category->brand?->name,
                'name' => $category->name,
                'slug' => $category->slug,
                'prefix' => $category->prefix,
                'status' => $category->status,
                'description' => $category->description,

                'image_url' => $category->image
                    ? Storage::url($category->image)
                    : null,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Category
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): JsonResponse {
        $validated = $this->validateCategory(
            $request
        );

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['slug'] = strtolower(
            trim($validated['slug'])
        );

        $validated['prefix'] = strtoupper(
            trim($validated['prefix'])
        );

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store(
                    'categories',
                    'public'
                );
        }

        $category = Category::query()
            ->create($validated);

        $category->load('brand');

        return response()->json([
            'status' => 'success',
            'message' => 'Category added successfully!',
            'category' => $this->tableData(
                $category
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Category $category
    ): JsonResponse {
        $validated = $this->validateCategory(
            $request,
            $category
        );

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['slug'] = strtolower(
            trim($validated['slug'])
        );

        $validated['prefix'] = strtoupper(
            trim($validated['prefix'])
        );

        $oldImage = $category->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            $newImage = $request
                ->file('image')
                ->store(
                    'categories',
                    'public'
                );

            $validated['image'] = $newImage;
        } else {
            unset($validated['image']);
        }

        try {
            DB::transaction(function () use (
                $category,
                $validated
            ): void {
                $category->update($validated);

                /*
                |--------------------------------------------------------------------------
                | Category inactive হলে ওই category-এর সব products inactive হবে
                |--------------------------------------------------------------------------
                */

                if ($category->status === 'Inactive') {
                    $category
                        ->products()
                        ->update([
                            'status' =>
                                Product::STATUS_INACTIVE,
                        ]);
                }
            });
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')
                    ->delete($newImage);
            }

            report($exception);

            return response()->json([
                'status' => 'error',
                'message' =>
                    'Category could not be updated.',
            ], 500);
        }

        if (
            $newImage &&
            $oldImage &&
            $oldImage !== $newImage
        ) {
            Storage::disk('public')
                ->delete($oldImage);
        }

        $category->refresh();
        $category->load('brand');

        return response()->json([
            'status' => 'success',
            'message' =>
                'Category updated successfully!',
            'category' => $this->tableData(
                $category
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Category $category
    ): JsonResponse {
        if ($category->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' =>
                    'This category contains products and cannot be deleted.',
            ], 422);
        }

        $image = $category->image;

        $category->delete();

        if ($image) {
            Storage::disk('public')
                ->delete($image);
        }

        return response()->json([
            'status' => 'success',
            'message' =>
                'Category deleted successfully!',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateCategory(
        Request $request,
        ?Category $category = null
    ): array {
        $categoryId = $category?->id;

        $brandId = $request->input(
            'brand_id'
        );

        return $request->validate([
            'brand_id' => [
                'required',
                'integer',
                'exists:brands,id',
            ],

            'name' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'categories',
                    'name'
                )
                    ->where(
                        function ($query) use (
                            $brandId
                        ) {
                            return $query->where(
                                'brand_id',
                                $brandId
                            );
                        }
                    )
                    ->ignore($categoryId),
            ],

            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

                Rule::unique(
                    'categories',
                    'slug'
                )
                    ->where(
                        function ($query) use (
                            $brandId
                        ) {
                            return $query->where(
                                'brand_id',
                                $brandId
                            );
                        }
                    )
                    ->ignore($categoryId),
            ],

            'prefix' => [
                'required',
                'string',
                'min:2',
                'max:5',
                'regex:/^[A-Za-z]+$/',

                Rule::unique(
                    'categories',
                    'prefix'
                )
                    ->where(
                        function ($query) use (
                            $brandId
                        ) {
                            return $query->where(
                                'brand_id',
                                $brandId
                            );
                        }
                    )
                    ->ignore($categoryId),
            ],

            'status' => [
                'required',

                Rule::in([
                    'Active',
                    'Inactive',
                ]),
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Category Query
    |--------------------------------------------------------------------------
    */

    private function categoryQuery(
        Request $request
    ): Builder {
        return Category::query()
            ->with('brand')

            ->when(
                $request->filled('search'),

                function (
                    Builder $query
                ) use ($request): void {
                    $search = trim(
                        (string) $request->input(
                            'search'
                        )
                    );

                    $query->where(
                        function (
                            Builder $query
                        ) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'slug',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'prefix',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'brand',

                                    function (
                                        Builder $query
                                    ) use ($search): void {
                                        $query->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                );
                        }
                    );
                }
            )

            ->when(
                $request->filled('brand_id'),

                function (
                    Builder $query
                ) use ($request): void {
                    $query->where(
                        'brand_id',
                        $request->input(
                            'brand_id'
                        )
                    );
                }
            )

            ->when(
                $request->filled('status'),

                function (
                    Builder $query
                ) use ($request): void {
                    $query->where(
                        'status',
                        $request->input(
                            'status'
                        )
                    );
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Table Response Data
    |--------------------------------------------------------------------------
    */

    private function tableData(
        Category $category
    ): array {
        return [
            'id' => $category->id,
            'brand_id' => $category->brand_id,
            'brand_name' => $category->brand?->name,
            'name' => $category->name,
            'slug' => $category->slug,
            'prefix' => $category->prefix,
            'status' => $category->status,
            'description' => $category->description,

            'image_url' => $category->image
                ? Storage::url($category->image)
                : null,
        ];
    }
}