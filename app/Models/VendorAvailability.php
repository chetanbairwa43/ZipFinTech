<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'week_day',
        'start_time',
        'end_time',
        'status',
    ];

    public static function getStoreAvailabilityByUser($user_id) {
        return static::where('user_id', $user_id)->get();
    }

    public static function getStoreOpenAndCloseTimeByUserAndWeekDay($user_id, $weekday) {
        return static::select('start_time','end_time','status')->where('user_id', $user_id)->where('week_day', $weekday)->first();
    }
}
