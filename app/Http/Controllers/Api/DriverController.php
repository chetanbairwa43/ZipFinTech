<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\OrderAcceptance;
use App\Helper\Helper;
use App\Http\Resources\Admin\DeliveryAcceptanceCollection;
use App\Http\Resources\Admin\AssignedOrdersCollection;
use Craftsys\Msg91\Facade\Msg91;
use Auth;
use App\Models\Order;
use App\Http\Resources\Admin\DriverInformationResource;
use App\Models\DriverProfile;
use DB;

class DriverController extends Controller
{
    public function driverDeliveryRequestList(Request $request){
        try {
            $user = Auth::guard('api')->user();
            $listData = OrderAcceptance::getOrderRequestByDrver($user->id);
            $data['username'] = $user->name ?? '';
            $data['delivery_mode'] = (boolean)$user->is_driver_online ;
            $data['deliveredOrders'] = count(Order::getCompleteOrderByDriver($user->id) ?? 0);
            $data['pendingOrders'] = count(Order::getPendingOrderByDriver($user->id) ?? 0);
            $data['list'] =  new DeliveryAcceptanceCollection($listData);

            return ResponseBuilder::success(trans('global.delivery_request'), $this->success,$data);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
    public function driverInformation(){
        try {
            $user = Auth::guard('api')->user();
            $data = DriverProfile::where('user_id',$user->id)->first(); 
            $this->response = new DriverInformationResource($data);
            return ResponseBuilder::success(trans('global.DRIVER_INFORMATION'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }

    }
    public function assignedOrder(Request $request){
        try {
            $validSet = [
                'order_id' => 'required',
                'status'   => 'required|in:accept,decline',
            ]; 

            $user = Auth::guard('api')->user();
          
            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $userOrder = Order::getOrderById($request->order_id);

            if(empty($userOrder)){
                return ResponseBuilder::error(trans('global.invalid_order_id'), $this->badRequest);
            }
            $requestDetails = OrderAcceptance::getOrderRequestByDrverAndOrder($user->id,$request->order_id);

            if($request->status=='decline'){

                $requestDetails->status='Decline';
                $requestDetails->save();

                return ResponseBuilder::successMessage(trans('global.delivery_req_declined'), $this->success);
            }

            $data = OrderAcceptance::getOrderRequestByDrver($user->id);
            $deliveryReq = $data->pluck('order_id')->toArray();
          
            if(!in_array($requestDetails->order_id,$deliveryReq)){
                return ResponseBuilder::error(trans('global.delivery_already_assigned'), $this->badRequest);
            }

            /**Assigned driver id in order table */

            $userOrder->driver_id = $user->id;
            $userOrder->save();

            /**Order request accepet update */

            $requestDetails->status='Accept';
            $requestDetails->save();
            
            /**Notification to user */
            
            $data = trans('notifications.ORDER_ASSIGNED_TO_DRIVER_USER');
            $userId = $userOrder->user->id;
            $title = 'Order assigned to driver';
            $orderId = $userOrder->id;
            Helper::pushNotification($data,$userId,$title,$orderId);


            /**Notification to driver */
            $data1 = trans('notifications.ORDER_ASSIGNED_TO_DRIVER');
            $userId1 = $userOrder->driver->id;
            $title1 = 'Order delivery assigned';
            $orderId1 = $userOrder->id;
            Helper::pushNotification($data1,$userId1,$title1,$orderId1);

            /**request accpect notification to vendor */
                      
            $data2 = trans('notifications.ORDER_REQ_ACCPET_DRIVER');
            $userId2 = $userOrder->vendor_id;
            $title2 = 'Delivery request accpeted by driver';
            $orderId2 = $userOrder->id;
            Helper::pushNotification($data2,$userId2,$title2,$orderId2);
            return ResponseBuilder::successMessage(trans('global.delivery_req_accepted'), $this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }

    
    public function assignedOrderList(Request $request){
        try {
            $user = Auth::guard('api')->user();
          
           
            $driverOrder = Order::getOrderByDriver($user->id, $request->keyword);
            $this->response = new AssignedOrdersCollection($driverOrder);
          
            return ResponseBuilder::success(trans('global.assigned_orders_list'), $this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
    public function driverDeliveryModeUpdate(Request $request){
        try {
            $user = Auth::guard('api')->user();
            $user->is_driver_online == 1 ? $user->is_driver_online = 0 : $user->is_driver_online = 1;
            $user->save();

            return ResponseBuilder::success(trans('global.driver_status'), $this->success,$user);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
    public function resendDeliveryOtp(Request $request){
        try {
           
            $validSet = [
                'order_id' => 'required',
            ]; 

            $user = Auth::guard('api')->user();
          
            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $userOrder = Order::getOrderById($request->order_id);

            if(empty($userOrder)){
                return ResponseBuilder::error(trans('global.invalid_order_id'), $this->badRequest);
            }
            $user = Auth::guard('api')->user();

            $data_otp_resend = $this->sendOtp($userOrder->user->phone);
            $userOrder->delivery_otp = isset($data_otp_resend['otp']) ? $data_otp_resend['otp'] : NULL;
            $userOrder->otp_created_at = now();
            $userOrder->save();

            if(isset($data_otp_resend['responseCode']) && ($data_otp_resend['responseCode'] != 200)) {
                return ResponseBuilder::error(isset($data_otp_resend['message']) ? $data_otp_resend['message'] : trans('global.SOMETHING_WENT'), $this->success); 
            }
            return ResponseBuilder::successMessage(trans('global.delivery_otp'), $data_otp_resend['responseCode']); 

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
    public function verifyDelivery(Request $request){
        DB::beginTransaction();
        try {
            $validSet = [
                'order_id' => 'required',
                'otp'       => 'required | integer |digits:4',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            
            $user = Auth::guard('api')->user();
            
            $userOrder = Order::getOrderById($request->order_id);

            if(empty($userOrder)){
                return ResponseBuilder::error(trans('global.invalid_order_id'), $this->badRequest);
            }

            if((strtotime($userOrder->otp_created_at) + 900) < strtotime(now())) 
            {
                return ResponseBuilder::error(trans('global.OTP_EXPIRED'), $this->success);    
            }
            if($userOrder->delivery_otp!=$request->otp){
                return ResponseBuilder::error(trans('global.INVALID_OTP'), $this->success);
            }
            
            /**
             * Vendor and Driver Commission
             */
            $userOrder->status = 'D';
            $userOrder->delivery_otp = NULL;
            $userOrder->otp_created_at = NULL;
            $order_detail_save = $userOrder->save();
            
            if($userOrder->driver_id == $userOrder->vendor_id) {
                $vendor_previous_earned_balance = $userOrder->vendor->earned_balance;
                $userOrder->vendor->earned_balance += ($userOrder->grand_total - $userOrder->commission_admin - $userOrder->tax);
                $user_balance_save = $userOrder->vendor->save();
                
                if($order_detail_save && $user_balance_save) {
                    $this->createWalletTransaction($userOrder->vendor_id, $user_type = 'V', $userOrder->vendor_id, $vendor_previous_earned_balance, $userOrder->vendor->earned_balance, $userOrder->grand_total, $userOrder->id, 'E', 'Earned form Order id '.$userOrder->id);
                }
            }
            else {
                $previous_earned_balance = $userOrder->driver->earned_balance;
                $userOrder->driver->earned_balance += $userOrder->commission_driver;
                $driver_user_balance_save = $userOrder->driver->save();
                
                if($order_detail_save && $driver_user_balance_save) {
                    $this->createWalletTransaction($userOrder->vendor_id, $user_type = 'D', $userOrder->driver_id, $previous_earned_balance, $userOrder->driver->earned_balance, $userOrder->commission_driver, $userOrder->id, 'E', 'Earned form Order Delivery');
                }
                
                $vendor_previous_earned_balance = $userOrder->vendor->earned_balance;
                $earned_balance_from_order = $userOrder->grand_total - $userOrder->commission_driver - $userOrder->commission_admin - $userOrder->tax;
                $userOrder->vendor->earned_balance += $earned_balance_from_order;
                $user_balance_save = $userOrder->vendor->save();
                
                if($order_detail_save && $user_balance_save) {
                    $this->createWalletTransaction($userOrder->vendor_id, $user_type = 'V', $userOrder->vendor_id, $vendor_previous_earned_balance, $userOrder->vendor->earned_balance, $earned_balance_from_order, $userOrder->id, 'E', 'Earned form Order id '.$userOrder->id);
                }
            }

            /**
             * Admin Commission
             */
            if($order_detail_save && ($userOrder->commission_admin > 0)) {
                $this->createWalletTransaction($userOrder->vendor_id, $user_type = 'A', NULL, NULL, NULL, $userOrder->commission_admin, $userOrder->id, 'E', 'Earned form Order id '.$userOrder->id);
            }

            /**Notification to user */
            $data = trans('notifications.ORDER_DELIVERED_USER');
            $userId = $userOrder->user->id;
            $title = 'Order Delivered';
            $orderId = $userOrder->id;
            $notification_type = 'order_delivered_user';
            Helper::pushNotification($data,$userId,$title,$orderId,$notification_type);

            //Notification to vendor
            $data1 = trans('notifications.ORDER_DELIVERED_VENDOR');
            $userId1 = $userOrder->vendor_id;
            $title1 = 'Order Delivered';
            $notification_type1 = 'order_delivered';
            Helper::pushNotification($data1,$userId1,$title1,$orderId,$notification_type);
            
            DB::commit();
            return ResponseBuilder::successMessage(trans('global.DELIVERY_VERIFIED'), $this->success);

        } catch (\Exception $e) {
            DB::rollback();
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
    public function orderStatusUpdate(Request $request){
        try {
            $validSet = [
                'order_id' => 'required',
                'status'   => 'required|in:pickup,delivered',
            ]; 

            $user = Auth::guard('api')->user();
          
            $isInValid = $this->isValidPayload($request, $validSet);

            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            $userOrder = Order::getOrderById($request->order_id);

            if(empty($userOrder)){
                return ResponseBuilder::error(trans('global.invalid_order_id'), $this->badRequest);
            }
            $user = Auth::guard('api')->user();
          
            if($request->status=='pickup'){

                $userOrder->status='PC';
                $userOrder->save();


                /**Notification to user */
                $notificationTitle='Order pickup';
                $notificationBody = 'Hii '.($userOrder->user->name ?? 'User').' Your order #'.$userOrder->id.'  successfully pickup';
                
                $notification_type='order_pickup';

                $this->createNotification($userOrder->user->id,$notificationTitle,$notificationBody,$notification_type);

                return ResponseBuilder::successMessage(trans('global.order_status_changed'), $this->success);
            }

            if($request->status=='delivered'){
                
                $data_otp = $this->sendOtp($userOrder->user->phone);

                if(isset($data_otp['responseCode']) && ($data_otp['responseCode'] != 200)) {
                    return ResponseBuilder::error(trans('global.SOMETHING_WENT'), $this->success); 
                }
                $userOrder->delivery_otp = isset($data_otp['otp']) ? $data_otp['otp'] : NULL;
                $userOrder->otp_created_at = now();
                $userOrder->save();

                /**Notification to user */

                $notificationTitle='Order delivery otp';
                $notificationBody = 'Hii '.($userOrder->user->name ?? 'User').', We sent otp for your order #'.$userOrder->id;
                
                $notification_type='delivery_otp';

                $this->createNotification($userOrder->user->id,$notificationTitle,$notificationBody,$notification_type);
                
            }

            $driverOrder = Order::getOrderByDriver($user->id);
            $this->response = new AssignedOrdersCollection($driverOrder);
          
            return ResponseBuilder::successMessage(trans('global.delivery_otp'), $data_otp['responseCode']); 

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    
    }
}
