<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumPackagingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'min_pieces', 'max_pieces', 'price', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'min_pieces' => 'integer',
        'max_pieces' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeShared($query)
    {
        return $query->whereNull('product_id')->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id');
    }
}
