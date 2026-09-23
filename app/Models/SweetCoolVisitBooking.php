<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SweetCoolVisitBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'email',
        'phone',
        'visit_date',
        'visit_time',
        'contact_reason',
        'role_tags',
        'message',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'role_tags' => 'array',
    ];
}
