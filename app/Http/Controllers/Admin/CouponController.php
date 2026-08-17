<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $coupons = $this->couponQuery($request)
            ->latest('id')
            ->get();

        if ($request->ajax()) {
            return view(
                'admin.coupons.partials.table-rows',
                compact('coupons')
            );
        }

        return view(
            'admin.coupons.index',
            compact('brands', 'coupons')
        );
    }

    public function show(Coupon $coupon): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'coupon' => $this->formData($coupon),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'code' => $this->normalizeCode(
                (string) $request->input('code')
            ),
        
            'new_customer_only' =>
                $request->boolean('new_customer_only'),
        
            'show_as_popup' =>
                $request->boolean('show_as_popup'),
        ]);

        $validated = $this->validateCoupon($request);
        $validated = $this->normalizeValidatedData($validated);

        $coupon = Coupon::create($validated);
        $coupon->load('brand');

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon added successfully!',
            'coupon' => $this->tableData($coupon),
        ]);
    }

    public function update(
        Request $request,
        Coupon $coupon
    ): JsonResponse {
        $request->merge([
            'code' => $this->normalizeCode(
                (string) $request->input('code')
            ),
        
            'new_customer_only' =>
                $request->boolean('new_customer_only'),
        
            'show_as_popup' =>
                $request->boolean('show_as_popup'),
        ]);

        $validated = $this->validateCoupon($request, $coupon);
        $validated = $this->normalizeValidatedData($validated);

        $coupon->update($validated);
        $coupon->refresh()->load('brand');

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon updated successfully!',
            'coupon' => $this->tableData($coupon),
        ]);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        if ($coupon->orders()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This coupon has orders and cannot be deleted.',
            ], 422);
        }

        $coupon->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon deleted successfully!',
        ]);
    }

    private function validateCoupon(
        Request $request,
        ?Coupon $coupon = null
    ): array {
        $discountMax = $request->input('discount_type')
            === Coupon::TYPE_PERCENTAGE
                ? 100
                : 999999;

        return $request->validate([
            'brand_id' => ['nullable', 'exists:brands,id'],
            'code' => [
                'required',
                'string',
                'max:60',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('coupons', 'code')
                    ->ignore($coupon?->id),
            ],
            'title' => ['nullable', 'string', 'max:120'],
            'discount_type' => [
                'required',
                Rule::in([
                    Coupon::TYPE_FIXED,
                    Coupon::TYPE_PERCENTAGE,
                ]),
            ],
            'discount_value' => [
                'required',
                'numeric',
                'min:1',
                "max:{$discountMax}",
            ],
            'max_discount_amount' => [
                'nullable',
                'numeric',
                'min:1',
                'max:999999',
            ],
            'min_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999',
            ],
            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:999999',
            ],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
            'status' => [
                'required',
                Rule::in([
                    Coupon::STATUS_ACTIVE,
                    Coupon::STATUS_INACTIVE,
                ]),
            ],

            'new_customer_only' => [
                'required',
                'boolean',
            ],

            'show_as_popup' => [
                'required',
                'boolean',
            ],

            'popup_badge' => [
                'nullable',
                'string',
                'max:100',
            ],

            'popup_title' => [
                'nullable',
                'string',
                'max:160',
            ],

            'popup_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'popup_button_text' => [
                'nullable',
                'string',
                'max:100',
            ],

            'popup_scroll_pixels' => [
                'nullable',
                'integer',
                'min:50',
                'max:5000',
            ],

            'topbar_text' => [
                'nullable',
                'string',
                'max:180',
            ],

            'topbar_applied_text' => [
                'nullable',
                'string',
                'max:180',
            ],

            'topbar_button_text' => [
                'nullable',
                'string',
                'max:80',
            ],

            'popup_apply_loading_text' => [
                'nullable',
                'string',
                'max:80',
            ],

            'popup_applied_text' => [
                'nullable',
                'string',
                'max:80',
            ],
        ]);
    }

    private function couponQuery(Request $request)
    {
        return Coupon::query()
            ->with('brand')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('brand', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('brand_id'), function ($query) use ($request): void {
                $query->where('brand_id', $request->input('brand_id'));
            })
            ->when($request->filled('discount_type'), function ($query) use ($request): void {
                $query->where('discount_type', $request->input('discount_type'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->input('status'));
            });
    }

    private function normalizeCode(string $code): string
    {
        return mb_strtoupper(trim($code));
    }

    private function normalizeValidatedData(array $validated): array
{
    $showAsPopup = (bool) (
        $validated['show_as_popup']
        ?? false
    );

    $newCustomerOnly = (bool) (
        $validated['new_customer_only']
        ?? false
    );

    /*
    | Popup coupon অবশ্যই new customer coupon হবে।
    */

    if ($showAsPopup) {
        $newCustomerOnly = true;
    }

    return [
        ...$validated,

        'brand_id' =>
            $validated['brand_id']
            ?? null,

        'title' =>
            $validated['title']
            ?? null,

        'max_discount_amount' =>
            $validated['max_discount_amount']
            ?? null,

        'min_order_amount' =>
            $validated['min_order_amount']
            ?? 0,

        'usage_limit' =>
            $validated['usage_limit']
            ?? null,

        'starts_at' =>
            $validated['starts_at']
            ?? null,

        'expires_at' =>
            $validated['expires_at']
            ?? null,

        'new_customer_only' =>
            $newCustomerOnly,

        'show_as_popup' =>
            $showAsPopup,

        'popup_badge' =>
            $validated['popup_badge']
            ?? null,

        'popup_title' =>
            $validated['popup_title']
            ?? null,

        'popup_description' =>
            $validated['popup_description']
            ?? null,

        'popup_button_text' =>
            $validated['popup_button_text']
            ?? null,

        'popup_scroll_pixels' =>
            (int) (
                $validated['popup_scroll_pixels']
                ?? 120
            ),

        'topbar_text' =>
            $validated['topbar_text']
            ?? null,

        'topbar_applied_text' =>
            $validated['topbar_applied_text']
            ?? null,

        'topbar_button_text' =>
            $validated['topbar_button_text']
            ?? null,

        'popup_apply_loading_text' =>
            $validated['popup_apply_loading_text']
            ?? null,

        'popup_applied_text' =>
            $validated['popup_applied_text']
            ?? null,
    ];
}

    private function tableData(Coupon $coupon): array
    {
        return [
            ...$this->formData($coupon),
            'brand_name' => $coupon->brand?->name ?: 'All Brands',
            'used_count' => $coupon->used_count,
            'starts_at_label' =>
                $coupon->starts_at?->format('d M Y h:i A') ?? 'Anytime',
            'expires_at_label' =>
                $coupon->expires_at?->format('d M Y h:i A') ?? 'No expiry',
        ];
    }

    private function formData(Coupon $coupon): array
    {
        return [
            'id' => $coupon->id,
            'brand_id' => $coupon->brand_id,
            'code' => $coupon->code,
            'title' => $coupon->title,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'max_discount_amount' => $coupon->max_discount_amount
                ? (float) $coupon->max_discount_amount
                : null,
            'min_order_amount' => (float) $coupon->min_order_amount,
            'usage_limit' => $coupon->usage_limit,
            'used_count' => $coupon->used_count,
            'starts_at' => $coupon->starts_at?->format('Y-m-d\TH:i'),
            'expires_at' => $coupon->expires_at?->format('Y-m-d\TH:i'),
            'status' => $coupon->status,
            'new_customer_only' =>
                (bool) $coupon->new_customer_only,

            'show_as_popup' =>
                (bool) $coupon->show_as_popup,

            'popup_badge' =>
                $coupon->popup_badge,

            'popup_title' =>
                $coupon->popup_title,

            'popup_description' =>
                $coupon->popup_description,

            'popup_button_text' =>
                $coupon->popup_button_text,

            'popup_scroll_pixels' =>
                (int) $coupon->popup_scroll_pixels,

            'topbar_text' =>
                $coupon->topbar_text,

            'topbar_applied_text' =>
                $coupon->topbar_applied_text,

            'topbar_button_text' =>
                $coupon->topbar_button_text,

            'popup_apply_loading_text' =>
                $coupon->popup_apply_loading_text,

            'popup_applied_text' =>
                $coupon->popup_applied_text,
        ];
    }
}
