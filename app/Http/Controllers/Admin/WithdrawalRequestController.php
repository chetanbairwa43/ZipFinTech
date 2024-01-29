<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Helper\Helper;

class WithdrawalRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = WithdrawalRequest::query()->join('users', 'withdrawal_requests.user_id', '=', 'users.id')->select('withdrawal_requests.*', 'users.fname','users.lname');

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where('fname', 'like', '%'.$data['keyword'].'%');
        }

        if($request->status){
            $data['status'] = $request->status;

            if($request->status == 'approved'){
                $query->where('withdrawal_requests.status', '=', 'A');
            }
            else if($request->status == 'rejected'){
                $query->where('withdrawal_requests.status', '=', 'R');
            }
            else if($request->status == 'pending'){
                $query->where('withdrawal_requests.status', '=', 'P');
            }
        }

        if($request->date_search){
            $data['date_search'] = $request->date_search;

            $query->wheredate('withdrawal_requests.created_at', $data['date_search']);
        }

        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.withdrawal-request.index',$data);
    }

    /**
     * Withdrawal Action
     *
     * @return \Illuminate\Http\Response
     */
    public function withdrawalAction($id, Request $request)
    {
        $withdrawal_request = WithdrawalRequest::where('id', $id)->first();
        $user = User::where('id', $withdrawal_request->user_id)->first();
        $previous_balance = $user->earned_balance;

        if($request->action == 'approve') {
            $withdrawal_request->status = 'A';
            $user->wallet_balance -= $withdrawal_request->amount;
            $user->save();
            $withdrawal_request->save();

            $data = trans('notifications.WITHDRWAL_REQ_ACCPET_USER');
            $userId = $user->id;
            $title = 'Withdrawal request accpeted';
            Helper::pushNotification($data,$userId,$title);

            // WalletTransaction::create([
            //     'user_id' => $withdrawal_request->user_id,
            //     'previous_balance' => $previous_balance,
            //     'current_balance' => $user->earned_balance,
            //     'amount' => $withdrawal_request->amount,
            //     // 'user_type' => 'V',
            //     'remark' => 'Withdrawal From Wallet',
            //     'status' => 'W',
            // ]);
        }
        else {
            $withdrawal_request->status = 'R';
            $withdrawal_request->save();

            $data = trans('notifications.WITHDRWAL_REQ_REJECT_USER');
            $userId = $user->id;
            $title = 'Withdrawal request rejected';
            Helper::pushNotification($data,$userId,$title);
        }
        return redirect()->back();
    }
}
