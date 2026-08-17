<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'color_hex',
        'size',
        'stock_quantity',
        'variant_sku',
        'status',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'status' => 'boolean',
    ];

    public function getNormalizedColorHexAttribute(): ?string
    {
        return self::normalizeColorHex($this->color_hex);
    }

    public static function normalizeColorHex(
        mixed $value
    ): ?string {
        $hex = strtoupper(
            trim((string) ($value ?? ''))
        );

        if ($hex === '') {
            return null;
        }

        if (preg_match('/^#([A-F0-9]{6})$/', $hex)) {
            return $hex;
        }

        if (preg_match('/^([A-F0-9]{6})$/', $hex)) {
            return '#'.$hex;
        }

        return null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
