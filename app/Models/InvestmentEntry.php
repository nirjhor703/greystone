<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentEntry extends Model
{
    public const TYPES = [
        'investor_investment' => 'Investor Investment',
        'business_cost' => 'Business Cost',
        'profit_payout' => 'Profit Payout',
        'capital_return' => 'Capital Return',
        'loan_received' => 'Loan Received',
        'loan_payment' => 'Loan Payment',
    ];

    public const CHANNELS = [
        'online' => 'Online',
        'offline' => 'Offline',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'active' => 'Active',
        'settled' => 'Settled',
        'returned' => 'Returned',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'investment_investor_id',
        'entry_type',
        'investment_channel',
        'entry_date',
        'active_date',
        'maturity_date',
        'amount',
        'purpose',
        'note',
        'attachment_path',
        'attachment_name',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'active_date' => 'date',
        'maturity_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(InvestmentInvestor::class, 'investment_investor_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->entry_type] ?? str($this->entry_type)->replace('_', ' ')->title();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? str($this->status)->replace('_', ' ')->title();
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->investment_channel] ?? str($this->investment_channel)->replace('_', ' ')->title();
    }

    public function countsAsActiveCapital(): bool
    {
        return $this->entry_type === 'investor_investment'
            && $this->status === 'active'
            && (! $this->active_date || $this->active_date->lte(now()));
    }
}
