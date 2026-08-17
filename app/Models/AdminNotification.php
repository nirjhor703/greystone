<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminNotification extends Model
{
    use HasFactory;

    public const CATEGORY_MAIN = 'main';
    public const CATEGORY_STOCK = 'stock';

    public const TYPE_NEW_ORDER = 'new_order';
    public const TYPE_COURIER_STATUS = 'courier_status';
    public const TYPE_LOW_STOCK = 'low_stock';
    public const TYPE_PERMISSION_CHANGED = 'permission_changed';

    protected $fillable = [
        'category',
        'type',
        'title',
        'message',
        'link_url',
        'notifiable_type',
        'notifiable_id',
        'meta',
        'reminder_date',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'reminder_date' => 'date',
        'read_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeCategory(
        Builder $query,
        string $category
    ): Builder {
        return $query->where('category', $category);
    }
}
