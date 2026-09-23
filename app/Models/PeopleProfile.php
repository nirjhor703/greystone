<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeopleProfile extends Model
{
    public const PAYMENT_TYPES = [
        'commission' => 'Commission Based',
        'gift' => 'Gift / Help',
        'salary' => 'Salary',
        'contract' => 'Contract',
        'mixed' => 'Mixed',
    ];

    protected $fillable = [
        'profile_type',
        'name',
        'title',
        'photo_path',
        'email',
        'phone',
        'optional_phone',
        'address',
        'emergency_contact',
        'nid_or_document',
        'joining_date',
        'member_id',
        'referrer_id',
        'user_id',
        'payment_type',
        'salary_amount',
        'salary_type',
        'commission_rate',
        'gift_balance',
        'story',
        'tax_note',
        'admin_enabled',
        'admin_role',
        'admin_permissions',
        'permission_override_enabled',
        'referral_enabled',
        'role_notes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'salary_amount' => 'decimal:2',
        'gift_balance' => 'decimal:2',
        'joining_date' => 'date',
        'admin_enabled' => 'boolean',
        'admin_permissions' => 'array',
        'permission_override_enabled' => 'boolean',
        'referral_enabled' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function typeLabel(): string
    {
        return str($this->profile_type)->replace('_', ' ')->title();
    }

    public function paymentTypeLabel(): string
    {
        return self::PAYMENT_TYPES[$this->payment_type] ?? str($this->payment_type)->replace('_', ' ')->title();
    }

}
