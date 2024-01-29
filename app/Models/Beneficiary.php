<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{

    // protected $table = 'transactions';

    protected $fillable = [
        'unique_id',
        'business_id',
        'account_holder_name',
        'first_name',
        'bank_name',
        'bank_code',
        'destination_address',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function user() {
        return $this->belongsTo(User::class,'unique_id','unique_id');
    }


}