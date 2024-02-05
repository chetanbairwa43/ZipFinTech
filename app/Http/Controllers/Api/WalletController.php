<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Helper\ResponseBuilder;
use App\Models\WalletTransaction;
use App\Models\Transaction;
use App\Models\RequestMoney;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Mail\NewSignUp;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Admin\WalletTransactionCollection;
use Carbon;
use Auth;
class WalletController extends Controller
{
    /**
    * My Wallet function
    *
    * @return \Illuminate\Http\Response
    */
    public function myWallet(Request $request){
        try {
            $user = Auth::guard('api')->user(); 
            $pagination = isset($request->pagination) ? $request->pagination : 10;
            $walletTransactions = WalletTransaction::getTransactionsByUser($user->id, $request->user_type, $pagination);
            $data['totalAmount'] = ($user->wallet_balance ?? 0) + ($user->earned_balance ?? 0);
            $data['withdrawalbleAmount'] = $user->earned_balance ?? 0;
            $data['unutilisedAmount'] = $user->wallet_balance ?? 0;
            $data['walletTransactions']  = new WalletTransactionCollection($walletTransactions);

            return ResponseBuilder::successWithPagination($walletTransactions, $data, trans('global.my_wallet'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->badRequest);
        }
    }

    /**
    * Add Money function
    *
    * @return \Illuminate\Http\Response
    */
    public function addMoney(Request $request){
        try {
            $user = Auth::guard('api')->user();

            // Validation start
            $validSet = [
                'payment_id' => 'required',
                'razorpay_signature' => 'required',
                'amount' => 'required',
                'response' => 'required | in:success,error'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            $data = WalletTransaction::create([
                'user_id' => $user->id,
                'payment_id' => $request->payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'previous_balance' => $user->wallet_balance,
                'current_balance' => $request->response == 'success' ? $user->wallet_balance + $request->amount : $user->wallet_balance,
                'amount' => $request->amount,
                'status' => $request->response == 'success' ? 'C' : 'F',
                'remark' => 'Add Money in Wallet',
            ]);

            $user->wallet_balance = ($request->response == 'success') ? $user->wallet_balance + $request->amount : $user->wallet_balance;
            $user->save();

            return ResponseBuilder::success(trans('global.ADD_MONEY_SUCCESS'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function requestMoney(Request $request) {

        if(!Auth::guard('api')->check())
        return ResponseBuilder::success("User not found", $this->unauthorized);
        $user = Auth::user();

        $validSet = [
            'pay_user_id'   => 'required|exists:users,id',
            'description'   => 'nullable|string',
            'amount'        => 'required',
        ]; 

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }

        try {
            //code...
            RequestMoney::create([
                'request_user_id'   => $user->id,
                'pay_user_id'       => $request->pay_user_id,
                'amount'            => $request->amount,
                'description'       => $request->description,
                'request_type'       => $request->request_type,
                
            ]);

            $users = User::where('id',$request->pay_user_id)->first();

            //mail code goes here
            $email = User::where('id',$request->pay_user_id)->pluck('email')->first();
            $mailData = EmailTemplate::getMailByMailCategory('Request Money');
            // $mailData = EmailTemplate::getMailByMailCategory('ZIP2ZIP request');
            if(isset($mailData)) {

                $arr1 = array('{amount}','{name}','{receiverName}','{zipTag}','{receEmail}','{recephone}');
                $arr2 = array($request->amount,$user->fname,$users->fname,$users->zip_tag,$users->email,$users->phone);

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
                    Mail::to($email)->send(new NewSignUp($config));
                } catch (\Throwable $th) {
                    //throw $th;
                }   
            }


            return ResponseBuilder::successMessage("Money request sent successfully", $this->success);

        } catch (\Throwable $th) {
            return $th->getMessage();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function sendEmail(Request $request) {

        if(!Auth::guard('api')->check())
        return ResponseBuilder::success("User not found", $this->unauthorized);
        $user = Auth::user();

        $validSet = [
            'email'            => 'required|email',
            'amount'           => 'required'
        ]; 

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }

        try {
            //code...
            // RequestMoney::create([
            //     'request_user_id'   => $user->id,
            //     'pay_user_id'       => $request->pay_user_id,
            //     'amount'            => $request->amount,
            //     'description'       => $request->description,
            //     'request_type'       => $request->request_type,
            // ]);
            $mytime = Carbon\Carbon::now();

            $user = User::where('id',Auth::user()->id)->first();
            //mail code goes here
            // $email = User::where('id',$request->pay_user_id)->pluck('email')->first();
            $users = User::where('email',$request->email)->first();
            $tids = Transaction::where('user_id',$user->id)->latest()->first();
            // $mailData = EmailTemplate::getMailByMailCategory('Request Money');
            $mailData = EmailTemplate::getMailByMailCategory('ZIP2ZIP request');
            if(isset($mailData)) {

                $arr1 = array('{amount}','{name}','{receiverName}','{zipTag}','{receEmail}','{recephone}','{receiver}');
                $arr2 = array($request->amount,$users->fname,$user->fname,$user->zip_tag,$user->email,$user->phone,$mytime);

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
                    Mail::to($users->email)->send(new NewSignUp($config));
                } catch (\Throwable $th) {
                    throw $th;
                }   
            }

            $mailDatas = EmailTemplate::getMailByMailCategory('ZIP to ZIP sent money');
            if(isset($mailDatas)) {

                $arr1 = array('{amount}','{name}','{receiverName}','{zipTag}','{receEmail}','{recephone}','{receiver}','{receive}','{received}','{tran}','{trandate}');
                $arr2 = array($request->amount,$user->fname,$user->fname,$user->zip_tag,$user->email,$user->phone,$users->email,$users->fname.' '.$users->lname, $users->phone,$tids->t_id,$tids->created_at);

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


            return ResponseBuilder::successMessage("Money request sent successfully", $this->success);

        } catch (\Throwable $th) {
            return $th->getMessage();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function sendEmailgloble(Request $request) {
        if(!Auth::guard('api')->check())
        return ResponseBuilder::success("User not found", $this->unauthorized);
        $user = Auth::user();

        $validSet = [
            'type'            => 'required',
          
        ]; 

        $isInValid = $this->isValidPayload($request, $validSet);
        if($isInValid){
            return ResponseBuilder::error($isInValid, $this->badRequest);
        }

        try {
            // $email = User::where('id',$request->pay_user_id)->pluck('email')->first();
            $data = User::where('id',$request->by_requested_id)->first();
            $mytime = Carbon\Carbon::now()->format('d-m-y');
            $mailData = EmailTemplate::getMailByMailCategory('Payment link request');

            if($request->type=='request Payment'){
                if(isset($mailData)) {

                    $arr1 = array('{requested_name}','{by_requested_name}','{amount}','{date}','{phone}','{email}','{generate_link}','{type}');
                    $arr2 = array($request->requested_id,$data->fname,$request->amount,$mytime,$data->phone,$data->email,$request->generate_link,$request->type);
    
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
                        throw $th;
                    }   
                }


                $mailDatas = EmailTemplate::getMailByMailCategory('Payment link send');

                if(isset($mailDatas)) {

                    $arr1 = array('{requested_name}','{by_requested_name}','{amount}','{date}','{phone}','{email}','{generate_link}','{type}');
                    $arr2 = array($request->requested_id,$data->fname,$request->amount,$mytime,$data->phone,$data->email,$request->generate_link,$request->type);

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
                        Mail::to($data->email)->send(new NewSignUp($config));
                    } catch (\Throwable $th) {
                        throw $th;
                    }   
                }
            }

            
           


            return ResponseBuilder::successMessage("Money request email sent successfully", $this->success);

        } catch (\Throwable $th) {
            return $th->getMessage();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

}