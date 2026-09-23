<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referrer extends Model
{
    protected $fillable = [
        'member_id',
        'name',
        'code',
        'commission_rate',
        'balance',
        'gift_balance',
        'successful_referrals',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'balance' => 'decimal:2',
        'gift_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function referredMembers(): HasMany { return $this->hasMany(Member::class, 'referred_by_id'); }

    public function totalBalance(): float
    {
        return (float) $this->balance + (float) $this->gift_balance;
    }
}
