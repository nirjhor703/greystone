<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberCoupon extends Model
{
    protected $fillable = ['member_id', 'coupon_id', 'source', 'status', 'used_at'];
    protected $casts = ['used_at' => 'datetime'];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
}
