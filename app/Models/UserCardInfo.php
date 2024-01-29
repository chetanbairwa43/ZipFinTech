<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCardInfo extends Model
{
    protected $table='users_cards_info';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'card_id',
        'card_number',
        'resposnse',
        'expiry_month',
        'expiry_year',
        'cvv',
        'last_4',
        'card_currency',
        'brand',
        'card_holder_id',
        'card_name',
    ];


    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    // public function card_holder_detail() {
    //     return $this->hasOne(CardHolderDetails::class, 'cardholder_id', 'card_holder_id');
    // }

}
