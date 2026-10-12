<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignationResponsibility extends Model
{
    protected $fillable = ['designation_id', 'description', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];
    public function designation(): BelongsTo { return $this->belongsTo(Designation::class); }
}
