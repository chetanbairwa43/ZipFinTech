<?php

namespace App\Helper;
use Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Coupon;
use App\Models\WalletTransaction;
use App\Models\CouponInventory;
use File;
use App\Models\Notification;
class Helper
{
    public static function fireBasePushNotification($notification)
    {
        try { 
            $getNotification = Notification::getNotificationById($notification->id);
            $getToken = User::getUserById($getNotification->user_id);
            
            $firebaseToken[]= $getToken->device_token;
          
            $SERVER_API_KEY = env('FIREBASE_SERVER_KEY');
            
            
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $getNotification->title,
                    "body"  => $getNotification->body,
                ]
            ];
      
            $dataString = json_encode($data);
    
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
    
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    
            $response = curl_exec($ch);
            return $response;


            } catch (\Exception $e) {
                return false;
            }
    }

    public static function pushNotification($data,$userID,$title = null,$orderID = null, $notification_type = null)
    { 
       try {
        $userName = !empty(User::getNameById($userID)) ? User::getNameById($userID) :'User';
        $arr1 = array('{user}','{orderNo}');
        $arr2 = array($userName->name,$orderID);
        $msg = str_replace($arr1, $arr2, $data);
   
        return Helper::createNotification($userID,$title,$msg, $notification_type = null);
         
        
        } catch (\Exception $e) {
            return false;
        }
      
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
       
       $getToken = User::getUserById($notification->user_id);
            
            $firebaseToken[]= $getToken->device_token;
          
            $SERVER_API_KEY = env('FIREBASE_SERVER_KEY');
            
            
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $notification->title,
                    "body"  => $notification->body,
                ]
            ];
      
            $dataString = json_encode($data);
    
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
    
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    
            $response = curl_exec($ch);
            return $response;
     
    }
    public static function generateReferCode()
    {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    
        return strtoupper(substr(str_shuffle($str_result),0, 10));
    }
    
    public static function createTransaction($userID,$previousBalance,$currentBalance,$amount,$status,$remark,$orderId=null,$user_type='C')
    {
       if(!empty($userID) && !empty($previousBalance) && !empty($currentBalance) && !empty($amount) && !empty($status)) {
            $walletTransaction = WalletTransaction::create([
                'user_id'                => $userID,
                'previous_balance'       => $previousBalance,
                'current_balance'        => $currentBalance,
                'amount'                 => $amount,
                'status'                 => $status,
                'order_id'               => $orderId ?? null,
                'remark'                 => $remark ?? '',
                'user_type'              => $user_type ?? 'C',
            ]);
            return $walletTransaction;
       }
       return false;
    }
    public static function storeImage($image, $destinationPath, $old_image = null)
    {
        try {
            if(!empty($old_image)) {
                if(File::exists($destinationPath.'/'.$old_image)) {
                    unlink($destinationPath.'/'.$old_image);
                }
            }
            $file = $image;
            $name =time().'-'.$file->getClientOriginalName();
            $file->move($destinationPath, $name);
            
            return $name;
        } catch (\Exception $e) {
            return 0;
        }
    }
    public static function imageThumbnail($image,$destinationPath,$height,$width,$old_image = null)
    {   
        //  $destinationPath = public_path('/uploads/category-images');
        try {
            if(!empty($old_image)) {
                if(File::exists($destinationPath.'/'.$old_image)) {
                    unlink($destinationPath.'/'.$old_image);
                }
            }
        
            $imageName =time().'-'.$image->getClientOriginalName();

            $img = Image::make($image->path());

            $img->resize($height,$width, function ($const) {
                $const->aspectRatio();
            })->save($destinationPath.'/'.$imageName);

            // $image->move($destinationPath, $imageName);
        
            return $imageName;

        } catch (\Exception $e) {
            return 0;
        }
    }
    // public static function removeImage($destinationPath, $old_image = null)
    // {
    //     try {
    //         if(!empty($old_image)) {
    //             if(File::exists($destinationPath.'/'.$old_image)) {
    //                 unlink($destinationPath.'/'.$old_image);
    //             }
    //         }
    //         return 'Image Removed';
    //     } catch (\Exception $e) {
    //         return 0;
    //     }
    // }

    public static function generateOtp()
    {
        return rand(1111,9999);
    }
    public static function dayFromNumber($day)
    {

        $days = [
           '1' => 'Monday',
           '2' => 'Tuesday',
           '3' => 'Wednesday',
           '4' => 'Thursday',
           '5' => 'Friday',
           '6' => 'Saturday',
           '7' => 'Sunday'
        ];

        return $days[$day];

    }
    public static function userCartClear($userID)
    {
        $userCart=Cart::where('user_id',$userID)->first();
        if(!empty($userCart)){
            $userCart->delete();
        }
        $getCartDetails=CartDetail::where('user_id',$userID)->delete();
        return true;
    }


    public static function Messages() {
        $jsonString = file_get_contents(storage_path('json/message.json'));
        $data = json_decode($jsonString, true);
        return $data;
    }

    public static function units() {
        return $units = [
            'kg' => 'kg',
            'grm' => 'grm',
            'ltr' => 'ltr',
            'ml' => 'ml',
            'dozen' => 'dozen',
            'pieces' => 'pieces',
        ];
    }

    public static function deliveryRange() {
        return $range = [
            '500' => '500 meter',
            '1' => '1 km',
            '2' => '2 km',
            '3' => '3 km',
            '4' => '4 km',
            '5' => '5 km',
            '6' => '6 km',
            '7' => '7 km',
            '8' => '8 km',
            '9' => '9 km',
            '10' => '10 km',
            '15' => '15 km',
            '20' => '20 km',
            '25' => '25 km',
            '30' => '30 km',
            '35' => '35 km',
            '40' => '40 km',
            '45' => '45 km',
            '50' => '50 km',
        ];
    }

    public static function notificationRange() {
        return $range = [
            '5' => '5 km',
            '10' => '10 km',
            '15' => '15 km',
            '20' => '20 km'
        ];
    }
    
    public static function distance($lat1, $lon1, $lat2, $lon2, $unit='K') {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
          return 0;
        }
        else {
          $theta = (float)$lon1 - (float)$lon2;
          $dist = sin(deg2rad((float)$lat1)) * sin(deg2rad((float)$lat2)) +  cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) * cos(deg2rad((float)$theta));
          $dist = acos((float)$dist);
          $dist = rad2deg((float)$dist);
          $miles = (float)$dist * 60 * 1.1515;
          $unit = strtoupper($unit);
      
          if ($unit == "K") {
            return ($miles * 1.609344);
          }
        }
    }
    
    public static function orderStatus() {
        return $status = [
            'OP' => 'Order Placed',
            'A' => 'Accepted',
            'R' => 'Reject',
            'PC' => 'Pickup',
            'RR' => 'Return Request',
            'RF' => 'Refund',
            'D' => 'Delivered',
            'P' => 'Pending',
        ];
    }
    public static function driverOrderStatus() {
        return $status = [
            'A'   => 'Pickup',
            'PC'  => 'Delivered',
        ];
    }
    public static function walletTransactionsStatus() {
        return $status = [
            'C'  => 'Credit',
            'D'  => 'Debit',
            'RF' => 'Refund',
            'W'  => 'Withdrawal',
            'E'  => 'Earn',
            'F'  => 'Failed'
        ];
    }

      public static function couponValid($userId) {
        
         /**Coupon value */
         $userCart=Cart::userTempCartData($userId);
         $userCartData=Cart::getUserCart($userId);
         $getCartVendor = '';
         $cartCost = 0; 

         foreach($userCartData as $item){
           $getCartVendor=$item->getProductData->vendor_id;
           $cartCost = $cartCost + (($item->getVariantData->price)*$item->qty);
         }

         $todayDate = date('Y-m-d');
         $getCoupon=Coupon::getCouponByVendor($userCart->coupon_code,$getCartVendor);

         /**If coupon code invalid */
         $couponValue=1;

         if(!empty($getCoupon)){

            if($getCoupon->valid_from > $todayDate || $getCoupon->valid_to < $todayDate){
                $couponValue=0;
            }
   
            $couponInventroy=CouponInventory::getCouponInventoryByUser($userId,$getCoupon->coupon_code);
            
            /**if user already used coupon */
            if($getCoupon->max_reedem<=count($couponInventroy)){
                $couponValue=0;
            }
   
            /**if Coupon usage limit has been reached */
            if($getCoupon->remainig_user==0){
                $couponValue=0;
            }
           
   
            if($getCoupon->min_order_value > $cartCost){
                $couponValue=0;
            }
         }else{
            $couponValue=0;
         }

        if($couponValue==0 && !empty($userCart)){
            $userCart->coupon_code = null;
            $userCart->save();
            return false;
        }else{
            return true;
        }
       
    }
    
    public static function vendorOrderFilter() {
        return $filter = [
            'this_week' => 'This Week',
            'last_week' => 'This Week',
            'this_month' => 'This Month',
            'last_month' => 'This Month',
            'custom' => 'Custom',
        ];
    }

}
