<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\BeneficiaryCollection;
use App\Http\Resources\Admin\BankDetailsResource;
use App\Http\Resources\Admin\UserResource;
use Auth;
use DB;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserBank;
use App\Models\UserCard;
use App\Models\Beneficiary;
use App\Models\UserCardInfo;
use App\Models\EmailTemplate;
use App\Models\VirtualAccounts;
use App\Models\Setting;
use App\Models\CardHolderDetails;
use App\Models\BankLoan;
use App\Models\UserMeta;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSignUp;
use Carbon\Carbon;
use Validator;
use App\Helper\Helper;

use Illuminate\Support\Facades\Hash;
use App\Helper\ResponseBuilder;

class UserDetailsController extends Controller
{

    public function details(Request $request)
    {
        $today = Carbon::now()->format('Y/m/d');
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required | email | unique:users',
            'phone' => 'required | digits:10 | integer',
            'username' => 'required | unique:users',
            'date_of_birth' => 'before:'.$today.'| date_format:Y/m/d',
        ]);

        try {
            if($validate->fails()) {
                return ResponseBuilder::error($validate->errors()->first(), $this->badRequest);
            }

            if(User::where('email',$request->email && 'phone',$request->phone)->first())
            {
                return response([
                    'message' => 'User already exists',
                    'status' => 'failed'
                ]);
            }

            $user = User::create([
                'name' => $request -> name,
                'email' => $request -> email,
                'phone' => $request -> phone,
                'purpose_Category' => $request -> purpose_Category,
                'date_of_birth' => $request -> date_of_birth,
                'username' => $request -> username,
                'residential_address' => $request -> residential_address,
            ]);
            return ResponseBuilder::successMessage('User Details', $this->success, $user);

        } catch(exception $e) {
            return ResponseBuilder::error($e -> Message(), $this -> badRequest);
        }
    }

    public function search(Request $request)
    {
        // return $request->all();
        // $user = Auth()->guard('api')->user();
        // return $user;
        // // Validation start
        // $validSet = [
        //     'zip_tag' => 'required | string'
        //     'zip_tag' => 'required | string'
        //     'zip_tag' => 'required | string'
        // ];

        // $isInValid = $this->isValidPayload($request, $validSet);
        // if($isInValid){
        //     return ResponseBuilder::error($isInValid, $this->badRequest);
        // }
        // // Validation end
        $data['email'] = $request->email;
        $data['phone'] = $request->phone;
        $data['zip_tag'] = $request->zip_tag;

        // return $data;
        $q = User::query();
        if(isset($request->email)) {
            $q->where('email', 'like', "%$request->email%");
        }
        if(isset($request->phone)) {
            $q->where('phone', 'like', "%$request->phone%");
        }
        if(isset($request->zip_tag)) {
            $q->where('zip_tag', 'like', "%$request->zip_tag%");
        }

        $data = $q->first();
    
        $user = new UserResource($data);

        if(!$user)
        return ResponseBuilder::successMessage('No data found', $this->notFound);

        return ResponseBuilder::success('User Data', $this->success, $user);
    }


    public function saveTransaction(Request $request) {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        $validSet = [
            'type'              =>  'required|in:cr,dr',
            'about'             =>  'nullable',
            'amount'            =>  'required|numeric',
            'beneficiary_id'    =>  'nullable|numeric',
            'description'       =>  'nullable',
            'complete_response' =>  'nullable',
            'dataplan'          =>  'nullable',
            'phone'             =>  'nullable|numeric',
            'user_id'           =>  'required|exists:users,id',
            'send_type'         =>  'nullable|in:otherusers,ziptozip,request,send'
        ];

        $customeMessage = [
            'user_id.exists'    => "User id doestn't exist in our system"
        ];

        $isInValid = $this->isValidPayload($request, $validSet,$customeMessage);

        if($isInValid)
        return ResponseBuilder::error($isInValid, $this->badRequest);

        try {
            //code...
            $saveTran = Transaction::create([
                'user_id'           =>  $request->user_id,
                'transaction_type'  =>  $request->type,
                't_id'              =>  'Zip',
                'sender_id'         =>  $request->sender_id,
                'receiver_id'      =>  $request->receiver_id,
                'amount'            =>  $request->amount,
                'phone'             =>  $request->phone ?? null,
                'data_code'         =>  $request->data_code ?? null,
                'telcos'            =>  $request->telcos ?? null,
                'dataplan'          =>  $request->dataplan ?? null,
                'transaction_about' =>  $request->about,
                'description'       =>  $request->description ?? null,
                'complete_response' =>  $request->complete_response,
                'user_type'         =>  $request->send_type ?? null,
                'beneficiary_id'    =>  $request->beneficiary_id ?? null,
            ]);

            // dd($saveTran);

             Transaction::where('id',$saveTran->id)->update([
                't_id' => "ZIP".str_pad($saveTran->id, 7, "0", STR_PAD_LEFT)
            ]);
            $tid = Transaction::where('id',$saveTran->id)->pluck('t_id')->first();
            $tids = Transaction::where('id',$saveTran->id)->first();
            $users = VirtualAccounts::where('user_id',$tids->user_id)->first();
              $dataArray  = json_decode($users['accountInformation'], true);
              $bankName = $dataArray['bankName'];
              $dataArray  = json_decode($users['KYCInformation'], true);
              $firstName = $dataArray['firstName'];
            $fname = $saveTran->user ? $saveTran->user->fname : "";
            $lname = $saveTran->user ? $saveTran->user->lname : "";
            $loginName =  $fname ." ". $lname;
            if($request->about == "Send Cash")
            {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('Sent receipt'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}');
    
                    $arr2 = array($loginName ??'',$request->amount ??'',$firstName ??'', $tid ??'-',$saveTran->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$users->accountNumber,$bankName);
    
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
            }elseif($request->about == "Request Cash")
            {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('Bank request'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}','{phone}');
    
                    $arr2 = array($loginName ??'',$request->amount ??'',$firstName ??'', $tid ??'-',$saveTran->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$users->accountNumber,$bankName,$user->phone);
    
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
            }elseif($request->about == "Fund To Account")
            {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('Add funds'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}','{phone}');
    
                    $arr2 = array($loginName ??'',$request->amount ??'',$firstName ??'', $tid ??'-',$saveTran->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$users->accountNumber,$bankName,$user->phone);
    
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
            }elseif($request->about == "Withdrawl Cash")
            {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('Withdrawal'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}','{phone}');
    
                    $arr2 = array($loginName ??'',$request->amount ??'',$firstName ??'', $tid ??'-',$saveTran->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$users->accountNumber,$bankName,$user->phone);
    
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
            }elseif($request->about == "ziptozip sent")
            {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('ZIP to ZIP sent money'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}','{phone}');
    
                    $arr2 = array($loginName ??'',$request->amount ??'',$firstName ??'', $tid ??'-',$saveTran->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$users->accountNumber,$bankName,$user->phone);
    
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
            }elseif($request->about == "Buy Airtime" || $request->about == "Buy Cable Tv" || $request->about == "Buy Internet" || $request->about == "Buy Electricity")
            {
                $mailData = EmailTemplate::getMailByMailCategory(strtolower('service'));
                if(isset($mailData)) {
    
                    $arr1 = array('{name}','{amount}','{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{phone}','{telcos}');
    
                    $arr2 = array($loginName ??'',$request->amount ??'', $tid ??'-',$saveTran->created_at->format('d F Y'),$request->about ??'',$request->dataplan,$request->phone,$request->telcos);
    
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
            

            return ResponseBuilder::successMessage('Data save successfully', $this->success);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th->getMessage().' at line '.$th->getLine() .' at file '.$th->getFile(),$this->badRequest);
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function saveUserBank(Request $request) {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        $validSet = [
            'destinationAddress'    =>  'required|numeric',
            'firstName'             =>  'required',
            'bank_name'            =>  'required',
            'bank_code'            =>  'required',
        ];


        $isInValid = $this->isValidPayload($request, $validSet);

        if($isInValid)
        return ResponseBuilder::error($isInValid, $this->badRequest);

        try {
            //code...
            $userBank = UserBank::where('destinationAddress',$request->destinationAddress)->where('user_id',$user->id)->first();
            if($userBank){
                return ResponseBuilder::error('Already exists with this bank account number', $this->badRequest);
            }else{

                UserBank::create([
                    'user_id'   => $user->id,
                    'destinationAddress'    => $request->destinationAddress,
                    'firstName'    => $request->firstName,
                    'bank_name'    => $request->bank_name,
                    'bank_code'    => $request->bank_code,
                ]);
    
                return ResponseBuilder::successMessage('Bank details save successfully', $this->success);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }

    }

    public function userBankList() {

        if(!Auth::guard('api')->check())
        return ResponseBuilder::error(__("User not found"), $this->unauthorized);

        $user = Auth::user();
        try {
            //code...
            $data = UserBank::where('user_id',$user->id)->latest()->get()
            ->map(function($value){
                return [
                    'id'            => $value->id,
                    'bank_code'     => $value->bank_code,
                    'bank_name'     => $value->bank_name,
                    'destinationAddress'     => $value->destinationAddress,
                    'firstName'     => $value->firstName,
                ];
            });

            if($data->count() == 0)
            return ResponseBuilder::error('No data found', $this->notFound);

            return ResponseBuilder::successMessage('User Bank list', $this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }


    public function saveCardDetails(Request $request){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);

        $user = Auth::user();
        $validSet = [
            'card_id'            =>  'required',
            'cardholder_id'      =>  'required',
            'card_type'          =>  'required',
            'resposnse'          =>  'nullable',
            'card_currency'      =>  'required',
        ];
        $isInValid = $this->isValidPayload($request, $validSet);

        if($isInValid)
        return ResponseBuilder::error($isInValid, $this->badRequest);

        try {
            UserCard::create([
                'user_id'   => $user->id,
                'card_id'   => $request->card_id,
                'cardholder_id'   => $request->cardholder_id,
                'card_type'   => $request->card_type,
                'resposnse'   => $request->resposnse ?? null,
                'card_currency'   => $request->card_currency,

            ]);
            return ResponseBuilder::successMessage('Card details save successfully', $this->success);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function saveBeneficiary(Request $request) {
        $valid = [
            'unique_id'         => 'required',
            'bank_name'         => 'required',
            'destination_address'     => 'required',
            'first_name'        => 'required',
            'account_holder_name'     => 'required',
            'business_id'       => 'required',
            'bank_code'         => 'required'
        ];

        $isInValid = $this->isValidPayload($request, $valid);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }
        try {
            //code...
            // Beneficiary::create($request->all());
            $beneficiary = Beneficiary::where('destination_address',$request->destination_address)->where('unique_id',$request->unique_id)->first();
            if(!empty($beneficiary)){
                return ResponseBuilder::error('Beneficiary already exists with this account number', $this->badRequest);
            }else{
                Beneficiary::create($request->all());
            }

            return ResponseBuilder::successMessage('Beneficiary details save successfully', $this->success);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }

    }

    public function getBeneficiary(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        try {
            //code...
            $user = Auth::user();
            if(count($user->bebeficiaries) == 0)
            return ResponseBuilder::error('No data found', $this->notFound);

            $this->response = new BeneficiaryCollection($user->bebeficiaries);
            return ResponseBuilder::successMessage('Beneficiary details', $this->success,$this->response);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }

    }

    public function favouriteBeneficiary(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        try {
            //code...
            $user = Auth::user();

            $fbeneficiary =  Beneficiary::select('beneficiaries.*', DB::raw('IFNULL(t.transaction_count, 0) as transaction_count'))
            ->leftJoinSub(function ($query) {
                $query->select('beneficiary_id', DB::raw('COUNT(id) as transaction_count'))
                    ->from('transactions')
                    ->groupBy('beneficiary_id');
            }, 't', 'beneficiaries.id', '=', 't.beneficiary_id')
            ->where('beneficiaries.unique_id', $user->unique_id)
            ->orderByDesc('transaction_count')
            ->limit(10)
            ->get();

            if(count($fbeneficiary) == 0)
            return ResponseBuilder::error('No data found', $this->notFound);

            $this->response = new BeneficiaryCollection($fbeneficiary);
            return ResponseBuilder::successMessage('Favourite beneficiary details', $this->success,$this->response);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function currentBalance(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);
    
        try {
            $user = Auth::user();

            $setting = Setting::getAllSettingData();
            $payout_fee = $setting['payout_fee'];
            $cashout_fee = $setting['cashout_fee'];
            $cashin_fee = $setting['cashin_fee'];
            $service_fee = $setting['service_fee'];
            $bridgeCard_fee = $setting['bridgeCard_fee'];
            $fxrate_fee = $setting['bridgeCard_fxrate_fee'];
            // $cardCreation_fee = $setting['cardCreation_fee'];
            $payout_fee = (int)$payout_fee;
            $cashoutFee = (int)$cashout_fee;
            $cashinFee = (int)$cashin_fee;
            $serviceFee = (int)$service_fee;
            $bridgeCardFee = (int)$bridgeCard_fee;
            $fxRateFee = (int)$fxrate_fee;

            $fee = Helper::bridgeCardCalculation();
            $bridgeCard_fee = $setting['cardCreation_fee'];
            $cardCreation_fee = $bridgeCard_fee * $fee;
            
            $fees = [
                'payout_fee' => $payout_fee,
                'cashout_fee' => $cashoutFee,
                'cashin_fee' => $cashinFee,
                'service_fee' => $serviceFee,
                'bridgeCard_fee' => $bridgeCardFee,
                'fx-rate_fee' => $fxRateFee
            ];
            
            $pay = (int)$setting['cardCreation_fee'];
            $convertedFees = $cardCreation_fee;
           
            $userMeta = UserMeta::where('user_id',$user->id)->pluck('value','key');
            $userMetaFirst = UserMeta::where('user_id',$user->id)->where('key','hide_balance')->get();
           
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

            $userLoan = BankLoan::where('user_id',$user->id)->first();
            if(!empty($userLoan))
            {
                $loan_apply = true;
            }else{
                $loan_apply = false;
            }

            $userCard = UserCard::where('user_id',$user->id)->first();
            if(!empty($userCard))
            {
                $user_card = true;
            }else{
                $user_card = false;
            }
            
            return ResponseBuilder::successMessage('Current balance', $this->success,['current_balance'=> $user->available_amount,'fee'=>$fees,'charges' => $pay,'converted_fees' => $convertedFees, 'setting' => $data,'loan_applied' => $loan_apply,'user_card' => $user_card]);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th->getMessage().' at line '.$th->getLine() .' at file '.$th->getFile(),$this->badRequest);
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function serachByzip(Request $request){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);

        $valid = [
            'zip_tag'         => 'required',
        ];

        $isInValid = $this->isValidPayload($request, $valid);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }

        try {
            $user = User::where('zip_tag',$request->zip_tag)->first();

            $data = new UserResource($user);
            //code...
            if(!$data)
            return ResponseBuilder::error('No data found', $this->notFound);

            return ResponseBuilder::successMessage('Search result', $this->success,$data);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function saveCardInfo(Request $request){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);

        $rules = [
            'card_number'       =>      'required',
            'card_id'           =>      'required',
            'card_name'         =>      'required',
            'card_currency'     =>      'required',
            'last_4'            =>      'required',
            'cvv'               =>      'required',
            'expiry_year'       =>      'required',
            'expiry_month'      =>      'required',
            'brand'             =>      'required',
            'card_holder_id'    =>      'required',
        ];

        $isInValid = $this->isValidPayload($request, $rules);
        if($isInValid)
        return ResponseBuilder::error($isInValid, $this->badRequest);

        try {
            //code...
            $user = Auth::user();
            UserCardInfo::create([
                'card_number'       =>      $request->card_number,
                'card_id'           =>      $request->card_id,
                'card_name'         =>      $request->card_name,
                'card_currency'     =>      $request->card_currency,
                'last_4'            =>      $request->last_4,
                'cvv'               =>      $request->cvv,
                'expiry_year'       =>      $request->expiry_year,
                'expiry_month'      =>      $request->expiry_month,
                'brand'             =>      $request->brand,
                'card_holder_id'    =>      $request->card_holder_id,
                'user_id'           =>      $user->id,
            ]);
            return ResponseBuilder::successMessage('Card info saved successfully', $this->success);
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th -> getMessage(), $this -> badRequest);
        }
    }

    public function cardInfo(){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        try {
            //code...
            $user = Auth::user();
            $data = UserCardInfo::where('user_id',$user->id)->orderBy('created_at','DESC')->first();
            // return $data = UserCardInfo::where('user_id',$user->id)->whereHas('card_holder_detail', function($q) {$q->where('status', 'verified');})->orderBy('created_at','DESC')->first();
            // return $data = UserCardInfo::where('user_id',$user->id)->whereHas(function($q){ $q->where('status', 'verified');})->orderBy('created_at','DESC')->first();
            if(!$data)
            return ResponseBuilder::error('No data found', $this->notFound);
        
            $cardHolder_details = CardHolderDetails::where('user_id', $user->id)->where('cardholder_id', $data->card_holder_id)->where('status', 'verified')->first();
            if(!$cardHolder_details) {
                return ResponseBuilder::error('Please contact customer support', $this->badRequest);
            }
            // return $data->whereHas('card_holder_detail', function($q) {$q->where('status', 'verified');});

            unset($data->created_at);
            unset($data->updated_at);
            unset($data->user_id);
            unset($data->resposnse);
            return ResponseBuilder::successMessage('Card info', $this->success,$data);

        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error($th   ->getMessage(), $this->badRequest);
        }
    }
    public function Freshwork(Request $request){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        try {
            $validSet = [
                'freshwork_id'    => 'required',
            ];

            $isInValid = $this->isValidPayload($request, $validSet);
            if ($isInValid) {
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            //code...
            $user = Auth::user();
            $user->freshwork_id   = $request->freshwork_id;
            $user->save();
            return ResponseBuilder::successMessage('Freshwork Save successfully', $this->success);

        } catch (\Throwable $th) {

            return ResponseBuilder::error($th   ->getMessage(), $this->badRequest);
        }
    }
    public function userImage(Request $request){
        if(!Auth::guard('api')->check())
        return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        try {
            $validSet = [
                'user_image'    => 'required|mimes:jpg,png,jpeg',
                // ,png,jpeg|max:2048|dimensions:min_width=100,min_height=100,max_width=500,max_height=500
            ];

            $isInValid = $this->isValidPayload($request, $validSet);
            if ($isInValid) {
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            //code...
            $imagePath = config('app.profile_image');
            $user = Auth::user();
            $user->user_image = $request->hasfile('user_image') ? Helper::storeImage($request->file('user_image'), $imagePath, $request->user_image) : (isset($request->user_image) ? $request->user_image : '');
            // $user->user_image   = $request->user_image;
            $user->save();
            $selfie_image = url('uploads/profile-images/'.$user->user_image);
            return ResponseBuilder::successMessage('Image Save successfully', $this->success,$selfie_image);

        } catch (\Throwable $th) {

            return ResponseBuilder::error($th   ->getMessage(), $this->badRequest);
        }
    }
    public function getUserImage(Request $request) {
        if (!Auth::guard('api')->check()) {
            return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        }
    
        try {
            $user = Auth::user();
    
            // Get the path to the user's image file
            $imagePath = config('app.profile_image') . '/' . $user->user_image;
    
            // Check if the image file exists
            if (file_exists($imagePath)) {
                // Read the image file into a binary string
                $imageData = file_get_contents($imagePath);
    
                // Encode the binary image data to base64
                $base64Image = base64_encode($imageData);
                $base64ImageWithPrefix = 'data:image/jpg;base64,' . $base64Image;
                return ResponseBuilder::success('User Data', $this->success, ['user_image' => $base64ImageWithPrefix]);
                // return ResponseBuilder::success('User Data',, $this->success);
            } else {
                return ResponseBuilder::error('Image not found', $this->notFound);
            }
        } catch (\Throwable $th) {
            return ResponseBuilder::error($th->getMessage(), $this->badRequest);
        }
    }
   
    public function bankDetails(Request $request){
        if (!Auth::guard('api')->check()) {
            return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        }
        try {
            $user = Auth::user();
            $bank = VirtualAccounts::where('user_id',$user->id)->first();
            $this->response = new BankDetailsResource($bank);
            return ResponseBuilder::successMessage('Bank details', $this->success,$this->response);
        
        } catch (\Throwable $th) {
            return ResponseBuilder::error($th->getMessage(), $this->badRequest);
        }  
    }

    public function createBeneficiary(Request $request){
        if(!Auth::guard('api')->check())
            return ResponseBuilder::error('Unauthorized', $this->unauthorized);
        $valid = [
            // 'unique_id'         => 'required',
            // 'bank_name'         => 'required',
            'destination_address'     => 'required',
            'first_name'        => 'required',
            'account_holder_name'     => 'required',
            'business_id'       => 'required',
        ];

        $isInValid = $this->isValidPayload($request, $valid);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }        
        try{
            $user = Auth::user();
            $beneficiary = Beneficiary::create([
                'unique_id' => $request->unique_id,
                'first_name' => $request->first_name,
                'destination_address' => $request->destination_address,
                'account_holder_name' => $request->account_holder_name,
                'business_id' => $request->business_id
            ]);

            return ResponseBuilder::successMessage('Beneficiary Created!', $this->success, $beneficiary);
        } catch (\Throwable $th) {
            return ResponseBuilder::error($th->getMessage(), $this->badRequest);
        }
    }

    public function appVersion(Request $request)
    {
        $valid = [
            'version_code' => 'required'
        ];

        $isInvalid = $this->isValidPayload($request, $valid);
        if ($isInvalid) {
            return ResponseBuilder::error($isInvalid, $this->badRequest);
        }

        try {
            $setting = Setting::where('key', 'app_version')->update(['value' => $request->version_code]);
            return ResponseBuilder::successMessage('Version Updated', $this->success);
        } catch (\Throwable $e) {
            return ResponseBuilder::error($e->getMessage() . ' at line ' . $e->getLine() . ' at file ' . $e->getFile(), $this->badRequest);
        }
    }

    public function getVersion(Request $request)
    {
        $valid = [
            'version_code' => 'required',
            'type' => 'required|in:ios,android'
        ];

        $isInvalid = $this->isValidPayload($request, $valid);
        if ($isInvalid) {
            return ResponseBuilder::error($isInvalid, $this->badRequest);
        }

        try {
            if($request->type == 'ios'){
                $setting = Setting::where('key', 'app_version_ios')->first();
                $link = 'https://apps.apple.com/in/app/recs/id6473147636';
            }else{
                $setting = Setting::where('key', 'app_version')->first();
                $link = 'https://apps.apple.com/in/app/recs/id6473147636';
            }
            
            if (isset($setting) && $setting->value == $request->version_code) {
                return ResponseBuilder::successMessage('Success', $this->success,['link' => $link, 'is_updated' => true]);
            }else{
                return ResponseBuilder::successMessage('Success', $this->success,['link' => $link, 'is_updated' => false]);
            }
        } catch (\Throwable $e) {
            // return ResponseBuilder::error($e->getMessage() . ' at line ' . $e->getLine() . ' at file ' . $e->getFile(), $this->badRequest);
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function fxRate()
    {
        $fxrate = Helper::currencyRate();
        return $result = json_decode($fxrate,true);
        // return ResponseBuilder::successMessage('Success', $this->success,['fx-rate' => $result]);
    }


}


