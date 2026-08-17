<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivityLog extends Model
{
    use HasFactory;

    public const ACTION_CONFIRMED = 'order_confirmed';
    public const ACTION_UPDATED = 'order_updated';
    public const ACTION_QC_PASSED = 'qc_passed';
    public const ACTION_QC_ISSUE = 'qc_issue';
    public const ACTION_QC_RESOLVED = 'qc_resolved';
    public const ACTION_SENT_STEADFAST = 'sent_steadfast';
    public const ACTION_CANCELLED = 'order_cancelled';

    protected $fillable = [
        'order_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
