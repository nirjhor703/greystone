<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Brand;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $cartSummary = $this->cartSummary($request);

        if ($cartSummary['items_total'] <= 0 || !$cartSummary['brand_id']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your cart is empty.',
            ], 422);
        }

        $coupon = Coupon::query()
            ->with('brand')
            ->whereRaw('UPPER(code) = ?', [
                mb_strtoupper(trim($validated['code'])),
            ])
            ->first();

        if (!$coupon) {
            $request->session()->forget('checkout_coupon');

            return response()->json([
                'status' => 'error',
                'message' => 'Coupon code was not found.',
            ], 422);
        }

        $unavailableMessage = $this->unavailableMessage(
            $coupon,
            $cartSummary['brand_id'],
            $cartSummary['items_total']
        );

        if ($unavailableMessage) {
            $request->session()->forget('checkout_coupon');

            return response()->json([
                'status' => 'error',
                'message' => $unavailableMessage,
            ], 422);
        }

        $phone = preg_replace(
            '/\D+/',
            '',
            (string) ($validated['phone'] ?? '')
        );

        if ($coupon->new_customer_only) {
            if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
                $request->session()->forget('checkout_coupon');

                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'Enter your phone number before using this new customer coupon.',
                ], 422);
            }

            $hasPreviousOrder = Order::query()
                ->where('phone', $phone)
                ->where(
                    'status',
                    '!=',
                    Order::STATUS_CANCELLED
                )
                ->exists();

            if ($hasPreviousOrder) {
                $request->session()->forget('checkout_coupon');

                return response()->json([
                    'status' => 'error',
                    'message' =>
                        'This offer is available only for new customers.',
                ], 422);
            }
        }

        $snapshot = $coupon->snapshot($cartSummary['items_total']);

        $request->session()->put('checkout_coupon', $snapshot);

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon applied successfully.',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'title' => $coupon->title,
                'discount_amount' => $snapshot['discount_amount'],
            ],
        ]);
    }

    public function remove(Request $request): JsonResponse
    {
        $request->session()->forget('checkout_coupon');

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon removed.',
        ]);
    }

    public function available(Request $request): JsonResponse
    {
        $cartSummary = $this->cartSummary($request);
        $brandId = $cartSummary['brand_id'];
        $itemsTotal = $cartSummary['items_total'];

        if ($itemsTotal <= 0 || !$brandId) {
            return response()->json([
                'status' => 'success',
                'coupons' => [],
                'items_total' => $itemsTotal,
            ]);
        }

        $coupons = Coupon::query()
            ->with('brand')
            ->active()
            ->where(function ($query) use ($brandId): void {
                $query->whereNull('brand_id')
                    ->orWhere('brand_id', $brandId);
            })
            ->orderByRaw(
                'CASE WHEN brand_id IS NULL THEN 1 ELSE 0 END'
            )
            ->latest('id')
            ->get()
            ->filter(
                fn (Coupon $coupon): bool =>
                    $coupon->isUsableNow()
            )
            ->values()
            ->map(function (Coupon $coupon) use ($itemsTotal): array {
                $minOrderAmount = (float) $coupon->min_order_amount;
                $remainingAmount = max(
                    $minOrderAmount - $itemsTotal,
                    0
                );
                $progress = $minOrderAmount > 0
                    ? min(($itemsTotal / $minOrderAmount) * 100, 100)
                    : 100;

                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'title' => $coupon->title,
                    'brand_name' => $coupon->brand?->name,
                    'discount_label' => $coupon->discountLabel(),
                    'discount_amount' => $coupon->discountAmount(
                        max($itemsTotal, $minOrderAmount)
                    ),
                    'min_order_amount' => $minOrderAmount,
                    'remaining_amount' => $remainingAmount,
                    'progress' => round($progress, 1),
                    'eligible' => $remainingAmount <= 0,
                    'new_customer_only' => (bool) $coupon->new_customer_only,
                    'expires_at' => $coupon->expires_at?->format('d M Y'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'items_total' => $itemsTotal,
            'coupons' => $coupons,
        ]);
    }

    private function cartSummary(Request $request): array
    {
        $cart = collect(
            $request->session()->get('cart', [])
        )->values();

        $brandIds = $cart
            ->pluck('brand_id')
            ->unique()
            ->values();

        $itemsTotal = (float) $cart->sum(function (array $item): float {
            return (float) $item['unit_price']
                * (int) $item['quantity'];
        });

        return [
            'brand_id' => $brandIds->count() === 1
                ? (int) $brandIds->first()
                : null,
            'items_total' => $itemsTotal,
        ];
    }

    private function unavailableMessage(
        Coupon $coupon,
        int $brandId,
        float $itemsTotal
    ): ?string {
        if ($coupon->status !== Coupon::STATUS_ACTIVE) {
            return 'This coupon is inactive.';
        }

        if (!is_null($coupon->brand_id)
            && (int) $coupon->brand_id !== $brandId) {
            return 'This coupon is not available for this brand.';
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return 'This coupon has not started yet.';
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return 'This coupon has expired.';
        }

        if (!is_null($coupon->usage_limit)
            && $coupon->used_count >= $coupon->usage_limit) {
            return 'This coupon usage limit is over.';
        }

        if ($itemsTotal < (float) $coupon->min_order_amount) {
            return 'Minimum order amount for this coupon is ৳'
                .number_format((float) $coupon->min_order_amount, 2)
                .'.';
        }

        return null;
    }

    public function popup(Request $request): JsonResponse
    {
        $brandId = $request->filled('brand_id')
            ? (int) $request->input('brand_id')
            : null;

        $activeBrand = $brandId
            ? Brand::query()
                ->whereKey($brandId)
                ->where('is_active', true)
                ->first()
            : null;

        $coupon = Coupon::query()
            ->with('brand')
            ->active()
            ->popup()
            ->where(function ($query) use ($brandId): void {
                $query->whereNull('brand_id');

                if ($brandId) {
                    $query->orWhere(
                        'brand_id',
                        $brandId
                    );
                }
            })
            ->orderByRaw(
                'CASE WHEN brand_id IS NULL THEN 1 ELSE 0 END'
            )
            ->latest('id')
            ->get()
            ->first(
                fn (Coupon $coupon): bool =>
                    $coupon->isUsableNow()
            );

        if (!$coupon) {
            return response()->json([
                'status' => 'empty',
                'coupon' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'coupon' => $coupon->popupData($activeBrand),
        ]);
    }
}
