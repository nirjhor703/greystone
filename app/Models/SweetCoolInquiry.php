<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SweetCoolInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'product_id',
        'source_page',
        'customer_name',
        'phone',
        'email',
        'company_name',
        'interest_type',
        'quantity_note',
        'preferred_contact',
        'message',
        'page_url',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
