<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'from_name',
        'from_email',
        'email_category',
        'email_subject',
        'email_content',
        'status',
    ];

    public static function getMailByMailCategory($category) {
        return static::where('email_category', $category)->first();
    }
    
}
