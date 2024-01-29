<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\CartCollection;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\Admin\OrderCollection;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSignUp;
use App\Helper\ResponseBuilder;
use App\Helper\Helper;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Setting;
use App\Models\CouponInventory;
use App\Models\Coupon;
use App\Models\WalletTransaction;
use Auth;
use App\Models\OrderDetail;
use App\Models\CartDetail;
use App\Models\User;
use App\Models\OrderAcceptance;
use App\Models\EmailTemplate;
use DB;

class OrdersController extends Controller
{
    public function addOrder(Request $request){
        DB::beginTransaction();
        try {
            $validSet = [
                'payment_type' => 'required |in:cod,online',
            ]; 
            
            $isInValid = $this->isValidPayload($request, $validSet);
            
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user();
            Helper::couponValid($user->id);
            $userCart=Cart::userTempCartData($user->id);
            $userCartData=Cart::getUserCart($user->id);
            
            if(empty($userCart)){
                return ResponseBuilder::error(trans('global.emtpy_cart'), $this->badRequest);
            }
            
            $cartPaymentSummary = $this->cartPaymentSummary($user->id);
            
            if(!empty($userCart->coupon_code)){
                $couponData =  Coupon::getCouponsByCode($userCart->coupon_code);
                $CouponInventory = CouponInventory::create([
                    'user_id'          => $user->id, 
                    'coupon_code'      => $userCart->coupon_code,
                    'discount_type'    => $couponData->discount_type,
                    'discounted_price' => $cartPaymentSummary['couponDiscount'],
                ]);
            }
       
            $vendor_id = $this->userCartVendorID($user->id);
            $vendor_data = User::getUserById($vendor_id);
            if($request->payment_type=='cod'){
                $newOrder = Order::create([
                    'user_id'             => $user->id,
                    'address_id'          => $userCart->address_id,
                    'coupon_inventory_id' => $CouponInventory->id ?? null,
                    'item_total'          => $cartPaymentSummary['subTotal'],
                    'surcharge'           => $cartPaymentSummary['surCharge'],
                    'tax'                 => $cartPaymentSummary['tax_and_fee'],
                    'delivery_charges'    => $cartPaymentSummary['deliveryCharge'],
                    'packing_fee'         => $cartPaymentSummary['packingFee'],
                    'tip_amount'          => $userCart->tip_amount ?? 0,
                    'grand_total'         => $cartPaymentSummary['total'],
                    'vendor_id'           => $vendor_id,
                    'driver_id'           => $vendor_data->self_delivery ? $vendor_id : NULL,
                    'order_type'          => 'C',
                    'commission_driver'   => $cartPaymentSummary['driver_commission'] ?? 0 ,
                    'commission_admin'    => $cartPaymentSummary['adminCommission'] ?? 0 ,
                    'status'              => 'OP',
                    'tax_id_1'            => json_encode($cartPaymentSummary['tax_1']),
                    'tax_id_2'            =>json_encode($cartPaymentSummary['tax_2']),
                ]);
                
                if($newOrder){
                    foreach($userCartData as $item){
                        OrderDetail::create([
                        'order_id'    => $newOrder->id,
                        'product_id'  => $item->getProductData->product->id,
                        'price'       => $item->getVariantData->price,
                        'variant_id'  => $item->getVariantData->id,
                        'item_qty'    => $item->getVariantData->variant_qty.' '.$item->getVariantData->variant_qty_type,
                        'qty'         => $item->qty,
                        ]);
                    }
                }
                $this->sendNotificationAndMailOnOrderPlaced($user->id, $newOrder->id);
            }

            if($request->payment_type=='online'){
                $newOrder = Order::create([
                    'user_id'             => $user->id,
                    'address_id'          => $userCart->address_id,
                    'coupon_inventory_id' => $CouponInventory->id ?? null,
                    'item_total'          => $cartPaymentSummary['subTotal'],
                    'surcharge'           => $cartPaymentSummary['surCharge'],
                    'tax'                 => $cartPaymentSummary['tax_1']['amount'] + $cartPaymentSummary['tax_2']['amount'],
                    'delivery_charges'    => $cartPaymentSummary['deliveryCharge'],
                    'packing_fee'         => $cartPaymentSummary['packingFee'],
                    'tip_amount'          => $userCart->tip_amount ?? 0,
                    'grand_total'         => $cartPaymentSummary['total'],
                    'vendor_id'           => $vendor_id,
                    'driver_id'           => $vendor_data->self_delivery ? $vendor_id : NULL,
                    'order_type'          => 'O',
                    'commission_driver'   => $cartPaymentSummary['driver_commission'] ?? 0 ,
                    'commission_admin'    => $cartPaymentSummary['adminCommission'] ?? 0 ,
                    'status'              => 'P',
                ]);
                
                if($newOrder){
                    foreach($userCartData as $item){
                        $orderDetails[] = [
                            'order_id'    => $newOrder->id,
                            'product_id'  => $item->getProductData->product->id,
                            'price'       => $item->getVariantData->price,
                            'variant_id'  => $item->getVariantData->id,
                            'item_qty'    => $item->getVariantData->variant_qty.' '.$item->getVariantData->variant_qty_type,
                            'qty'         => $item->qty,
                        ];
                    }
                    $order_detail = OrderDetail::upsert($orderDetails,['order_id','product_id','price','variant_id','item_qty','qty']);
                }
            }

            $userCart->delete();
            $getCartDetails=CartDetail::where('user_id',$user->id)->delete();
            
            $orderDetails = Order::getOrderById($newOrder->id);
            $this->response = new OrderResource($orderDetails);
            DB::commit();
            return ResponseBuilder::success(trans('global.order_added'), $this->success,$this->response);

        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function successPayment(Request $request){
        try {
            $validSet = [
                'order_id' => 'required | integer',
            ]; 
            
            $isInValid = $this->isValidPayload($request, $validSet);
            
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }

            $user = Auth::guard('api')->user();
            
            $order = Order::getOrderById($request->order_id);
            $order->status = 'OP';
            $order->save();
            $this->response = new OrderResource($order);
            if(isset($request->wallet_deduction)) {
                if($request->wallet_deduction < $user->wallet_balance) {
                    $previous_balance = $user->wallet_balance;
                    $user->wallet_balance = $user->wallet_balance - $request->wallet_deduction;
                    // $user->save();
                    $this->createWalletTransaction($order->vendor_id, $user_type = 'C', $user->id, $previous_balance, $user->wallet_balance, $request->wallet_deduction, $request->order_id, 'D', 'Order Placed');
                }
                else {
                    $remaining_amount = $request->wallet_deduction - (($user->wallet_balance > 0) ? $user->wallet_balance : 0) ;
                    $previous_balance = $user->wallet_balance;
                    $user->wallet_balance = (($user->wallet_balance > 0) ? 0 : $user->wallet_balance);
                    // $user->save();
                    if($previous_balance > 0) {
                        $this->createWalletTransaction($order->vendor_id, $user_type = 'C', $user->id, $previous_balance, $user->wallet_balance, $request->wallet_deduction, $request->order_id, 'D', 'Order Placed');
                    }
                    $previous_earned_balance = $user->earned_balance;
                    $user->earned_balance -= $remaining_amount;
                    $this->createWalletTransaction($order->vendor_id, $user_type = 'C', $user->id, $previous_earned_balance, $user->earned_balance, $request->wallet_deduction, $request->order_id, 'D', 'Order Placed');
                }
                $user->save();
            }

            if(isset($request->online_deduction)) {
                $this->createWalletTransaction($order->vendor_id, $user_type = 'C', $user->id, null, null, $request->online_deduction, $request->order_id, 'D', 'Order Placed', $request->payment_id, $request->razorpay_signature);
            }

            $this->sendNotificationAndMailOnOrderPlaced($user->id, $request->order_id);
            return ResponseBuilder::success(trans('global.order_added'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    public function sendNotificationAndMailOnOrderPlaced($id, $order_id) {
        try {
            $userId = $this->userCartVendorID($id);
            $title = 'New Order Alert';
            $notification_type = 'new_order';
            $send_notification = Helper::pushNotification(trans('notifications.NEW_ORDER_VENDOR'), $userId, $title, '', $notification_type);

            $orderDetails = Order::getOrderById($order_id);

            $settingData = Setting::getAllSettingData();
            $img = url('/'.config('app.logo').'/'.$settingData['logo_1']);
            $mailData = EmailTemplate::getMailByMailCategory(strtolower('new order'));
            if(isset($mailData)) {
                $order_items = '';
                foreach ($orderDetails->orderItem as $items) {
                        $order_items .= '<tr style="border-collapse: collapse;border-bottom: 1px solid #eaedf1; "><td><h6 style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53; margin: 10px 0px;">' . $items->products->name  . ' </h6></td>
                        <td><h6 align="center" style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53; margin: 10px 0px; align: center;">' . $items->item_qty .' x '. $items->qty. ' </h6></td>
                        <td><h6 align="center" style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53; margin: 10px 0px; align: center;">₹ ' . $items->price . ' </h6></td>
                        <td><h6 align="right" style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53;  align: right; margin: 10px 0px;">₹ ' . $items->price * $items->qty . '</h6></td>
                        </tr>';
                }
                $orderType = ($orderDetails->order_type=='C') ? 'COD' : 'Online';
                $arr1 = array('{image}','{products_list}','{order_number}','{order_date}','{sub_total}','{surcharge}','{tax}','{delivery_charge}','{packing_fee}','{tip_amount}','{payment_mode}','{grand_total}');

                $arr2 = array($img,$order_items,$orderDetails->id,$orderDetails->created_at->format('d F Y') ,$orderDetails->item_total,$orderDetails->surcharge,$orderDetails->tax,$orderDetails->delivery_charges,$orderDetails->packing_fee,$orderDetails->tip_amount ?? 0,$orderType,$orderDetails->grand_total);

                $msg = $mailData->email_content;
                $msg = str_replace($arr1, $arr2, $msg);
                
                $config = [
                    'from_email' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_ADDRESS'),
                'name' => isset($mailData->from_email) ? $mailData->from_email : env('MAIL_FROM_NAME'),
                'subject' => $mailData->email_subject, 
                'message' => $msg,
                ];

                if(isset($settingData['admin_mail']) && !empty($settingData['admin_mail'])){
                    Mail::to($settingData['admin_mail'])->send(new NewSignUp($config));
                }
                if(!empty($orderDetails->vendor->email)){
                    Mail::to($orderDetails->vendor->email)->send(new NewSignUp($config));
                }
            }
            return ResponseBuilder::successMessage(trans('global.MAIL_SUCCESS'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->badRequest);
        }
    }

    public function orderAccept(Request $request){
        try {
            if(empty($request->order_id)){
                return ResponseBuilder::success(trans('global.order_id'), $this->success,$this->response);
            }

            $user = Auth::guard('api')->user();
            $userOrder = Order::getOrderById($request->order_id);

            if(empty($userOrder)){
                return ResponseBuilder::error(trans('global.invalid_order_id'), $this->badRequest);
            }
            if($userOrder->vendor_id!=$user->id){
                return ResponseBuilder::error(trans('global.invalid_order_id'), $this->badRequest);
            }
            if($userOrder->status=='A'){
                return ResponseBuilder::error(trans('global.order_already_accepted'), $this->badRequest);
            }

           if($userOrder->vendor->self_delivery==false){
                $latitude = $userOrder->vendor->vendor->lat;
                $longitude = $userOrder->vendor->vendor->long;
                $distance = 5;
                $getDiver = User::getDriversWithStoreDistance($latitude,$longitude,$distance);
            
                if(count($getDiver)>0){
                    foreach($getDiver as $item){
                        $OrderAcceptance = OrderAcceptance::create([
                            'order_id'  =>   $userOrder->id,
                            'user_id'   =>   $item->id,
                            'is_pickup' =>    0,
                            'status' =>      'Pending',
                        ]);
                    }
                }

                $userOrder->status='A';
                $userOrder->save();

                $notificationTitle='Order accpected';
                $notificationBody = 'Hii '.($userOrder->user->name ?? 'User').' Your order #'.$userOrder->id.' accpected by shop';
                $notification_type='order_accpected';
                
                $this->createNotification($userOrder->user->id,$notificationTitle,$notificationBody,$notification_type);

                $data = trans('notifications.ORDER_ACCEPT_USER');
                $userId = $userOrder->user->id;
                $title = 'Order accpected';
                $orderID = $userOrder->id;
                Helper::pushNotification($data , $userId , $title , $orderID);
           }
           else{
                    $userOrder->status='A';
                    $userOrder->save();
                    
                    $data = trans('notifications.ORDER_ACCEPT_USER');
                    $userId = $userOrder->user->id;
                    $title = 'Order accpected';
                    $orderID = $userOrder->id;
                    Helper::pushNotification($data , $userId , $title , $orderID);
           }

            $this->response = new OrderResource($userOrder);
            return ResponseBuilder::success(trans('global.ORDER_ACCEPET'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }


    public function myOrders(Request $request){
        try {
            $user = Auth::guard('api')->user();
            $userOrders = Order::getOrderList($type='user', $user->id, $request->status, $request->filter, '', $request->start_date, $request->end_date);

            if(empty($userOrders)){
                return ResponseBuilder::error(trans('global.order_list_emtpy'), $this->badRequest);
            }
            
            $this->response = new OrderCollection($userOrders);
            return ResponseBuilder::success(trans('global.order_data'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
    
    public function orderDetails(Request $request){
        try {
            $user = Auth::guard('api')->user();

            // Validation start
            $validSet = [
                'order_id' => 'required | integer'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end


            $userOrder = Order::getOrderById($request->order_id);

            if(empty($userOrder)){
                return ResponseBuilder::successMessage(trans('global.invalid_order_id'), $this->success);
            }

            $this->response = new OrderResource($userOrder);
            return ResponseBuilder::success(trans('global.order_data'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }

    /**
     * Display Payment Option.
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentOption(Request $request){
        try {
            $user = Auth::guard('api')->user();

            $cod_option = Setting::getDataByKey('cod');

            if(empty($cod_option)){
                return ResponseBuilder::successMessage(trans('global.INVALID_PAYMENT_OPTION'), $this->success);
            }

            $this->response->earned_balance = (string)$user->earned_balance + (($user->wallet_balance > 0) ? $user->wallet_balance : 0);
            $this->response->cod = (boolean)$cod_option->value;
            return ResponseBuilder::success(trans('global.PAYMENT_OPTION'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
}
