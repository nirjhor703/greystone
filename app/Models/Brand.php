<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'mobile_logo',
        'favicon',
        'offer_banners',
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
        'offer_banners' => 'array',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function sweetCoolInquiries(): HasMany
    {
        return $this->hasMany(SweetCoolInquiry::class);
    }
}
