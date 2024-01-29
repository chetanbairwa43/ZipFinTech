<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankLoan extends Model
{
    use HasFactory;
    protected $table= "bank_loans";
    protected $fillable = [
      'user_id',
      'loan_purpose',
      'residential_status',
      'employed_status',
      'monthly_income',
      'duration_of_loan',
      'increament',
      'desired_amount',
      'is_approved',
      'loan_amount',
      'loan_duration',
      'flat_fee',
      'fee_type',
      'repayment_method',
      'repayment_date',
      'warning_date',
      'collection_method',
      'repayment_schedule',

    ];

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public static function searchUser($keyword) {
      return static::where('user_id', 'like', '%'.$keyword.'%')->where('user',1)->get();
    }
    // public function vendor(){
    //   return $this->hasOne(User::class, 'user_id','name');
    // }

    // public static function allActiveBanks() {
    //     return static::where('status',1)->pluck('name','id');
    // }
}
