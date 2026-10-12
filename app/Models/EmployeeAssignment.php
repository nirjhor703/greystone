<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAssignment extends Model
{
    protected $fillable = ['people_profile_id', 'department_id', 'designation_id', 'assignment_label', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function profile(): BelongsTo { return $this->belongsTo(PeopleProfile::class, 'people_profile_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function designation(): BelongsTo { return $this->belongsTo(Designation::class); }
}
