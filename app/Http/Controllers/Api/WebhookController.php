<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WebhookDetails;
use App\Models\VirtualAccounts;
use App\Models\UserCard;
use App\Models\CardHolderDetails;
use App\Models\Setting;
use App\Helper\Helper;
use mervick\aesEverywhere\AES256;
use Auth;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user = Auth::user()->first();
        // $user = User::create([
        //     'user_id' => Auth::user()->id,
        //     'business' => $request->business,
        //     'virtualAccount' => $request->virtualAccount,
        //     'sessionId' => $request->sessionId,
        //     'senderAccountName' => $request->senderAccountName,
        //     'senderAccountNumber' => $request->senderAccountNumber,
        //     'senderBankName'    => $request->senderBankName,
        //     'sourceCurrency'    => $request->sourceCurrency,
        //     'sourceAmount'  => $request->sourceAmount,
        //     'description'   => $request->description,
        //     'amountReceived' => $request->amountReceived,
        //     'fee' => $request->fee,
        //     'customerName' => $request->customerName,
        //     'settlementDestination' => $request->settlementDestination,
        //     'status' => $request->status,
        //     'initiatedAt' => $request->initiatedAt,
        //     'reference' => $request->reference
        // ]);
        return view('admin.webhook.pay');
    }

    public function handle(Request $request)
    {
        $decrypt = AES256::decrypt('webhook secret', 'Bridgecard Secret Key');
        // Handle the incoming webhook data
        $data = $request->all();
        // $webData = WebhookDetails::create([
        //     'user_id' => 520,
        //     'webhook_type' => 'bridgeCard',
        //     'type' => $data['event'] ?? 'card_credit',
        //     'trans_response' => json_encode($data) ?? 'trdt'
        // ]);
      
        
        
        // $data['event']
        if($data['event'] == 'charge.successful'){
            $user = User::where('id',$data['data']['metadata']['userId'])->first();
            $virtual = VirtualAccounts::where('user_id',$user->id)->first();

            $setting = Setting::getAllSettingData();
            $cashin_fee = $setting['cashin_fee'];

            $webData = WebhookDetails::create([
                'user_id' => $data['data']['metadata']['userId'],
                'webhook_type' => 'fincra',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);
            
            
            $trans = Transaction::create([
                'user_id'   => $data['data']['metadata']['userId'],
                'receiver_id' => $data['data']['metadata']['userId'],
                'transaction_type' => 'cr',
                't_id' => $user->unique_id,
                'transaction_about' => 'Payment Link',
                'amount' => $data['data']['amountToSettle'] - $cashin_fee,
                'phone' => $data['data']['customer']['phoneNumber']
            ]);
            
            $user->wallet_balance += $trans->amount;
            $user->save();

            $dataArray  = json_decode($virtual['accountInformation'], true);
            $bankName = $dataArray['bankName'];
            $dataArray  = json_decode($virtual['KYCInformation'], true);
            $firstName = $dataArray['firstName'];

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Request Money'));
            if(isset($mailData)) {

                $arr1 = array('{requested_name}','{amount}','{date}','{senderName}','{senderAccNumber}','{senderBankName}');

                $arr2 = array($user->fname,$trans->amount,$trans->created_at,$firstName,$virtual->accountNumber,$bankName);

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

        
            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($user->id, 'Payment', $msg);
        }

        if($data['event'] == 'collection.successful'){
            $virtual = VirtualAccounts::where('business_id',$data['data']['virtualAccount'])->first();
            $users = User::where('id',$virtual->user_id)->first();

            $setting = Setting::getAllSettingData();
            $cashin_fee = $setting['cashin_fee'];
            // $user->available_amount - $cashin_fee;

            $webData = WebhookDetails::create([
                'user_id' => $users->id,
                'webhook_type' => 'fincra',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);
    
            $trans = Transaction::create([
                'user_id'   => $users->id,
                'receiver_id' => $users->id,
                'transaction_type' => 'cr',
                't_id' => $users->unique_id,
                'transaction_about' => 'Bank Transfer',
                'amount' => $data['data']['amountReceived'] - $cashin_fee,
                'phone' => $users->phone
            ]);

            $users->wallet_balance += $trans->amount;
            $users->save();


            $dataArray  = json_decode($virtual['accountInformation'], true);
            $bankName = $dataArray['bankName'];
            $dataArray  = json_decode($virtual['KYCInformation'], true);
            $firstName = $dataArray['firstName'];

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Request Money'));
                if(isset($mailData)) {
    
                    $arr1 = array('{requested_name}','{amount}','{date}','{senderName}','{senderAccNumber}','{senderBankName}');
    
                    $arr2 = array($users->fname,$trans->amount,$trans->created_at,$firstName,$virtual->accountNumber,$bankName);
    
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
                        Mail::to($users->email)->send(new NewSignUp($config));
                    } catch (\Throwable $th) {
                        throw $th;
                    } 
                }

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($users->id, 'Payment', $msg);
        }

        if($data['event'] == 'collection.failed'){
            $virtual = VirtualAccounts::where('business_id',$data['data']['virtualAccount'])->first();
            $users = User::where('id',$virtual->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $users->id,
                'webhook_type' => 'fincra',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($users->id, 'Payment', $msg);
        }

        if($data['event'] == 'payout.successful')
        {
            $userData = Transaction::where('customer_reference',$data['data']['customerReference'])->first();
            $user = User::where('id',$userData->user_id)->first();
            $users = VirtualAccounts::where('user_id',$user->id)->first(); 
         
            $webData = WebhookDetails::where('customer_reference', $data['data']['customerReference'])->update([
                'type' => $data['event'],
                'webhook_type' => 'fincra',
                'trans_response' => json_encode($data)
            ]);


            if(!empty($users))
            {
                $dataArray  = json_decode($users['accountInformation'], true);
                $bankName = $dataArray['bankName'];
                $dataArray  = json_decode($users['KYCInformation'], true);
                $firstName = $dataArray['firstName'];
            }
            $fname = $user->fname;
            $lname = $user->lname;
            $loginName =  $fname ." ". $lname;

            // if(isset($pay) && isset($pay['success']) && $pay['success'] == true){
            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Sent receipt'));
            if(isset($mailData)) {
                $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}');

                $arr2 = array($loginName ??'',$userData->amount ??'',$firstName, $userData->t_id ??'-',$trans->created_at->format('d F Y'),$userData->about ??'',$userData->dataplan,$data['data']['recipient']['accountNumber'],$data['data']['recipient']['name']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($user->id, 'Payment', $msg);
            // $trans = Transaction::create([
            //     'user_id'   => $data['data']['metadata']['userId'],
            //     'receiver_id' => $data['data']['metadata']['userId'],
            //     'transaction_type' => 'dr',
            //     't_id' => $user->unique_id,
            //     'transaction_about' => 'PayOut',
            //     'amount' => $data['data']['amountReceived'],
            //     'phone' => $data['data']['customer']['phoneNumber']
            // ]);
        }

        if($data['event'] == 'payout.failed'){
            // $user = WebhookDetails::where('customer_reference',$data['data']['customerReference'])->first();
            $userData = Transaction::where('customer_reference',$data['data']['customerReference'])->first();
            // $user = User::where('id',$userData->user_id)->first();
            $users = VirtualAccounts::where('user_id',$user->id)->first();

            $webData = WebhookDetails::where('customer_reference', $data['data']['customerReference'])->first();
            $webData->type = $data['event'];
            $webData->webhook_type = 'fincra';
            $webData->trans_response = json_encode($data);
            $webData->save();

            $user = User::where('id',$webData->user_id)->first();

            $setting = Setting::getAllSettingData();

            $transaction = Transaction::where('customer_reference', $webData->customer_reference)->orderBy('created_at', 'DESC')->first();
            $trans = Transaction::create([
                'user_id'   => $user->id,
                'receiver_id' => $user->id,
                'customer_reference' => $webData->customer_reference,
                'transaction_type' => 'cr',
                't_id' => $webData->users->unique_id,
                'phone' => $transaction->phone,
                'transaction_about' => $transaction->transaction_about.' Credit',
                'amount' => $data['data']['amountReceived'] + $setting['payout_fee']
            ]);

            $user->wallet_balance += $trans->amount;
            $user->save();
            
            if(!empty($users))
            {
                $dataArray  = json_decode($users['accountInformation'], true);
                $bankName = $dataArray['bankName'];
                $dataArray  = json_decode($users['KYCInformation'], true);
                $firstName = $dataArray['firstName'];
            }
            $fname = $user->fname;
            $lname = $user->lname;
            $loginName =  $fname ." ". $lname;

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Sent receipt failed'));
            if(isset($mailData)) {
                $arr1 = array('{name}','{amount}','{r_name}', '{t_id}','{transaction_date}','{transaction_about}','{dataplan}','{accountNumber}','{bankname}');

                $arr2 = array($loginName ??'',$userData->amount ??'',$firstName, $userData->t_id ??'-',$trans->created_at->format('d F Y'),$userData->about ??'',$userData->dataplan,$data['data']['recipient']['accountNumber'],$data['data']['recipient']['name']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            $arr1 = array('{type}','{user}');
            $arr2 = array($data['event'], $webData->users->fname);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['event'] == 'card_creation_event.successful'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $transaction = Transaction::where('user_id', $user->id)->orderBy('created_at', 'DESC')->first();
            $users = VirtualAccounts::where('user_id',$user->id)->first();
            $dataArray  = json_decode($users['accountInformation'], true);
            $bankName = $dataArray['bankName'];
            $dataArray  = json_decode($users['KYCInformation'], true);
            $firstName = $dataArray['firstName'];
            $loginName = $user->fname.' '.$user->lname;

            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Card Create'));
            if(isset($mailData)) {

                $arr1 = array('{name}','{cardNumber}');

                $arr2 = array($loginName ??'',$data['data']['card_id']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            // $fee = Helper::bridgeCardCalculation();

            // $cardFee = Transaction::create([
            //     'user_id' => $user->id,
            //     'receiver_id' => $user->id,
            //     'transaction_type' => 'dr',
            //     't_id' => $user->unique_id,
            //     'transaction_about' => 'Fees for fund card',
            //     'amount' => $fee,
            //     'phone' => $user->phone
            // ]);

            // $balance = $user->available_amount - $fee;

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARD_CREATION_WEBHOOK'));
    
            Helper::fireBasePushNotification($user->id, 'Card Creation', $msg);
        }

        if($data['event'] == 'card_creation_event.failed'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();

            $transaction = Transaction::where('user_id', $user->id)->orderBy('created_at', 'DESC')->first();
            $users = VirtualAccounts::where('user_id',$user->id)->first();
            $dataArray  = json_decode($users['accountInformation'], true);
            $bankName = $dataArray['bankName'];
            $dataArray  = json_decode($users['KYCInformation'], true);
            $firstName = $dataArray['firstName'];
            $loginName = $user->fname.' '.$user->lname;

            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Card Creation Failed'));
            if(isset($mailData)) {

                $arr1 = array('{name}','{cardNumber}','{reason}');

                $arr2 = array($loginName ??'',$data['data']['card_id'],$data['data']['reason']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($user->id, 'Payment', $msg);

            // $fee = Helper::bridgeCardCalculation();

            // $cardFee = Transaction::create([
            //     'user_id' => $user->id,
            //     'receiver_id' => $user->id,
            //     'transaction_type' => 'cr',
            //     't_id' => $user->unique_id,
            //     'transaction_about' => 'Fees for fund card',
            //     'amount' => $fee,
            //     'phone' => $user->phone
            // ]);

            // $balance = $user->available_amount - $fee;
            
        }

        if($data['event'] == 'card_debit_event.successful'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $amount = $data['data']['amount'] / 100;
            
            $trans = Transaction::create([
                'user_id'   => $user->id,
                'receiver_id' => $user->id,
                'transaction_type' => 'dr',
                't_id' => $user->unique_id,
                'transaction_about' => $data['data']['description'],
                'amount' => $amount,
                'currency' => $data['data']['currency'],
                'phone' => $user->phone
            ]);

            $user->wallet_balance -= $trans->amount;
            $user->save();
            $loginName = $user->fname.' '.$user->lname;

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Card Payment'));
            if(isset($mailData)) {

                $arr1 = array('{name}','{amount}','{transaction_date}','{cardNumber}');

                $arr2 = array($loginName ??'',$amount ??'',$trans->created_at->format('d F Y'),$data['data']['card_id']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));
            
            $arr1 = array('{type}','{cardID}');
            $arr2 = array($data['event'],$data['event']['card_id']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARD_PAYMENT'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }


        if($data['event'] == 'card_debit_event.declined'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $amount = $data['data']['amount'] / 100;
            
            $trans = Transaction::create([
                'user_id'   => $user->id,
                'receiver_id' => $user->id,
                'transaction_type' => 'cr',
                't_id' => $user->unique_id,
                'transaction_about' => $data['data']['description'],
                'amount' => $amount,
                'currency' => $data['data']['currency'] ?? 'USD',
                'phone' => $user->phone
            ]);

            $user->wallet_balance += $trans->amount;
            $user->save();

            $loginName = $user->fname.' '.$user->lname;

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Card Payment Failed'));
            if(isset($mailData)) {

                $arr1 = array('{name}','{amount}','{transaction_date}','{cardNumber}','{declinedReason}');

                $arr2 = array($loginName ??'',$amount ??'',$trans->created_at->format('d F Y'),$data['data']['card_id'],$data['data']['decline_reason']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));
            
            $arr1 = array('{type}','{cardID}');
            $arr2 = array($data['event'],$data['data']['card_id']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARD_PAYMENT'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['event'] == 'card_credit_event.successful'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $loginName = $user->fname.' '.$user->lname;

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Card Credit'));
            if(isset($mailData)) {

                $arr1 = array('{name}','{amount}','{transaction_date}','{cardNumber}');

                $arr2 = array($loginName ??'',$data['data']['amount'] ??'',$data['data']['transaction_date'],$data['data']['card_id']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));
            
            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);

            // $setting = Setting::getAllSettingData();
            // $bridgeCard_fee = $setting['bridgeCard_fee'];

            // $trans = Transaction::create([
            //     'user_id'   => $user->id,
            //     'receiver_id' => $user->id,
            //     'transaction_type' => 'cr',
            //     't_id' => $user->unique_id,
            //     'transaction_about' => 'Card Fees',
            //     'amount' => $data['data']['amount'] + $bridgeCard_fee,
            //     'phone' => $user->phone
            // ]);
        }

        if($data['event'] == 'card_credit_event.failed'){
        // if($data['event'] == 'card_credit_event.successful'){
            
            $fee = Helper::bridgeCardCalculation();
            $setting = Setting::getAllSettingData();
            $bridgeCard_fee = $setting['bridgeCard_fee'];
            // $cardFund_fee = $bridgeCard_fee * $fee;

            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            
            $trans = Transaction::create([
                'user_id'   => $user->id,
                'receiver_id' => $user->id,
                'transaction_type' => 'cr',
                't_id' => $user->unique_id,
                'transaction_about' => $data['data']['description'],
                'amount' => $data['data']['amount'],
                'phone' => $user->phone
            ]);
            
            $user->wallet_balance += $trans->amount;
            $user->save();
            $balance = $user->available_amount + $bridgeCard_fee;

            $loginName = $user->fname.' '.$user->lname;

            $mailData = EmailTemplate::getMailByMailCategory(strtolower('Card Credit Failed'));
            if(isset($mailData)) {

                $arr1 = array('{name}','{amount}','{transaction_date}','{cardNumber}');

                $arr2 = array($loginName ??'',$data['data']['amount'] ??'',$data['data']['transaction_date'],$data['data']['card_id']);

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

            $config = [
                'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $data,
            ];
                
            Mail::to('deveneoxys@gmail.com')->send(new NewSignUp($config));

            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CHARGE_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['event'] == 'cardholder_verification.successful'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            $cardHolder = CardHolderDetails::where('cardholder_id', $data['data']['cardholder_id'])->first();
            $cardHolder->status = 'verified';
            $cardHolder->save();

            $data = [
                'key' => 'createCard',
                'card_currency' => 'USD',
                'card_type' => 'virtual',
                'card_brand' => 'Visa',
            ];
            $request = json_encode($data);

            $response = Helper::bridgeCard('cards/create_card', 'POST', $request, $request->key);
                    
            $userCard = UserCard::create([
                'user_id' => $cardHolder->user_id,
                'card_id' => $response['data']['card_id'],
                'card_currency' => $response['data']['currency'],
                'card_type' => 'virtual',
                'card_brand' => 'Visa',
                'cardholder_id' => $cardHolder->cardholder_id,
                'resposnse' => json_encode($response)
            ]);
            
            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARDHOLDER_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }


        if($data['event'] == 'cardholder_verification.failed'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            // $cardHolder = CardHolderDetails::create([
            //     'user_id' => $user->id ?? '420',
            //     'cardholder_id' => $data['data']['cardholder_id'] ?? '88859631948a41ff97908c6201720301',
            //     'status' => 'un-verified',
            //     'response' => json_encode($data) ?? 'cardholder_verification.failed'
            // ]);
            
            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARDHOLDER_FAIL_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['event'] == 'card_unload_event.successful'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);
            
            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARDHOLDER_FAIL_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['event'] == 'card_unload_event.failed'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);
            
            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARDHOLDER_FAIL_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['event'] == 'card_reversal_event.successful'){
            $userCard = CardHolderDetails::where('cardholder_id',$data['data']['cardholder_id'])->first();
            $user = User::where('id',$userCard->user_id)->first();
            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'bridgeCard',
                'type' => $data['event'],
                'trans_response' => json_encode($data)
            ]);

            // $trans = Transaction::create([
            //     'user_id'   => $user->id,
            //     'receiver_id' => $user->id,
            //     'transaction_type' => 'cr',
            //     't_id' => $user->unique_id,
            //     'transaction_about' => 'Card Reversal',
            //     'description' => $data['data']['description'],
            //     'amount' => $data['data']['amount'],
            //     'phone' => $user->phone
            // ]);
            
            $arr1 = array('{type}');
            $arr2 = array($data['event']);
            $msg = str_replace($arr1, $arr2, trans('notifications.CARDHOLDER_FAIL_WEBHOOK'));
    
            Helper::fireBasePushNotification($webData->user_id, 'Payment', $msg);
        }

        if($data['data']['content']['transactions']['status'] == 'reversed'){
            $transUser = Transaction::where('customer_reference',$data['data']['requestId'])->first();
            $user = User::where('id',$transUser->user_id)->first();

            $setting = Setting::getAllSettingData();
            $service_fee = $setting['service_fee'];

            $trans = Transaction::create([
                'user_id'   => $user->id,
                'receiver_id' => $user->id,
                'transaction_type' => 'cr',
                't_id' => $user->unique_id,
                'transaction_about' => 'Card Reversal',
                'description' => $data['data']['description'],
                'amount' => $data['data']['amount'] + $service_fee,
                'phone' => $user->phone,
                'customer_reference' => $transUser->customer_reference
            ]);

            $balance = $user->available_amount + $service_fee;
            
            $user->wallet_balance += $trans->amount;
            $user->save();

            $webData = WebhookDetails::create([
                'user_id' => $user->id,
                'webhook_type' => 'services',
                'type' => $data['data']['response_description'],
                'trans_response' => json_encode($data)
            ]);

        }
        // $webData->save();
        
        // Example: Log the data
        // \Log::info('Webhook received:', $data);

        // Return a response (if required)
        return response()->json(['message' => 'Webhook received'], 200);
    }
    
}
