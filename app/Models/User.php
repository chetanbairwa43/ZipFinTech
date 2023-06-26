<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class User extends Authenticatable
{
    protected $table='users';
    use HasApiTokens, HasFactory, Notifiable;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_driver',
        'is_vendor',
        'wallet_balance',
        'name',
        'phone',
        'email',
        'purpose_Category',
        'date_of_birth',
        'username',
        'residential_address',
        'otp',
        'profile_image',
        'latitude',
        'longitude',
        'location',
        'default_address',
        'referal_code',
        'device_token',
        'device_id',
        'is_driver_online',
        'is_vendor_online',
        'delivery_range',
        'self_delivery',
        'admin_commission',
        'as_driver_verified',
        'as_vendor_verified',
        'featured_store',
        'password',
        'status',
        'otp_created_at',
        'otp_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function driver() {
        return $this->hasOne(DriverProfile::class,'user_id');
    }
    
    public function vendor() {
        return $this->hasOne(VendorProfile::class, 'user_id');
    }
    public function staffPermissions() {
        return $this->hasMany(StaffPermissions::class, 'user_id','id');
    }

    public function vendor_availability() {
        return $this->hasMany(VendorAvailability::class, 'user_id');
    }

    public function vendorProducts(){
      return $this->hasMany(VendorProduct::class,'vendor_id','id');
    }

    public function bank_account() {
        return $this->hasOne(BankAccount::class,'user_id');
    }

    public static function getAllVendorsNameAndId() {
        return static::where('is_vendor', '=', 1)->pluck('name', 'id');
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
    /**
     * Get User Data By Phone no.
     * takes parameter phone no.
     * returns user's data
     */
    public static function findByPhone($phone = null) {
        return static::where('phone', $phone)->first();
    }

    /**
     * Get User Data By Referal Code.
     * takes parameter referal code.
     * returns user's data
     */
    public static function findByReferalCode($referal_code = null) {
        return static::where('referal_code', $referal_code)->first();
    }

    /**
     * Get User Name By Id.
     * takes parameter User id.
     * returns user's name
     */
    public static function getNameById($id) {
        return static::where('id', $id)->first('name');
    }
    public static function getUserById($id) {
        return static::where('id', $id)->first();
    }
     /**
     * Get vendor By Id.
     * takes parameter User id.
     * returns vendor's data
     */

    public static function getVendorByID($id) {
        return static::where('id', $id)->where('is_vendor',1)->first();
    }
     /**
     * Get vendor search result.
     * takes parameter keyword.
     * returns vendor's data(Array)
     */

     public static function searchVendor($keyword) {
        return static::where('name', 'like', '%'.$keyword.'%')->where('is_vendor',1)->get();
    }
    /**
     * Get Vendor's Name and Id .
     * 
     * returns vendors's Name and Id
     */
    public static function getVendorNameAndId() {
        return static::where('is_vendor', '=', 1)->pluck('name', 'id');
    }
     
    // public static function featuredStoreWithDistance($latitude, $longitude,$limit){
    //     $haversine = "(
    //         6371 * acos(
    //             cos(radians(" .$latitude. "))
    //             * cos(radians(`latitude`))
    //             * cos(radians(`longitude`) - radians(" .$longitude. "))
    //             + sin(radians(" .$latitude. ")) * sin(radians(`latitude`))
    //         )
    //     )";

    //     $data = static::select("*")
    //         ->where('is_vendor', 1)
    //         ->where('featured_store',true)
    //         ->where('as_vendor_verified',true)
    //         ->selectRaw("round($haversine, 2) AS distance")
    //         ->take($limit)
    //         ->get();

    //     return $data;
    // }
    // public static function storeDistance($latitude, $longitude, $distance, $page) {
         
     
    //     $haversine = "(
    //         6371 * acos(
    //             cos(radians(" .$latitude. "))
    //             * cos(radians(`latitude`))
    //             * cos(radians(`longitude`) - radians(" .$longitude. "))
    //             + sin(radians(" .$latitude. ")) * sin(radians(`latitude`))
    //         )
    //     )";
    //     // $data = static::with('vendor_availability')->whereHas('vendor_availability', function($q) {
    //     //         $q->where('status', 1);
    //     //     })->get();

    //     $data = static::select("*")
    //         ->where('is_vendor', 1)
    //         ->where('as_vendor_verified', 1)
    //         // ->where('is_vendor_online', 1)
    //         // ->with('vendor')
    //         // ->with('vendor_available')->whereHas('vendor_available')
    //         ->selectRaw("round($haversine, 2) AS distance")
    //         // ->where("distance", "<=", 'delivery_range')
    //         ->having("distance", "<=", $distance)
    //         ->orderby("distance", "asc")
    //         ->paginate($page);

    //     return $data;
    // }
    public static function getDriversWithStoreDistance($latitude, $longitude, $distance) {
         
        $haversine = "(
            6371 * acos(
                cos(radians(" .$latitude. "))
                * cos(radians(`latitude`))
                * cos(radians(`longitude`) - radians(" .$longitude. "))
                + sin(radians(" .$latitude. ")) * sin(radians(`latitude`))
            )
        )";

        $data = static::select("*")
            ->where('is_driver', 1)
            ->where('is_driver_online', 1)
            ->where('as_driver_verified', 1)
            ->where('status', 1)
            ->selectRaw("round($haversine, 2) AS distance")
            ->having("distance", "<=", $distance)
            ->orderby("distance", "asc")
            ->get();

        return $data;
    }

    // public static function getStoreCategoryWise($latitude, $longitude, $distance, $pagination, $vendor_id) {
         
    //     $haversine = "(
    //         6371 * acos(
    //             cos(radians(" .$latitude. "))
    //             * cos(radians(`latitude`))
    //             * cos(radians(`longitude`) - radians(" .$longitude. "))
    //             + sin(radians(" .$latitude. ")) * sin(radians(`latitude`))
    //         )
    //     )";

    //     $data = User::select("*")
    //     ->whereIn('id', $vendor_id)
    //     ->where('as_vendor_verified', 1)
    //     // ->where('is_vendor_online', 1)
    //     // ->with('vendor')
    //     // ->with('vendor_available')->whereHas('vendor_available')
    //     ->selectRaw("round($haversine, 2) AS distance")
    //     // ->where("distance", "<=", 'delivery_range')
    //     ->having("distance", "<=", $distance)
    //     ->orderby("distance", "asc")
    //     ->paginate($pagination);

    //     return $data;
    // }
}
