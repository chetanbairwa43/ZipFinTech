<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Helper\Helper;
use App\Http\Resources\Admin\CartCollection;
use App\Http\Resources\Admin\LatestProductCollection;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\Admin\OrderCollection;
use Auth;
use App\Models\VendorProduct;
use App\Models\Notification;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\CouponInventory;
use DB;

class CartController extends Controller
{
    public function addToCart(Request $request){
        DB::beginTransaction();
        try {
            /**Validation */
            $validSet = [
                'variant_id' => 'required |integer',
                'product_id' => 'required |integer',
                'qty'        => 'required | integer|min:1'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user();

            /**Check user cart-user can't add multiple vendor product */
            $userCartData = Cart::getUserCart($user->id);
            
            $vendorArray=[];
            foreach($userCartData as $item){
                $vendorArray[]=$item->getProductData->vendor_id;
            }
            
            $getProductVariant=VendorProduct::getProductVariantById($request->variant_id);
            $getProduct=VendorProduct::getProductbyID($request->product_id);
            $getProductVariantByIdAndProduct=VendorProduct::getProductVariantByIdAndProduct($request->variant_id,$request->product_id);
 
            if(empty($getProductVariantByIdAndProduct)){
                return ResponseBuilder::error(trans('global.invalid_variant_id'), $this->badRequest);
            }

            /**Get Product variant */
            if(empty($getProduct)){
                return ResponseBuilder::error(trans('global.invalid_product_id'), $this->badRequest);
            }

            if(empty($getProductVariant)){
                return ResponseBuilder::error(trans('global.invalid_variant_id'), $this->badRequest);
            }
            $userCartData=Cart::getUserCart($user->id);
            if(count($userCartData)>0){
                if(!in_array($getProductVariant->getProduct->vendor_id,$vendorArray)){
                    $userCartDataTemp=Cart::userTempCartData($user->id);
                    $userCartDataTemp->delete();
                    $getCartDetails=CartDetail::where('user_id',$user->id)->delete();
                }
            }
        

            /** Check user cart*/
                
            $userCart=Cart::where('user_id',$user->id)->first();

            if(empty($userCart)){

                $newCartItem=Cart::create([
                    'user_id' => $user->id,
                    ]);

                CartDetail::create([
                    'cart_id'                   => $newCartItem->id,
                    'user_id'                   => $user->id,
                    'vendor_product_variant_id' => $request->variant_id,
                    'qty'                       => $request->qty,
                    'vendor_product_id'        => $getProductVariant->vendor_product_id,
                    ]);


                $userCartData=Cart::getUserCart($user->id);
                $this->response = new CartCollection($userCartData);
                DB::commit();
                return ResponseBuilder::success(trans('global.product_add_cart'), $this->success,$this->response);

            }else{

                $getCardVariant=CartDetail::getVarint($user->id,$request->variant_id);

                if(!empty($getCardVariant)){
            
                    $getCardVariant->qty=$request->qty;
                    $getCardVariant->save();

                    $userCartData=Cart::getUserCart($user->id);
                    $this->response = new CartCollection($userCartData);
                    DB::commit();
                    return ResponseBuilder::success(trans('global.cart_updated'), $this->success,$this->response);

                }else{
                    CartDetail::create([
                        'cart_id'                   => $userCart->id,
                        'user_id'                   => $user->id,
                        'vendor_product_variant_id' => $request->variant_id,
                        'qty'                       => $request->qty,
                        'vendor_product_id'        => $getProductVariant->vendor_product_id,
                        ]);

                    $userCartData=Cart::getUserCart($user->id);
                    $this->response = new CartCollection($userCartData);
                    DB::commit();
                    return ResponseBuilder::success(trans('global.product_add_cart'), $this->success,$this->response);
                }

            }

        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

   /**My cart function
    * get route
      return cart data 
    */

    public function myCart(){
        try {
           
            $user = Auth::guard('api')->user();
            
            $UserCart=Cart::userTempCartData($user->id);
            $userCartData=Cart::getUserCart($user->id);
            $data['cartItems'] = new CartCollection($userCartData);
          
            if(!empty($UserCart)){
                $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
                $data['orderAddress']= $UserCart->deliveryAddress ?? NULL;
            }
         
            return ResponseBuilder::success(trans('global.my_cart'), $this->success,$data);

        } catch (\Exception $e) {
            $user = Auth::guard('api')->user();
            Helper::userCartClear($user->id);
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function cartRelatedProduct(){
        try {
            $user = Auth::guard('api')->user();

            $productData= $this->getCartRelatedProducts($user->id);
            $page=20;
            $data=$this->customPaginate($productData,$page);

            $this->response=new LatestProductCollection($data);
            return ResponseBuilder::successWithPagination($data, $this->response, trans('global.cart_related_product'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function orderTip(request $request){
        try {
            $validSet = [
                'tip_amount' => 'required |integer',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $user = Auth::guard('api')->user(); 
            $sendTip=Cart::userTempCartData($user->id);
            if(empty($sendTip)){
                return ResponseBuilder::error(trans('global.emtpy_cart'),$this->badRequest);
            }
            
            $sendTip->tip_amount=$request->tip_amount;
            $sendTip->save();
            
            $userCartData=Cart::getUserCart($user->id);
        
            $data['cartItems'] = new CartCollection($userCartData);
            if(!empty($sendTip)){
                $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
            }
            return ResponseBuilder::success(trans('global.tip_send'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
   
    public function couponApply(request $request){
        try {
            $validSet = [
                'coupon_code' => 'required',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid,$this->badRequest);
            }

            $user = Auth::guard('api')->user(); 

            $userCart=Cart::userTempCartData($user->id);
            if(empty($userCart)){

                return ResponseBuilder::error(trans('global.emtpy_cart'),$this->badRequest);
        
            }
            $getCartVendor = $this->userCartVendorID($user->id); 
            $getCoupon=Coupon::getCouponByVendor($request->coupon_code,$getCartVendor);
            
            /**If coupon code invalid */
            if(empty($getCoupon)){
                return ResponseBuilder::error(trans('global.invalid_coupon_code'), $this->success);
            }
            
            $todayDate = date('Y-m-d');

            if($getCoupon->valid_from > $todayDate || $getCoupon->valid_to < $todayDate){
                return ResponseBuilder::error(trans('global.expired_coupon_code'), $this->success);
            }

            $couponInventroy=CouponInventory::getCouponInventoryByUser($user->id,$getCoupon->coupon_code);
            
            /**if user already used coupon */
            if($getCoupon->max_reedem<=count($couponInventroy)){
                return ResponseBuilder::error(trans('global.coupon_already_used'), $this->success);
            }

            /**if Coupon usage limit has been reached */
            if($getCoupon->remainig_user==0){
                return ResponseBuilder::error(trans('global.coupon_usage'), $this->success);
            }

            $cartCost = $this->cartTotal($user->id);

            if($getCoupon->min_order_value > $cartCost){
                return ResponseBuilder::error(trans('global.copoun_min_value'),$this->success);
            }
                
            $userCart->coupon_code=$getCoupon->coupon_code;
            $userCart->save();

            $userCartData=Cart::getUserCart($user->id);
            $data['cartItems'] = new CartCollection($userCartData);

            if(!empty($userCart)){

                $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
            }
            return ResponseBuilder::success(trans('global.coupon_applied'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function updateCart(request $request){
        try {
            $validSet = [
                'cart_item_id' => 'required |integer',
                'qty' => 'required |integer|min:1',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user(); 

            $cartDetail=CartDetail::getCartByUserAndID($user->id,$request->cart_item_id);
            
            if(empty($cartDetail)){
                return ResponseBuilder::error(trans('global.invalid_cart_id'), $this->badRequest);
            }

            $cartDetail=CartDetail::findOrFail($request->cart_item_id);
            $cartDetail->qty=$request->qty;
            $cartDetail->save();
        
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
    /**
     * 
     */
    public function removeCoupon(request $request){
        try {
            $user = Auth::guard('api')->user();
           
            $userCart=Cart::userTempCartData($user->id);

            if(empty($userCart)){
                return ResponseBuilder::error(trans('global.emtpy_cart'), $this->badRequest);
            }

            $userCart->coupon_code=null;
            $userCart->save();
            
            $userCartData=Cart::getUserCart($user->id);
            $data['cartItems'] = new CartCollection($userCartData);
                
            if(!empty($userCart)){
             $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
            }

            return ResponseBuilder::success(trans('global.cart_updated'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    /**
     * 
     */
    public function removeCartItem(request $request){
        try {
            $validSet = [
                'cart_item_id' => 'required |integer',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user(); 
            $CartDetail=CartDetail::getCartByUserAndID($user->id,$request->cart_item_id);
            
            if(empty($CartDetail)){
                return ResponseBuilder::error(trans('global.invalid_cart_id'), $this->badRequest);
            }
               
            $CartDetail->delete();
            $userCartData=Cart::getUserCart($user->id);
            if(count($userCartData)==0){
                $UserCart=Cart::userTempCartData($user->id);
                $UserCart->delete();
            }
            $data['cartItems'] = new CartCollection($userCartData);
            
            if(count($userCartData)>0){
                $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
            }

            return ResponseBuilder::success(trans('global.cart_updated'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    /**
     * 
     */
    public function removeTip(){
        try {
            $user = Auth::guard('api')->user();
             
            $cartData=Cart::userTempCartData($user->id);

            if(empty($cartData)){
                return ResponseBuilder::error(trans('global.emtpy_cart'), $this->badRequest);
            }

            $cartData->tip_amount=null;
            $cartData->save();

            $userCartData=Cart::getUserCart($user->id);

            $data['cartItems'] = new CartCollection($userCartData);
            
            if(!empty($cartData)){
                $data['cartPaymentSummary']= $this->cartPaymentSummary($user->id);
            }

            return ResponseBuilder::success(trans('global.cart_updated'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
