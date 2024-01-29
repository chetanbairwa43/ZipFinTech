<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CouponInventory;

class CouponInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = CouponInventory::query()->join('users', 'coupon_inventories.user_id', '=', 'users.id')->select('coupon_inventories.*', 'users.name');

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where(function ($query_new) use ($data) {
                $query_new->where('name', 'like', '%'.$data['keyword'].'%')
                ->orwhere('coupon_code', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->status){
            $data['status'] = $request->status;

            if($request->status == 'active'){
                $query->where('status', '=', 1);
            }
            else {
                $query->where('status', '=', 0);
            }
        }

        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.coupon-inventory.index',$data);
    }
}
