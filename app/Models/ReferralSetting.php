<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    protected $fillable = ['reward_amount'];
    protected $casts = ['reward_amount' => 'decimal:2'];

    public static function current(): self
    {
        return static::firstOrCreate([], ['reward_amount' => 50]);
    }
}
