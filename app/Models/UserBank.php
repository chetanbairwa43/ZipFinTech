<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'destinationAddress',
        'firstName',
        'user_id',
    ];

    protected $table = 'users_bank';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
