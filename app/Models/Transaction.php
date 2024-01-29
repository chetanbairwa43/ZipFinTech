<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $casts = [
        'amount' => 'decimal:2',
    ];
    
    protected $fillable = [
        'user_id',
        'transaction_type',
        'amount',
        'transaction_about',
        'description',
        'complete_response',
        'beneficiary_id',
        't_id',
        'user_type',
        'comon_id',
        'sender_id',
        'receiver_id',
        'phone',
        'telcos',
        'currency',
        'data_code',
        'dataplan',
        'customer_reference',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];


    public function user() {
        return $this->belongsTo(User::class);
    }
    public function sender()
    {
        return $this->hasOne(User::class, 'id','sender_id');
    }
    public function receiver()
    {
        return $this->hasOne(User::class, 'id','receiver_id');
    }


}
