<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'user_id',
        'account_name',
        'account_no',
        'ifsc_code',
        'status',
    ];

    public function bank() {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    public static function getAccountDetailByUserId($id) {
        return static::where('user_id', $id)->first();
    }
}
