<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Helper\Helper;
use App\Models\Setting;
use App\Models\UserReferal;
use App\Models\EmailTemplate;
use App\Models\Transaction;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Auth;
use App\Models\VirtualAccounts;
use App\Models\User;
use App\Models\UserMeta;
use App\Models\UserAddress;
use App\Models\WebhookDetails;
use App\Models\Faq;
use App\Models\SupportQueries;
use App\Models\SupportCategories;
use App\Models\UserCard;
use App\Models\CardHolderDetails;
use App\Models\UserCardInfo;
use Illuminate\Support\Facades\Hash;
use App\Mail\NewSignUp;
use DB;

class CommanController extends Controller
{

    public function virtualAccount(Request $request){
        // return $request;
        $validSet = [
            'phone_email' => 'required',
            // 'flw_ref' => 'required|string',
            // 'order_ref' => 'required',
            'accountNumber' => 'required',
            // 'frequency' => 'required',
            // 'bank_name' => 'required',
        ];

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }

        try {
            $user = User::findByPhoneOrEmail($request->phone_email);
            // dd($user);
            if(!$user){
                return ResponseBuilder::error('User not found', $this->badRequest);
            }
            $data = VirtualAccounts::create([
                'user_id' => $user->id,
                'accountType'        => $request->accountType,
                'currency'           => $request->currency,
                'business'           => $request->business,
                'business_id'        => $request->business_id,
                'accountNumber'      => $request->accountNumber,
                'creation_origin'    =>'ZIP app',
                'KYCInformation'     => json_encode($request->KYCInformation),
                'accountInformation' => json_encode($request->accountInformation),
            ]);
            // $data->json_decode->KYCInformation;
            // $data->json_decode->accountInformation;
            // $jsonCode= json_encode($data);
            $data->save();

            return ResponseBuilder::successMessage('Success', $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT').$e->getMessage(), $this->badRequest);
        }
    }
    public function faq(){
        $data = Faq::getAllActiveFaq()->map( function($data){
         return [
             'question' =>  $data->question,
             'answer' =>  $data->answer,
         ];
        });
        return ResponseBuilder::successMessage('Success  ', $this->success,$data);
     }
    public function supportCategories(){
        $data = SupportCategories::all()->map( function($data){
         return [
             'id' =>  $data->id,
             'title' =>  $data->title,
         ];
        });
        return ResponseBuilder::successMessage('Success  ', $this->success,$data);
     }
    public function submitQuery(Request $request){
        $validSet = [
            'support_category' => 'required',
            'description' => 'required',
        ];

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }
        $user = Auth::guard('api')->user();

        $data = SupportQueries::create([
            'support_category' => $request->support_category,
            'user_id' => $user->id,
            'description' => $request->description,
        ]);

        $mailData = EmailTemplate::getMailByMailCategory(strtolower('support'));
        $Setting = Setting::where('key','support_email')->first();
        if(isset($mailData) && isset($Setting->value)  && !empty($Setting->value)) {
            $arr1 = array('{email}','{message}');
            $arr2 = array($user->email ?? $user->phone,$data->description);

            $email_content = $mailData->email_content;
            $email_content = str_replace($arr1, $arr2, $email_content);

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject,
                'message' => $email_content,
            ];

