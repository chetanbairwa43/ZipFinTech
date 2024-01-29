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
use App\Http\Resources\Admin\UserAddressResource;
// use App\Http\Resources\Admin\UserAddressResource;

class UserAddressController extends Controller
{
    // Add or Update User Address 
    public function address(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'postal_code' => 'required | digits:6',
            'street_name' => 'required',
            'house_number' => 'required',
            'state' => 'required',
            'city' => 'required',
            'country' => 'required',
        ]);
        if($validate->fails()) {
            return ResponseBuilder::error($validate->errors()->first(), $this->badRequest);
        }

        try {
            // 
            $user = UserAddress::updateOrCreate([
                'id' => $request->id
            ], [
                'street_name' => $request->street_name,
                'house_number' => $request->house_number,
                'additional' => $request->additional,
                'postal_code' => $request->postal_code,
                'state' => $request->state,
                'city' => $request->city,
                'country' => $request->country,
            ]);
            return ResponseBuilder::successMessage('User Address', $this->success, $user);

        } catch(\Throwable $th){
            return ResponseBuilder::error($th->getMessage(), $this->badRequest);
        }
    }

    public function addUserAddress(Request $request) {
        // 
        if(Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        } else {
            return ResponseBuilder::error(__("User not found"), $this->unauthorized);
        }
        $validate = Validator::make($request->all(), [
            // 'postal_code' => 'required | min:6',
            'street_name' => 'required',
            'house_number' => 'required',
            'state' => 'required',
            'city' => 'required',
            'country' => 'required',
            'phone'     => 'nullable|numeric|digits:11'
        ]);
        if($validate->fails()) {
            return ResponseBuilder::error($validate->errors()->first(), $this->badRequest);
        }

        if(isset($request->phone)){
            $user->update(['phone' => $request->phone]);
        }

        try {
            // 
            $userAddress = UserAddress::updateOrCreate(
                [ 'user_id' => $user->id,],
                [
                'street_name' => $request->street_name,
                'house_number' => $request->house_number,
                'additional' => $request->additional,
                'postal_code' => $request->postal_code,
                'state' => $request->state,
                'city' => $request->city,
                'country' => $request->country,
            ]);
            
            if($request->is_first_time == '1'){
                $user->default_address = $userAddress->id;
                $user->save();
            }

            return ResponseBuilder::successMessage('User Address Added Successfully', $this->success);

        } catch(\Throwable $th){
            return ResponseBuilder::error($th->getMessage(), $this->badRequest);
        }
    }

    public function addresslist(Request $request){
        if(!$request)
        {
            return ResponseBuilder::error(__("please add address"), $this->unauthorized);
        }
        try {
            $user = Auth::guard('api')->user();
            $getAddresse=UserAddress::where('user_id',$user->id)->first();
            // $getAddresse = [
            //     'id'                 => $getAddresse->id,
            //     'street_name'        => $getAddresse->street_name,
            //     'house_number'       => $getAddresse->house_number,
            //     'additional'         => $getAddresse->additional ?? "",
            //     'postal_code'        => $getAddresse->postal_code,
            //     'state'       => $getAddresse->state,
            //     'city'               => $getAddresse->city,
            //     'country'            => $getAddresse->country,
            // ];
            return ResponseBuilder::success(trans('global.my_address'), $this->success,$getAddresse);

        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(),$this->badRequest);
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
            }
        }
}