<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',

        'product_name',
        'product_code',
        'product_image',

        'size',
        'color',
        'packaging_type',
        'packaging_tier_id',
        'packaging_tier_name',
        'packaging_min_pieces',
        'packaging_max_pieces',
        'packaging_charge',

        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'packaging_charge' => 'decimal:2',
        'packaging_tier_id' => 'integer',
        'packaging_min_pieces' => 'integer',
        'packaging_max_pieces' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
