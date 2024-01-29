<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\VendorCollection;
use App\Http\Resources\Admin\VendorAvailabilityCollection;
use App\Http\Resources\Admin\VendorOrderListCollection;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\Admin\VendorProductResource;
use App\Http\Resources\Admin\VendorProductCollection;
use App\Http\Resources\Admin\VendorProfileResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\VendorAvailability;
use App\Helper\ResponseBuilder;
use App\Models\Tax;
use App\Models\CouponInventory;
use App\Models\Setting;
use App\Models\Product;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use App\Helper\Helper;
use App\Models\VendorProductVariant;
use Carbon\Carbon;
use Auth;
use File;
use DB;

class VendorController extends Controller
{
    /**
     * Dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        try {
       
            $user = Auth::guard('api')->user();
            
            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }
            
            $order_list = Order::getOrderList($type='vendor', $user->id, null, null, null, null, null, null);
            
            if(empty($order_list)) {
                return ResponseBuilder::success(trans('global.order_list_emtpy'),$this->success,[]);
            }
            
            $dayOfTheWeek = Carbon::now()->dayOfWeek;
            $store_data = VendorAvailability::getStoreOpenAndCloseTimeByUserAndWeekDay($user->id, ($dayOfTheWeek == 0 ? 7 : $dayOfTheWeek));
            $vendor_last_month_order_received = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)->selectRaw('COUNT(*) as total_orders')->where('vendor_id',$user->id)->first();
            $vendor_last_month_earning = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)->selectRaw('SUM(`grand_total`) as total_earning')->where('status', 'D')->where('vendor_id',$user->id)->groupBy('status')->first();
            $last_month_order_ids = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)->where('vendor_id',$user->id)->pluck('id');
            $last_month_total_items = OrderDetail::whereIn('order_id', $last_month_order_ids)->selectRaw('COUNT(*) as sold_items')->where('status', 'A')->groupBy('status')->first();
            $vendor_last_to_last_month_earning = Order::whereMonth('created_at', Carbon::now()->subMonth()->subMonth()->month)->selectRaw('SUM(`grand_total`) as total_earning')->where('status', 'D')->where('vendor_id',$user->id)->groupBy('status')->first();

            $vendor_order_received = Order::whereMonth('created_at', Carbon::now()->month)->selectRaw('COUNT(*) as total_orders')->where('vendor_id',$user->id)->first();
            $vendor_earning = Order::whereMonth('created_at', Carbon::now()->month)->selectRaw('SUM(`grand_total`) as total_earning')->where('status', 'D')->where('vendor_id',$user->id)->groupBy('status')->first();
            $order_ids = Order::whereMonth('created_at', Carbon::now()->month)->where('vendor_id',$user->id)->pluck('id');
            $total_items = OrderDetail::whereIn('order_id', $order_ids)->selectRaw('COUNT(*) as sold_items')->where('status', 'A')->groupBy('status')->first();
            $gross_sale = ($vendor_last_month_earning->total_earning ?? 0) - ($vendor_earning->total_earning ?? 0);

            $current_earning = $vendor_earning->total_earning ?? 0;
            $last_earning = $vendor_last_month_earning->total_earning ?? 0;
            $last_to_last_earning = $vendor_last_to_last_month_earning->total_earning ?? 0;
            $current_gross_sale = $current_earning - $last_earning;
            $last_gross_sale = $last_earning - $last_to_last_earning;
            $current_sold_items = $total_items->sold_items ?? 0;
            $last_sold_items = $last_month_total_items->sold_items ?? 0;
            $current_orders = $vendor_order_received->total_orders ?? 0;
            $last_orders = $vendor_last_month_order_received->total_orders ?? 0;

            $gross_sales_percent = $last_gross_sale > 0 ? round(((($current_gross_sale-$last_gross_sale)/$last_gross_sale)*100), 2) : $current_gross_sale;
            $earning_percent = $last_earning > 0 ? round(((($current_earning-$last_earning)/$last_earning)*100), 2) : $current_earning;
            $sold_item_percent = $last_sold_items > 0 ? round(((($current_sold_items-$last_sold_items)/$last_sold_items)*100), 2) : $current_sold_items;
            $order_received_percent = $last_orders > 0 ? round(((($current_orders-$last_orders)/$last_orders)*100), 2) : $current_orders;

            $this->response->start_time = $store_data->start_time ?? '';
            $this->response->end_time = $store_data->end_time ?? '';
            $this->response->store_status = $store_data->status ?? 0;
            $this->response->username = (string)$user->name??'';
            $this->response->gross_sales = (string)$current_gross_sale;
            $this->response->gross_sales_percent = (string)$gross_sales_percent;
            $this->response->earning = (string)$current_earning;
            $this->response->earning_percent = (string)$earning_percent;
            $this->response->sold_items = (string)$current_sold_items;
            $this->response->sold_items_percent = (string)$sold_item_percent;
            $this->response->order_received = (string)$current_orders;
            $this->response->order_received_percent = (string)$order_received_percent;
            $this->response->store = (boolean)$user->is_vendor_online;
            $this->response->self_delivery = (boolean)$user->self_delivery;
            $this->response->order_list = new VendorOrderListCollection($order_list);
            
            return ResponseBuilder::success(trans('global.order_data'), $this->success, $this->response);
            
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display a listing of all stores.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
       
            $user = Auth::guard('api')->user();

            $latitude = $user->latitude;
            $longitude = $user->longitude;

            $distance = -1;
            $page = ($request->pagination) ? $request->pagination : 10;

            if(!empty($latitude) && !empty($longitude)){
                $data = VendorProfile::storeDistance($latitude, $longitude, $distance, $page);
            }else{
                $data=[];
                return ResponseBuilder::error(trans('global.no_stores'), $this->badRequest);
            }
            
            if(count($data) > 0) {
                $this->response = new VendorCollection($data);
                return ResponseBuilder::successWithPagination($data, $this->response, trans('global.all_stores'), $this->success);
            }
            return ResponseBuilder::successWithPagination($data, [], trans('global.no_stores'), $this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Stores Timing function
     *
     * @return \Illuminate\Http\Response
     */
    public function storeAvailability(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }

