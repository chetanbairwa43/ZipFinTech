<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

    public static function allActiveBanks() {
        return static::where('status',1)->pluck('name','id');
    }
}
