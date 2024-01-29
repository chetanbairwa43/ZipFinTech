<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table='user_addresses';
    use HasFactory;
    protected $fillable = [
        'street_name',
        'house_number',
        'additional',
        'postal_code',
        'state',
        'city',
        'country',
        'user_id'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function getAddressesByUser($userID) {
        return static::where('user_id', $userID)->first();
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

}
