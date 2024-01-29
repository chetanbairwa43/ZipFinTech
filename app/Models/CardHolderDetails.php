<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardHolderDetails extends Model
{
    protected $table='cardholder_details';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'cardholder_id',
        'status',
        'resposnse'
    ];


    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function cardData()
    {
        return $this->hasMany(UserCard::class, 'cardholder_id', 'cardholder_id');
    }

    public function userCardHolder()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
