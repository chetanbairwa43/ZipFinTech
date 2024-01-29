<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookDetails extends Model
{
    use HasFactory;
    protected $table = 'webhook_details';
    protected $fillable = [
        'user_id',
        'webhook_type',
        'customer_reference',
        'trans_response',
        'type'
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


