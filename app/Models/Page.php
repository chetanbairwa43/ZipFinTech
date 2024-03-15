<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];

    public static function getPageBySlug($slug) {
        return static::where('slug', $slug)->first();
    }

    public static function getPageById($id) {
        return static::where('id', $id)->first();
    }
}
