<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Cart summary
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'cart' => $this->cartResponse($request),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Add item to cart
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'brand_id' => [
            'required',
            'integer',
            'exists:brands,id',
        ],

        'product_id' => [
            'required',
            'integer',
            'exists:products,id',
        ],

        'color' => [
            'required',
            'string',
            'max:100',
        ],

        'color_hex' => [
            'nullable',
            'string',
            'regex:/^#?[A-Fa-f0-9]{6}$/',
        ],

        'items' => [
            'required',
            'array',
            'min:1',
        ],

        'items.*.size' => [
            'required',
            'string',
            Rule::in(Product::AVAILABLE_SIZES),
        ],

        'items.*.quantity' => [
            'required',
            'integer',
            'min:1',
        ],
    ]);

    $brand = Brand::query()
        ->whereKey($validated['brand_id'])
        ->where('is_active', true)
        ->firstOrFail();

    $product = Product::query()
        ->with([
            'primaryImage',
            'images',
            'variants',
        ])
        ->whereKey($validated['product_id'])
        ->where('brand_id', $brand->id)
        ->where('status', Product::STATUS_ACTIVE)
        ->firstOrFail();

    $color = trim($validated['color']);

    $primaryImage = $product->primaryImage
        ?? $product->images->first();

    $unitPrice = $this->productPrice($product);

    $cart = $request->session()->get('cart', []);

    /*
    |--------------------------------------------------------------------------
    | Add every selected size as a separate cart line
    |--------------------------------------------------------------------------
    */

    $addedCount = 0;
    $skippedExistingCount = 0;

    foreach ($validated['items'] as $selectedItem) {
        $size = trim(
            (string) $selectedItem['size']
        );

        $quantity = (int) $selectedItem['quantity'];

        $cartKey = $this->makeCartKey(
            $product->id,
            $size,
            $color
        );

        $existingQuantity = isset($cart[$cartKey])
            ? (int) $cart[$cartKey]['quantity']
            : 0;

        if ($existingQuantity > 0) {
            $skippedExistingCount++;
            continue;
        }

        $newQuantity = $existingQuantity + $quantity;

        /*
         * Validate exact Product + Color + Size stock.
         * Existing cart quantity is also included.
         */
        $variant = $this->validateVariant(
            $product,
            $size,
            $color,
            $newQuantity
        );

        $cart[$cartKey] = [
            'key' => $cartKey,

            'brand_id' => $brand->id,
            'brand_name' => $brand->name,

            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->product_code,

            'image_url' => $primaryImage
                ? Storage::url($primaryImage->image)
                : null,

            'size' => $size,
            'color' => $color,
            'color_hex' => $variant?->normalized_color_hex
                ?: ProductVariant::normalizeColorHex(
                    $validated['color_hex'] ?? null
                ),

            'quantity' => $newQuantity,
            'unit_price' => $unitPrice,
        ];

        $addedCount++;
    }

    $request->session()->put('cart', $cart);
    $request->session()->forget('checkout_coupon');

    $message = 'Product added to cart.';

    if ($addedCount > 1) {
        $message = 'Selected sizes added to cart.';
    }

    if ($addedCount > 0 && $skippedExistingCount > 0) {
        $message = 'Existing cart items were kept. New selections were added.';
    } elseif ($addedCount === 0 && $skippedExistingCount > 0) {
        $message = 'These selections are already in your cart.';
    }

    return response()->json([
        'status' => 'success',
        'message' => $message,

        'cart' => $this->cartResponse($request),
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Update cart quantity
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        string $cartKey
    ): JsonResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $cart = $request->session()->get('cart', []);

        if (!isset($cart[$cartKey])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found.',
            ], 404);
        }

        $cartItem = $cart[$cartKey];

        $product = Product::query()
            ->with('sizeStocks')
            ->whereKey($cartItem['product_id'])
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        $quantity = (int) $validated['quantity'];

        $this->validateVariant(
            $product,
            $cartItem['size'] ?? null,
            $cartItem['color'] ?? null,
            $quantity
        );

        $cart[$cartKey]['quantity'] = $quantity;

        $request->session()->put('cart', $cart);
        $request->session()->forget('checkout_coupon');

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated.',
            'cart' => $this->cartResponse($request),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Remove cart item
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        string $cartKey
    ): JsonResponse {
        $cart = $request->session()->get('cart', []);

        if (!isset($cart[$cartKey])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found.',
            ], 404);
        }

        unset($cart[$cartKey]);

        $request->session()->put('cart', $cart);
        $request->session()->forget('checkout_coupon');

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from cart.',
            'cart' => $this->cartResponse($request),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Clear cart
    |--------------------------------------------------------------------------
    */

    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget([
            'cart',
            'checkout_coupon',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared.',
            'cart' => $this->cartResponse($request),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function validateVariant(
        Product $product,
        ?string $size,
        ?string $color,
        int $quantity
    ): ?ProductVariant {
        if (!$color) {
            abort(
                response()->json([
                    'status' => 'error',
                    'message' => 'Please select a color.',
                    'errors' => [
                        'color' => [
                            'Please select a color.',
                        ],
                    ],
                ], 422)
            );
        }
    
        if (!$size) {
            abort(
                response()->json([
                    'status' => 'error',
                    'message' => 'Please select a size.',
                    'errors' => [
                        'size' => [
                            'Please select a size.',
                        ],
                    ],
                ], 422)
            );
        }
    
        if ($quantity < 1) {
            abort(
                response()->json([
                    'status' => 'error',
                    'message' => 'Quantity must be at least 1.',
                    'errors' => [
                        'quantity' => [
                            'Quantity must be at least 1.',
                        ],
                    ],
                ], 422)
            );
        }
    
        /*
        |--------------------------------------------------------------------------
        | Find exact color + size variant
        |--------------------------------------------------------------------------
        */
    
        $variant = $product->variants
            ->first(function ($variant) use (
                $color,
                $size
            ): bool {
                return (bool) $variant->status
                    && mb_strtolower(
                        trim($variant->color)
                    ) === mb_strtolower(
                        trim($color)
                    )
                    && trim($variant->size)
                        === trim($size);
            });
    
        if (!$variant) {
            abort(
                response()->json([
                    'status' => 'error',
                    'message' =>
                        'Selected color and size combination is invalid.',
                    'errors' => [
                        'variant' => [
                            'Selected color and size combination is invalid.',
                        ],
                    ],
                ], 422)
            );
        }
    
        $availableStock = (int) $variant->stock_quantity;
    
        if ($availableStock < 1) {
            abort(
                response()->json([
                    'status' => 'error',
                    'message' =>
                        "Size {$size} is unavailable for {$color}.",
                    'errors' => [
                        'stock' => [
                            "Size {$size} is unavailable for {$color}.",
                        ],
                    ],
                ], 422)
            );
        }
    
        if ($quantity > $availableStock) {
            abort(
                response()->json([
                    'status' => 'error',
                    'message' =>
                        "Only {$availableStock} item(s) available for {$color} / {$size}.",
                    'errors' => [
                        'quantity' => [
                            "Only {$availableStock} item(s) available for {$color} / {$size}.",
                        ],
                    ],
                ], 422)
            );
        }

        return $variant;
    }

    private function makeCartKey(
        int $productId,
        ?string $size,
        ?string $color
    ): string {
        return sha1(
            implode('|', [
                $productId,
                $size ?: '-',
                mb_strtolower($color ?: '-'),
            ])
        );
    }

    private function productPrice(Product $product): float
    {
        if (
            !is_null($product->sale_price)
            && (float) $product->sale_price
                < (float) $product->regular_price
        ) {
            return (float) $product->sale_price;
        }

        return (float) $product->regular_price;
    }

    private function cartResponse(Request $request): array
    {
        $cart = collect(
            $request->session()->get('cart', [])
        );

        $items = $cart
            ->map(function (array $item): array {
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                return [
                    ...$item,

                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,

                    'line_total' =>
                        $quantity * $unitPrice,
                ];
            })
            ->values();

        $itemsTotal = (float) $items->sum(
            'line_total'
        );

        return [
            'items' => $items,
            'count' => (int) $items->sum('quantity'),
            'unique_items' => $items->count(),
            'items_total' => $itemsTotal,
            'coupon' => $request->session()->get('checkout_coupon'),
        ];
    }
}
