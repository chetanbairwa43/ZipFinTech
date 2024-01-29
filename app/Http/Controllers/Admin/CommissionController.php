<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\Order;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminCommission(Request $request)
    {
        $query = WalletTransaction::query()->join('vendor_profiles', 'wallet_transactions.vendor_id', '=', 'vendor_profiles.user_id')->select('wallet_transactions.*', 'vendor_profiles.store_name')->where('user_type', 'A');

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where(function ($query_new) use ($data) {
                $query_new->where('store_name', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->date_search){
            $data['date_search'] = $request->date_search;

            $query->wheredate('wallet_transactions.created_at', $data['date_search']);
        }

        $data['items'] = $request->items ? $request->items : 10;

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.admin-commission.index',$data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function taxCommission(Request $request)
    {
        $query = Order::query()->join('vendor_profiles', 'orders.vendor_id', '=', 'vendor_profiles.user_id')->select('orders.*', 'vendor_profiles.store_name')->where('orders.status', 'D')->where('tax', '!=', 0);

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where(function ($query_new) use ($data) {
                $query_new->where('store_name', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->date_search){
            $data['date_search'] = $request->date_search;

            $query->wheredate('orders.created_at', $data['date_search']);
        }

        $data['items'] = $request->items ? $request->items : 10;

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.tax-commission.index',$data);
    }
}
