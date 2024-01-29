<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\OrderAddressCollection;
use App\Http\Resources\Admin\CartCollection;
use Auth;
class OrderAddressController extends Controller
{
    public function addressList(Request $request){

        try {
            $user = Auth::guard('api')->user();
            $getAddresse=UserAddress::getAddressesByUser($user->id);

            $getAddresse = new OrderAddressCollection($getAddresse);
            return ResponseBuilder::success(trans('global.my_order_address'), $this->success,$getAddresse);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
            }
        }
        
        public function removeAddress(Request $request){

            try {
                $validSet = [
                    'address_id' => 'required |integer',
                ]; 
    
                $isInValid = $this->isValidPayload($request, $validSet);
    
                if($isInValid){
                    return ResponseBuilder::error($isInValid, $this->badRequest);
                }
    
                $user = Auth::guard('api')->user();

                $getAddresse=UserAddress::getAddressesByUserAndID($user->id,$request->address_id);
         
                 if(empty($getAddresse)){
                    return ResponseBuilder::error(trans('global.invalid_address_id'), $this->badRequest);
                 }
                 $getAddresse->delete();

                 $getAddresseData=UserAddress::getAddressesByUser($user->id);
                $this->response = new OrderAddressCollection($getAddresseData);
                return ResponseBuilder::success(trans('global.my_order_address'), $this->success,$this->response);
    
            } catch (\Exception $e) {
                return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
                }
        }

        public function addAddress(Request $request){

            try {
                $validSet = [
                    'location'   =>'required',
                    'flat_no'    =>'required',
                    'street'     =>'required',
                    'landmark'   =>'required',
                    'address_type'=>'required'
                ]; 
    
                $isInValid = $this->isValidPayload($request, $validSet);
    
                if($isInValid){
                    return ResponseBuilder::error($isInValid, $this->badRequest);
                }
    
                $user = Auth::guard('api')->user();
                $getLatiLong = $this->lookForPoints($request->location);
          
                UserAddress::create([
                    'longitude'   =>  $getLatiLong['geometry']['location']['lng'] ?? '',
                    'latitude'    =>  $getLatiLong['geometry']['location']['lat'] ?? '',
                    'location'    =>  $request->location,
                    'flat_no'     =>  $request->flat_no,
                    'street'      =>  $request->street,
                    'landmark'    =>  $request->landmark,
                    'address_type'=>  $request->address_type,
                     'user_id'    =>  $user->id,
                ]);
               

                $getAddresseData=UserAddress::getAddressesByUser($user->id);
                $this->response = new OrderAddressCollection($getAddresseData);

                return ResponseBuilder::success(trans('global.address_added'), $this->success,$this->response);
    
            } catch (\Exception $e) {
                return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
           }
        }

        public function editAddress(Request $request){

            try {
                $validSet = [
                    'address_id' =>'required|Integer',
                    'location'   =>'required',
                    'flat_no'    =>'required',
                    'street'     =>'required',
                    'landmark'   =>'required',
                    'address_type'=>'required'
                ]; 
    
                $isInValid = $this->isValidPayload($request, $validSet);
    
                if($isInValid){
                    return ResponseBuilder::error($isInValid, $this->badRequest);
                }
    
                $user = Auth::guard('api')->user();

                $getAddresse=UserAddress::getAddressesByUserAndID($user->id,$request->address_id);
         
                 if(empty($getAddresse)){
                    return ResponseBuilder::error(trans('global.invalid_address_id'), $this->badRequest);
                 }

                $getLatiLong = $this->lookForPoints($request->location);

                 $getAddresse->longitude     = $getLatiLong['geometry']['location']['lng'] ?? '';
                 $getAddresse->latitude      = $getLatiLong['geometry']['location']['lat'] ?? '';
                 $getAddresse->location      = $request->location;
                 $getAddresse->flat_no       = $request->flat_no;
                 $getAddresse->street        = $request->street;
                 $getAddresse->landmark      = $request->landmark;
                 $getAddresse->address_type  = $request->address_type;
                 $getAddresse->save();
             
              

                $getAddresseData=UserAddress::getAddressesByUser($user->id);
                $this->response = new OrderAddressCollection($getAddresseData);

                return ResponseBuilder::success(trans('global.address_added'), $this->success,$this->response);
    
            } catch (\Exception $e) {
                return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
           }
        }


        public function chooseOrderAddress(Request $request){

            try {
                $validSet = [

                    'address_id' =>'required|Integer',

                ]; 
    
                $isInValid = $this->isValidPayload($request, $validSet);
    
                if($isInValid){
                    return ResponseBuilder::error($isInValid, $this->badRequest);
                }
    
                $user = Auth::guard('api')->user();

                $getAddresse=UserAddress::getAddressesByUserAndID($user->id,$request->address_id);
         
                 if(empty($getAddresse)){
                    return ResponseBuilder::error(trans('global.invalid_address_id'), $this->badRequest);
                 }
                 $UserCart=Cart::userTempCartData($user->id);
                 
                 if(empty($UserCart)){
                    return ResponseBuilder::error(trans('global.emtpy_cart'), $this->badRequest);
                 }

                 $UserCart->address_id = $request->address_id;
                 $UserCart->save();
              
                 $userCartData=Cart::getUserCart($user->id);
                 $data['cartItems'] = new CartCollection($userCartData);
                 
                 if(!empty($userCartData)){
                  $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
                 }
 
                 return ResponseBuilder::success(trans('global.cart_updated'), $this->success,$data);
    
            } catch (\Exception $e) {
                return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
           }
        }
}