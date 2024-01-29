<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;
        protected $table = "notifications";

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'type',
        'body',
        'image',
        'status'
    ];
    
    public static function getNotificationByuser($userID) {
        return static::where('user_id',$userID)->Orderby('id','desc')->get();
    }
    public static function getNotificationById($id) {
        return static::where('id',$id)->first();
    }
}
