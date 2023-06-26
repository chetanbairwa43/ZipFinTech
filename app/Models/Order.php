<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'coupon_inventory_id',
        'item_total',
        'surcharge',
        'tax',
        'packing_fee',
        'vendor_id',
        'driver_id',
        'delivery_charges',
        'tip_amount',
        'grand_total',
        'driver_id',
        'commission_driver',
        'commission_admin',
        'order_type',
        'status',
        'delivery_otp',
        'tax_id_1',
        'tax_id_2'
    ];


    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function vendor() {
        return $this->hasOne(User::class, 'id', 'vendor_id');
    }

    public function driver() {
        return $this->hasOne(User::class, 'id', 'driver_id');
    }
    
    public function orderItem() {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
    public function orderAddress() {
        return $this->hasOne(UserAddress::class, 'id', 'address_id');
    }

    public function coupon() {
        return $this->hasOne(CouponInventory::class, 'id', 'coupon_inventory_id');
    }

    // public static function getOrderByUserId($userId,$month=null,$status=null) {
    //     $q = static::where('user_id',$userId)->orderBy('id','desc');
    //     if(!empty($month)){
    //         $q->whereMonth('created_at',$month)->whereYear('created_at',date('Y'));
    //     }
    //     if(!empty($status)){
    //         $q->where('status',$status);
    //     }
    //     return $q->get();
    // }
    public static function getOrderByDriver($userId,$status=null) {
        $q = static::where('driver_id',$userId)->orderBy('updated_at','desc');
        if(!empty($status)){
            $q->where('status',$status);
        }
        return $q->get();
    }
    public static function getPendingOrderByDriver($userId) {
        $q = static::where('driver_id',$userId)->where('status','!=','D');
        return $q->get();
    }
    public static function getCompleteOrderByDriver($userId) {
        $q = static::where('driver_id',$userId)->where('status','D');
        return $q->get();
    }
    public static function getOrderById($orderId) {
        return static::where('id',$orderId)->first();
    }

    public static function getOrderList($type, $id, $status=null, $filter=null, $keyword=null, $start_date=null, $end_date=null, $limit = null) {
        // return static::where('vendor_id', $vendor_id)->orderBy('created_at', 'desc')->get();
        if($type == 'user') {
            $query = static::where('user_id', $id)->orderBy('created_at', 'desc');
            // $q = static::where('user_id',$userId)->orderBy('id','desc');
        }
        // if($type == 'driver') {
        //     $query = static::where('driver_id', $id)->orderBy('created_at', 'desc');
        // }
        if($type == 'vendor') {
            $query = static::where('vendor_id', $id)->orderBy('created_at', 'desc');
        }

        // $query = static::where('vendor_id', $vendor_id)->orderBy('created_at', 'desc');

        if(!empty($status)){
            $query->where('status',$status);
        }
        if(!empty($keyword)){
            $query->where('id','like', '%'.$keyword.'%');
        }
        if(!empty($filter)){
            if($filter == 'this_week') {
                $startDate = Carbon::now()->startOfWeek()->startOfDay();
                $endDate = Carbon::now()->endOfWeek()->endOfDay();
                // $query->whereBetween('created_at', [Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay()]);
            }
            if($filter == 'last_week') {
                $startDate = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
                $endDate = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
                // $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek()->startOfDay(), Carbon::now()->subWeek()->endOfWeek()->endOfDay()]);
            }
            if($filter == 'this_month') {
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfMonth()->endOfDay();
                // $query->whereBetween('created_at', [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay()]);
            }
            if($filter == 'last_month') {
                $startDate = Carbon::now()->startOfMonth()->subMonth()->startOfDay();
                $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                // $query->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay()]);
            }
            if($filter == 'custom') {
                isset($start_date) ? $startDate = Carbon::createFromFormat('Y-m-d', $start_date)->startOfDay() : '';
                isset($end_date) ? $endDate = Carbon::createFromFormat('Y-m-d', $end_date)->endOfDay() : '';
            }
            if((isset($startDate)) && (isset($endDate))) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }
        if(!empty($limit)){
            $query->limit($limit);
        }
        return $query->get();
    }
}
