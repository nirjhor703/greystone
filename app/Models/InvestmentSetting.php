<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, string $default = ''): string
    {
        return (string) (static::query()->where('key', $key)->value('value') ?? $default);
    }

    public static function setValue(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
