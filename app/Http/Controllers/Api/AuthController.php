<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Helper\Helper;
use App\Models\Setting;
use App\Models\UserReferal;
use App\Models\EmailTemplate;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Auth;
use App\Mail\NewSignUp;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\VirtualAccounts;
use App\Models\AfricaVerification;
use Illuminate\Support\Facades\Hash;
use Log;

class AuthController extends Controller
{
    /**
     * User Login/Register Function
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */


    public function transferLimit(Request $request){
        $user = Auth::guard('api')->user();
        $this->response =   number_format($user->tranfer_limit,2,".") ;
        return ResponseBuilder::successMessage('Success', $this->success,$this->response); 

    }
    public function transferLimitSave(Request $request){

        $validSet = [
            'tranfer_limit' => 'required | numeric',
            'pin' => 'required | digits:4 | numeric',
        ];

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }
        $user = Auth::guard('api')->user();
        if( $user->pin!= $request->pin){
            return ResponseBuilder::successMessage('Invalid pin. Try again', $this->badRequest); 
        }
        $user->tranfer_limit = $request->tranfer_limit;
        $user->save();
        return ResponseBuilder::successMessage('Transfer limit set successfully', $this->success); 

    }
    public function login(Request $request) {
        // Validation start
        $validSet = [
            'phone_email' => 'required',
            'password' => 'required'
        ];

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }
        // Validation end

        try { 
            $user = User::findByPhoneOrEmail($request->phone_email);
            if($user) {
                if(!$user->status) {
                    return ResponseBuilder::error(trans('global.USER_BLOCKED'),$this->badRequest);
                }
                $user->device_token = $request->device_token;
                $user->save();
               
              
                if($request->type == 'phone'){
                    $loginData = ['phone' => request('phone_email'), 'password' => request('password')];
                } else {
                    $loginData = ['email' => request('phone_email'), 'password' => request('password')];
                }
                if(Auth::attempt($loginData)){
                    $user = Auth::user();

                    $token = $user->createToken('Token')->accessToken;
                    $data = $this->setAuthResponse($user);
                    
                    return ResponseBuilder::successwithToken($token, $data, 'Login Success', $this->success);

                }
                else{
                   return ResponseBuilder::error('Oops! Your password seems to be incorrect. Please try again or click on “Forgot Password” to get a new one.', $this->badRequest);
                }
                
            } else {
                return ResponseBuilder::error('Login Details Incorrect',$this->badRequest); 
            }


            /**Mail to admin */
            // $settingData = Setting::getAllSettingData();
        
            // $img = url('/'.config('app.logo').'/'.$settingData['logo_1']);
            // $mailData = EmailTemplate::getMailByMailCategory(strtolower('new user register'));
            // // if(isset($mailData)) {

            // //     $arr1 = array('{image}', '{number}');
            // //     $arr2 = array($img, $request->phone);

            // //     $msg = $mailData->email_content;
            // //     $msg = str_replace($arr1, $arr2, $msg);
               
            // //     $config = [
            // //         'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
            // //         'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
            // //         'subject' => $mailData->email_subject, 
            // //         'message' => $msg,
            // //     ];
  
            // //     if(isset($settingData['admin_mail']) && !empty($settingData['admin_mail'])){
            // //         Mail::to($settingData['admin_mail'])->send(new NewSignUp($config));
            // //     }
            // // }
            
            // // if($request->type == 'phone') {
            // //     $otpStatus = $this->sendTermiiOTP($request->phone_email, $otp);
            // // }
            
            return ResponseBuilder::error('Login Details Incorrect',$this->badRequest); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').$e->getMessage(), $this->badRequest);
        }
    }

    /**
     * OTP Verification
     * @param \Illuminate\Http\Request $request, phone, otp
     * @return \Illuminate\Http\Response
     */
    public function userRegister(Request $request){
        // 
        $validSet = [
            // "email" => "required | unique:users,email,id,deleted_at,NULL",
            "email" => "required | unique:users,email,NULL,deleted_at,deleted_at,NULL",
            "phone" => "required | unique:users,phone,NULL,deleted_at,deleted_at,NULL",
            // "type" => "required",
            //'bvn' => 'required|unique:bvn,bvn',
             'bvn' => 'required_if:type,==,email | unique:users,bvn,NULL,deleted_at,deleted_at,NULL',
            'password' => 'required|confirmed|min:8',
        ];
        
        $isInValid = $this->isValidPayload($request, $validSet);

        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }

        try {
            $otp = Helper::generateOtp();
            $user = User::where(function($query) {
                $query->where('phone',  request('phone' ))->orWhere('email', request('email'))
                ->orWhere('bvn', request('bvn'));
            })->first();

            if($user) {
                //
                if($user->status) {
                    return ResponseBuilder::error(__("User Already Exist"), $this->badRequest);
                } else {
                    //
                    $user->otp = $otp;
                    if(isset($request->bvn)){
                        $user->bvn = $request->bvn;
                    }
                    $user->password = Hash::make($request->password);
                    $user->save();
                }
            } else {

                if(isset($request->bvn)){
                    $userData['bvn'] = $request->bvn;
                }
                $userData['password'] = Hash::make($request->password);
                $userData['created_origin'] =('ZIP app');
                $userData['phone'] = $request->phone;
                $userData['email'] = $request->email;

                $user = User::create($userData);

                if(!empty($user)){
                    $uid['unique_id'] = "ZIPUID".str_pad($user->id, 7, "0", STR_PAD_LEFT);
                    User::where('id',$user->id)->update($uid);
                    $user->roles()->sync(2);
                    $user->otp = $otp;
                    $user->save();
                }
            }

            // Assign OTP 
            $user->roles()->sync(2);
            $this->response->otp = $otp;
            $this->response->user = $user;

            $to = $request->phone;
            
            $response = Helper::termii($to, $otp); 
       
            $fname = $user->fname ? ucfirst($user->fname) : "";
            $lname = $user->lname ? ucfirst($user->lname) : "";
            $loginName =  $fname ." ". $lname;

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Welcome-OTP'));
            if(isset($mailData)) {

                $arr1 = array('{otp}','{name}');
                $arr2 = array($otp,$loginName);

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
                    Mail::to($request->email)->send(new NewSignUp($config));
                } catch (\Throwable $th) {
                    //throw $th;
                }   
            }

        } catch (\Throwable $th) {
            //throw $th;return ResponseBuilder::error(__("User Already Exist"), $this->badRequest);
        }
        
        return ResponseBuilder::success('Registered Successfully! Please verify your OTP.', 200, $this->response);
    }

    /**
     * OTP Verification
     * @param \Illuminate\Http\Request $request, phone, otp
     * @return \Illuminate\Http\Response
     */
    public function verifyOtp(Request $request) {
        try {
            // Validation start
            $validSet = [
                'phone_email' => 'required',
                'otp' => 'required|digits:6',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            $user = User::findByPhoneOrEmail($request->phone_email);

            if(!$user){
                return ResponseBuilder::error('OTP not valid or user not valid!', $this->badRequest);
            }
            $message = $user->otp_verified ? trans('global.MOBILE_VERIFIED') : trans('global.USER_VERIFIED');

            if((isset($user->otp_created_at)) && ((strtotime($user->otp_created_at) + 900) < strtotime(now()))) {
                return ResponseBuilder::error(trans('global.OTP_EXPIRED'), $this->success);
            }
            if((isset($user->otp)) && ($request->otp != $user->otp)) {
                return ResponseBuilder::error(trans('global.INVALID_OTP'), $this->success);
            }
            // $user = Auth::login($user);

            $user->otp = null;
            $user->otp_created_at = null;
            $user->status = 1;
            $user->otp_verified = 1;
            $user->device_token = $request->device_token;
            $user->save();
            
            $token = $user->createToken('Token')->accessToken;
            $data = $this->setAuthResponse($user);
            
            return ResponseBuilder::successwithToken($token, $data, $message, $this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').' Error:'.$e->getMessage(),$this->badRequest);
        }
    }
    // public function africaVerification(Request $request) {
    //     try {
    //         // Validation start
    //         $validSet = [
    //             'phone_email' => 'required',
    //             'selfie' => 'required',
    //             'fname' => 'required',
    //             'lname' => 'required',
    //             'dob' => 'required',
    //             'bvn' => 'required',
    //         ]; 
    //         $isInValid = $this->isValidPayload($request, $validSet);
    //         if($isInValid){
    //             return ResponseBuilder::error($isInValid, $this->badRequest);
    //         }
    //         // Validation end

    //         $user = User::findByPhoneOrEmail($request->phone_email);

    //         if(!$user){
    //             return ResponseBuilder::error('User not valid!', $this->badRequest);
    //         }

    //         // return $user;
    //         //   if(empty($user->fname) || empty($user->lname) || empty($user->dob) || empty($user->bvn) ){
    //         //    return ResponseBuilder::error('Please complete your profile first',$this->badRequest);
    //         //   }
    //           $verifitionData =  Helper::bvnVerification($request->bvn ,$user->email , $request->dob , $request->fname ,  $request->lname , $request->selfie);
    //           $verifitionData =  json_decode($verifitionData,true);

    //           if($verifitionData['verificationStatus'] == 'VERIFIED'){
    //             $user->verification_image  = $request->selfie;
    //             $user->is_africa_verifed  = $request->verificationStatus == 'VERIFIED' ? true : false ;
    //             $user->save();
    //             return ResponseBuilder::success('VERIFIED', 200, $this->response);
    //           }
    //           return ResponseBuilder::error('NOT VERIFIED', $this->badRequest,$this->response);

    //     } catch (\Exception $e) {
    //         return ResponseBuilder::error(trans('global.SOMETHING_WENT').': Check your details',$this->badRequest);
    //     }
    // }
    public function africaVerification(Request $request) {
        try {
            // Validation start
            $validSet = [
                'user_id'  => 'required',
                'verificationStatus'   => 'required',
              
            ]; 
            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            $user = User::getUserById($request->user_id);

            if(!$user){
                return ResponseBuilder::error('User not valid!', $this->badRequest);
            }

            if($request->verificationStatus == 'VERIFIED'){
                $user->is_africa_verifed  = $request->verificationStatus == 'VERIFIED' ? true : false ;
                $user->save();
                return ResponseBuilder::successMessage('VERIFIED', 200);
                          }

              else{
                return ResponseBuilder::error('NOT VERIFIED', $this->badRequest);
              }
            //   }
              

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').': Check your details',$this->badRequest);
        }
    }
    public function myProfile(Request $request) {
        if(!Auth::guard('api')->check()){
            return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        }
        try {
            $user = Auth::user();
            $data = $this->setAuthResponse($user);    
            return ResponseBuilder::success('Success', 200, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').' Error:'.$e->getMessage(),$this->badRequest);
        }
    }
    public function settings(Request $request) {
        try {
            $this->response->authKey =  config('app.AFRICA_AUTH_KEY') ?? '';
            $this->response->userID =  config('app.AFRICA_USER_KEY') ?? '';
            return ResponseBuilder::success('Success', 200, $this->response);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').' Error:'.$e->getMessage(),$this->badRequest);
        }
    }
    /**
     * User Resend Otp Verify Function
     * @param \Illuminate\Http\Request $request, phone, otp
     * @return \Illuminate\Http\Response
     */
    public function resendOtp(Request $request) {
        try {
            // Validation start
            $validSet = [
                'phone' => 'required',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end
 
            $user = User::findByPhone($request->phone);
            $data_otp_resend = $this->sendOtp($request->phone);
            $user->otp = isset($data_otp_resend['otp']) ? $data_otp_resend['otp'] : NULL;
            $user->otp_created_at = now();
            $user->save();

            if(isset($data_otp_resend['responseCode']) && ($data_otp_resend['responseCode'] != 200)) {
                return ResponseBuilder::error(isset($data_otp_resend['message']) ? $data_otp_resend['message'] : trans('global.SOMETHING_WENT'), $this->success); 
            }
            return ResponseBuilder::successMessage(trans('global.OTP_SENT'), $data_otp_resend['responseCode']); 

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * User Profile Update
     * @param \Illuminate\Http\Request $request, name, email, phone
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request) {
        try {
            $user = Auth::guard('api')->user();
            // Validation start
            $validSet = [
                'first_name' => 'required',
                'last_name' => 'required',
                'dob'       => 'required|date_format:d-m-Y',
                'profile_image' => 'mimes:jpeg,png,jpg',
                'email'     => 'required|email',
                'phone'     => 'required|numeric|digits_between:10,12'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            $imagePath = config('app.profile_image');
            $profileImageOld = $user->profile_image;

            $user->fname = isset($request->first_name) ? ucfirst($request->first_name) : '';
            $user->lname = isset($request->last_name) ? ucfirst($request->last_name) : '';
            $user->dob = isset($request->dob) ? $request->dob : '';
            $user->phone = isset($request->phone) ? $request->phone : '';
            $user->email = isset($request->email) ? $request->email : '';
            $user->profile_image = $request->hasfile('profile_image') ? Helper::storeImage($request->file('profile_image'), $imagePath, $profileImageOld) : (isset($profileImageOld) ? $profileImageOld : '');
            $user->save();
            $data = $this->setAuthResponse($user);

            return ResponseBuilder::successMessage(trans('global.profile_updated'), $this->success, $data); 
            
        } catch (\Exception $e) {
            return $e->getMessage();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function updateSettings(Request $request) {
        try {

            $user = Auth::guard('api')->user();
            // $data ['allow_notification'] = $request->allow_notification == '1' ? true : false ;
            $data ['hide_balance'] = $request->hide_balance == '1' ? true : false ;
            $data ['enable_security_lock'] = $request->enable_security_lock == '1' ? true : false ;
            $data ['transaction_pin'] = $request->transaction_pin == '1' ? true : false ;
            $data ['enable_fingerprints'] = $request->enable_fingerprints == '1' ? true : false ;
           
            foreach($data as $key => $value){
                UserMeta::updateOrCreate([
                    'user_id' => $user->id,
                    'key' => $key,
                    ],[
                        'value' => $value,
                    ]);
            }

            return ResponseBuilder::successMessage('Settings updated', $this->success, $data); 
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function notificationSettings(Request $request) {
        try {

            $user = Auth::guard('api')->user();
            $data ['push_notification'] = $request->push_notification == '1' ? true : false ;
            $data ['email_notification'] = $request->email_notification == '1' ? true : false ;

            foreach($data as $key => $value){
                UserMeta::updateOrCreate([
                    'user_id' => $user->id,
                    'key' => $key,
                    ],[
                        'value' => $value,
                    ]);
            }

            return ResponseBuilder::successMessage('Notification updated', $this->success, $data); 
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
  
    public function getUserSettingsData(Request $request) {
        try {

            $user = Auth::guard('api')->user();
            $userMeta = UserMeta::where('user_id',$user->id)->pluck('value','key');
            $userMetaFirst = UserMeta::where('user_id',$user->id)->where('key','hide_balance')->get();
            $userMetaSecond = UserMeta::where('user_id',$user->id)->where('key','push_notification')->get();

            if((count($userMeta)>0)){
                foreach($userMeta as $key => $item){
                    $data[$key] = $item == '1' ? true : false ;
                }
            }
            if((count($userMetaFirst)==0)){
                $data['hide_balance'] = false ;
                $data['enable_security_lock'] =  false ;
                $data['transaction_pin'] = false ;
                $data['enable_fingerprints'] =  false ;
            }
            if(count($userMetaSecond)==0){  
                $data['push_notification'] = false ;
                $data['email_notification'] = false ;
            }
            return ResponseBuilder::successMessage('success', $this->success, $data); 
            
        } catch (\Exception $e) {
            return $e;
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function verifyPinSecurity(Request $request) {
        try {
            $validSet = [
                'pin' => 'required | digits:4 | numeric',
            ];
    
            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $user = Auth::guard('api')->user();
            if( $user->pin!= $request->pin){
                return ResponseBuilder::successMessage('Invalid pin. Try again', $this->badRequest); 
            }
            return ResponseBuilder::successMessage('Pin verified successfully', $this->success); 
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function changeZipPin(Request $request) {
        try {
            $validSet = [
            'new_zip_pin' => 'required|Integer|digits:4',
            'old_zip_pin' => 'required|Integer|digits:4',
           ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user();
            if($user->pin != $request->old_zip_pin){
                return ResponseBuilder::error('Invalid old pin', $this->badRequest);
            }

            $user->pin = $request->new_zip_pin;
            $user->save();
            return ResponseBuilder::successMessage('Pin changed successfully', $this->success); 
            
        } catch (\Exception $e) {
            return $e;
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function sendOtpForPin(Request $request) {
        try {
            $validSet = [
                'type' => 'required|in:phone,email',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user();
            $otp = rand(1000,9999);

            if($request->type == 'phone' && empty($user->phone)){
                return ResponseBuilder::error('Register mobile no. first', $this->badRequest);
            }
            if($request->type == 'email' && empty($user->email)){
                return ResponseBuilder::error('Register email address first', $this->badRequest);
            }

             if($request->type == 'phone') {
                $otpStatus = $this->sendTermiiOTP($user->phone, $otp);
             }

            if($request->type == 'email') {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('pin_reset'));
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
                        Mail::to($user->email)->send(new NewSignUp($config));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }   
                }
            }
            $user->pin_reset_otp = $otp;
            $user->save();

            return ResponseBuilder::successMessage('OTP Send Successfully', $this->success); 
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
 public function verifyOtpPin(Request $request) {
        try {
            $validSet = [
                'otp' => 'required|Integer|digits:4',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $user = Auth::guard('api')->user();
            
            if($user->pin_reset_otp != $request->otp){
                return ResponseBuilder::error('Invalid OTP', $this->badRequest);
            }

            return ResponseBuilder::successMessage('OTP verified successfully', $this->success); 
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function updateDetails(Request $request) {
        
        if(Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        } else {
            return ResponseBuilder::error(__("User not found"), $this->unauthorized);
        }
        $validSet = [
            'phone'     => 'nullable|numeric|digits:11',
        ]; 

        $customeMessage = [
            'phone.numeric'     => 'This is not valid phone number',
            'phone.digits'     => 'Phone number must be 11 digits',
        ];

        $isInValid = $this->isValidPayload($request, $validSet, $customeMessage);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }
        try {
            $request = $request->except(['_token']);
            // dd($request);
            User::where('id', $user->id)->update($request);

            return ResponseBuilder::successMessage(trans('global.profile_updated'), $this->success); 
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').((env('APP_DEBUG', false))?$e->getMessage():''),$this->badRequest);
        }
    }

    public function checkZipTag(Request $request) {

        if(Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        } else {
            return ResponseBuilder::error(__("User not found"), $this->unauthorized);
        }
        // Validation start
        $validSet = [
            'zip_tag' => 'required|unique:users,zip_tag,NULL,deleted_at,deleted_at,NULL',
        ]; 

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        } else {
            return ResponseBuilder::successMessage(trans('global.zip_tag_found'), $this->success); 
        }
    }
    /**
     * User Profile
     * @return \Illuminate\Http\Response
     */
    public function userProfile() {
        try {
            $user = Auth::guard('api')->user();  
            $data = $this->setAuthResponse($user);
            return ResponseBuilder::successMessage(trans('global.profile_detail'), $this->success, $data); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * User logout Function
     * @return \Illuminate\Http\Response
     */
    public function logout() {
        try {
            if(!Auth::guard('api')->check()) {
                return ResponseBuilder::error(trans('LOGIN'), $this->badRequest);
            }
            
            Auth::guard('api')->user()->token()->revoke();
            return ResponseBuilder::successMessage(trans('LOG OUT Successfull'), $this->success); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->badRequest);
        }
    }

    public function updateLocation(Request $request) {
        try {
            $user = Auth::guard('api')->user();
       
            $validSet = [
                'latitude' => 'required',
                'longitude' => 'required',
            ]; 
            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();
            
            return ResponseBuilder::successMessage(trans('global.location_update'), $this->success); 

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->badRequest);
        }
    }

    public function setAuthResponse($user) {
        return $this->response->user =  new UserResource($user);
    }


    public function deleteAccount() {
        try {
            $user = Auth::guard('api')->user();

            User::where('id', $user->id)->delete();
            return ResponseBuilder::successMessage('User Deleted Successfully!', $this->success); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->badRequest);
        }
    }
    public function sendOtpLogin(Request $request)
    {
        try {
            // Validation start
            $validSet = [
                'email' => 'required | email',
            ];
            // return $validSet;
            $isInValid = $this->isValidPayload($request, $validSet);
            if ($isInValid) {
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $user = Auth::guard('api')->user();
            $otp = Helper::generateOtp();
            // $user->otp = NULL;
            // $user->otp_created_at = NULL;
            // // $user->otp_verified = 1;
            // // return $user;
            // $user->save();
    
            $user->otp = $otp;
            $user->otp_created_at = now();
            $user->save();
    
            $mailData = EmailTemplate::getMailByMailCategory('sendotp');
            // return $mailData;
    
           
    
            $arr1 = array('{otp}',  '{name}');
            $arr2 = array($otp,  $user->name);
    
            $msg = $mailData->email_content;
            $msg = str_replace($arr1, $arr2, $msg);
    
            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_name) ? $mailData->from_name : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject,
                'message' => $msg,
            ];
    
            //Send Mail
            // Mail::to($email)->send(new SendMail($config));
            Mail::to($request->email)->send(new NewSignUp($config));

            // $data = $this->OTP($request->email,$user);
            // dd($data);
            $this->response->otp = $otp;

            // $mailData = EmailTemplate::getMailByMailCategory(strtolower('forgotpassword'));

            return ResponseBuilder::success(trans('global.OTP_SENT'), $this->success, $this->response);
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage().' at line '.$e->getLine() .' at file '.$e->getFile(),$this->badRequest);
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->badRequest);
        }
    }

    public function africaVerificationUser(Request $request){
        try {
            $data =  AfricaVerification::create([
                'user_id'         => $request->user_id,
                'email'           => $request->email,
                'gender'          => $request->gender,
                'dob'             => $request->dob,
                'phone'           => $request->phone ?? null,
                'country'         => $request->country,
                'nin'             => (int)$request->nin ??  null,
                // 'nin' => $request->nin !== null ? (int)$request->nin : null,
                'bvn'             => $request->bvn,
                'nationality'     => $request->nationality,
                'full_name'       => $request->full_name,
                'first_name'      => $request->first_name,
                'last_name'       => $request->last_name,
                'middle_name'       => $request->middle_name,
                'alternate_phone'       => $request->alternate_phone,
                'state_of_origin'       => $request->state_of_origin,
                'state_of_residence'       => $request->state_of_residence,
                'lga_of_origin'       => $request->lga_of_origin,
                'lga_of_residence'       => $request->lga_of_residence,
                'address_line_2'       => $request->address_line_2,
                'address_line_3'       => $request->address_line_3,
                'marital_status'       => $request->marital_status,
                'watchlisted'       => $request->watchlisted ??  null,
                'avatar'       => $request->avatar,

            ]);
            return ResponseBuilder::successMessage('Africa Verification details save successfully', $this->success, $data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }
   
    public function getAfricaVerificationUser(Request $request) {
        try {
           
            $data = AfricaVerification::where('user_id',$request->user_id)->first();
            $data->avatar = substr($data->avatar, 0, -5);
            return ResponseBuilder::successMessage(trans('global.profile_detail'), $this->success, $data); 
        } catch (\Exception $e) {
            return $e;
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function OTP($email, $user)
    {

        $otp = Helper::generateOtp();
        // $user->otp = NULL;
        // $user->otp_created_at = NULL;
        // // $user->otp_verified = 1;
        // // return $user;
        // $user->save();

        $user->otp = $otp;
        $user->otp_created_at = now();
        $user->save();

        $mailData = EmailTemplate::getMailByMailCategory('sendotp');
        // return $mailData;

       

        $arr1 = array('{otp}',  '{name}');
        $arr2 = array($otp,  $user->name);

        $msg = $mailData->email_content;
        $msg = str_replace($arr1, $arr2, $msg);

        $config = [
            'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
            'name' => isset($mailData->from_name) ? $mailData->from_name : env('MAIL_FROM_NAME'),
            'subject' => $mailData->email_subject,
            'message' => $msg,
        ];

        //Send Mail
        // Mail::to($email)->send(new SendMail($config));
        Mail::to($email)->send(new NewSignUp($config));
        // dd($otp);

        return $otp;
    }

    public function forgotPassword(Request $request) {
        try {
            // Validation start
            $validSet = [
                'email' => 'required | email',
            ]; 
            
            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            $user = User::getUserByEmail($request->email);

            if(!$user) {
                return ResponseBuilder::error(trans('global.NOT_REGISTERED'), $this->badRequest);
            }

            if(!$user->status) {
                return ResponseBuilder::error(trans('global.USER_BLOCKED'), $this->badRequest);
            }

            // $data = $this->OTP($request->email, $user);
            $data = Helper::generateOtp();    
            $user->otp = $data;
            $user->otp_created_at = now();
            $user->otp_verified = 0;
            $user->save();

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('forgot-password'));
            if(isset($mailData)) {

                $arr1 = array('{otp}','{name}');
                $arr2 = array($data,$user->fname.' '.$user->lname);

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
                    Mail::to($request->email)->send(new NewSignUp($config));
                } catch (\Throwable $th) {
                    //throw $th;
                }   
            }
            $this->response->otp = $data;


            return ResponseBuilder::success(trans('global.OTP_SENT'),$this->success, $this->response);
            
        } catch (\Exception $e) {
            return $e;
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function resetPassword(Request $request) {
        try {
            // Validation start
            $validSet = [
                'email' => 'required | email',
                'password' => ['required','string', 'min:8', 'same:confirm_password'],
                'confirm_password' => ['required', 'string', 'min:8'],
                // 'password' => ['required', 'regex:/^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%]).*$/', 'string', 'min:8', 'same:confirm_password'],
                // 'confirm_password' => ['required', 'regex:/^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%]).*$/', 'string', 'min:8'],
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end
 
            $user = User::getUserByEmail($request->email);

            $user->password = Hash::make($request->password);
            $user->save();
            
            $mailData = EmailTemplate::getMailByMailCategory(strtolower('change password'));
            if(isset($mailData)) {

                $arr1 = array('{name}');
                $arr2 = array($user->fname.' '.$user->lname);

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
                    Mail::to($request->email)->send(new NewSignUp($config));
                } catch (\Throwable $th) {
                    //throw $th;
                }   
            }

            return ResponseBuilder::successMessage(trans('global.PASSWORD_CHANGED'),$this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

}