<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'mobile_logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'background_color',
        'button_color',
        'text_color',
        'font_family',
        'header_style',
        'footer_style',
        'contact_number',
        'email',
        'facebook_link',
        'instagram_link',
        'whatsapp_link',
        'address',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}