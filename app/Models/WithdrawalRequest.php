<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'remark',
        'status',
    ];

    public function user() {
        return $this->hasOne(User::class,'id','user_id');
    }

    public static function getDataByUserId($user_id) {
        return static::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
    }
}
