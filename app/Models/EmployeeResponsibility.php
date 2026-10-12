<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeResponsibility extends Model
{
    protected $fillable = ['people_profile_id', 'description', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];
    public function profile(): BelongsTo { return $this->belongsTo(PeopleProfile::class, 'people_profile_id'); }
}
