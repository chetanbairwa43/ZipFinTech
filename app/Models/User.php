<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSignUp;
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
        'name',
        'fname',
        'lname',
        'country_code',
        'phone',
        'email',
        'date_of_birth',
        'username',
        'bvn',
        'otp',
        'profile_image',
        // 'latitude',
        // 'longitude',
        'unique_id',
        'freshwork_id',
        'default_address',
        'device_token',
        'device_id',
        'featured_store',
        'password',
        'status',
        'otp_created_at',
        'otp_verified',
        'zip_tag',
        'verification_image',
        'is_africa_verifed',
        'pin_reset_otp',
        'tranfer_limit',
        'birth_place',
        'dob',
        'nationality',
        'pin',
        'gender',
        'primary_purpose',
        'cardholder_id',
        'created_origin',
        'user_image',
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

    public function virtual()
    {
        return $this->hasOne(VirtualAccounts::class,'user_id','id');
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // public function virtualAccounts() {
    //     return $this->hasOne(VirtualAccounts::class,'user_id');
    // }

    // public function vendor() {
    //     return $this->hasOne(VendorProfile::class, 'user_id');
    // }
    // public function staffPermissions() {
    //     return $this->hasMany(StaffPermissions::class, 'user_id','id');
    // }

    // public function vendor_availability() {
    //     return $this->hasMany(VendorAvailability::class, 'user_id');
    // }

    // public function vendorProducts(){
    //   return $this->hasMany(VendorProduct::class,'vendor_id','id');
    // }

    public function bank_account() {
        return $this->hasOne(BankAccount::class,'user_id');
    }
    public function virtualAccounts() {
        return $this->hasOne(VirtualAccounts::class,'user_id','id');
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

    public static function getUserByBVN($bvn) {
        return static::where('bvn', $bvn)->first();
    }

    public static function getUserByPhone($phoneNumber) {
        return static::where('phone', $phoneNumber)->first();
    }

    public static function findByPhoneOrEmail($phoneEmail = null) {
        return static::where(function($query) use ($phoneEmail) {
            $query->where('phone', $phoneEmail)
                ->orWhere('email', $phoneEmail);
        })->first();
    } 
    public static function getUserByEmail($email) {
        return static::where('email', $email)->first();
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

    public static function getDataByType($type, $zip_id=NULL, $email=NULL, $phone=null ) {
        $query = static::where('type', $type);

            if($type == 'zip_id') {
                $query->where('zip_id', $zip_id);
            }
            if($type == 'email') {
                $query->where('email', $email);
            }
            if($type == 'phone') {
                $query->where('phone', $phone);
            }

        return $data = $query;
    }

    public function getAvailableAmountAttribute()
    {
        $credits = $this->transactions()->where('transaction_type', 'cr')->sum('amount');
        $debits = $this->transactions()->where('transaction_type', 'dr')->sum('amount');

        return $credits - $debits;
    }

    public function getLastTransactionAttribute()
    {
        return $this->transactions()->latest('created_at')->first();
    }

    public function generateTwoFactorCode()
    {
        $this->timestamps = false; //Dont update the 'updated_at' field yet

        // $otp = rand(100000, 999999);
        $otp = 123456;

        $this->two_factor_code = $otp;
        // $this->two_factor_expires_at = now()->addMinutes(5);
        $this->save();

        //send email
        $mailData = EmailTemplate::getMailByMailCategory(strtolower('2FA'));
        if(isset($mailData)) {

            $arr1 = array('{otp}');
            $arr2 = array($otp);

            $email_content = $mailData->email_content;
            $email_content = str_replace($arr1, $arr2, $email_content);

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject,
                'message' => $email_content,
            ];

            try {
                //code...
                Mail::to($this->email)->send(new NewSignUp($config));
            } catch (\Throwable $th) {
                throw $th;
            }
        }

    }

    /**
     * Reset the MFA code generated earlier
     */
    public function resetTwoFactorCode()
    {
        $this->timestamps = false; //Dont update the 'updated_at' field yet

        $this->two_factor_code = null;
        $this->two_factor_expires_at = now();
        $this->save();
    }

    public function address(){
        return $this->hasOne(UserAddress::class,'id','user_id');
    }

    public function cardData(){
        return $this->hasOne(UserCard::class);
    }

    public function bebeficiaries(){
        return $this->hasMany(Beneficiary::class,'unique_id','unique_id');
    }

}
