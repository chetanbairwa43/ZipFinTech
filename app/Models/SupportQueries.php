<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportQueries extends Model
{
    use HasFactory;

    protected $table ='support_queries';
    protected $fillable = [
            'support_category',
            'user_id',
            'description',
            'query_response',
            'is_close',
        ];
        public function users()
        {
            return $this->hasOne(User::class,'id','user_id');
        }
        public function supportCategories()
        {
            return $this->hasOne(SupportCategories::class,'id','support_category');
        }


}
