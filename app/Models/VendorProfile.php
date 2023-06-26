<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Helper\Helper;
use App\Http\Controllers\Controller;

class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_image',
        'aadhar_no',
        'pan_no',
        'bank_statement',
        'pan_card_image',
        'aadhar_front_image',
        'aadhar_back_image',
        'lat',
        'long',
        'location',
        'address',
        'remark',
        'status',
    ];

    public function vendor() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function vendor_availability() {
        return $this->hasMany(VendorAvailability::class, 'user_id');
    }

    public static function getDataByUserId($user_id) {
        return static::where('user_id', $user_id)->first();
    }

    public static function getVendorNameAndId() {
        return static::where('status', 1)->pluck('store_name', 'user_id');
    }

    public static function getNameById($id) {
        return static::where('user_id', $id)->first('store_name');
    }

     /**
     * Get vendor By Id.
     * takes parameter User id.
     * returns vendor's data
     */

     public static function getDataById($id) {
        return static::where('id', $id)->where('status',1)->first();
    }

    public static function featuredStoreWithDistance($latitude, $longitude,$limit){
        $haversine = "(
            6371 * acos(
                cos(radians(" .$latitude. "))
                * cos(radians(`lat`))
                * cos(radians(`long`) - radians(" .$longitude. "))
                + sin(radians(" .$latitude. ")) * sin(radians(`lat`))
            )
        )";

        $data = static::join('users', 'users.id', 'vendor_profiles.user_id')
            ->select("vendor_profiles.*", "users.is_vendor", 'users.featured_store', 'users.as_vendor_verified', 'users.delivery_range')
            ->where('users.is_vendor', 1)
            ->where('users.featured_store',true)
            ->where('users.as_vendor_verified',true)
            ->where('users.is_vendor_online', 1)
            ->selectRaw("round($haversine, 2) AS distance")
            ->take($limit)
            ->get();

        $selectedVendors = [];
        foreach ($data as $key => $value) {
            $singleDistance = Helper::distance($latitude, $longitude, $value->lat, $value->long, "K");
            $value['miles'] = $singleDistance;
            if((float)$value->delivery_range > (float)$singleDistance) {
                $selectedVendors[] = $value;
            }
        }
        $selectedVendorsList = Controller::customPaginate($selectedVendors,$limit);;
        return $selectedVendorsList;
    }

    /**
     * Only For Api 
     */
    public function vendor_available() {
        $today = Carbon::now();
        $dayOfTheWeek = $today->dayOfWeek;
        return $this->hasMany(VendorAvailability::class, 'user_id')
                    ->where('status', 1)
                    ->whereTime('start_time', '<=', $today->format("H:i"))
                    ->whereTime('end_time', '>=', $today->format("H:i"))
                    ->where('week_day', $dayOfTheWeek);
    }

    public static function storeDistance($latitude, $longitude, $distance, $page) {
         
     
        $haversine = "(
            6371 * acos(
                cos(radians(" .$latitude. "))
                * cos(radians(`lat`))
                * cos(radians(`long`) - radians(" .$longitude. "))
                + sin(radians(" .$latitude. ")) * sin(radians(`lat`))
            )
        )";
        // $data = static::with('vendor_availability')->whereHas('vendor_availability', function($q) {
        //         $q->where('status', 1);
        //     })->get();

        // $data = static::join('users', 'users.id', 'vendor_profiles.user_id')
        //     ->select("vendor_profiles.*", "users.is_vendor", 'users.featured_store', 'users.as_vendor_verified', 'users.is_vendor_online')
        //     ->where('users.is_vendor', 1)
        //     ->where('users.as_vendor_verified', 1)
        //     ->where('users.is_vendor_online', 1)
        //     // ->with('vendor')
        //     // ->with('vendor_available')->whereHas('vendor_available')
        //     ->selectRaw("round($haversine, 2) AS distance")
        //     // ->where("distance", "<=", 'delivery_range')
        //     ->having("distance", "<=", $distance)
        //     ->orderby("distance", "asc")
        //     ->paginate($page);

            
        // dd($distance);

        $query = static::join('users', 'users.id', 'vendor_profiles.user_id')
            ->select("vendor_profiles.*", "users.is_vendor", 'users.featured_store', 'users.as_vendor_verified', 'users.is_vendor_online', 'users.delivery_range')
            ->where('users.is_vendor', 1)
            ->where('users.as_vendor_verified', 1)
            ->where('users.is_vendor_online', 1)
            // ->with('vendor')
            // ->with('vendor_available')->whereHas('vendor_available')
            ->selectRaw("round($haversine, 2) AS distance");
            // ->where("distance", "<=", 'delivery_range')
            if($distance > 0) {
                $query->having("distance", "<=", $distance);
            }

            // $query->Raw("distance > users.delivery_range"); ///having("distance", ">", 'users.delivery_range');
            
            $data = $query->orderby("distance", "asc")->paginate($page);

            $selectedVendors = [];
            foreach ($data as $key => $value) {
                $singleDistance = Helper::distance($latitude, $longitude, $value->lat, $value->long, "K");
                $value['miles'] = $singleDistance;
                if((float)$value->delivery_range > (float)$singleDistance) {
                    $selectedVendors[] = $value;
                }
            }
            $selectedVendorsList = Controller::customPaginate($selectedVendors,$page);;
        return $selectedVendorsList;
    }



    // public function getDistance($lat1,  $longitude, $lat2, $value->long, $unit) {

        
    // }

    public static function getStoreCategoryWise($latitude, $longitude, $distance, $pagination, $vendor_id) {
         
        $haversine = "(
            6371 * acos(
                cos(radians(" .$latitude. "))
                * cos(radians(`lat`))
                * cos(radians(`long`) - radians(" .$longitude. "))
                + sin(radians(" .$latitude. ")) * sin(radians(`lat`))
            )
        )";

        $data = static::join('users', 'users.id', 'vendor_profiles.user_id')
        ->select("vendor_profiles.*", "users.is_vendor", 'users.featured_store', 'users.as_vendor_verified', 'users.is_vendor_online', 'users.delivery_range')
        ->whereIn('user_id', $vendor_id)
        ->where('users.as_vendor_verified', 1)
        ->where('users.is_vendor_online', 1)
        // ->with('vendor_available')->whereHas('vendor_available')
        ->selectRaw("round($haversine, 2) AS distance")
        ->having("distance", "<=", $distance)
        ->orderby("distance", "asc")
        ->paginate($pagination);

        $selectedVendors = [];
        foreach ($data as $key => $value) {
            $singleDistance = Helper::distance($latitude, $longitude, $value->lat, $value->long, "K");
            $value['miles'] = $singleDistance;
            if((float)$value->delivery_range > (float)$singleDistance) {
                $selectedVendors[] = $value;
            }
        }
        $selectedVendorsList = Controller::customPaginate($selectedVendors,$pagination);;
        return $selectedVendorsList;
    }
}
