<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getDataByKey($key) {
        return static::where('key', $key)->first();
    }

    public static function getAllSettingData() {
        return static::pluck('value','key');
    }
}
