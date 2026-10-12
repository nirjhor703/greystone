<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    protected $fillable = ['department_id', 'name', 'description', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function responsibilities(): HasMany { return $this->hasMany(DesignationResponsibility::class); }
    public function profiles(): HasMany { return $this->hasMany(PeopleProfile::class, 'primary_designation_id'); }
}
