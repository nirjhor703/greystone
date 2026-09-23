<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeopleProfileType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'default_permissions',
        'is_system',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'default_permissions' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];
}
