<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SweetCoolPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'logo_path',
        'hero_image_path',
        'teaser_kicker',
        'teaser_title',
        'teaser_description',
        'teaser_button_text',
        'hero_kicker',
        'hero_title',
        'hero_description',
        'hero_button_text',
        'intro_heading',
        'intro_description',
        'section_one_title',
        'section_one_body',
        'section_two_title',
        'section_two_body',
        'section_three_title',
        'section_three_body',
        'promo_badges',
        'gallery_images',
        'factory_images',
        'product_images',
    ];

    protected $casts = [
        'promo_badges' => 'array',
        'gallery_images' => 'array',
        'factory_images' => 'array',
        'product_images' => 'array',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(
            ['slug' => 'sweet-cool'],
            static::defaultAttributes()
        );
    }

    public static function defaultAttributes(): array
    {
        return [
            'teaser_kicker' => 'Our Factory',
            'teaser_title' => 'Sweet Cool',
            'teaser_description' => 'Feel free to visit our factory if you want to work with us.',
            'teaser_button_text' => "Let's Dive into SweetCool",
            'hero_kicker' => 'Factory Tour and Sourcing',
            'hero_title' => 'Inside Sweet Cool',
            'hero_description' => 'Upload your latest factory, product, and sourcing visuals from the dashboard and this page will present them with a polished showroom feel.',
            'hero_button_text' => 'Book a Visit',
            'intro_heading' => 'Built for factory visits, sourcing talks, and bulk conversations.',
            'intro_description' => 'Use this page to show your factory story, production mood, and product-ready capability in one place.',
            'section_one_title' => 'Factory floor',
            'section_one_body' => 'Show real production, setup, and facility visuals from the admin dashboard.',
            'section_two_title' => 'Product highlights',
            'section_two_body' => 'Display ready-to-sell items, showroom images, and sourcing references in clean sliders.',
            'section_three_title' => 'Schedule a visit',
            'section_three_body' => 'Visitors can request a time slot, and already-booked date/time combinations are automatically blocked.',
            'promo_badges' => [
                'Bulk order ready',
                '30% sale',
                'Factory visit open',
                'Custom sourcing',
            ],
            'gallery_images' => [],
            'factory_images' => [],
            'product_images' => [],
        ];
    }
}
