<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;
    protected $table = 'virtual_accounts';
    protected $fillable = [
        'user_id',
        'business',
        'virtualAccount',
        'sessionId',
        'senderAccountName',
        'senderAccountNumber',
        'senderBankName',
        'sourceCurrency',
        'sourceAmount',
        'description',
        'amountReceived',
        'fee',
        'customerName',
        'settlementDestination',
        'status',
        'initiatedAt',
        'reference'
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


