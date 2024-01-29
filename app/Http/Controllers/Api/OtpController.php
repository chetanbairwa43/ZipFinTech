<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Exception;
use Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendEmail;
use App\Models\User;
use App\Helper\ResponseBuilder;


class OtpController extends Controller
{
    public function phonerequestOtp(Request $request)
    {
        try {
            // Validation start
             $validSet = [
                'phone' => 'required | digits:10 | integer'
            ]; 
            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end
            $user = User::findByPhone($request->phone);
            if($user) {
                if(!$user->status) {
                    return ResponseBuilder::error(trans('global.USER_BLOCKED'),$this->badRequest);
                }
                $user->device_id = $request->device_id ?? null;
                $user->device_token = $request->device_token ?? null;
                $data_otp = $this->sendOtp($request->phone);
                if(isset($data_otp['responseCode']) && ($data_otp['responseCode'] != 200)) {
                     return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->success);
                    }
                    $user->otp = isset($data_otp['otp']) ? $data_otp['otp'] : NULL;
                     $user->otp_verified = 0;
                     $user->save();
                     return ResponseBuilder::successMessage(trans('global.OTP_SENT'), $data_otp['responseCode']);
            }
            if($request->referred_code) {
                 $user = User::findByReferalCode($request->referred_code);
                 if(!$user) {
                    return ResponseBuilder::error(trans('global.CODE_INVALID'), $this->success);
                }
                $previousBalance = $user->earned_balance;
                $bonusAmount = Setting::getDataByKey('referal_amount');
                $user->earned_balance += $bonusAmount->value;
                $user->save();
                $data_otp = $this->sendOtp($request->phone);
               
                if(isset($data_otp['responseCode']) && ($data_otp['responseCode'] != 200)) {
                    return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->success);
                }
                $userData = User::create([
                    'phone'        => $request->phone,
                    'referal_code' => Helper::generateReferCode(),
                    'otp' => isset($data_otp['otp']) ? $data_otp['otp'] : NULL,
                    'otp_created_at' => Carbon::now(),
                    'otp_verified' => 0,
                 ]);
                }
                else {
                    $data_otp = $this->sendOtp($request->phone);
                    if(isset($data_otp['responseCode']) && ($data_otp['responseCode'] != 200)) {
                        return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->success);
                    }
                    $userData = User::create([
                        'phone' => $request->phone,
                        'referal_code' => Helper::generateReferCode(),
                        'otp' => isset($data_otp['otp']) ? $data_otp['otp'] : NULL,
                        'otp_created_at' => Carbon::now(),
                        'otp_verified' => 0,
                    ]);
                }
                $userData->roles()->sync(2);
            
            /**Mail to admin */
            $settingData = Setting::getAllSettingData();
            $img = url('/'.config('app.logo').'/'.$settingData['logo_1']);
            $mailData = EmailTemplate::getMailByMailCategory(strtolower('new user register'));
            if(isset($mailData)) {
                $arr1 = array('{image}', '{number}');
                $arr2 = array($img, $request->phone);
                
                $msg = $mailData->email_content;
                $msg = str_replace($arr1, $arr2, $msg);
                
                $config = [
                    'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                    'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                    'subject' => $mailData->email_subject, 
                    'message' => $msg,
                ];
                
                if(isset($settingData['admin_mail']) && !empty($settingData['admin_mail'])){
                    Mail::to($settingData['admin_mail'])->send(new NewSignUp($config));
                }
            }
            return ResponseBuilder::successMessage(trans('global.OTP_SENT'), $data_otp['responseCode']); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
   
        
        // $otp = rand(100000,999999);
    //     Log::info("otp = ".$otp);
    //     $user = User::where('email',$request->email)->get();
    //     $user -> otp = $otp;
    //     $user -> save();   
    //     if($user){
    //     // send otp in the email
    //     $mail_details = [
    //         'subject' => 'Testing Application OTP',
    //         'body' => 'Your OTP is : '. $otp
    //     ];
       
    //      \Mail::to($request->email)->send(new sendEmail($mail_details));
       
    //    return ResponseBuilder::success(trans('messages.OTP_SUCCESS'), $this->success,$this->response);
    //     }
    //     else{
    //         return ResponseBuilder::error(trans('messages.OTP_INVALID'), $this->badRequest);
    //     }

    public function verifyOtp(Request $request){
    
        $user  = User::where([['email','=',$request->email],['otp','=',$request->otp]])->first();
        if($user){
            auth()->login($user, true);
            User::where('email','=',$request->email)->update(['otp' => null]);
            $accessToken = auth()->user()->createToken('authToken')->accessToken;

            return response(["status" => 200, "message" => "Success", 'user' => auth()->user(), 'access_token' => $accessToken]);
        }
        else{
            return ResponseBuilder::error(trans('messages.OTP_INVALID'), $this->badRequest);
        }
    }

    public function testRout(Request $request) {
        $validator = Validator::make($request->all(), [
            'vehical_no' => 'required',
        ]);
        if ($validator->fails()) {
            $er=[];$i=0;
             foreach($validator->errors() as $err){
                $er[$i++]=$err[0]; 
                return $err;
             }
            return response()->json(['Status'=>'Failure','Description'=>'please enter vehical no.','Errors'=>'data not found','Results'=>null],403);
        }

    	// if(!isset($request->vehical_no) && empty($request['vehical_no'])){
    		
    	// 	return response()->json(['Status'=>'Failure','Description'=>'please enter vehical no.','Errors'=>'data not found','Vehicle'=>[]]);
    	// }

    	// $vehical_no =str_replace(" ",'', trim($request->vehical_no));

    	// $url = "https://externalapipreview.etelimited.co.uk/api/vehicle/makes";

    	// $url = "https://externalapi.etelimited.co.uk/api/vehicle/details/".$vehical_no;

		//  $result = $this->apiCurl($url);
		// new api for vehical verify
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1/vehicles',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => json_encode([
			'registrationNumber' => $request->vehical_no,
		]),
		  CURLOPT_HTTPHEADER => array(
			'Accept: /',
			'Content-Type: application/json',
			'x-api-key: SNQZ4KOSo688BdxfjUK8C3YymyxIb6A29v3UZwWg'
		  ),
		));
		
		$response = curl_exec($curl);
		
		curl_close($curl);
		$result = json_decode($response,true);
		//    dd($result['errors'][0]['status']);
		 if(isset($result['errors'][0]['status']) && $result['errors'][0]['status'] == "400"){
               return response()->json(['Status'=>"Failure",'Description'=>'Please try again , server not connected','Errors'=>'data not found','Results'=>[]],200);
         }

         if(isset($result['make'])){

			$newresult['Model'] = '_';
			$newresult['Make'] = $result['make'];
			$r = $newresult;

			return response()->json(['Status'=>'Success','Description'=>'OK','Errors'=>"",'Results'=>$r]);

		}

		else{

		return response()->json(['Status'=>'Failure','Description'=>'The vehicle could not be converted.','Errors'=>'This ('.$request->vehical_no.") vehicle no. doesn't exist",'Results'=>null],200);

		}

    }

}