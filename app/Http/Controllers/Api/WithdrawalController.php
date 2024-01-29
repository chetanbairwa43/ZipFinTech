<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\WithdrawalCollection;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\WithdrawalRequest;
use Auth;
use App\Mail\NewSignUp;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\BankAccount;
class WithdrawalController extends Controller
{
    /**
     * Withdrawal Request Function.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $data = WithdrawalRequest::getDataByUserId($user->id);

            $this->response->earnedBalance = (string)$user->earned_balance ?? '';
            $this->response->withdrawalList = new WithdrawalCollection($data);
            return ResponseBuilder::success(trans('global.WITHDRAWAL_LIST'), $this->success, $this->response); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Withdrawal Request Function.
     *
     * @return \Illuminate\Http\Response
     */
    public function withdrawalRequest(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            // Validation start
            $validSet = [
                'amount' => 'required | integer',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
          
            // Validation end

            $bank_detail = BankAccount::getAccountDetailByUserId($user->id);

            if(!$bank_detail) {
                return ResponseBuilder::error(trans('global.NO_ACCOUNT'), $this->success);
            }
            if($user->earned_balance < $request->amount) {
                return ResponseBuilder::error(trans('global.INSUFFICIENT_BALANCE'), $this->success);
            }

            $this->response = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'P',
            ]);
            
            $settingData = Setting::getAllSettingData();
        
            $img = url('/'.config('app.logo').'/'.$settingData['logo_1']);
            $mailData = EmailTemplate::getMailByMailCategory(strtolower('withdraw request'));
            if(isset($mailData)) {

                $arr1 = array('{image}', '{userMobile}','{amount}');
                $arr2 = array($img, $user->phone,$request->amount);

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

            return ResponseBuilder::success(trans('global.WITHDRAWAL_REQUEST_SUCCESS'), $this->success, $this->response);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
