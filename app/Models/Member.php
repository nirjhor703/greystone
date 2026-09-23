<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    public const DIVISIONS = [
        'dhaka' => 'Dhaka',
        'chattogram' => 'Chattogram',
        'rajshahi' => 'Rajshahi',
        'khulna' => 'Khulna',
        'barishal' => 'Barishal',
        'sylhet' => 'Sylhet',
        'rangpur' => 'Rangpur',
        'mymensingh' => 'Mymensingh',
    ];

    protected $fillable = [
        'name', 'email', 'password', 'gender', 'mobile', 'mobile_2',
        'address', 'date_of_birth', 'division', 'google_id', 'avatar_url',
        'email_verified_at', 'marketing_consent_at', 'referred_by_id',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'marketing_consent_at' => 'datetime',
        ];
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0] ?: $this->name;
    }

    public function referrer(): BelongsTo { return $this->belongsTo(Referrer::class, 'referred_by_id'); }
    public function walletCoupons(): HasMany { return $this->hasMany(MemberCoupon::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
}