            $vendor_available_id = VendorAvailability::where('user_id', $user->id)->pluck('id')->toArray();

            $data = array();
            
            for ($i=1; $i <= 7; $i++) { 
                $data[] = [
                    'user_id' => $user->id,
                    'week_day' => $i, 
                    'start_time' => ($request->status[$i-1]>0) ? $request->start_time[$i-1] : '09:00', 
                    'end_time' => ($request->status[$i-1]>0) ? $request->end_time[$i-1] : '17:00',
                    'status' => ($request->status[$i-1]>0) ? $request->status[$i-1] : 0,
                ]
                + (!empty($vendor_available_id) ? ['id' => $vendor_available_id[$i-1]] : []);
            }
            
            VendorAvailability::upsert($data, ['id','user_id','week_day'],['start_time','end_time','status']);
            
            $store_timing = VendorAvailability::getStoreAvailabilityByUser($user->id);
            $this->response = new VendorAvailabilityCollection($store_timing);
            
            return ResponseBuilder::success(trans('global.VENDOR_AVAILABILTIY_SAVE'), $this->success, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
     /**
     * Stores Timing function
     *
     * @return \Illuminate\Http\Response
     */
    public function storeTiming()
    {
        try {
            $user = Auth::guard('api')->user();

            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }

            $storeTiming = VendorAvailability::getStoreAvailabilityByUser($user->id);
          
            $this->response = new VendorAvailabilityCollection($storeTiming);
            
            return ResponseBuilder::success(trans('global.VENDOR_AVAILABILTIY'), $this->success, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Vendor Order Listing function
     *
     * @return \Illuminate\Http\Response
     */
    public function orderList(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }

            $order_list = Order::getOrderList($type='vendor', $user->id, $request->status, $request->filter, $request->keyword, $request->start_date, $request->end_date);

            if(empty($order_list)) {
                return ResponseBuilder::success(trans('global.order_list_emtpy'),$this->success,[]);
            }
            $this->response->earned_balance = (string)$user->earned_balance;
            $this->response->order_list = new VendorOrderListCollection($order_list);
            
            return ResponseBuilder::success(trans('global.order_data'), $this->success, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Vendor reject variant function
     *
     * @return \Illuminate\Http\Response
     */
    public function rejectVariant(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            
            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }

            // Validation start
            $validSet = [
                'order_variant_id' => 'required | integer'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
          
            // Validation end

            $variant_product = OrderDetail::getVariantProductById($request->order_variant_id);
            $variant_product->status = 'R';
            $variant_product->save();

            $accepted_items = OrderDetail::getAllAcceptedProductByOrderId($variant_product->order_id);

            $taxAndFee = 0;
            $adminCommission = 0;
            $subTotal = 0;
            $discount_price = 0;
            $order_data = Order::getOrderById($variant_product->order_id);

            if(isset($accepted_items) && count($accepted_items)>0) {
                $netVariantTax = [];
                $getNetTax = [];
                $netTax = [];
                $tax1Type = '';
                $tax2Type = ''; 
                foreach ($accepted_items as $item) {
                    // Tax Calculation 
                    
                    if(!empty($item->products->tax_id)){
                        $getTax = Tax::getTaxById($item->products->tax_id);
                        $tax1Type = !empty($getTax) ? $getTax->title.' '.$getTax->tax_percent.'%' : '';
                        if(!array_key_exists($tax1Type, $netTax)) {
                            $netTax[$tax1Type] = !empty($getTax) ? $getTax->tax_percent : 0;
                        }
                    }
                    else {
                        if(!empty($item->products->category->tax_id)){
                            $getTax=Tax::getTaxById($item->products->category->tax_id);
                            $tax1Type = !empty($getTax) ? $getTax->title.' '.$getTax->tax_percent.'%' : '';
                            if(!array_key_exists($tax1Type, $netTax)) {
                                $netTax[$tax1Type] = !empty($getTax) ? $getTax->tax_percent : 0;
                            }
                        }
                    }

                    if(!empty($item->products->tax_id_2)) {
                        $getTax2 = Tax::getTaxById($item->products->tax_id_2);
                        $tax2Type = !empty($getTax2) ? $getTax2->title.' '.$getTax2->tax_percent.'%' : '';
                        if(!array_key_exists($tax2Type, $netTax)) {
                            $netTax[$tax2Type] = !empty($getTax2) ? $getTax2->tax_percent : 0;
                        }
                    }

                    $variantTax = ($item->price)*($item->qty);

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
                    // return $netTax2;
                    // $netVariantTax += ((($item->price)*($item->qty))*($netTax/100));
                    // $netVariantTax2 += ((($item->price)*($item->qty))*($netTax2/100));
                    
                    // $taxAndFee += ((($item->price)*($item->qty))*($netTax/100));
                    $subTotal += (($item->price)*($item->qty));
                    
                    if((isset($item->products)) && (isset($item->products->Category))) {
                        if($item->products->Category->admin_commission_type == 'percentage') {
                            $adminCommission += ((($item->price)*($item->qty))*($item->products->Category->admin_commission/100));
                        }
                        else {
                            $adminCommission += $item->products->Category->admin_commission;
                        }
                    }
                }
                if(count($getNetTax) > 0){
                    foreach ($getNetTax as $key => $value) {
                        $taxTypeArray[] = ['type' => $key, 'amount' => $value];
                    }
                }

                if(isset($order_data->coupon)) {
                    $coupon_detail = $order_data->coupon->couponDetail;

                    if($subTotal > $coupon_detail->min_order_value) {
                        if($coupon_detail->discount_type == 'P') {
                            $discount_price = $subTotal * ($coupon_detail->amount/100);
                            if($discount_price > $coupon_detail->max_discount) {
                                $discount_price = $coupon_detail->max_discount;
                            }
                        }
                        else {
                            $discount_price = $coupon_detail->amount;
                        }
                        $order_data->coupon->discounted_price = $discount_price;
                    }
                    else {
                        $coupon_inventory = CouponInventory::FindOrFail($order_data->coupon_inventory_id);
                        $coupon_inventory->delete();
                        $order_data->coupon_inventory_id = NULL;
                    }
                }

                $order_data->commission_admin = $adminCommission;
                $order_data->item_total = $subTotal;
                $tax_1 = isset($taxTypeArray[0]) ? $taxTypeArray[0] : null;
                $tax_2 = isset($taxTypeArray[1]) ? $taxTypeArray[1] : null;
                $order_data->tax_id_1 = isset($tax_1) ? json_encode($tax_1) : null;
                $order_data->tax_id_2 = isset($tax_2) ? json_encode($tax_2) : null;
                $tax_1_amount = isset($tax_1) ? $tax_1['amount'] : 0;
                $tax_2_amount = isset($tax_2) ? $tax_2['amount'] : 0;
                $order_data->tax = $tax_1_amount + $tax_2_amount;
                
                $order_data->grand_total = ($subTotal + $order_data->tax + $order_data->surcharge + $order_data->delivery_charges + $order_data->packing_fee + $order_data->tip_amount) - $discount_price;
            }
            else {
                $order_data->status = 'R';
            }
            $order_data->save();

            $data = trans('notifications.PRO_REJECT_IN_ORDER_USER');
            $userId = $order_data->user_id;
            $title = 'Order accpected';
            $orderID = $order_data->id;
            Helper::pushNotification($data , $userId , $title , $orderID);

            $this->response = new OrderResource($order_data);
            return ResponseBuilder::success(trans('global.PRODUCT_REJECT'), $this->success, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Vendor add Product function
     *
     * @return \Illuminate\Http\Response
     */
    public function addProduct(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }

            // Validation start
            $validSet = [
                'product_id' => 'required | integer',
                'category_id' => 'required | integer',
                'image' => 'mimes:jpeg,png,jpg'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
          
            // Validation end

            if(!isset($request->id)) {
                $vendor_data = VendorProduct::getVariantByProductAndCategoryId($user->id, $request->product_id, $request->category_id);
                if($vendor_data) {
                    $this->response = new VendorProductResource($vendor_data);
                    return ResponseBuilder::success(trans('global.PRODUCT_EXISTS'), $this->success, $this->response);
                }
            }
            else {
                $vendorData = VendorProduct::getProductbyID($request->id);
                if($vendorData) {
                    if($user->id != $vendorData->vendor_id) {
                        return ResponseBuilder::successMessage(trans('global.WRONG_PRODUCT'), $this->success);
                    }
                }
            }

            $vendor_product_variant_id = isset($request->product_variant_id) ? (explode(',',$request->product_variant_id)) : '';

            $imagePath = config('app.vendor_product_image');

            $oldImageName = basename($request->imageOld);
            $newImageName = basename($request->imageOld);
            if(!$request->hasfile('image')) {
                if(isset($request->imageOld)) {
                    if(File::exists(config('app.product_image').'/'.$oldImageName)) {
                        $newImageName = time().'-'.$oldImageName;
                        File::copy(config('app.product_image').'/'.$oldImageName, config('app.vendor_product_image').'/'.$newImageName);
                    }
                }
            }

            $data = VendorProduct::updateOrCreate(
                [
                    'id' => $request->id,
                ],
                [
                    'vendor_id' => $user->id,
                    'category_id' => $request->category_id,
                    'product_id' => $request->product_id,
                    'image' => $request->hasfile('image') ? Helper::storeImage($request->file('image'),$imagePath,$request->imageOld) : (isset($newImageName) ? $newImageName : ''),
                ]
            );

            $i = 0;
            $variants_data_new = [];
            $remove_variant_id = [];

            $productVariants = VendorProductVariant::where('vendor_product_id' ,$request->id)->pluck('id')->toArray();

            if(!empty($vendor_product_variant_id)) {
                $remove_variant_id = array_diff($productVariants,$vendor_product_variant_id);
            }

            foreach($request->variants as $variant) {

                if(!empty($vendor_product_variant_id) && (isset($vendor_product_variant_id[$i]))) {
                    $variants_data[] = $variant 
                    + (!empty($request->id) ? ['vendor_product_id' => $request->id] : ['vendor_product_id' => $data->id])
                    + (['id' => $vendor_product_variant_id[$i]]);
                }
                else {
                    $variants_data_new[] = $variant 
                    + (!empty($request->id) ? ['vendor_product_id' => $request->id] : ['vendor_product_id' => $data->id]);
                }
                $i++;
            }

            if(!empty($request->id)) {
                VendorProductVariant::upsert($variants_data, ['id'],['vendor_product_id','market_price','variant_qty','variant_qty_type','min_qty','max_qty','price']);
            }
            if(count($variants_data_new)>0) {
                VendorProductVariant::upsert($variants_data_new, ['id'],['vendor_product_id','market_price','variant_qty','variant_qty_type','min_qty','max_qty','price']);
            }

            if(count($remove_variant_id)>0) {
                VendorProductVariant::whereIn('id',$remove_variant_id)->delete();
            }

            $this->response = new VendorProductResource($data);
            return ResponseBuilder::success($request->id ? trans('global.PRODUCT_UPDATED') : trans('global.PRODUCT_ADD'), $this->success, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
    * Vendor Product Listing function
    *
    * @return \Illuminate\Http\Response
    */
    public function vendorProductList(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            if(!$user->as_vendor_verified) {
                return ResponseBuilder::error(trans('global.VENDOR_UNVERIFIED'), $this->badRequest);
            }

            $data = VendorProduct::getAllProductsByVendorId($user->id, $request->keyword);

            if(empty($data)) {
                return ResponseBuilder::success(trans('global.no_products'), $this->success, []);
            }
            $this->response = new VendorProductCollection($data);
            return ResponseBuilder::success(trans('global.all_products'), $this->success, $this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display a single Vendor Product.
     *
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        try {
            $data = VendorProduct::getActiveVendorProductDetailsByID($id);

            if(empty($data)) {
                return ResponseBuilder::success(trans('global.no_product'),$this->success,[]);
            }

            $this->response = new VendorProductResource($data);
            return ResponseBuilder::success(trans('global.product'),$this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Venndor Product Status Change.
     *
     * @return \Illuminate\Http\Response
     */
    public function vendorProductStatusUpdate($id){
        try {
            $user = Auth::guard('api')->user();

            $data= VendorProduct::where('id',$id)->first();
            if(!$data) {
                return ResponseBuilder::error(trans('global.no_product'), $this->badRequest);
            }

            $data->status = $data->status == 1 ? 0 : 1;
            $data->save();
            $this->response = new VendorProductResource($data);
            return ResponseBuilder::success(trans('global.PRODUCT_STATUS'), $this->success, $this->response);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display Vendor Information.
     *
     * @return \Illuminate\Http\Response
     */
    public function vendorInformation(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $data = VendorProfile::getDataByUserId($user->id);
            $this->response = new VendorProfileResource($data);
            return ResponseBuilder::success(trans('global.VENDOR_INFORMATION'), $this->success, $this->response); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Edit Vendor Information.
     *
     * @return \Illuminate\Http\Response
     */
    public function vendorInformationEdit(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $validSet = [
                'store_name' => 'required',
                'location' => 'required',
                'address' => 'nullable | string',
            ]; 
            $isInValid = $this->isValidPayload($request, $validSet);
            
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            
            $data = VendorProfile::getDataByUserId($user->id);
            $imagePath = config('app.vendor_document');
            $oldStoreImage = $data->store_image;
            $data->store_name = $request->store_name;
            $data->location = $request->location;
            $getLatiLong = $this->lookForPoints($request->location);
            $data->long =  $getLatiLong['geometry']['location']['lng'] ?? '';
            $data->lat =  $getLatiLong['geometry']['location']['lat'] ?? '';
            $data->store_image = $request->hasfile('store_image') ? Helper::storeImage($request->file('store_image'), $imagePath, $oldStoreImage) : (isset($oldStoreImage) ? $oldStoreImage : '');
            if(isset($request->address)) {
                $data->address = $request->address;
            }
            $data->save();
            $this->response = new VendorProfileResource($data);
            return ResponseBuilder::success(trans('global.VENDOR_INFORMATION_EDIT'), $this->success, $this->response); 
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Store Status Change.
     *
     * @return \Illuminate\Http\Response
     */
    public function StoreStatusUpdate(){
        try {
            $user = Auth::guard('api')->user();

            $user->is_vendor_online = $user->is_vendor_online == 1 ? 0 : 1;
            $user->save();
            
            return ResponseBuilder::successMessage(trans('global.STORE_STATUS'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Self Delivery Status Change.
     *
     * @return \Illuminate\Http\Response
     */
    public function SelfDeliveryStatusUpdate(){
        try {
            $user = Auth::guard('api')->user();

            $user->self_delivery = $user->self_delivery == 1 ? 0 : 1;
            $user->save();
            
            return ResponseBuilder::successMessage(trans('global.SELF_DELIVERY_STATUS'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    
}
