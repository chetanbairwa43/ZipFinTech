<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    protected $table='users_card';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'card_id',
        'cardholder_id',
        'card_type',
        'card_brand',
        'resposnse',
        'card_currency',
    ];


    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function cardholder_details()
    {
        return $this->belongsTo(CardHolderDetails::class, 'cardholder_id', 'cardholder_id');
    }

}
