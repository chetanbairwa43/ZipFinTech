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
use App\Models\Setting;
use Carbon\Carbon;
use File;
use App\Models\Notification;
class Helper
{
    public static function fireBasePushNotification($user_id, $notification_title, $notification_body)
    {
        try {
            $getToken = User::getUserById($user_id);
            
            $firebaseToken[]= $getToken->device_token;
          
            $SERVER_API_KEY = env('FIREBASE_SERVER_KEY');
            
            
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $notification_title,
                    "body"  => strip_tags($notification_body),
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
    public static function bvnVerification($bvnNo , $email = null, $dob , $fname, $lname, $imageData){

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://api.verified.africa/sfx-verify/v3/id-service/', [
        'body' => '{"searchParameter":"'.$bvnNo.'","verificationType":"BVN-FACE-MATCH-LIVENESS-VERIFICATION","dob":"'.$dob.'","firstName":"'.$fname.'","lastName":"'.$lname.'","selfieToDatabaseMatch":"true","selfie":"'.$imageData.'"}',
        'headers' => [
            'accept' => 'application/json',
            'apiKey' => 'hPuinQmwlfZlRpOrY2yL',
            'content-type' => 'application/json',
            'userid' => '1684246314513',
        ],
        ]);
        return $response->getBody();

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
            // dd($file);
            
            return $name;
        } catch (\Exception $e) {
            return 0;
        }
    }


    // public static function storeImage($image, $destinationPath, $old_image = null)
    // {
    //     try {
    //         if (!empty($old_image)) {
    //             $oldImagePath = $destinationPath.'/'.$old_image;
    //             if (Storage::exists($oldImagePath)) {
    //                 Storage::delete($oldImagePath);
    //             }
    //         }

    //         $file = $image;
    //         $name = time().'-'.$file->getClientOriginalName();

    //         // Store the file using the Storage facade
    //         Storage::putFileAs($destinationPath, $file, $name);

    //         return $name;
    //     } catch (\Exception $e) {
    //         \Log::error('Error in storeImage method: ' . $e->getMessage());
    //         return null; // Return null in case of an exception
    //     }
    // }


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
        return rand(111111,999999);
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

    public static function Messages() {
        $jsonString = file_get_contents(storage_path('json/message.json'));
        $data = json_decode($jsonString, true);
        return $data;
    }

    public static function notificationRange() {
        return $range = [
            '5' => '5 km',
            '10' => '10 km',
            '15' => '15 km',
            '20' => '20 km'
        ];
    }

    public static function fincraVerification($KYCInformation){
// dd($KYCInformation);
        $client = new \GuzzleHttp\Client();
        
        $response = $client->request('POST', 'https://sandboxapi.fincra.com/profile/virtual-accounts/requests/', [
        'body' => '{"currency":"NGN","accountType":"individual","KYCInformation":'.$KYCInformation.',"channel":"providus"}',
          'headers' => [
            'accept' => 'application/json',
            'api-key' => 'm98zn3Y70MXGu1VaZNhYOZO7CbULj6uU',
            'content-type' => 'application/json',
          ],
        ]);
        
        return $response->getBody();
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

    public static function termii($to, $otp) {
        $curl = curl_init();
        $apiKey = env("TERMII_API_KEY", "");
    
        $data = array(
            "api_key" => $apiKey,
            "to" => $to,
            "from" => "N-Alert",
            "sms" => "Hello from ZIP, Please find your ZIP Cash Authentication code: {$otp}",
            "type" => "plain",
            "channel" => "dnd"
        );
    
        $data = json_encode($data);
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.ng.termii.com/api/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'api-key: ' . $apiKey,
                'Content-Type: application/json'
            ),
        ));
    
        $response = curl_exec($curl);
    
        curl_close($curl);
        return $response;
    }

    // public static function linkGeneration($amount, $currency, $name, $email)
    // {
    //     $curl = curl_init();

    //     // Prepare the data for the POST request
    //     $postData = array(
    //         "amount" => $amount,
    //         "currency" => $currency,
    //         "customer" => array(
    //             "name" => $name,
    //             "email" => $email
    //         )
    //     );

    //     // Convert the data to JSON format
    //     $jsonData = json_encode($postData);

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => env("LIVE_URL").'/checkout/payments',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => $jsonData,
    //         CURLOPT_HTTPHEADER => array(
    //             'api-key: '.env("LIVE_KEY"),
    //             'x-pub-key: pk_test_NjQ1MjliZDJiZmRmMjhlN2MxOGFhOWRhOjoxMjc5NDc=',
    //             'x-business-id: 64529bd2bfdf28e7c18aa9da',
    //             'Accept: application/json',
    //             'Content-Type: application/json'
    //         ),
    //     ));

    //     $response = curl_exec($curl);
        
    //     curl_close($curl);
    //     return $response;
    // }

    public static function linkGeneration($amount, $currency, $name, $email ,$phoneNumber, $user_id)
    {
        
        $curl = curl_init();

        $postData = array(
            "amount" => (int)$amount,
            "currency" => $currency,
            "customer" => array(
                "name" => $name,
                "email" => $email,
                "phoneNumber" => $phoneNumber
            ),
            "paymentMethods" => [
                "bank_transfer",
                "card"
            ],
            "metadata" => [
                "userId" => $user_id
            ],
            "feeBearer"=> "customer",
            "settlementDestination" => "wallet",
            "defaultPaymentMethod" => "card"
        );

        // Convert the data to JSON format
        $jsonData = json_encode($postData);

        curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://sandboxapi.fincra.com/checkout/payments',
        CURLOPT_URL => env("LIVE_URL").'/checkout/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'x-pub-key: pk_NjQ1Zjk0MDRiYzgxODQ3YzQwZTQ0OGEwOjoxOTg1OTI=',
            // 'x-pub-key: pk_test_NjQ1MjliZDJiZmRmMjhlN2MxOGFhOWRhOjoxMjc5NDc=',
            'x-business-id: '.env('BUSINESS_ID'),
            'api-key: '.env("LIVE_KEY"),
            'content-type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
            
    public static function virtual_account($virtualAccountId) {
    // public static function virtual_account() {
        $curl = curl_init();
        
        $url = env("LIVE_URL").'/profile/virtual-accounts/'.urlencode($virtualAccountId);
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'api-key: '.env("LIVE_KEY"),
                'Accept: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }

    public static function fincraUsers(){
        $client = new \GuzzleHttp\Client();
        
        $response = $client->request('GET', env("LIVE_URL").'/profile/virtual-accounts/?currency=NGN&status=approved', [
          'headers' => [
            'accept' => 'application/json',
            'api-key' => env("LIVE_KEY"),
          ],
        ]);
        
        return $response->getBody();
    }

    public static function fincrabeneficiaries($business_id, $pageNo = 1, $perPage = 10){
        $requestData = [
            'page' => $pageNo,
            'perPage' => $perPage,
        ];

        $client = new \GuzzleHttp\Client();
    
        $response = $client->request('GET', env("LIVE_URL").'/profile/beneficiaries/business/' . $business_id, [
            'headers' => [
                'accept' => 'application/json',
                'api-key' => env("LIVE_KEY"),
                'content-type' => 'application/json',
            ],
            'query' => $requestData, // Send the data as query parameters
        ]);
    
        return $response->getBody();
    }

    public static function beneficiariesBussines($businessID) {

        $curl = curl_init();

        $url = env("LIVE_URL").'/profile/beneficiaries/business/' . urlencode($businessID);

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            // 'api-key: 16GH53GI9AzB17ov5QCgk1kw9m2uIGf3'
            'api-key:'.env("LIVE_KEY"),
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public static function payouts($business,$sourceCurrency,$destinationCurrency, $amount, $description, $customerReference, $firstName, $type, $accountHolderName, $accountNumber, $bank_code, $paymentDestination){
        $curl = curl_init();

        $postData = array(
            "business" => $business,
            "sourceCurrency" => $sourceCurrency,
            "destinationCurrency" => $destinationCurrency,
            "amount" => $amount,
            "description"=> $description,
            "customerReference" => $customerReference,
            "beneficiary" => array(
                "firstName" => $firstName,
                "type" => $type,
                "accountHolderName" => $accountHolderName,
                "accountNumber" => $accountNumber,
                "bankCode" => $bank_code,
            ),
            "paymentDestination"=> $paymentDestination,
        );
        $jsonData = json_encode($postData);

        curl_setopt_array($curl, array(
        CURLOPT_URL => env("LIVE_URL").'/disbursements/payouts',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'api-key:'.env("LIVE_KEY"),
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    // public static function payouts(){
    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //     CURLOPT_URL => env("LIVE_URL").'/disbursements/payouts',
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => '',
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 0,
    //     CURLOPT_FOLLOWLOCATION => true,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => 'GET',
    //     CURLOPT_POSTFIELDS =>'{
    //         "business": "645f9404bc81847c40e448a0"
    //     }',
    //     CURLOPT_HTTPHEADER => array(
    //         'Accept: application/json',
    //         // 'api-key: 16GH53GI9AzB17ov5QCgk1kw9m2uIGf3',
    //         'api-key:'.env("LIVE_KEY"),
    //         'Content-Type: application/json'
    //     ),
    //     ));

    //     $response = curl_exec($curl);

    //     curl_close($curl);
    //     return $response;
    // }

    public static function createVirtualAcc($accountType, $currency, $firstName, $lastName, $bvn, $dateOfBirth)
    {
        $curl = curl_init();

        $postData = array(
            "currency" => $currency,
            "accountType" => $accountType,
            "KYCInformation" => array(
                "firstName" => $firstName,
                "lastName" => $lastName,
                "bvn" => $bvn
            ),
            "dateOfBirth"=> $dateOfBirth ,
            "channel" => "globus"
        );

        // Convert the data to JSON format
        $jsonData = json_encode($postData);

        curl_setopt_array($curl, array(
        CURLOPT_URL => env("LIVE_URL").'/profile/virtual-accounts/requests/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'api-key:'.env("LIVE_KEY"),
            // 'api-key: 16GH53GI9AzB17ov5QCgk1kw9m2uIGf3',
            'Accept: application/json',
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public static function createBeneficiary($businessID, $first_name, $accountHolderName, $type, $currency, $paymentDestination, $destinationAddress)
    {
        $curl = curl_init();
        $url = env("LIVE_URL").'/profile/beneficiaries/business/'.urlencode($businessID);

        $postData = array(
            "firstName" => $first_name,
            "accountHolderName" => $accountHolderName,
            "type" => $type,
            "currency" => $currency,
            "paymentDestination" => $paymentDestination,
            "destinationAddress" => $destinationAddress,
        );

        // Convert the data to JSON format
        $jsonData = json_encode($postData);

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        // CURLOPT_POSTFIELDS =>'{
        //     "firstName" : '.$first_name.',
        //     "accountHolderName" : '.$accountHolderName.',
        //     "type" : '.$type.',
        //     "currency" : '.$currency.',
        //     "paymentDestination" : '.$paymentDestination.',
        //     "destinationAddress" : '.$destinationAddress.'
        
        // }',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            // 'api-key: 16GH53GI9AzB17ov5QCgk1kw9m2uIGf3',
            'api-key:'.env("LIVE_KEY"),
            'Content-Type: application/json'
        ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }

    public static function accountResolve($accountNumber, $bankCode)
    {
        $curl = curl_init();

        $postData = array(
            "accountNumber" => $accountNumber,
            "bankCode" => $bankCode,
            "type"     => "nuban"
        );

        // Convert the data to JSON format
        $jsonData = json_encode($postData);

        curl_setopt_array($curl, array(
          CURLOPT_URL => env("LIVE_URL").'/core/accounts/resolve',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $jsonData,
          CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'api-key:'.env("LIVE_KEY"),
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }

   public static function bankList()
   {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => env("LIVE_URL").'/core/banks?currency=NGN&country=NG',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'api-key:'.env("LIVE_KEY"),
        ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;  
    }

   public static function services($url, $method, $postData){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => env("VTPASS_URL_LIVE").$url,
    //   CURLOPT_URL => 'https://sandbox.vtpass.com/api/'.$url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POSTFIELDS => !empty($postData) ? $postData->toArray() : [],
    //   CURLOPT_POSTFIELDS => $postData,
      CURLOPT_HTTPHEADER => array(
        'accept: application/json',
        'secret-key:'.env("VTPASS_SECRET_KEY_LIVE"),
        'public-key:'.env("VTPASS_PUBLIC_KEY_LIVE"),
        'api-key:'.env("VTPASS_API_KEY_LIVE"),
        // 'Authorization: Basic YXlvZGFwb0B6aXBsaW1pdGVkLmNvbTpEQGx0b241MA== , Bearer ',
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return json_decode($response, 1);
   }

   public static function bridgeCard($url, $method, $postData, $key)
    {
        $curl = curl_init();

        $postDataArray = is_object($postData) ? $postData->toArray() : $postData;

        if($key == 'cardDetails')
        {
            // $apiUrl = 'https://issuecards-api-bridgecard-co.relay.evervault.com/v1/issuing/sandbox/';
            $apiUrl = env("BRIDGE_CARDDETAILS_URL_LIVE");
        }
        else{
            // $apiUrl = 'https://issuecards.api.bridgecard.co/v1/issuing/sandbox/';
            $apiUrl = env("BRIDGE_CARD_URL_LIVE");
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => !empty($postDataArray) ? json_encode($postDataArray) : '{}', // Convert to JSON if not empty
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: Bearer '.env("BRIDGE_CARD_TOKEN"),
                // 'secret key: '.env("BRIDGE_CARD_SECRETKEY"),
                // 'token: Bearer at_test_a1f95e5af450d1d490e4b3a80ee50d44a0c058632a514fabf123cd211f1daf7e7bd09da169ee060746b95de5484018707c83f3327e5d4131eef112969af2fa1a2d1e9f9f932dbbb8ceef7e6ca3af1a0af1157188f3c2d05d68623bfed1e085fe907237d85d403d41c97334667cf3d656e7e4bc874565dafe466f4d1f109bb2dbaca43b1eb1de8cc38849c0d96d936d66602cb24474bd43a9ff4db139b82faadd6523d5137f72b5dc0637225b665016c436e8b4a3b0548aade184bd2cc2ef19990c4965c13ef7cdb0058202aa1ee85aa87faec1776853b5c0c880d911425fe075ef5c35be6e683ea58c186f48278ae430c038432092b4c12f7755c52aad7b81b1',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public static function currencyRate()
    {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://issuecards.api.bridgecard-issuing-app.com/v1/issuing/cards/fx-rate',
        //   CURLOPT_URL => 'https://issuecards.api.bridgecard.co/v1/issuing/cards/fx-rate',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }

    public static function bridgeCardCalculation(){
        
        // $response = self::currencyRate();
        $response = 1200;
        // $responseData = json_decode($response, true);
        $setting = Setting::getAllSettingData();
        $bridgeCard_fxrate_fee = $setting['bridgeCard_fxrate_fee'];

        // $pay = $responseData['data']['NGN-USD'] * $bridgeCard;
        $fxRate = $response / 100;
        // $fxRate = $responseData['data']['NGN-USD'] / 100;
        $pay = $fxRate + $bridgeCard_fxrate_fee;
    
        return $pay;
    }
  

    

}
