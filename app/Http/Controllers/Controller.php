<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\Admin\CartCollection;
use Salman\GeoCode\Services\GeoCode;
use Craftsys\Msg91\Facade\Msg91;
use stdClass;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\VendorProduct;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\CartDetail;
use App\Models\Notification;
use App\Models\OrderNote;
use App\Models\WalletTransaction;
use Validator;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $serverError = 500;
    protected $badRequest = 400;
    protected $unauthorized = 401;
    protected $forbidden = 403;
    protected $notFound = 404;
    protected $success = 200;
    protected $noContent = 204;
    protected $partialContent = 206;

    protected $response;
    protected $responseNew;
    protected $msg;

    public function __construct() {
        $this->response = new stdClass();
        $this->responseNew = new stdClass();
        $this->msg = Helper::Messages();
    }

    static public function isValidPayload(Request $request, $validSet){
        $validator = Validator::make($request->all(), $validSet);

        if($validator->fails()) {
            return $validator->errors()->first();
        }
    }
    static public function sendOtpOnNumber($otp,$MobileNumber){

       $sendOtp = Msg91::otp($otp)->to($MobileNumber)->send();
       return $sendOtp;

    }
    static public function customPaginate($items, $perPage, $pagination = null, $options = [])
    {
        $pagination = $pagination ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($pagination, $perPage), $items->count(), $perPage, $pagination, $options);
    }

   
    static public function getCartRelatedProducts($userID)
    {
        $userCartData=Cart::getUserCart($userID);
        $cartVendor=[];
        foreach($userCartData as $item){
            $cartVendor[]=$item->getProductData->vendor_id;
        } 
        $dataArray=[];
        $cartVendor = array_unique($cartVendor);
        if(count($cartVendor)>0){
            foreach($cartVendor as $item){
                $cartVendorProducts=VendorProduct::getProductByVendorID($item);
                foreach($cartVendorProducts as $val){
                    $dataArray[]=$val;
                }
            }
        }else{
            $dataArray=VendorProduct::latestProducts();
        }
        return $dataArray;
    }

    static public function cartTotal($userID)
    {
        $userCartData=Cart::getUserCart($userID);
                 
        $cartTotal=0;
        foreach($userCartData as $item){
           
            $cartTotal = $cartTotal+ (($item->getVariantData->price)*$item->qty);
        }
        return $cartTotal;

    }
    static public function lookForPoints($address)
    {
        $getPoints = new GeoCode();
        return $getPoints->getLatAndLong($address); 
    }

    static public function cartPaymentSummary($userID)
    {   
        $couponValid = Helper::couponValid($userID);
        $userCartData=Cart::getUserCart($userID);
        $UserCart=Cart::userTempCartData($userID);
        $settingsData = Setting::getAllSettingData();

        $cartTotal=0;

        $products=[];
        $adminCommission = 0;
        foreach($userCartData as $item){
            if((isset($item->getVariantData)) && (isset($item->getVariantData->getProduct)) && (isset($item->getVariantData->getProduct->category))) {
                if($item->getVariantData->getProduct->category->admin_commission_type == 'percentage') {
                    $adminCommission += ((($item->getVariantData->price)*($item->qty))*($item->getVariantData->getProduct->category->admin_commission/100));
                }
                else {
                    $adminCommission += $item->getVariantData->getProduct->category->admin_commission;
                }
            }
            $vendor=$item->getProductData->vendor;
            $cartTotal = $cartTotal+ (($item->getVariantData->price)*$item->qty);
            $products[] = $item->vendor_product_id;

        }

        $data['adminCommission'] = $adminCommission;
        $data['subTotal']=$cartTotal ?? 0;
          

        if(!empty($UserCart->coupon_code)){
            
            $getCoupon=Coupon::getCouponsByCode($UserCart->coupon_code);

            if($getCoupon->discount_type=='P'){
                
                $userCartData=Cart::getUserCart($userID);
                $cartCost=0;
                foreach($userCartData as $item){
                
                    $cartCost = $cartTotal+ (($item->getVariantData->price)*$item->qty);
                }

                $couponDiscountPercent = $cartCost * $getCoupon->amount;
                $couponDiscount = $couponDiscountPercent/100;
                 
                if($couponDiscount > $getCoupon->max_discount){
                  $couponDiscount = $getCoupon->max_discount;
                }
              }else{
                  $couponDiscount=$getCoupon->amount;
              }
              $data['couponDiscount']=$couponDiscount;
              $data['couponCode']=$UserCart->coupon_code;

        }else{
            $data['couponDiscount']=0;
            $data['couponCode']='';
        }

        /**
         * Delivery Charge
         */
        $storeLatitude = $vendor->vendor->lat ?? '';
        $storeLongitude = $vendor->vendor->long ?? '';

        $userLatitude = $UserCart->deliveryAddress->latitude ?? '';
        $userLongitude = $UserCart->deliveryAddress->longitude ?? '';
        
        if(!empty($storeLatitude) && !empty($storeLongitude) && !empty($userLatitude) && !empty($userLongitude)){
            $dileveryDistance=Helper::distance($storeLatitude,$storeLongitude,$userLatitude,$userLongitude);
        }

        if(!isset($dileveryDistance)) {
            $deliveryCharge=0;
        }
        else {
            $deliveryCharge = $settingsData['delivery_charge_5km'] ?? 0;
            if($dileveryDistance > 5){
                $dileveryDistance = $dileveryDistance-5;
                $deliveryCharge=$deliveryCharge + ($dileveryDistance * $settingsData['delivery_charge_per_km']);
            }
            else {
                if($dileveryDistance <= 1) { $deliveryCharge = $settingsData['delivery_charge_1km'] ?? 0; }
                else if(($dileveryDistance > 1) && ($dileveryDistance <= 2)) { $deliveryCharge = $settingsData['delivery_charge_2km'] ?? 0; }
                else if(($dileveryDistance > 2) && ($dileveryDistance <= 3)) { $deliveryCharge = $settingsData['delivery_charge_3km'] ?? 0; }
                else if(($dileveryDistance > 3) && ($dileveryDistance <= 4)) { $deliveryCharge = $settingsData['delivery_charge_4km'] ?? 0; }
                else { $deliveryCharge = $deliveryCharge; }
            }
        }

        $data['driver_commission'] = number_format((float)$deliveryCharge, 0, '.', '');

        if($data['subTotal'] >= $settingsData['min_order_value']) {
            $deliveryCharge=0;
        }
        // else {
        //     $storeLatitude = $vendor->vendor->lat ?? '';
        //     $storeLongitude = $vendor->vendor->long ?? '';

        //     $userLatitude = $UserCart->deliveryAddress->latitude ?? '';
        //     $userLongitude = $UserCart->deliveryAddress->longitude ?? '';
            
        //     if(!empty($storeLatitude) && !empty($storeLongitude) && !empty($userLatitude) && !empty($userLongitude)){
        //         $dileveryDistance=Helper::distance($storeLatitude,$storeLongitude,$userLatitude,$userLongitude);
        //     }

        //     if(!isset($dileveryDistance)) {
        //         $deliveryCharge=0;
        //     }
        //     else {
        //         $deliveryCharge = $settingsData['delivery_charge_5km'] ?? 0;
        //         if($dileveryDistance > 5){
        //             $dileveryDistance = $dileveryDistance-5;
        //             $deliveryCharge=$deliveryCharge + ($dileveryDistance * $settingsData['delivery_charge_per_km']);
        //         }
        //         else {
        //             if($dileveryDistance <= 1) { $deliveryCharge = $settingsData['delivery_charge_1km'] ?? 0; }
        //             else if(($dileveryDistance > 1) && ($dileveryDistance <= 2)) { $deliveryCharge = $settingsData['delivery_charge_2km'] ?? 0; }
        //             else if(($dileveryDistance > 2) && ($dileveryDistance <= 3)) { $deliveryCharge = $settingsData['delivery_charge_3km'] ?? 0; }
        //             else if(($dileveryDistance > 3) && ($dileveryDistance <= 4)) { $deliveryCharge = $settingsData['delivery_charge_4km'] ?? 0; }
        //             else { $deliveryCharge = $deliveryCharge; }
        //         }
        //     }
            
        // }

        $VariantProduct=array_unique($products);
        
        $getNetTax = [];
        $tax1Type = '';
        $tax2Type = '';
        /**$VariantProduct-- array of cart varinats products */
        foreach($VariantProduct as $item){
            $getProductbyID=VendorProduct::getProductbyID($item);

            if(empty($getProductbyID)){
                Cart::removeCartItemById($userCartData->id);
            }

            $netTax = [];
            if(empty($getProductbyID->product->tax_id)){
              
                if(empty($getProductbyID->product->Category->tax_id)){
                  $netTax = 0;
                }else{
                    $getTax=Tax::getTaxById($getProductbyID->product->Category->tax_id);
                    $tax1Type = !empty($getTax) ? $getTax->title.' '.$getTax->tax_percent.'%' : '';
                    if(!array_key_exists($tax1Type, $netTax)) {
                        $netTax[$tax1Type] = !empty($getTax) ? $getTax->tax_percent : 0;
                    }
                }

            }else{
                $getTax = Tax::getTaxById($getProductbyID->product->tax_id);
                $tax1Type = !empty($getTax) ? $getTax->title.' '.$getTax->tax_percent.'%' : '';
                if(!array_key_exists($tax1Type, $netTax)) {
                    $netTax[$tax1Type] = !empty($getTax) ? $getTax->tax_percent : 0;
                }
            }
            
            if(!empty($getProductbyID->product->tax_id_2)) {
                $getTax2 = Tax::getTaxById($getProductbyID->product->tax_id_2);
                $tax2Type = !empty($getTax2) ? $getTax2->title.' '.$getTax2->tax_percent.'%' : '';
                if(!array_key_exists($tax2Type, $netTax)) {
                    $netTax[$tax2Type] = !empty($getTax2) ? $getTax2->tax_percent : 0;
                }
            }

            /**$netTax -- product net tax */
            $CartDetailVariant = CartDetail::where('user_id',$userID)->where('vendor_product_id',$getProductbyID->id)->get();
            $netVariantTax = [];
            
            foreach($CartDetailVariant as $item){
                $variantTax = $item->qty * $item->getVariantData->price;

                if(isset($netTax[$tax1Type])) {
                    if(array_key_exists($tax1Type, $netVariantTax)) {
                        $netVariantTax[$tax1Type] = $netVariantTax[$tax1Type] + (($variantTax * $netTax[$tax1Type])/100);
                    }
                    else {
                        $netVariantTax[$tax1Type] = (($variantTax * $netTax[$tax1Type])/100);
                    }
                }

                if(isset($netTax[$tax2Type])) {
                    if(array_key_exists($tax2Type, $netVariantTax)) {
                        $netVariantTax[$tax2Type] = $netVariantTax[$tax2Type] + (($variantTax * $netTax[$tax2Type])/100);
                    }
                    else {
                        $netVariantTax[$tax2Type] = (($variantTax * $netTax[$tax2Type])/100);
                    }
                }
            }

            if(isset($netTax[$tax1Type])) {
                if(array_key_exists($tax1Type, $getNetTax)) {
                    $getNetTax[$tax1Type] = $getNetTax[$tax1Type] + $netVariantTax[$tax1Type];
                }
                else {
                    $getNetTax[$tax1Type] = $netVariantTax[$tax1Type];
                }
            }

            if(isset($netTax[$tax2Type])) {
                if(array_key_exists($tax2Type, $getNetTax)) {
                    $getNetTax[$tax2Type] = $getNetTax[$tax2Type] + $netVariantTax[$tax2Type];
                }
                else {
                    $getNetTax[$tax2Type] = $netVariantTax[$tax2Type];
                }
            }
        }

        if(count($getNetTax) > 0){
            foreach ($getNetTax as $key => $value) {
                $taxTypeArray[] = ['type' => $key, 'amount' => $value];
            }
        }

        $deliveryCharge = number_format((float)$deliveryCharge, 0, '.', '');
        $tipAmount= !empty($UserCart->tip_amount) ? $UserCart->tip_amount : 0;
       
        $data['free_delivery_min_order_value'] = isset($settingsData['min_order_value']) ? $settingsData['min_order_value'] :0 ;
        $data['deliveryCharge'] = ($deliveryCharge==0) ? 0 : $deliveryCharge;
        $data['surCharge'] = isset($settingsData['surcharge']) ? $settingsData['surcharge'] :0 ;
        $data['tipAmount'] = $tipAmount;
        $data['packingFee'] = isset($settingsData['packing_charge']) ? $settingsData['packing_charge'] :0 ;
        $data['tax_1'] = isset($taxTypeArray[0]) ? $taxTypeArray[0] : null;
        $data['tax_2'] =  isset($taxTypeArray[1]) ? $taxTypeArray[1] : null;
        $netTotal = $data['subTotal'] - $data['couponDiscount'];
        $tax_1_amount = isset($data['tax_1']) ? $data['tax_1']['amount'] : 0;
        $tax_2_amount = isset($data['tax_2']) ? $data['tax_2']['amount'] : 0;
        $data['tax_and_fee'] =  $tax_1_amount + $tax_2_amount;

        $total_amount = $netTotal + $data['tax_and_fee'] + $tipAmount + $deliveryCharge + $data['surCharge'] + $data['packingFee'];
        $data['total'] = round($total_amount, 2);
        
        return $data;

    }

    static public function getValidCouponsByUser($userId)
    { 
        $todayDate = date('Y-m-d');
        $userCartData=Cart::getUserCart($userId);

        $vendorId = '';

        foreach($userCartData as $item){
          $vendorId=$item->getProductData->vendor_id;
        } 
        $getCoupon = Coupon::getCouponsByVendor($vendorId);

        return $getCoupon;
    }
    static public function userCartVendorID($userId)
    { 
        $todayDate = date('Y-m-d');
        $userCartData=Cart::getUserCart($userId);
    
        foreach($userCartData as $item){
          $vendorId=$item->getProductData->vendor_id;
        }
        return $vendorId;
    }
  
    static public function createNotification($userId,$title=null,$body=null,$notification_type=null)
    {
       if(!isset($userId) || empty($userId)){
            return false;
       }
       $notification = Notification::create([
        'user_id'           => $userId,
        'title'             => $title,
        'body'              => $body,
        'notification_type' => $notification_type,
       ]);
       
       return $notification;
    }

    static public function staffPermissonsArray()
    { 
       $dataArray=[
        'dashboard',
        'user_management',
        'users',
        'permissions',
        'roles',
        'pages',
        'categories',
        'products',
        'store_products',
        'slider',
        'faq',
        'email_templates',
        'coupons',
        'coupon_inventory',
        'banks',
        'orders',
        'withdrawal_requests',
        'tax',
        'setting_management',
        'site_setting',
        'app_setting',
        'notification',
        'wallet_transactions',
       ];
       return  $dataArray;
    }

    public static function createOrderLog($orderId, $order_status, $note=null)
    {
       if(!isset($orderId) || empty($orderId)){
            return false;
       }
       $log = OrderNote::updateOrCreate([
            'order_id' => $orderId,
            'status' => $order_status,
        ],[
            'order_id' => $orderId,
            'status' => $order_status,
            'note' => $note,
        ]);

        return $log;
    }

    /**
     * Send OTP
     * @param phone Number
     * @return \Illuminate\Http\Response
     */
    public function sendOtp($phone){
        try {
            $otp = (int)Helper::generateOtp();
            $response = Msg91::otp($otp)->to('91'.$phone)->template(env("Msg91_TEMPLATE_ID"))->send();
            
            return ["responseCode"=>$response->getStatusCode(), "message"=>trans('global.OTP_SENT'), "otp"=>$otp];
        } catch (\Exception $e) {
            return ["responseCode"=>$this->badRequest, "message"=>trans('global.SOMETHING_WENT')];
        }
    }

    public function createWalletTransaction($vendor_id, $user_type = null, $id, $previous_balance=null, $current_balance=null, $amount, $order_id, $status, $remark=null, $payment_id=null, $razorpay_signature=null) {
        return WalletTransaction::create([
            'user_id' => $id,
            'payment_id' => $payment_id,
            'razorpay_signature' => $razorpay_signature,
            'previous_balance' => $previous_balance,
            'current_balance' => $current_balance,
            'amount' => $amount,
            'order_id' => $order_id,
            'vendor_id' => $vendor_id,
            'user_type' => $user_type ?? 'C',
            'status' => $status,
            'remark' => $remark,
        ]);
    }
}
