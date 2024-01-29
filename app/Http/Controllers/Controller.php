<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\Admin\CartCollection;
use Salman\GeoCode\Services\GeoCode;
use Craftsys\Msg91\Facade\Msg91;
use stdClass;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\VendorProduct;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\CartDetail;
use App\Models\Notification;
use App\Models\OrderNote;
use App\Models\WalletTransaction;
use Validator;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $serverError = 500;
    protected $badRequest = 400;
    protected $unauthorized = 401;
    protected $forbidden = 403;
    protected $notFound = 404;
    protected $success = 200;
    protected $noContent = 204;
    protected $partialContent = 206;

    protected $response;
    protected $responseNew;
    protected $msg;

    public function __construct() {
        $this->response = new stdClass();
    }

    static public function isValidPayload(Request $request, $validSet,$customeMessage = []){
        $validator = Validator::make($request->all(), $validSet,$customeMessage);

        if($validator->fails()) {
            return $validator->errors()->first();
        }
    }
    static public function sendOtpOnNumber($otp,$MobileNumber){

       $sendOtp = Msg91::otp($otp)->to($MobileNumber)->send();
       return $sendOtp;

    }
    static public function customPaginate($items, $perPage, $pagination = null, $options = [])
    {
        $pagination = $pagination ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($pagination, $perPage), $items->count(), $perPage, $pagination, $options);
    }
  
    static public function createNotification($userId,$title=null,$body=null,$notification_type=null)
    {
       if(!isset($userId) || empty($userId)){
            return false;
       }
       $notification = Notification::create([
        'user_id'           => $userId,
        'title'             => $title,
        'body'              => $body,
        'notification_type' => $notification_type,
       ]);
       
       return $notification;
    }

    static public function staffPermissonsArray()
    { 
       $dataArray=[
        'dashboard',
        'user_management',
        'users',
        'permissions',
        'roles',
        'pages',
        'categories',
        'products',
        'store_products',
        'slider',
        'faq',
        'email_templates',
        'coupons',
        'coupon_inventory',
        'banks',
        'orders',
        'withdrawal_requests',
        'tax',
        'setting_management',
        'site_setting',
        'app_setting',
        'notification',
        'wallet_transactions',
       ];
       return  $dataArray;
    }

    public static function createOrderLog($orderId, $order_status, $note=null)
    {
       if(!isset($orderId) || empty($orderId)){
            return false;
       }
       $log = OrderNote::updateOrCreate([
            'order_id' => $orderId,
            'status' => $order_status,
        ],[
            'order_id' => $orderId,
            'status' => $order_status,
            'note' => $note,
        ]);

        return $log;
    }

    /**
     * Send OTP
     * @param phone Number
     * @return \Illuminate\Http\Response
     */
    public function sendOtp($phone){
        // try {
        //     $otp = (int)Helper::generateOtp();
        //     $response = Msg91::otp($otp)->to('91'.$phone)->template(env("Msg91_TEMPLATE_ID"))->send();
            
        //     return ["responseCode"=>$response->getStatusCode(), "message"=>trans('global.OTP_SENT'), "otp"=>$otp];
        // } catch (\Exception $e) {
        //     return ["responseCode"=>$this->badRequest, "message"=>trans('global.SOMETHING_WENT')];
        // }
        try {
            //code...
            $termii = new \Zeevx\LaraTermii\LaraTermii("TLz4smlmxN5nrvcMw5KhXSTr8XR8dZQpdNWoNnySpW6T6H1Vs9zlR1EGcmR6Nl");
            // $termii->sendOTP(int $to, string $from, string $message_type, int $pin_attempts, int $pin_time_to_live, int $pin_length, string $pin_placeholder, string $message_text, string $channel = "generic");
            $to='7790980197'; $from='ZipFinTech'; $message_type='plain'; $pin_attempts=2; $pin_time_to_live=1; $pin_length=6; $pin_placeholder=''; $message_text='Hi User, '.$otp.' is your verification code zip international limited.';
            return $termii->sendOTP($to, $from, $message_type, $pin_attempts, $pin_time_to_live, $pin_length, $pin_placeholder, $message_text, $channel = "generic");
            return ["responseCode"=>$response->getStatusCode(), "message"=>trans('global.OTP_SENT'), "otp"=>$otp];

        } catch (\Throwable $th) {
            //throw $th;
            return ["responseCode"=>$this->badRequest, "message"=>trans('global.SOMETHING_WENT')];
        }
        // $curl = curl_init();
        // $data = array("api_key" => "TLz4smlmxN5nrvcMw5KhXSTr8XR8dZQpdNWoNnySpW6T6H1Vs9zlR1EGcmR6Nl", "to" => $phone,  "from" => "ZipFinTech",
        // "sms" => "OTP: 123456",  "type" => "plain",  "channel" => "generic" );

        // $post_data = json_encode($data);

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => "https://api.ng.termii.com/api/sms/send",
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => $post_data,
        //     CURLOPT_HTTPHEADER => array(
        //     "Content-Type: application/json"
        //     ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);
        // return $response;
    }

    public function sendTermiiOTP($phone, $otp, $pin_length = 6, $message_type='plain', $from='ZipFinTech', $pin_attempts=2, $pin_time_to_live=1, $pin_placeholder=''){
        try {
            //code...
            // $termii = new \Zeevx\LaraTermii\LaraTermii("TLz4smlmxN5nrvcMw5KhXSTr8XR8dZQpdNWoNnySpW6T6H1Vs9zlR1EGcmR6Nl");
            // // $termii->sendOTP(int $to, string $from, string $message_type, int $pin_attempts, int $pin_time_to_live, int $pin_length, string $pin_placeholder, string $message_text, string $channel = "generic");
            // $message_text='Hi User, '.$otp.' is your verification code zip international limited.';
            // return $termii->sendOTP($phone, $from, $message_type, $pin_attempts, $pin_time_to_live, $pin_length, $pin_placeholder, $message_text, $channel = "generic");
            // return ["responseCode"=>$response->getStatusCode(), "message"=>trans('global.OTP_SENT'), "otp"=>$otp];
            
            
            $curl = curl_init();
            $data = array("api_key" => "TLz4smlmxN5nrvcMw5KhXSTr8XR8dZQpdNWoNnySpW6T6H1Vs9zlR1EGcmR6Nl", "to" => $phone,  "from" => "zip cash", "sms" => "OTP: ".$otp,  "type" => "plain",  "channel" => "generic" );

            $post_data = json_encode($data);

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ng.termii.com/api/sms/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            
            // dd($response);

        } catch (\Throwable $th) {
            //throw $th;
            
        }
    }

    public function createWalletTransaction($vendor_id, $user_type = null, $id, $previous_balance=null, $current_balance=null, $amount, $order_id, $status, $remark=null, $payment_id=null, $razorpay_signature=null) {
        return WalletTransaction::create([
            'user_id' => $id,
            'payment_id' => $payment_id,
            'razorpay_signature' => $razorpay_signature,
            'previous_balance' => $previous_balance,
            'current_balance' => $current_balance,
            'amount' => $amount,
            'order_id' => $order_id,
            'vendor_id' => $vendor_id,
            'user_type' => $user_type ?? 'C',
            'status' => $status,
            'remark' => $remark,
        ]);
    }

}
