<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function allKeyValues(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('global_app_settings', 3600, function () {
            try {
                return static::pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public static function getAllSettings(): array
    {
        return static::allKeyValues();
    }

    public static function get(string $key, $default = null)
    {
        $all = static::allKeyValues();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, $value, string $group = 'general')
    {
        \Illuminate\Support\Facades\Cache::forget('global_app_settings');
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
