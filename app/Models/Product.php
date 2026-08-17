<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';

    public const STOCK_IN = 'In Stock';
    public const STOCK_OUT = 'Out of Stock';

    public const AUDIENCE_MEN = 'men';
    public const AUDIENCE_WOMEN = 'women';
    public const AUDIENCE_BOTH = 'both';

    public const AVAILABLE_SIZES = [
        '3XS',
        '2XS',
        'XS',
        'S',
        'M',
        'L',
        'XL',
        '2XL',
        '3XL',
        '4XL',
        '5XL',
        '6XL',
        '7XL',
    ];

    protected $fillable = [
        'brand_id',
        'category_id',
        'audience',
        'name',
        'slug',
        'product_code',
        'regular_price',
        'sale_price',
        'stock_quantity',
        'stock_status',
        'colors',
        'material',
        'short_description',
        'description',
        'care_instructions',
        'is_featured',
        'is_new_arrival',
        'status',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'sizes' => 'array',
        'colors' => 'array',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
    ];

    

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true);
    }

    public function getDisplayPriceAttribute(): string
    {
        return number_format(
            $this->sale_price ?: $this->regular_price,
            2
        );
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null
            && $this->sale_price < $this->regular_price;
    }

    public function sizeStocks(): HasMany
    {
        return $this->hasMany(ProductSizeStock::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function sweetCoolInquiries(): HasMany
    {
        return $this->hasMany(SweetCoolInquiry::class);
    }






    public function getTotalVariantStockAttribute(): int
    {
        if ($this->relationLoaded('variants')) {
            return (int) $this->variants->sum('stock_quantity');
        }

        return (int) $this->variants()->sum('stock_quantity');
    }

    public function availableVariantColors()
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants
                ->where('status', true)
                ->where('stock_quantity', '>', 0)
                ->pluck('color')
                ->unique()
                ->values();
        }

        return $this->variants()
            ->active()
            ->inStock()
            ->distinct()
            ->orderBy('color')
            ->pluck('color');
    }

    public function variantsForColor(string $color)
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants
                ->where('status', true)
                ->filter(
                    fn (ProductVariant $variant) =>
                        mb_strtolower($variant->color)
                        === mb_strtolower($color)
                )
                ->values();
        }

        return $this->variants()
            ->active()
            ->whereRaw('LOWER(color) = ?', [
                mb_strtolower($color),
            ])
            ->orderBy('size')
            ->get();
    }

    public function syncStockFromVariants(): int
    {
        $totalStock = (int) $this
            ->variants()
            ->where('status', true)
            ->sum('stock_quantity');

        $this->forceFill([
            'stock_quantity' => $totalStock,
            'stock_status' => $totalStock > 0
                ? self::STOCK_IN
                : self::STOCK_OUT,
        ])->save();

        return $totalStock;
    }
}
