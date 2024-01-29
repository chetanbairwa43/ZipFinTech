<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccounts extends Model
{
    use HasFactory;
    protected $table = 'virtual_accounts';
    protected $fillable = [
        'user_id',
        'accountType',
        'currency',
        'business',
        'business_id',
        'accountNumber',
        'bank_name',
        'KYCInformation',
        'accountInformation',
        'creation_origin',
        'card_type'
    ];

    /**
         * Get the user associated with the VirtualAccounts
         *
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         */
        public function virtual()
        {
            return static::where('user_id',237)->pluck('business_id');
        }
        public function users()
        {
            return $this->hasOne(User::class,'id','user_id');
        }

}


