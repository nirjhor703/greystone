<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentInvestor extends Model
{
    public const TYPES = [
        'external' => 'External Investor',
        'member' => 'Member',
        'employee' => 'Employee',
    ];

    public const LIFECYCLE_STATUSES = [
        'active' => 'Active',
        'inactive' => 'Temporarily Inactive',
        'due_pending' => 'Due Pending',
        'settled' => 'Fully Settled',
    ];

    protected $fillable = [
        'member_id',
        'people_profile_id',
        'name',
        'type',
        'phone',
        'email',
        'term_months',
        'agreement_note',
        'attachment_path',
        'attachment_name',
        'is_active',
        'lifecycle_status',
        'closed_at',
        'due_capital',
        'due_profit',
        'due_total',
    ];

    protected $casts = [
        'term_months' => 'integer',
        'is_active' => 'boolean',
        'closed_at' => 'datetime',
        'due_capital' => 'decimal:2',
        'due_profit' => 'decimal:2',
        'due_total' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function peopleProfile(): BelongsTo
    {
        return $this->belongsTo(PeopleProfile::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(InvestmentEntry::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? str($this->type)->replace('_', ' ')->title();
    }

    public function lifecycleLabel(): string
    {
        return self::LIFECYCLE_STATUSES[$this->lifecycle_status] ?? str($this->lifecycle_status)->replace('_', ' ')->title();
    }
}
