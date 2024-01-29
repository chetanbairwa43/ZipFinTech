<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMoney extends Model
{
    use HasFactory;

    protected $table = 'request_money';

    protected $fillable = [
        'request_user_id',
        'pay_user_id',
        'description',
        'amount',
        'status',
        'request_type',
        'generate_link',
    ];

    public function payer() {
        return $this->belongsTo(User::class,'pay_user_id');
    }

    public function moneyResciever() {
        return $this->belongsTo(User::class,'request_user_id');
    }

}
