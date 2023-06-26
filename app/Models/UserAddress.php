<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table='user_addresses';
    use HasFactory;
    protected $fillable = [
        'street_name',
        'house_number',
        'additional',
        'postal_code',
        'state',
        'city',
        'country',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
