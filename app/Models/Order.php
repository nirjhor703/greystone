<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'Pending';
    public const STATUS_CONFIRMED = 'Confirmed';
    /** @deprecated Admin flow now uses Confirmed instead. */
    public const STATUS_PROCESSING = 'Processing';
    /** @deprecated Admin flow now uses Confirmed instead. */
    public const STATUS_SHIPPED = 'Shipped';
    public const STATUS_DELIVERED = 'Delivered';
    public const STATUS_CANCELLED = 'Cancelled';

    public const PAYMENT_UNPAID = 'Unpaid';
    public const PAYMENT_PAID = 'Paid';

    public const PAYMENT_COD = 'cash_on_delivery';

    public const SOURCE_CART = 'cart';
    public const SOURCE_BUY_NOW = 'buy_now';
    public const SOURCE_ADMIN_MANUAL = 'admin_manual';
    public const SOURCE_PHONE = 'phone';
    public const SOURCE_FACEBOOK = 'facebook';
    public const SOURCE_INSTAGRAM = 'instagram';
    public const SOURCE_WHATSAPP = 'whatsapp';
    public const SOURCE_STORE = 'store';
    public const SOURCE_OTHER = 'other';

    public const CHANNEL_ONLINE = 'online';
    public const CHANNEL_OFFLINE = 'offline';

    public const QC_NOT_CHECKED = 'not_checked';
    public const QC_PASSED = 'passed';
    public const QC_ISSUE = 'issue';

    protected $fillable = [
        'order_number',
        'invoice_number',
        'member_id',
        'brand_id',
    
        'customer_name',
        'phone',
        'alternative_phone',
        'customer_email',
    
        'delivery_area',
        'district',
        'area_thana',
        'road_no',
        'house_no',
        'full_address',
        'order_note',
    
        'payment_method',

        'coupon_id',
        'coupon_code',
        'coupon_discount_amount',
        'coupon_snapshot',
        'vat_enabled',
        'vat_percent',
        'vat_amount',
    
        'items_total',
        'delivery_charge',
        'grand_total',
    
        'status',
        'payment_status',

        'confirmed_by_user_id',
        'confirmed_at',
        'qc_status',
        'qc_by_user_id',
        'qc_checked_at',
        'qc_issue_note',
        'qc_resolved_by_user_id',
        'qc_resolved_at',
    
        'steadfast_consignment_id',
        'courier_status',
        'sent_to_steadfast_at',
        'steadfast_sent_by_user_id',
        'steadfast_response',
        'steadfast_error',
    
        'order_source',
        'order_channel',
        'source_note',
        'added_by_user_id',
    ];

    protected $casts = [
        'items_total' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'vat_enabled' => 'boolean',
        'vat_percent' => 'decimal:2',
        'vat_amount' => 'decimal:2',

        'sent_to_steadfast_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'qc_checked_at' => 'datetime',
        'qc_resolved_at' => 'datetime',
        'steadfast_response' => 'array',
        'coupon_snapshot' => 'array',
    ];

    public static function adminStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_DELIVERED,
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_CART => 'Website Cart',
            self::SOURCE_BUY_NOW => 'Website Buy Now',
            self::SOURCE_ADMIN_MANUAL => 'Admin Manual',
            self::SOURCE_PHONE => 'Phone Order',
            self::SOURCE_FACEBOOK => 'Facebook',
            self::SOURCE_INSTAGRAM => 'Instagram',
            self::SOURCE_WHATSAPP => 'WhatsApp',
            self::SOURCE_STORE => 'Physical Store',
            self::SOURCE_OTHER => 'Other',
        ];
    }

    public static function channelOptions(): array
    {
        return [
            self::CHANNEL_ONLINE => 'Online',
            self::CHANNEL_OFFLINE => 'Offline',
        ];
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'pending',
            self::STATUS_CONFIRMED => 'confirmed',
            self::STATUS_CANCELLED => 'cancelled',
            self::STATUS_DELIVERED => 'delivered',
            default => 'pending',
        };
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function steadfastSentBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'steadfast_sent_by_user_id'
        );
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'confirmed_by_user_id'
        );
    }

    public function qcBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'qc_by_user_id'
        );
    }

    public function qcResolvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'qc_resolved_by_user_id'
        );
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(OrderActivityLog::class);
    }

    public function qcStatusLabel(): string
    {
        return match ($this->qc_status ?: self::QC_NOT_CHECKED) {
            self::QC_PASSED => 'QC Passed',
            self::QC_ISSUE => 'QC Issue',
            default => 'QC Not Checked',
        };
    }

    public function qcStatusBadgeClass(): string
    {
        return match ($this->qc_status ?: self::QC_NOT_CHECKED) {
            self::QC_PASSED => 'active',
            self::QC_ISSUE => 'cancelled',
            default => 'pending',
        };
    }
}
