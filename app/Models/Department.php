<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'sort_order', 'is_active'];
    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];

    public function designations(): HasMany { return $this->hasMany(Designation::class); }
    public function profiles(): HasMany { return $this->hasMany(PeopleProfile::class, 'primary_department_id'); }
}
