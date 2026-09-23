<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseMilestone extends Model
{
    protected $fillable = ['step', 'title', 'description', 'coupon_id', 'is_active'];

    protected $casts = ['step' => 'integer', 'is_active' => 'boolean'];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
