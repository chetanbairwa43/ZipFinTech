<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AfricaVerification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'africa-verification-user';
    protected $fillable = [
        'user_id',
        'email',
        'gender',
        'dob',
        'phone',
        'country',
        'nin',
        'bvn',
        'nationality',
        'full_name',
        'first_name',
        'middle_name',
        'last_name',
        'alternate_phone',
        'state_of_origin',
        'state_of_residence',
        'lga_of_origin',
        'lga_of_residence',
        'address_line_2',
        'address_line_3',
        'marital_status',
        'avatar',
        'watchlisted',
         
    ];

    public static function getAfricaVerification() {
        return static::where('status', 1)->get();
    }
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
