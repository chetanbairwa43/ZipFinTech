<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = WalletTransaction::query()->join('users', 'wallet_transactions.user_id', '=', 'users.id')->select('wallet_transactions.*', 'users.name', 'users.phone');

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where(function ($query_new) use ($data) {
                $query_new->where('name', 'like', '%'.$data['keyword'].'%')
                ->orwhere('phone', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->status){
            $data['status'] = $request->status;

            if($request->status == 'credit'){
                $query->where('wallet_transactions.status', '=', 'C');
            }
            else if($request->status == 'debit'){
                $query->where('wallet_transactions.status', '=', 'D');
            }
            else if($request->status == 'refund'){
                $query->where('wallet_transactions.status', '=', 'RF');
            }
            else if($request->status == 'withdrawal'){
                $query->where('wallet_transactions.status', '=', 'W');
            }
            else if($request->status == 'earn'){
                $query->where('wallet_transactions.status', '=', 'E');
            }
            else if($request->status == 'failed'){
                $query->where('wallet_transactions.status', '=', 'F');
            }
        }

        if($request->date_search){
            $data['date_search'] = $request->date_search;

            $query->wheredate('wallet_transactions.created_at', $data['date_search']);
        }

        $data['items'] = $request->items ? $request->items : 10;

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.wallet-transaction.index',$data);
    }
}