            try {
                Mail::to($Setting->value)->send(new NewSignUp($config));
            } catch (\Throwable $th) {

            }
        }

        return ResponseBuilder::successMessage('Thank you for contacting us, we will get back to you as soon as possible.', $this->success);
     }
    public function changePassword(Request $request){
        try {
            $user = Auth::guard('api')->user();
            // Validation start
            $validSet = [
                'old_password' => 'required',
                'new_password' => 'required|string|same:confirm_password',
                'confirm_password' => 'required',
            ];

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            if(!Hash::check($request->old_password,$user->password)) {
                return ResponseBuilder::error('Incorrect old password', $this->badRequest);
            }

            $user->password = Hash::make($request->new_password);
            $user->update();

            return ResponseBuilder::successMessage('Password updated', $this->success);

        } catch (\Exception $e) {
            return $e;
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    Public function transactionlist(Request $request)
    {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            $data = Transaction::where('user_id',$user->id)->latest()->get()
            ->map(function($value){
                return [
                    'user_id'     => $value->user_id,
                    'beneficiary_id'     => $value->beneficiary_id,
                    'transaction_type'     => ($value->transaction_type == 'cr')?'Credit':'Debit',
                    'comon_id'     => $value->comon_id,
                    't_id'     => $value->t_id,
                    'amount'     => number_format($value->amount, 2, '.', ','),
                    'currency'   => $value->currency,
                    'transaction_about'     => $value->transaction_about,
                    'user_type'     => $value->user_type,
                    'created_at'     => $value->created_at->format('d-m-y'),
                ];
            });

            if($data->count() == 0)
            return ResponseBuilder::error('No data found', $this->notFound);

            return ResponseBuilder::successMessage('All Transaction list', $this->success,$data);
        } catch (\Throwable $th) {
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }
     public function transactionreceive(Request $request)
     {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = Transaction::where(['user_id'=>$user->id ,'transaction_type'=>'cr'])->latest()->get()
            ->map(function($value){
                return [
                    'user_id'     => $value->user_id,
                    'beneficiary_id'     => $value->beneficiary_id,
                    'transaction_type'     => 'Credit',
                    'comon_id'     => $value->comon_id,
                    't_id'     => $value->t_id,
                    'amount'     => number_format($value->amount, 2, '.', ','),
                    'currency'     => $value->currency,
                    'transaction_about'     => $value->transaction_about,
                    'user_type'     => $value->user_type,
                    'created_at'     => $value->created_at->format('d-m-y'),
                ];
            });
            if($data->count() == 0)
            return ResponseBuilder::error('No data found', $this->notFound);

            return ResponseBuilder::successMessage('Received Transaction List', $this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
     }


     public function transactionsend(Request $request)
     {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = Transaction::where(['user_id'=> $user->id ,'transaction_type'=>'dr'])->latest()->get()
            ->map(function($value){
                return [
                    'user_id'     => $value->user_id,
                    'beneficiary_id'     => $value->beneficiary_id,
                    'transaction_type'     => "Debit",
                    'comon_id'     => $value->comon_id,
                    't_id'     => $value->t_id,
                    'amount'     => number_format($value->amount, 2, '.', ','),
                    'currency'  => $value->currency,
                    'transaction_about'     => $value->transaction_about,
                    'user_type'     => $value->user_type,
                    'created_at'     => $value->created_at->format('d-m-y'),
                ];
            });

            if($data->count() == 0)
            return ResponseBuilder::error('No data found', $this->notFound);

            return ResponseBuilder::successMessage('Send Transaction List', $this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
     }

     public function requestMoneyMail(Request $request){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $validSet = [
            'zip_user_id' => 'required|exists:users,id',
            'amount'        => 'required',
            'type'        => 'required|in:send,request'
        ];

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }
        $user = Auth::user();
        // $tid = Transaction::where('user_id',$user->id)->latest()->pluck('t_id');
        $tids = Transaction::where('user_id',$user->id)->latest()->first();
        // return $tids;
        $mytime = Carbon::now();
        try {
            $Data = User::where('id',$request->zip_user_id)->select('email','name','fname')->first();
            if($Data) {
                if($request->type == 'request'){
                        $mailData = EmailTemplate::getMailByMailCategory('ZIP2ZIP request');
                        if(isset($mailData)) {

                            $arr1 = array('{amount}','{name}','{receiverName}','{zipTag}','{receEmail}','{recephone}','{receiver}');
                            $arr2 = array($request->amount,$Data->fname,$user->fname,$user->zip_tag,$user->email,$user->phone,$mytime);

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
                                Mail::to($Data->email)->send(new NewSignUp($config));
                            } catch (\Throwable $th) {
                                throw $th;
                            }   
                        }

                        $mailDatas = EmailTemplate::getMailByMailCategory('ZIP to ZIP sent money');
                        if(isset($mailDatas)) {

                            $arr1 = array('{amount}','{name}','{receiver}','{receive}','{received}','{tran}','{trandate}');
                            $arr2 = array($request->amount,$user->fname,$Data->email,$Data->fname.' '.$Data->lname, $Data->phone,$tids->t_id,$tids->created_at);

                            $email_content = $mailDatas->email_content;
                            $email_content = str_replace($arr1, $arr2, $email_content);
                        
                            $config = [
                                'from_email' => isset($mailDatas->from_email) ? $mailDatas->from_email : env('MAIL_FROM_ADDRESS'),
                                'name' => isset($mailDatas->from_email) ? $mailDatas->from_email : env('MAIL_FROM_NAME'),
                                'subject' => $mailDatas->email_subject, 
                                'message' => $email_content,
                            ];
                            
                            try {
                                //code...
                                Mail::to($user->email)->send(new NewSignUp($config));
                            } catch (\Throwable $th) {
                                throw $th;
                            } 
                        }
            }else{
                    $mailData = EmailTemplate::getMailByMailCategory('ZIP2ZIP request');
                    if(isset($mailData)) {

                        $arr1 = array('{amount}','{name}','{receiverName}','{zipTag}','{receEmail}','{recephone}','{receiver}');
                        $arr2 = array($request->amount,$Data->fname,$user->fname,$user->zip_tag,$user->email,$user->phone,$mytime);

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
                            Mail::to($Data->email)->send(new NewSignUp($config));
                        } catch (\Throwable $th) {
                            throw $th;
                        }   
                    }

                    $mailDatas = EmailTemplate::getMailByMailCategory('ZIP to ZIP sent money');
                        if(isset($mailDatas)) {

                            $arr1 = array('{amount}','{name}','{receiverName}','{zipTag}','{receEmail}','{recephone}','{receiver}','{receive}','{received}','{tran}','{trandate}');
                            $arr2 = array($request->amount,$user->fname,$user->fname,$user->zip_tag,$user->email,$user->phone,$Data->email,$Data->fname.' '.$Data->lname, $Data->phone,$tids->t_id,$tids->created_at);

                            $email_content = $mailDatas->email_content;
                            $email_content = str_replace($arr1, $arr2, $email_content);
                        
                            $config = [
                                'from_email' => isset($mailDatas->from_email) ? $mailDatas->from_email : env('MAIL_FROM_ADDRESS'),
                                'name' => isset($mailDatas->from_email) ? $mailDatas->from_email : env('MAIL_FROM_NAME'),
                                'subject' => $mailDatas->email_subject, 
                                'message' => $email_content,
                            ];
                            
                            try {
                                //code...
                                Mail::to($user->email)->send(new NewSignUp($config));
                            } catch (\Throwable $th) {
                                throw $th;
                            }   
                        }
                }

            }

        return ResponseBuilder::successMessage('Mail sent successfully',$this->success);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }

     }

    public function buyAirtimeList(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = Transaction::where(['user_id'=> $user->id ,'transaction_about'=>'Buy Airtime'])
                    ->latest()
                    ->get()
                    ->map(function($value){
                        return [
                            'user_id'     => $value->user_id,
                            'comon_id'     => $value->comon_id,
                            't_id'     => $value->t_id,
                            'amount'     => $value->amount,
                            'telcos'     => $value->telcos,
                            'phone'     => $value->phone,
                            'transaction_about'     => $value->transaction_about,
                            'user_type'     => $value->user_type,
                            'created_at'     => $value->created_at->format('d-m-y'),
                        ];
                    });
            return ResponseBuilder::success('Buy airtime',$this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function buyDataList(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = Transaction::where(['user_id'=> $user->id ,'transaction_about'=>'Buy Internet'])
                    ->latest()
                    ->get()
                    ->map(function($value){
                        return [
                            'user_id'     => $value->user_id,
                            'comon_id'     => $value->comon_id,
                            't_id'     => $value->t_id,
                            'amount'     => $value->amount,
                            'phone'     => $value->phone,
                            'telcos'     => $value->telcos,
                            'dataplan'     => $value->dataplan,
                            'data_code'     => $value->data_code,
                            'transaction_about'     => $value->transaction_about,
                            'user_type'     => $value->user_type,
                            'created_at'     => $value->created_at->format('d-m-y'),
                        ];
                    });
            return ResponseBuilder::success('Buy data',$this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function buyElectricity(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = Transaction::where(['user_id'=> $user->id ,'transaction_about'=>'Buy Electricity'])
                    ->latest()
                    ->get()
                    ->map(function($value){
                        return [
                            'user_id'     => $value->user_id,
                            'comon_id'     => $value->comon_id,
                            't_id'     => $value->t_id,
                            'amount'     => $value->amount,
                            'phone'     => $value->phone,
                            'dataplan '     => $value->dataplan ,
                            'telcos'     => $value->telcos,
                            'description'     => $value->description,
                            'transaction_about'     => $value->transaction_about,
                            'user_type'     => $value->user_type,
                            'created_at'     => $value->created_at->format('d-m-y'),
                        ];
                    });
            return ResponseBuilder::success('Buy electricity',$this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function buyCableTv() {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = Transaction::where(['user_id'=> $user->id ,'transaction_about'=>'Buy Cabel Tv'])
                    ->latest()
                    ->get()
                    ->map(function($value){
                        return [
                            'user_id'     => $value->user_id,
                            'comon_id'     => $value->comon_id,
                            't_id'     => $value->t_id,
                            'amount'     => $value->amount,
                            'phone'     => $value->phone,
                            'dataplan '     => $value->dataplan,
                            'telcos'     => $value->telcos,
                            'data_code'     => $value->data_code,
                            'description'     => $value->description,
                            'transaction_about'     => $value->transaction_about,
                            'user_type'     => $value->user_type,
                            'created_at'     => $value->created_at->format('d-m-y'),
                        ];
                    });
            return ResponseBuilder::success('Buy Cabel Tv List',$this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }
    Public function keysList(Request $request)
    {
        try {
            //code...
            $settingData = Setting::getAllSettingData();

            $this->response->token = $settingData['test_token'];
            $this->response->api_Key = $settingData['api_Key'];
            $this->response->secret_key = $settingData['secret_key'];
            $this->response->public_key = $settingData['public_key'];
            $this->response->business_id = $settingData['business_id'];
            return ResponseBuilder::successMessage('All Transaction list', $this->success,$this->response);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }
    
    public function generateLink(Request $request){
        try{
            $validSet = [
                'api-key' => 'required',
                'key' => 'required',
                'businessID'  => 'required_if:key,beneficiariesBussines',
                'amount'  => 'required_if:key,link',
                'currency'  => 'required_if:key,link,createAcc',
                'customer'  => 'required_if:key,link',
                'virtualAccountId'  => 'required_if:key,virtualAccount',
                // 'business'  => 'required_if:key,payouts,createAcc',
                // 'phone_email' => 'required_if:key,createAcc',
                // 'accountType' => 'required_if:key,createAcc',
                'KYCInformation' => 'required_if:key,createAcc',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            if($request->input('api-key') != env("LIVE_KEY")){
                return ResponseBuilder::error('Unauthorized', $this->badRequest);
            }

            if($request->key == 'link'){
                $users = Auth::guard('api')->user();
                $user = User::where('email',$request->customer['email'])->first();
                $setting = Setting::getAllSettingData();
                $payout_fee = $setting['payout_fee'];
                $payout_fee = (int)$payout_fee;
                $now = Carbon::now('GMT+1');
                $unique_code = $now->format('YmdHis').$users->id;
                // $user = $users->id;
                $link = Helper::linkGeneration($request->amount, $request->currency, $request->customer['name'], $request->customer['email'], $request->customer['phoneNumber'],$user->id);

                $trans = Transaction::create([
                    'user_id'   => $user->id,
                    'receiver_id' => $user->id,
                    'transaction_type' => 'dr',
                    't_id' => $user->unique_id,
                    'transaction_about' => 'Link Generation Fees',
                    'amount' => $request->amount + $payout_fee,
                    'phone' => $user->phone,
                    'customer_reference' => $unique_code
                ]);

                $user->wallet_balance -= $trans->amount;
                $user->save();

                // $balance = $user->available_amount - $payout_fee;
                
                return json_decode($link);

            } 
            elseif($request->key == 'beneficiariesBussines'){
                $bussines = Helper::beneficiariesBussines($request->businessID);
                return json_decode($bussines);

            } elseif($request->key == 'payouts'){
                $user = User::where('id', $request->user_id)->first();
                $id = $user->id;
                $web = WebhookDetails::latest('created_at')->first();
                $webId = $web->id + 1;
                $custReference = $id."-".$webId;
                $request['customerReference'] = $custReference;

                $setting = Setting::getAllSettingData();
                $payout_fee = $setting['payout_fee'];
                $cash_out = $setting['cashout_fee'];
                $payout_fee = (int)$payout_fee;
                // $cashout_fee = (int)$cashout_fee;
                $amount = (int)$request->amount + $payout_fee;
                // if($request->beneficiary['about'] == "Pay Out")
                // {
                //     $amount = (int)$request->amount + $payout_fee;
                // } else {
                //     $amount = (int)$request->amount + $cashout_fee;
                // }
                

                $referenceId = WebhookDetails::create([
                    'user_id' => $id,
                    'customer_reference' => $custReference
                ]);

                $trans = Transaction::create([
                    'user_id' => $id,
                    'customer_reference' => $custReference,
                    'transaction_type' => 'dr',
                    't_id' => $user->unique_id,
                    'amount' => $amount,
                    'phone' => $user->phone,
                    'transaction_about' => $request->beneficiary['about'],
                ]);

                $balance = $user->available_amount - $payout_fee;

                $user->wallet_balance -= $trans->amount;
                $user->save();

                $tid = Transaction::where('id',$trans->id)->pluck('t_id')->first();
                $tids = Transaction::where('id',$trans->id)->first();
                $users = VirtualAccounts::where('accountNumber',$request->beneficiary['accountNumber'])->first(); 
                if(!empty($users))
                {
                    $dataArray  = json_decode($users['accountInformation'], true);
                    $bankName = $dataArray['bankName'];
                    $dataArray  = json_decode($users['KYCInformation'], true);
                    $firstName = $dataArray['firstName'];
                }
                $fname = $trans->user ? $trans->user->fname : "";
                $lname = $trans->user ? $trans->user->lname : "";
                $loginName =  $fname ." ". $lname;

                if($user->available_amount <= $amount){
                    return ResponseBuilder::error('You do not have enough balance to make this payment', $this->badRequest,$user->available_amount);
                }

                $pay = Helper::payouts($request->business,$request->sourceCurrency,$request->destinationCurrency,$request->amount,$request->description,$custReference,$request->beneficiary['firstName'],$request->beneficiary['type'],$request->beneficiary['accountHolderName'],$request->beneficiary['accountNumber'],$request->beneficiary['bank_code'],$request->paymentDestination);

                if($request->beneficiary['about'] == "Cash Out" || $request->beneficiary['about'] == "Pay Out")
                {
                    $mailData = EmailTemplate::getMailByMailCategory(strtolower('Sent receipt'));
                    if(isset($mailData)) {
        
                        $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}');
        
                        $arr2 = array($loginName ??'',$amount ??'',$firstName ?? $request->beneficiary['accountHolderName'], $tid ??'-',$trans->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$request->beneficiary['accountNumber'],$request->beneficiary['firstName']);
        
                        $msg = $mailData->email_content;
                        $msg = str_replace($arr1, $arr2, $msg);
                        $email_content = $mailData->email_content;
                        $email_content = str_replace($arr1, $arr2, $email_content);
                    
                            $config = [
                            'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                            'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                            'subject' => $mailData->email_subject, 
                            'message' => $email_content,
                        ];
                        
                        try {
                            Mail::to($user->email)->send(new NewSignUp($config));
                        } catch (\Throwable $th) {
                            throw $th;
                        } 
                    }
                }
               
                return json_decode($pay);
                
            } elseif($request->key == 'createAcc'){
                $acc = Helper::createVirtualAcc($request->accountType, $request->currency, $request->KYCInformation['firstName'], $request->KYCInformation['lastName'], $request->KYCInformation['bvn'], Carbon::createFromFormat('d-m-Y', $request->dateOfBirth)->format('m-d-Y'));
                $user = User::getUserByBVN($request->KYCInformation['bvn']);
                $acc = json_decode($acc);
                if(!empty($acc) && $acc->success == true) {
                    $create_account = VirtualAccounts::create([
                        'user_id'            => $user->id,
                        'accountType'        => $acc->data->accountType,
                        'currency'           => $acc->data->currency,
                        'business'           => $acc->data->business,
                        'business_id'        => $acc->data->_id,
                        'accountNumber'      => $acc->data->accountNumber,
                        'creation_origin'    => 'ZIP app',
                        'KYCInformation'     => json_encode($acc->data->KYCInformation),
                        'accountInformation' => json_encode($acc->data->accountInformation),
                    ]);
                    $result = $create_account->save();
                }
                if(!empty($acc) && $acc->success == true && $result) {
                    return $acc;
                }
                else {
                    return ResponseBuilder::error($acc->error, $this->badRequest);
                }
                    
            } elseif($request->key == 'beneficiaryCreate'){
                $beneficiary = Helper::createBeneficiary($request->businessID,$request->first_name,$request->accountHolderName,$request->type,$request->currency,$request->paymentDestination,$request->destinationAddress);
                return json_decode($beneficiary);
            } elseif($request->key == 'resolve'){
                $resolve = Helper::accountResolve($request->accountNumber, $request->bankCode);
                return json_decode($resolve);
            } elseif($request->key == 'banklist'){
                $bank = Helper::bankList();
                return json_decode($bank);
            }
            // elseif($request->key == 'telcos'){
            //     return $tel = Helper::telcos();
            //     return $tel;
            // }
           
        } catch (\Throwable $th) {
            \Log::error($th);
            return ResponseBuilder::error($th->getMessage(), $this->badRequest);
        }
    }

    public function vtpassServices(Request $request){
        switch($request->key) {
            case "services":
                $identifier = $request->identifier;
                $response = Helper::services('services?identifier='.$identifier, 'GET', '');
                if($response['response_description'] == 000) {
                    return ResponseBuilder::success('service', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;

            case "variations":
                $serviceID = $request->serviceID;
                $response = Helper::services('service-variations?serviceID='. $serviceID, 'GET', '');
                if(isset($response['response_description']) && $response['response_description'] == 000 || $response['code'] == 011) {
                    return ResponseBuilder::success('Variations', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error(isset($response['response_description']) ? $response['response_description'] : $response['content']['errors'], $this->badRequest);
                }
                break;

            case "pay":
                $user = User::where('id',Auth::user()->id)->first();
                $now = Carbon::now('GMT+1');
                $unique_code = $now->format('YmdHis').Auth::user()->id;
                $request['request_id'] = $unique_code;

                $loginName =  $user->fname ." ". $user->lname;

                $setting = Setting::getAllSettingData();
                $service_fee = $setting['service_fee'];

                if($user->available_amount <= $request->amount){
                    return ResponseBuilder::error('You Do not have enough balance to make this payment', $this->badRequest,$user->available_amount);
                }
                if(empty($request['billersCode']))
                {
                    $request['billersCode'] = $request['phone'];
                }

                $response = Helper::services('pay', 'POST', $request);

                if (isset($response) && isset($response['code']) && $response['code'] == '000') {
                    $trans = Transaction::create([
                        'user_id'   => $user->id,
                        'receiver_id' => $user->id,
                        'transaction_type' => 'dr',
                        't_id' => $user->unique_id,
                        'transaction_about' => $response['content']['transactions']['type'],
                        'amount' => $response['content']['transactions']['amount'] + $service_fee,
                        'phone' => $user->phone,
                        'telcos' =>  $request->telcos ?? null,
                        'dataplan' =>  $request->data_code ?? null,
                        'customer_reference' => $unique_code
                    ]);

                    $user->wallet_balance -= $trans->amount;
                    $user->save();
                }
                // $balance = $user->available_amount - $service_fee;
                
                $webData = WebhookDetails::create([
                    'user_id' => Auth::user()->id,
                    'webhook_type' => 'services',
                    'customer_reference' => $unique_code,
                    // 'type' => $response['content']['transactions']['type'],
                    'type' => $response['response_description'],
                    'trans_response' => json_encode($response)
                ]);

                if($response['code'] != 000){
                    $trans = Transaction::create([
                        'user_id'   => $user->id,
                        'receiver_id' => $user->id,
                        'transaction_type' => 'cr',
                        't_id' => $user->unique_id,
                        'transaction_about' => 'Transaction Failed',
                        // 'amount' => $response['content']['transactions']['amount'],
                        'amount' => $request->amount,
                        'phone' => $user->phone,
                        'customer_reference' => $unique_code
                    ]);

                    $user->wallet_balance += $trans->amount;
                    $user->save();
    
                    // $balance = $user->available_amount + $service_fee;
                }

                $mailData = EmailTemplate::getMailByMailCategory(strtolower('service'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{phone}','{telcos}','{mainToken}','{bonusToken}');
    
                    $arr2 = array($loginName ??'',$trans->amount ??'', $user->unique_id ??'-',$trans->created_at->format('d F Y'),$trans->transaction_about ??'',$trans->dataplan,$request->phone ?? $user->phone,$trans->telcos, isset($response['token']) && !empty($response['token']) ? 'Main Token: '.$response['token'] : '', isset($response['bonusToken']) && !empty($response['bonusToken']) ? 'Bonus Token: '.$response['bonusToken'] : '');
    
                    $msg = $mailData->email_content;
                    $msg = str_replace($arr1, $arr2, $msg);
                    $email_content = $mailData->email_content;
                    $email_content = str_replace($arr1, $arr2, $email_content);
                
                         $config = [
                        'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                        'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                        'subject' => $mailData->email_subject, 
                        'message' => $email_content,
                    ];
                    
                    try {
                        Mail::to($user->email)->send(new NewSignUp($config));
                    } catch (\Throwable $th) {
                        throw $th;
                    } 
                }

                // $arr1 = array('{type}');
                // $arr2 = array($response['content']['transactions']['type']);
                // $msg = str_replace($arr1, $arr2, trans('notifications.PAYMENT_DETAILS'));

                // Helper::fireBasePushNotification($user->id, 'Payment', $msg);
                
                if($response['code'] == 000) {
                    return ResponseBuilder::success('Pay Details', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;
            
            case "requery":
                $response = Helper::services('requery', 'POST', $request);
                if($response['code'] == 000) {
                    return ResponseBuilder::success('Transaction Details', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;

            case "airtime-countries":
                $response = Helper::services('get-international-airtime-countries', 'GET', '');
                if($response['response_description'] == 000) {
                    return ResponseBuilder::success('Countries', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;
                
            case "airtime-product-types":
                $code = $request->code;
                $response = Helper::services('get-international-airtime-product-types?code='.$code, 'GET', '');
                if($response['response_description'] == 000) {
                    return ResponseBuilder::success('Country Code', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;

            case "airtime-operators":
                $code = $request->code;
                $product_type_id = $request->product_type_id;
                $response = Helper::services('get-international-airtime-operators?code='.$code.'&product_type_id='.$product_type_id, 'GET', '');
                if($response['response_description'] == 000) {
                    return ResponseBuilder::success('Operators', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;

            case "service-variations":
                $serviceID = $request->serviceID;
                $operator_id = $request->operator_id;
                $product_type_id = $request->product_type_id;
                $response = Helper::services('service-variations?serviceID='. $serviceID.'&operator_id='.$operator_id.'&product_type_id='.$product_type_id, 'GET', '');
                if($response['response_description'] == 000) {
                    return ResponseBuilder::success('Service Variations', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;

            case "merchant-verify":
                $response = Helper::services('merchant-verify', 'POST', $request);
                if($response['code'] == 000) {
                    return ResponseBuilder::success('Meter Details', $this->success, $response);
                }
                else {
                    return ResponseBuilder::error($response['response_description'], $this->badRequest);
                }
                break;
        }
    }

    public function bridgeCard(Request $request)
    {
        switch($request->key) {
            case "deleteCard":
                $card_id = $request->card_id;
                $card = UserCardInfo::where('card_id',$card_id)->first();
                $cardUser = UserCard::where('card_id',$card_id)->first();
                
                if($card) {
                    $response = Helper::bridgeCard('cards/delete_card/'.$card_id, 'DELETE', '', $request->key);
                    // dd($response);
                    $result = $card->delete();
                    if($result && $cardUser) {
                        $cardUser->delete();
                    }
                    return $response;
                } else {
                    return ResponseBuilder::error('Card not found or already deleted.', $this->badRequest);
                    // return [
                    //     'status' => 'error',
                    //     'message' => 'Card not found or already deleted.',
                    // ];
                }
                // $card->delete();
                // return $response = Helper::bridgeCard('cards/delete_card/'.$card_id, 'DELETE', '', $request->key);
                break;
            
            case "createCard":

                $user = User::where('id',Auth::user()->id)->first();
                $fee = Helper::bridgeCardCalculation();
                $cardHolder = CardHolderDetails::where('user_id', $user->id)->where('status', 'verified')->orderBy('created_at','desc')->first();
                // $cardHolder = CardHolderDetails::where('cardholder_id', $request->cardholder_id)->where('status', 'verified')->first();
                // return $cardHolder;
                if(!$cardHolder) {
                    $address = UserAddress::where('user_id',$user->id)->first();
                    $identity_data['id_type'] = "NIGERIAN_BVN_VERIFICATION";
                    $identity_data['bvn'] = $user->bvn ?? '';
                    $identity_data['selfie_image'] = !empty($user->user_image) ? url('uploads/profile-images/'.$user->user_image) : '';
                    $mata_user_id['user_id'] = $user->id;
                    $address_data['address'] = $address->street_name;
                    $address_data['city'] = $address->city;
                    $address_data['state'] = $address->state;
                    $address_data['country'] = $address->country;
                    $address_data['postal_code'] = $address->postal_code ?? 000000;
                    $address_data['house_no'] = $address->house_number;
                    // $request['selfie_image'] = !empty($user->user_image) ? url('uploads/profile-images/'.$user->user_image) : '';
                    $request['first_name'] = $user->fname ?? '';
                    $request['last_name'] = $user->lname ?? '';
                    $request['phone'] = $user->phone ?? '';
                    $request['email_address'] = $user->email ?? '';
                    $request['identity'] = $identity_data;
                    $request['meta_data'] = $mata_user_id ?? '';
                    $request['address'] = $address_data ?? '';
                  
                    $response = Helper::bridgeCard('cardholder/register_cardholder_synchronously', 'POST', $request, 'registerCardHolder');
                    
                    if(!isset($response['status'])) {
                        return ResponseBuilder::error('Please Contact Customer Support', $this->badRequest);
                        // return ResponseBuilder::error($response, $this->badRequest);
                    }
                    if (isset($response) && isset($response['status']) && $response['status'] == 'success') {
                        $cardHolder = CardHolderDetails::create([
                            'user_id' => Auth::user()->id,
                            'cardholder_id' => $response['data']['cardholder_id'],
                            'status' => 'un-verified',
                            'response' => json_encode($response)
                        ]); 

                        // $user->is_africa_verifed = 1;
                        // $user->save();
                    }

                    // $request['cardholder_id'] = $response_card_holder['data']['cardholder_id'];

                    // $response = Helper::bridgeCard('cards/create_card', 'POST', $request, $request->key);
                    
                    // if (isset($response['status']) && $response['status'] == 'success') {
                    //     $userCard = UserCard::create([
                    //         'user_id' => Auth::user()->id,
                    //         'card_id' => $response['data']['card_id'],
                    //         'card_currency' => $response['data']['currency'],
                    //         'card_type' => $request->card_type,
                    //         'card_brand' => $request->card_brand,
                    //         'cardholder_id' => $response_card_holder['data']['cardholder_id'],
                    //         'resposnse' => json_encode($response)
                    //     ]);
                    // }
                }
                else {
                    $existingCard = UserCard::where('card_brand', $request->card_brand)
                        ->where('cardholder_id', $cardHolder->cardholder_id)
                        ->first();

                    if ($existingCard) {
                        return [
                            'status' => 'error',
                            'message' => 'A '.$request->card_brand.' card already exists for this cardholder ID.',
                        ];
                    }
                    $request['cardholder_id'] = $cardHolder->cardholder_id;
    
                    $response = Helper::bridgeCard('cards/create_card', 'POST', $request, $request->key);
                    if (isset($response['status']) && $response['status'] == 'success') {
                        $userCard = UserCard::create([
                            'user_id' => Auth::user()->id,
                            'card_id' => $response['data']['card_id'],
                            'card_currency' => $response['data']['currency'],
                            'card_type' => $request->card_type,
                            'card_brand' => $request->card_brand,
                            // 'cardholder_id' => $request->cardholder_id,
                            'cardholder_id' => $cardHolder->cardholder_id,
                            'resposnse' => json_encode($response)
                        ]);
                        $setting = Setting::getAllSettingData();
                        $bridgeCard_fee = $setting['cardCreation_fee'];
                        $cardCreation_fee = $bridgeCard_fee * $fee;

                        $cardFee = Transaction::create([
                            'user_id' => Auth::user()->id,
                            'receiver_id' => Auth::user()->id,
                            'transaction_type' => 'dr',
                            't_id' => $user->unique_id,
                            'transaction_about' => 'Fees for card creation',
                            'amount' => $cardCreation_fee,
                            'phone' => $user->phone
                        ]);

                        $balance = $user->available_amount - $cardCreation_fee;

                        $user->wallet_balance -= $cardFee->amount;
                        $user->save();

                    }
                }
                return $response;
                break;

            case "fundCard":
                $user = User::where('id',Auth::user()->id)->first();
                $users = VirtualAccounts::where('user_id',$user->id)->first();
                $dataArray  = json_decode($users['accountInformation'], true);
                $bankName = $dataArray['bankName'];
                $dataArray  = json_decode($users['KYCInformation'], true);
                $firstName = $dataArray['firstName'];
                $loginName = $user->fname.' '.$user->lname;
                $response = Helper::bridgeCard('cards/fund_card_asynchronously', 'PATCH', $request ,$request->key);

                if (isset($response['status']) && $response['status'] == 'success') {
                    $fee = Helper::bridgeCardCalculation();

                    $setting = Setting::getAllSettingData();
                    $bridgeCard_fee = $setting['bridgeCard_fee'];
                    // $cardFund_fee = $bridgeCard_fee * $fee;

                    $cardFee = Transaction::create([
                        'user_id' => Auth::user()->id,
                        'receiver_id' => Auth::user()->id,
                        'transaction_type' => 'dr',
                        't_id' => $user->unique_id,
                        'transaction_about' => 'Fees for fund card',
                        'amount' => $bridgeCard_fee,
                        'phone' => $user->phone
                    ]);

                    $balance = $user->available_amount - $bridgeCard_fee;

                    $user->wallet_balance -= $cardFee->amount;
                    $user->save();

                    $mailData = EmailTemplate::getMailByMailCategory(strtolower('Add funds card'));
                    if(isset($mailData)) {
        
                        $arr1 = array('{name}','{amount}','{r_name}','{transaction_date}','{accountNumber}','{email}','{phone}');
        
                        $arr2 = array($loginName ??'',$request->amount ??'',$firstName ??'',$cardFee->created_at->format('d F Y'),$users->accountNumber,$user->email,$user->phone);
        
                        $msg = $mailData->email_content;
                        $msg = str_replace($arr1, $arr2, $msg);
                        $email_content = $mailData->email_content;
                        $email_content = str_replace($arr1, $arr2, $email_content);
                    
                             $config = [
                            'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                            'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                            'subject' => $mailData->email_subject, 
                            'message' => $email_content,
                        ];
                        
                        try {
                            Mail::to($user->email)->send(new NewSignUp($config));
                        } catch (\Throwable $th) {
                            throw $th;
                        } 
                    }
                }
                return $response;

                break;

            case "fundIssue":
                return $response = Helper::bridgeCard('cards/fund_issuing_wallet', 'PATCH', $request, $request->key);
                break;

            case "registerCardHolder":
                $user = User::where('id',Auth::user()->id)->first();
                // $request['selfie_image'] = !empty($user->user_image) ? url('uploads/profile-images/'.$user->user_image) : '';
                // return $request->address->postal_code;
                // return $request['address']['postal_code'];
                DB::table('error_log')->insert(['response' => $request]);
                $response = Helper::bridgeCard('cardholder/register_cardholder_synchronously', 'POST', $request, $request->key);
                DB::table('error_log')->insert(['response' => json_encode($response)]);

                if(isset($response) && isset($response['status']) && $response['status'] == 'success') {
                    $cardHolder = CardHolderDetails::create([
                        'user_id' => Auth::user()->id,
                        'cardholder_id' => $response['data']['cardholder_id'],
                        'response' => json_encode($response),
                        'status' => 'verified'
                    ]);

                    $user->is_africa_verifed = 1;
                    $user->save();
                }

                return $response;
                break;

            case "cardDetails":
                $card_id = $request->card_id;
                return $response = Helper::bridgeCard('cards/get_card_details?card_id='.$card_id, 'GET', '', $request->key);
                break;

            case "cardBalance":
                $card_id = $request->card_id;
                return $response = Helper::bridgeCard('cards/get_card_balance?card_id='.$card_id, 'GET', '', $request->key);
                break;

            case "freezeCard":
                $card_id = $request->card_id;
                return $response = Helper::bridgeCard('cards/freeze_card?card_id='.$card_id, 'PATCH', '', $request->key);
                break;

            case "unfreezeCard":
                $card_id = $request->card_id;
                return $response = Helper::bridgeCard('cards/unfreeze_card?card_id='.$card_id, 'PATCH', '', $request->key);
                break;
        }
    }

    public function getCardHolder(Request $request)
    {
        try{
            $validSet = [
                'user_id' => 'required',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $cardHolder = CardHolderDetails::where('user_id',$request->user_id)->first();

            if($cardHolder){
                return ResponseBuilder::successMessage('Card Holder Details',$this->success,$cardHolder);
            }

            return ResponseBuilder::successMessage(trans('No Card Holder with this user id'),[]);
        }catch (\Exception $e) {
            return $e->getMessage();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }

    }
}