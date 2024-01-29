<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\BankAccountResource;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankLoan;

use Auth;

class BankController extends Controller
{
    /**
     * All Banks List.
     *
     * @return \Illuminate\Http\Response
     */
    public function banksList() {
        try {

            $data = Bank::select('id','name')->where('status',1)->get();

            if(!isset($data)) {
                return ResponseBuilder::error(trans('global.NO_BANK'),$this->success);
            }
            $this->response->banks = $data;
            return ResponseBuilder::success(trans('global.BANKS_LIST'),$this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display a Account Details of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function accountDetail() {
        try {
            $user = Auth::guard('api')->user();
            $data = BankAccount::getAccountDetailByUserId($user->id);
            if(!isset($data)) {
                return ResponseBuilder::error(trans('global.NO_ACCOUNT'),$this->success, []);
            }
            $this->response = new BankAccountResource($data);
            return ResponseBuilder::success(trans('global.ACCOUNT_DETAIL'),$this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Add/Edit User Account Details
     *
     * @return \Illuminate\Http\Response
     */
    public function addAccountDetail(Request $request) {
        try {
            // Validation start
            $validSet = [
                'bank' => 'required | integer',
                'account_name' => 'required',
                'account_no' => 'required',
                'ifsc_code' => 'required',
            ];

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            // Validation end
            $user = Auth::guard('api')->user();

            $data = BankAccount::getAccountDetailByUserId($user->id);

            if($data) {

                $data->bank_id = $request->bank;
                $data->user_id = $user->id;
                $data->account_name = $request->account_name;
                $data->account_no = $request->account_no;
                $data->ifsc_code = strtoupper($request->ifsc_code);
                $data->update();

                $this->response = new BankAccountResource($data);
                return ResponseBuilder::success(trans('global.ACCOUNT_DETAIL'),$this->success,$this->response);
            }
            $bankAccount = BankAccount::create(
                [
                    'bank_id' => $request->bank,
                    'user_id' => $user->id,
                    'account_name' => $request->account_name,
                    'account_no' => $request->account_no,
                    'ifsc_code' => strtoupper($request->ifsc_code),
                ]);

            if(!$bankAccount) {
                return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
            }

            $this->response = new BankAccountResource($bankAccount);
            return ResponseBuilder::success(trans('global.ACCOUNT_DETAIL'),$this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

     /**
     * Apply For Bank Loan.
     *
     * @return \Illuminate\Http\Response
     */
    public function applyForLoan(Request $request){
        // return "gdfg";
        // dd($request);
        try{
             // Validation start
            $validSet = [
                'loan_purpose' => 'required',
            ];
            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $user = Auth::guard('api')->user();
            // return $user;
            $bankLoan = BankLoan::create(
                [
                    'user_id' => $user->id,
                    'loan_purpose' => $request->loan_purpose,
                    'residential_status' => $request->residential_status,
                    'employed_status' => $request->employed_status,
                    'monthly_income' => $request->monthly_income,
                    'duration_of_loan' => $request->duration_of_loan,
                    'increament' => $request->increament,
                    'desired_amount' => $request->desired_amount,
                ]);
                return ResponseBuilder::successMessage(trans('global.LOAN_APPLIED'),$this->success);
// dd($bankLoan);
            if(!$bankLoan) {
                return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
            }
            } catch (\Exception $e) {
                return $e;
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
