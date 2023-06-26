<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPermissions extends Model
{
    use HasFactory;
    protected $table = 'staff_permissions';

    protected $fillable = [
        'user_id',
        'staff_permission',
    ];
}
