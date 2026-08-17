<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';

    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENTAGE = 'percentage';

    protected $fillable = [
        'brand_id',
        'code',
        'title',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'status',
        'new_customer_only',
        'show_as_popup',
        'popup_badge',
        'popup_title',
        'popup_description',
        'popup_button_text',
        'popup_scroll_pixels',
        'topbar_text',
        'topbar_applied_text',
        'topbar_button_text',
        'popup_apply_loading_text',
        'popup_applied_text',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'new_customer_only' => 'boolean',
        'show_as_popup' => 'boolean',
        'popup_scroll_pixels' => 'integer',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePopup(Builder $query): Builder
    {
        return $query
            ->where('show_as_popup', true)
            ->where('new_customer_only', true);
    }

    public function isAvailableFor(
        int $brandId,
        float $itemsTotal
    ): bool {
        $brandMatches = is_null($this->brand_id)
            || (int) $this->brand_id === $brandId;

        return $brandMatches
            && $this->isUsableNow()
            && $itemsTotal >= (float) $this->min_order_amount;
    }

    public function isUsableNow(): bool
    {
        $now = now();

        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if (!is_null($this->usage_limit)
            && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function discountAmount(float $itemsTotal): float
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $amount = $itemsTotal * ((float) $this->discount_value / 100);

            if (!is_null($this->max_discount_amount)) {
                $amount = min(
                    $amount,
                    (float) $this->max_discount_amount
                );
            }
        } else {
            $amount = (float) $this->discount_value;
        }

        return round(min($amount, $itemsTotal), 2);
    }

    public function discountLabel(): string
    {
        return $this->discount_type === self::TYPE_PERCENTAGE
            ? number_format((float) $this->discount_value, 0).'% OFF'
            : '৳'.number_format((float) $this->discount_value, 0).' OFF';
    }

    public function snapshot(float $itemsTotal): array
    {
        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'code' => $this->code,
            'title' => $this->title,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'max_discount_amount' => $this->max_discount_amount
                ? (float) $this->max_discount_amount
                : null,
            'min_order_amount' => (float) $this->min_order_amount,
            'discount_amount' => $this->discountAmount($itemsTotal),
        ];
    }

    public function popupData(?Brand $activeBrand = null): array
    {
        $brand = $this->brand ?: $activeBrand;

        $discountLabel = $this->discountLabel();

        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'code' => $this->code,

            'badge' => $this->popup_badge
                ?: 'New Customer Offer',

            'title' => $this->popup_title
                ?: $discountLabel.' your first order',

            'description' => $this->popup_description
                ?: 'Use this welcome code during checkout. This offer is available only for new customers.',

            'button_text' => $this->popup_button_text
                ?: 'Use This Coupon',

            'apply_loading_text' => $this->popup_apply_loading_text
                ?: 'Applying...',

            'applied_text' => $this->popup_applied_text
                ?: 'Applied',

            'topbar_text' => $this->topbar_text
                ?: 'You are new - enjoy {discount} your first order! Code {code}',

            'topbar_applied_text' => $this->topbar_applied_text
                ?: '{discount} locked in - order before the timer ends.',

            'topbar_button_text' => $this->topbar_button_text
                ?: 'Apply code',

            'scroll_pixels' => max(
                (int) $this->popup_scroll_pixels,
                50
            ),

            'discount_label' => $discountLabel,

            'colors' => [
                'primary' => $brand?->primary_color ?: '#333333',
                'button' => $brand?->button_color
                    ?: $brand?->primary_color
                    ?: '#333333',
                'background' => $brand?->background_color ?: '#ffffff',
            ],

            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
