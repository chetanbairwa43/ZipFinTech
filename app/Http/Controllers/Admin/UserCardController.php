<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserCard;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Helper\ResponseBuilder;

class UserCardController extends Controller
{
    
    public function index(Request $request)
    {
        $item = isset($request->items)?$request->items:10;
        $query = UserCard::query();
        if(isset($request->keyword)){
            $query->whereHas('user',function($q) use($request){
                $q->where('fname','LIKE',"%".$request->keyword."%")
                ->orWhere('lname','LIKE',"%".$request->keyword."%");
            });
        }

        $data = $query->latest()->paginate($item);

        return view('admin.usercard.index',compact('data'));
    }

}