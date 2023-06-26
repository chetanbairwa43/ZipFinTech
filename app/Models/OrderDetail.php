<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'qty',
        'item_qty',
        'status',
        'variant_id',
    ];

    public function products() {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public static function getVariantProductById($id) {
        return static::where('id', $id)->first();
    }

    public static function getAllAcceptedProductByOrderId($id) {
        return static::where('order_id', $id)->where('status','A')->get();
    }
}
