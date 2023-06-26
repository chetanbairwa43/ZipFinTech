<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\UserAddress;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Helper\ResponseBuilder;

class UserAddressController extends Controller
{
    public function address(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'postal_code' => 'required | integer | digits:6',
            'state' => 'required',
            'city' => 'required',
            'country' => 'required',
        ]);
        
        try{
            if($validate->fails()) {
                return ResponseBuilder::error($validate->errors()->first(), $this->badRequest);
            }
            $user = UserAddress::create([
                'street_name' => $request->street_name,
                'house_number' => $request->house_number,
                'additional' => $request->additional,
                'postal_code' => $request->postal_code,
                'state' => $request->state,
                'city' => $request->city,
                'country' => $request->country,
            ]);
            return ResponseBuilder::successMessage('User Address', $this->success, $user);
        }catch(exception $e){
            return ResponseBuilder::error($e->Message(), $this->badRequest);
        }
    }
}