<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $table='user_meta';
    use HasFactory;
    protected $fillable = [
        'key',
        'value',
        'user_id',
    ];
    
}
