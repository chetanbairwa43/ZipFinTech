<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\CurrencyTrait;
use App\Models\ProductAttribute;
use App\Models\CustomAttributes;
use App\Models\ProductVariants;
use App\Models\AttributeValue;
use App\Models\GuestUserData;
use Illuminate\Http\Request;
use App\Models\CouponUser;
use App\Models\ProductBid;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Wishlist;
use App\Models\UserBids;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Cart;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use Auth;
use DB;

use App\Models\VendorShipping;
use App\Models\VendorSetting;
use App\Models\Setting;

class CartApiController extends Controller
{
    use CurrencyTrait;
    public function index(Request $request)
    {
        $user = auth()->guard('api')->user();
      
        $currency_code = 'USD';

        if (!empty($user)) {
            $userid = $user->id;
            $cart = Cart::select('id', 'user_id', 'product_id', 'quantity', 'variation','start_date','sloat','sloat_end_time','is_shipping')->where('user_id', '=', $userid)->get();
            $s_sum = Cart::where('user_id', '=', $userid)->sum('price');

            $currency_code = isset($request->currency_code) ? $request->currency_code : (isset($user->currency) ? $user->currency : 'USD');
            $sub_sum = $this->currencyConvert($currency_code, $s_sum);
            $sum = round($sub_sum);
        } elseif (!empty($request->device_id)) {
            $cart = GuestUserData::select('device_id', 'product_id', 'quantity', 'variation','start_date','sloat','sloat_end_time')->where([['device_id', $request->device_id], ['type', 'cart']])->get();
            $sum = GuestUserData::where('device_id', '=', $request->device_id)->sum('price');
        } else {
            return response()->json(['status' => false, 'message' => "Cart is Empty", 'subtotal' => 0, 'shipping' => 0, 'total' => 0, 'discount' => 0, 'cart' => []], 200);
        }

        $shippingType = [];
        $shippingData = [];
        $shippingAmount = [];
        $productids = [];
        // $ship = [];
        
        // $shippingMethods = ['economic', 'standard', 'express', 'free'];

        // foreach ($shippingMethods as $method) {
        //     $key = $method . '_shipping_is_applied';
        //     $overKey = $method . '_shipping_over';

        //     if (Setting::getValueByKey($key) == 'on') {
        //         $ship[] = Setting::getDataByKey($overKey);
        //     }
        // }

        if (count($cart) > 0) {
            // $shippingType = [];
            // $shippingData = [];
            // $shippingAmount = [];
            // $productids = [];
            // $productdata = [];
            
            // $shippingMethods = ['economic', 'standard', 'express', 'free'];

            // foreach ($shippingMethods as $method) {
            //     $key = $method . '_shipping_is_applied';
            //     $overKey = $method . '_shipping_over';

            //     if (Setting::getValueByKey($key) == 'on') {
            //         $productdata[$overKey] = Setting::getValueByKey($overKey);
            //     }
            // }

        
            foreach ($cart as $c_key => $c_value) {
                $productids[] = $c_value->product_id;
                $product = Product::where('id', $c_value->product_id)->first();
                if ($product) {
                    $product['cart_id'] = $c_value->id;
                    $product['selected_sloat_start'] = $c_value->sloat ??'';
                    $product['selected_sloat_end'] = $c_value->sloat_end_time ?? '';
                    $product['selected_sloat_date'] = $c_value->start_date ?? '';
                    $data = [];
                    $gallery = json_decode($product->gallery_image);
                    if (!empty($gallery)) {
                        foreach ($gallery as $key1 => $value) {
                            $value1 = url('products/gallery/' . $value);
                            $data[] = $value1;
                        }
                        $product['gallery_image'] = $data;
                    }
                    $product['gallery_image'] = $data;

                    if (!empty($product->featured_image)) {
                        $product['featured_image'] = url('products/feature/' . $product->featured_image);
                    }

                    if ($product->parent_id > 0) {
                        $parent_product = Product::where('id', $product->parent_id)->first();
                        $product['slug'] = $parent_product->slug;
                        $product['featured_image'] = url('products/feature/' . $parent_product->featured_image);
                    }
                    //quantity
                    $product['qty'] = $c_value->quantity;
                    if($product->product_type == 'single' || $product->product_type == 'variants')
                    {
                        $product['is_shipping'] = true;
                    }else {
                        $product['is_shipping'] = false;
                    }
                    
                    //cart & wishlist
                    if (isset($userid)) {
                        // 
                        $Cart =    Cart::where('user_id', $userid)->where('product_id', $product->id)->first();
                        if (!empty($Cart)) {
                            $product['in_cart'] = true;
                        } else {
                            $product['in_cart'] = false;
                        }

                        $wishlistcheck = Wishlist::where('user_id', $userid)->where('product_id', $product->id)->first();
                        if (!empty($wishlistcheck)) {
                            $product['in_wishlist'] = true;
                        } else {
                            $product['in_wishlist'] = false;
                        }

                    } else {
                        $product['in_cart'] = false;
                        $product['in_wishlist'] = false;
                    }

                    // currency 
                    if (!empty($currency_code)) {
                        $currency = $this->currencyFetch($currency_code);
                        $product['currency_sign'] = $currency['sign'] ?? '';
                        $product['currency_code'] = $currency['code'] ?? '';
                    }
                    $productVariants = ProductVariants::select('id', 'variant_value', 'variant_price','variant_images')->where('parent_id', $c_value->product_id)->get();
                    // $productVariants = ProductVariants::where('parent_id', $product_id)->get();

                    $comb = [];
                    // if (!empty($productVariants->variant_images)) {
                    //     $productVariants['variant_images'] = url('products/gallery/' . $productVariants->variant_images);
                    // }
                    if (count($productVariants) > 0) {
                        foreach ($productVariants as $var_key => $var_val) {
                            $variatntdata = '';
                            $variat = '';
                            foreach (json_decode($var_val->variant_value) as $k => $v) {
                                $attrval = AttributeValue::where('attr_id', $v)->first();

                                $variatntdata .= ' '.$attrval->attr_value_name;

                                // $variat = $attrval->variant_images;
                                // dd($variat);

                            }
                            foreach (json_decode($var_val->variant_images) as $k => $var) {
                            
                            }
                            $comb[$var_key]['comb'] = $variatntdata;
                            // dd($comb[$var_key]['price']);
                        }
                        $product['variants_comb'] = $comb[$var_key]['comb'];
                    } else {
                        $product['variants_comb'] = [];
                    }

                    // currency conversion
                    $p_price = $this->currencyConvert($currency_code, $product->p_price);
                    $s_price = $this->currencyConvert($currency_code, $product->s_price);
                    $product->p_price = round($p_price);
                    $product->s_price = round($s_price);

                    if ($product->product_type == "card") {
                        $product['s_price'] = $c_value->card_amount;
                    }

                    //Attributes
                    $productAttributesdata = ProductAttribute::where('product_id', $product->id)->get();
                    if (count($productAttributesdata) > 0) {
                        $productAttributes = ProductAttribute::where('product_id', $product->id)->groupBy('attr_id')->pluck('attr_id');
                        $attr_data = [];
                        foreach ($productAttributes as $attr_key => $attr_val) {
                            $attr = Attribute::select('id', 'slug')->where('id', $attr_val)->first();
                            $attr_name['id'] = $attr->id;
                            $attr_name['name'] = $attr->slug;
                            $attr_data[] = $attr_name;
                        }
                        if (!empty($attr_data)) {
                            foreach ($attr_data as $data_key => $data_value) {
                                // 
                                $proattragain = ProductAttribute::where([['attr_id', $data_value['id']], ['product_id', $product->id]])->pluck('attr_value_id');
                                $attrval = [];
                                foreach ($proattragain as $attr_value_id) {
                                    $attr_value = AttributeValue::where('id', $attr_value_id)->first();
                                    $attrval[] = $attr_value->slug;
                                }
                                $attr_data[$data_key]['values'] = $attrval;
                            }
                        }
                        $product['attributes'] = $attr_data;
                    } else {
                        $product['attributes'] = [];
                    }

                    //  if product is variants
                    if ($product->product_type == 'variants') {
                        $productVariants = ProductVariants::select('id', 'variant_value', 'variant_price','variant_images')->where('id', $c_value->variation)->first();
                        $variantstypevalue = [];
                        $variantsdata = [];
                        if (!empty($productVariants)) {
                            foreach (json_decode($productVariants->variant_value) as $v_k => $v_val) {
                                // 
                                $AttributeValue = AttributeValue::where('id', $v_val)->first();
                                $variantstypevalue['variantion_type'] = $v_k;
                                $variantstypevalue['variantion_value'] = $AttributeValue->slug;
                                $variantsdata[] = $variantstypevalue;
                            }
                            $product['variants'] = $variantsdata;
                            $productVariant = json_decode ($productVariants->variant_images, true);
                            $product['featured_image'] = url('products/gallery/'.$productVariant[0]);
                        } else {
                            $product['variants'] = [];
                        }
                    } else {
                        $product['variants'] = [];
                    }
                }else{
                    return response()->json(['status' => true, 'subtotal' => 0,  'cart' => []], 200);
                }
               
                unset($c_value->user_id);
                unset($c_value->product_id);
                unset($c_value->id);
                unset($c_value->quantity);
                unset($c_value->device_id);

                $vendor = User::where('id', $product->vendor_id)->first();

                // $productdata[$vendor->store_name ?? 'Dirise Seller'][] = $product;
                $ship = [];
                $shippingMethods = ['economic', 'standard', 'express', 'free'];
                
                foreach ($shippingMethods as $method) {
                    $key = $method . '_shipping_is_applied';
                    $overKey = $method . '_shipping_over';
                    
                    if (Setting::getValueByKey($key) == 'on') {
                        $ship[] = Setting::getDataByKey($overKey)->toArray() + ['vendor_id' => $vendor->id];
                    }
                }

                $productdata[$vendor->store_name ?? 'Dirise Seller']['products'][] = $product;
                $productdata[$vendor->store_name ?? 'Dirise Seller']['shipping_types'] = $ship;
                // $productdata[$vendor->store_name ?? 'Dirise Seller'][] = [];

                if ($vendor) {
                    $vndorsetting = VendorSetting::where('vendor_id', $product->vendor_id)->pluck('value', 'name');
                    $adminsetting = Setting::pluck('value', 'name');
                    if($product->product_type=='virtual_product' || $product->product_type=='booking'){
                        $shippingAmount[$vendor->id] =  0 ;
                    }else{
                        if (isset($vndorsetting['free_shipping_is_applied']) && ($vndorsetting['free_shipping_is_applied'] == "on")) {
                            if (isset($vndorsetting['free_shipping_over']) && ($product->s_price >= $vndorsetting['free_shipping_over'])) {
                                // $shippingAmount[$vendor->id] = $this->currencyConvert($currency_code, $product->p_price);
                                $shippingAmount[$vendor->id] = 0;
                            } else {
                                $shippingAmount[$vendor->id] = $shippingAmount[$vendor->id] + $this->currencyConvert($currency_code, $vndorsetting['free_shipping_over']);
                            }
                        } 
                        
                        if (isset($vndorsetting['normal_shipping_is_applied']) && ($vndorsetting['normal_shipping_is_applied'] == "on")) {
                            $shippingAmount[$vendor->id] = (isset($shippingAmount[$vendor->id])?$shippingAmount[$vendor->id]:0) + $this->currencyConvert($currency_code, $vndorsetting['normal_price']);
                        } else {
                            $shippingAmount[$vendor->id] = (isset($shippingAmount[$vendor->id])?$shippingAmount[$vendor->id]:0) + $this->currencyConvert($currency_code, $adminsetting['normal_price']);
                            $userData= User::where('id',$vendor->id)->first(); 
                            // $OrderShipping[$userData->store_name ?? 'Dirise Seller'] = [
                            //     'store_id' => $vendor->id,
                            //     'store_name' => $userData->store_name ?? '',
                            //     'title' => 'Normal Shipping',
                            //     'ship_price' => $shippingAmount[$vendor->id],
                            // ];
                        }
                    }
                 
                }
            }
           
            
            //appaly coupon
            if ($request->coupon_code) {
                $currentDate =  Carbon\Carbon::now()->toDateString();
                $coupoon = Coupon::where('code', $request->coupon_code)->first();
                if (empty($coupoon)) {
                    return response()->json(['status' => false, 'message' => "invalid coupon code", 'subtotal' => $sum, 'total' => $sum, 'discount' => 0, 'cart' => $cart], 200);
                }

                $couponproduct = DB::table('coupon_product')->where('coupon_id', $coupoon->id)->whereIn('product_id', $productids)->first();
                if (!empty($coupoon)) {
                    if (!empty($coupoon->minimum_spend) && $coupoon->minimum_spend >= $sum) {
                        return response()->json(['status' => false, 'message' => "Coupon is not applicable", 'subtotal' => $sum, 'total' => $sum, 'discount' => 0, 'cart' => $cart], 200);
                    }

                    if (!empty($coupoon->maximum_spend) && $coupoon->maximum_spend <= $sum) {
                        return response()->json(['status' => false, 'message' => "Coupon is not applicable", 'subtotal' => $sum, 'total' => $sum, 'discount' => 0, 'cart' => $cart],  200);
                    }

                    //User coupon limit
                    $CouponUser = CouponUser::where('user_id', $userid)->first();
                    if (!empty($coupoon->limit_per_user)) {
                        if (isset($CouponUser->total_use_time) && ($coupoon->limit_per_user == $CouponUser->total_use_time)) {
                            return response()->json(['status' => false, 'message' => "Coupon is not applicable", 'subtotal' => $sum, 'total' => $sum, 'discount' => 0, 'cart' => $cart],  200);
                        }
                    }

                    //coupon expiry
                    $coupoonexpir = Coupon::where('code', $request->coupon_code)->whereDate('expiry_date', '<=', $currentDate)->first();
                    if (!empty($coupoonexpir)) {
                        return response()->json(['status' => false, 'message' => "Coupon expired"],  200);
                    }

                    //apply coupon
                    if (!empty($CouponUser)) {
                        $userlimit = $CouponUser->total_use_time;
                        $updateLimit = (int)$CouponUser->total_use_time + 1;
                        $CouponUser = CouponUser::where('id', $CouponUser->id)->update([
                            'coupon_id' => $coupoon->id,
                            'user_id' => $userid,
                            'total_use_time' => $updateLimit,
                        ]);
                    } else {
                        $CouponUser = CouponUser::create([
                            'coupon_id' => $coupoon->id,
                            'user_id' => $userid,
                            'total_use_time' => 1,
                        ]);
                    }

                    if ($coupoon->discount_type == "flat_rate") {
                        $couponAmount = $coupoon->coupon_amount;
                    } else {
                        $couponAmount = ($coupoon->coupon_amount * $sum) / 100;
                    }
                    $totalAmount = $sum - $couponAmount;
                    return response()->json(['status' => true, 'message' => "Success", 'subtotal' => $sum, 'total' => $totalAmount, 'discount' => $couponAmount, 'cart' => $cart], 200);
                }
            }
            $amount = round(array_sum($shippingAmount));
            return response()->json(['status' => true, 'message' => "Success", 'subtotal' => $sum, 'shipping' => $amount, 'total' => ($sum+$amount), 'discount' => 0, 'cart' => $productdata], 200);
            // return response()->json(['status' => true, 'message' => "Success", 'subtotal' => $sum, 'shipping' => $amount, 'total' => ($sum+$amount), 'discount' => 0,'shipping_type' => $ship, 'cart' => $productdata], 200);
            // return response()->json(['status' => true, 'message' => "Success", 'subtotal' => $sum, 'shipping' => $amount, 'total' => ($sum+$amount), 'discount' => 0, 'cart' => $productdata , 'OrderShipping' => $OrderShipping], 200);
        } else {
            return response()->json(['status' => true,'subtotal' => 0, 'shipping' => 0,  'cart' => []], 200);
        }
    }

    public function dabitaddtocart(Request $request)
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        }

        $user_id = $user->id;
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'card_amount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all())], 200);
        }
        $already = Cart::where('user_id', $user_id)->where('product_id', 404)->first();
        if (!empty($already)) {
            return response()->json(['status' => false, 'message' => 'already exist in cart'], 200);
        }
        $cart = Cart::create([
            "user_id" => $user_id,
            "card_amount" => $request->card_amount,
            "product_id" => $request->product_id,
            "quantity" => 1,
            "price" =>  $request->card_amount
        ]);
        return response()->json(['status' => true, 'message' => "Success",], 200);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function buyNowCheckoutDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'quantity' => 'required|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all()), 'subtotal' => 0,  'cart' => []], 200);
        }
       

        $product = Product::where('id', $request->product_id)->first();
        if (empty($product)) {
            return response()->json(['status' => false, 'message' => "product not found", 'subtotal' => 0,  'cart' => []], 200);
        }

        if ($product->in_stock <= 0) {
            return response()->json(['status' => false, 'message' => "product is out of stock", 'subtotal' => 0,  'cart' => []], 200);
        }
        $sum = $product->s_price*$request->quantity;
        $currency_code = 'USD';
        if($product->product_type == 'variants'){
            $validator = Validator::make($request->all(), [
                'variation' => 'required|exists:products_variants,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all()), 'subtotal' => 0,  'cart' => []], 200);
            }
        }

        if($product->product_type == 'booking'){
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date_format:Y-m-d',
                'time_sloat' => 'required',
                'sloat_end_time' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all()), 'subtotal' => 0,  'cart' => []], 200);
            } 
         }
         $returnData = [
            'start_date' => $request->start_date??'',
            'time_sloat' => $request->time_sloat??'',
            'sloat_end_time' => $request->sloat_end_time??'',
            'quantity' => $request->quantity??'',
         ];

                $shippingAmount = [];
               
                $vendor = User::where('id', $product->vendor_id)->first();
                if ($vendor) {
                    $vndorsetting = VendorSetting::where('vendor_id', $product->vendor_id)->pluck('value', 'name');
                    $adminsetting = Setting::pluck('value', 'name');
                    if($product->product_type=='virtual_product' || $product->product_type=='booking'){
                        $shippingAmount[$vendor->id] =  0 ;
                    }else{
                        if (isset($vndorsetting['free_shipping_is_applied']) && ($vndorsetting['free_shipping_is_applied'] == "on")) {
                            if (isset($vndorsetting['free_shipping_over']) && ($product->s_price >= $vndorsetting['free_shipping_over'])) {
                                // $shippingAmount[$vendor->id] = $this->currencyConvert($currency_code, $product->p_price);
                                $shippingAmount[$vendor->id] = 0;
                            } else {
                                $shippingAmount[$vendor->id] = $shippingAmount[$vendor->id] + $this->currencyConvert($currency_code, $vndorsetting['free_shipping_over']);
                            }
                        } 
                        
                        if (isset($vndorsetting['normal_shipping_is_applied']) && ($vndorsetting['normal_shipping_is_applied'] == "on")) {
                            $shippingAmount[$vendor->id] = (isset($shippingAmount[$vendor->id])?$shippingAmount[$vendor->id]:0) + $this->currencyConvert($currency_code, $vndorsetting['normal_price']);
                        } else {
                            $shippingAmount[$vendor->id] = (isset($shippingAmount[$vendor->id])?$shippingAmount[$vendor->id]:0) + $this->currencyConvert($currency_code, $adminsetting['normal_price']);
                        }
                    }
                 
                }
           
           
            $amount = round(array_sum($shippingAmount));
            return response()->json(['status' => true, 'message' => "Success", 'subtotal' => $sum, 'shipping' => $amount, 'total' => ($sum+$amount), 'discount' => 0,'return_data'=>$returnData,'prodcut_data' => $product], 200);
       

    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'product_id' => 'required',
                'quantity' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all()), 'subtotal' => 0,  'cart' => []], 200);
            }
            $varint = '';
            if ($request->quantity <= 0) {
                return response()->json(['status' => false, 'message' => 'please select quantity', 'subtotal' => $sum, 'cart' => $userCart]);
            }

            // $userid = Auth::user()->token()->user_id;
            $user = auth()->guard('api')->user();
            $product = Product::where('id', $request->product_id)->first();

            if (empty($product)) {
                return response()->json(['status' => false, 'message' => "product not found", 'subtotal' => 0,  'cart' => []], 200);
            }

            if ($product->in_stock <= 0) {
                return response()->json(['status' => false, 'message' => "product is out of stock", 'subtotal' => 0,  'cart' => []], 200);
            }

            // if($product->product_type == 'single' || $product->product_type == 'variants')
            // {
            //     $productShip = Cart::create([
            //         "is_shipping"         => 'true'
            //     ]);
            // }

            if($product->product_type == 'variants'){
                $validator = Validator::make($request->all(), [
                    'variation' => 'required|exists:products_variants,id',
                ]);
    
                if ($validator->fails()) {
                    return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all()), 'subtotal' => 0,  'cart' => []], 200);
                }
            }
            if($product->product_type == 'booking'){
                $validator = Validator::make($request->all(), [
                    'start_date' => 'required|date_format:Y-m-d',
                    'time_sloat' => 'required',
                    'sloat_end_time' => 'required',
                ]);
    
                if ($validator->fails()) {
                    return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all()), 'subtotal' => 0,  'cart' => []], 200);
                } 
            }
            if (!empty($user)) {
                $userid = $user->id;
                if ($product->product_type == 'variants') {
                    $varint = ProductVariants::where('id', $request->variation)->first();
                    $cart = Cart::where('product_id', $varint->p_id)->where('user_id', $userid)->first();
                    if ($varint) {
                        if (!empty($cart) && $cart->variation == $request->variation) {
                            $q = (int)$cart->quantity;
                            $quant = $q + $request->quantity;
                            $price = $quant * $varint->variant_price;
                            $cart_added = Cart::where('id', $cart->id)->update([
                                "quantity"          => $quant,
                                'product_id'        => $varint->p_id,
                                "price"             => $price,
                                // "is_shipping"         => 'true'
                            ]);

                            // 

                        } else {
                            // 
                            // if product with different varation 
                            $quantity = $request->quantity;
                            $price = $quantity * $varint->variant_price;

                            $cart_added = Cart::create([
                                'user_id'             => $userid,
                                'product_id'          => $varint->p_id,
                                'quantity'            => $request->quantity,
                                'variation'           => $request->variation,
                                "price"               => $price,
                                "vendor_id"           => $product->vendor_id,
                                "variation"           => $request->variation,
                                "start_date"          => $request->start_date,
                                "sloat"               => $request->time_sloat,
                                "sloat_end_time"               => $request->sloat_end_time,
                                // "is_shipping"         => 'true'
                            ]);
                        }
                    }
                } else {

                    $cart = Cart::where('product_id', $request->product_id)->where('user_id', $userid)->first();

                    if (!empty($cart) && ($product->product_type != 'variants')) {
                     
                        if($product->product_type == 'bid'){
                            return response()->json(['status' => false, 'message' => "Already Added to cart", 'subtotal' => 0,  'cart' => []], 200);
                        }
                        if($product->product_type == 'booking'){
                            $cartSloat = Cart::where('product_id', $request->product_id)->where('start_date', $request->start_date)->where('sloat', $request->time_sloat)->where('sloat_end_time', $request->sloat_end_time)->where('user_id', $userid)->first();
                             if($cartSloat){
                                return response()->json(['status' => false, 'message' => "Selected sloat already added to cart", 'subtotal' => 0,  'cart' => []], 200);
                             }else{
                               
                                $quant = $request->quantity;
                                $price = $quant * $product->s_price;
                                Cart::create([
                                    'user_id'             => $userid,
                                    'product_id'          => $request->product_id,
                                    'quantity'            => $request->quantity,
                                    'variation'           => null,
                                    'price'               => $price,
                                    'vendor_id'           => $product->vendor_id,
                                    'start_date'          => $request->start_date,
                                    'sloat'               => $request->time_sloat,
                                    "sloat_end_time"      => $request->sloat_end_time,
                                    // "is_shipping"         => 'false'
                                ]);
                             }
                            
                        }else{
                            //if cart not empty then check if this product exist then increment quantity
                            $q = (int)$cart->quantity;
                            $quant = $q + $request->quantity;
                            $price = $quant * $product->s_price;
                            $cart_added = Cart::where('id', $cart->id)->update([
                                "quantity" => $quant,
                                "price" => $price
                            ]);
                        }
                      
                        // 
                    } else {
                        if($product->product_type == 'bid'){
                            $bid_pp = UserBids::where('user_id',$userid)->where('product_id',$request->product_id)->where('status','winner')->select('bid_price')->first();

                            $quantity = $request->quantity;
                            $price = $quantity * (isset($bid_pp->bid_price) ? $bid_pp->bid_price : $product->s_price);
                            $cart_added = Cart::create([
                                'user_id'             => $userid,
                                'product_id'          => $request->product_id,
                                'quantity'            => $request->quantity,
                                'variation'           => $request->variation,
                                'price'               => $price,
                                'vendor_id'           => $product->vendor_id,
                                'start_date'          => $request->start_date,
                                'sloat'               => $request->time_sloat,
                                "sloat_end_time"      => $request->sloat_end_time,
                                // "is_shipping"         => 'false'
                            ]);
                        }
                        else{
                            // if cart is empty then this is the first product
                            $quantity = $request->quantity;
                            $price = $quantity * $product->s_price;
                            $cart_added = Cart::create([
                                'user_id'             => $userid,
                                'product_id'          => $request->product_id,
                                'quantity'            => $request->quantity,
                                'variation'           => $request->variation,
                                'price'               => $price,
                                'vendor_id'           => $product->vendor_id,
                                'start_date'          => $request->start_date,
                                'sloat'               => $request->time_sloat,
                                "sloat_end_time"               => $request->sloat_end_time,
                                // "is_shipping"         => 'false'
                            ]);
                        }
                        // 
                    }
                }

                $userCart = Cart::where('user_id', $userid)->get();
                $sum = Cart::where('user_id', $userid)->sum('price');

                foreach ($userCart as $key => $value) {
                    $userCart[$key]['variation'] =  json_decode($value->variation);
                }

                return response()->json(['status' => true, 'message' => 'Added to cart', 'subtotal' => $sum, 'cart' => $userCart]);

          

            } elseif (!empty($request->device_id)) {

                // 

                $guestCart = GuestUserData::where([['device_id', $request->device_id], ['product_id', $request->product_id], ['type', 'cart']])->first();
                $product = Product::where('id', $request->product_id)->first();

                if (empty($product)) {
                    return response()->json(['status' => false, 'message' => "product not found", 'subtotal' => 0,  'cart' => []], 200);
                }

                if ($product->product_type == 'variants') {

                    $varint = ProductVariants::where('id', $request->variation)->first();

                    $guestCart = GuestUserData::where([['device_id', $request->device_id], ['product_id', $varint->p_id], ['type', 'cart']])->first();

                    if (!empty($guestCart) && ($guestCart->variation == $request->variation)) {

                        $prevqty = $guestCart->quantity;
                        $newqty = $prevqty + $request->quantity;
                        $total_amount = $newqty * $varint->variant_price;

                        if ($varint->variant_stock >= $newqty) {
                            $guestCart->update([
                                'quantity'      => $newqty,
                                'product_id'    =>  $varint->p_id,
                                'price'         => $total_amount,
                            ]);
                            $guestCart->save();

                            return response()->json(['status' => true, 'message' => 'Added to cart', 'subtotal' => $total_amount, 'cart' => $guestCart]);
                        } else {
                            return response()->json(['status' => false, 'message' => 'product out of stock', 'subtotal' => 0, 'cart' => []]);
                        }

                        // 
                    } else {

                        $total_amount = $request->quantity * $varint->variant_price;
                        $guestCart = GuestUserData::create([
                            'device_id'     => $request->device_id,
                            'type'          => 'cart',
                            'quantity'      => $request->quantity,
                            'product_id'    => $varint->p_id,
                            'price'         => $total_amount,
                            'variation'     => $request->variation,
                            "start_date"    => $request->start_date,
                            "sloat"         => $request->time_sloat,
                            "sloat_end_time" => $request->sloat_end_time,
                        ]);

                        return response()->json(['status' => true, 'message' => 'Added to cart', 'subtotal' => $total_amount, 'cart' => $guestCart]);
                    }
                }

                // $guestCart = GuestUserData::where([['device_id', $request->device_id], ['product_id', $request->product_id], ['type', 'cart']])->first();

                if (!empty($guestCart) && $product->product_type != 'variants') {
                    // 

                    // $guestCart = GuestUserData::where([['device_id', $request->device_id], ['product_id', $varint->p_id], ['type', 'cart']])->first();

                    $prevqty = $guestCart->quantity;
                    $newqty = $prevqty + $request->quantity;
                    $total_amount = $newqty * $product->s_price;

                    if ($product->in_stock >= $newqty) {
                        $guestCart->update([
                            'quantity'      => $newqty,
                            'price'         => $total_amount,
                        ]);
                        $guestCart->save();
                        return response()->json(['status' => true, 'message' => 'Added to cart', 'subtotal' => $total_amount, 'cart' => $guestCart]);
                        // 
                    } else {
                        return response()->json(['status' => false, 'message' => 'product out of stock', 'subtotal' => 0, 'cart' => []]);
                    }
                    // 
                } else {
                    // 

                    $total_amount = $request->quantity * $product->s_price;
                    $guestCart = GuestUserData::create([

                        'device_id'     => $request->device_id,
                        'type'          => 'cart',
                        'quantity'      =>  $request->quantity,
                        'product_id'    =>  $request->product_id,
                        'price'         => $total_amount,
                        'type'          => 'cart',
                        'variation'     => $request->variation,
                        "start_date"    => $request->start_date,
                        "sloat"         => $request->time_sloat,
                        "sloat_end_time"               => $request->sloat_end_time,

                    ]);

                    return response()->json(['status' => true, 'message' => 'added to cart successfully', 'subtotal' => $total_amount, 'cart' => $guestCart]);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'user not found', 'subtotal' => 0, 'cart' => []], 200);
            }
        } catch (\Exception $th) {
           
            return response()->json(['status' => false, "message" => 'At Line number: '.$th->getLine().', '.$th->getMessage(), 'subtotal' => 0, 'cart' => []], 400);
        
        }
    }

    public function qtyupdate(Request $request)
    {

        //$userid = Auth::user()->token()->user_id;
        $user = auth()->guard('api')->user();
        $productArray = explode(",",$request->product_id);
        $quaArray = explode(",",$request->qty);
      
        if (!empty($user)) {
            $userid = $user->id;

            foreach($productArray as $key => $productItem){      


            $cart = Cart::where([['user_id', $userid], ['product_id', $productItem]])->first();
            if (empty($cart)) {
                return response()->json(['status' => false, 'message' => 'cart data not found']);
            }
            $product = Product::where('id', $cart->product_id)->first();
            if (empty($product)) {
                return response()->json(['status' => false, 'message' => 'product not found']);
            }

            if ($product->product_type == 'variants') {
                $cartdata = Cart::where([['user_id', $userid], ['product_id', $productItem], ['variation', $cart->variation]])->first();
                if (!empty($cartdata)) {
                    $productVariation = ProductVariants::where('id', $cartdata->variation)->first();
                    $qty = $quaArray[$key] ?? '1';
                    $price = $qty * $productVariation->variant_price;
                    $cartupdate = Cart::where('id', $cartdata->id)->update([
                        'quantity' =>  $qty,
                        'price' => $price,

                    ]);
                    $newqty = Cart::where('id', $cartdata->id)->first();
                    // return response()->json(['status' => true, 'message' => 'quantity updated proid:' . $cartdata->product_id . ' qty' . $newqty->quantity]);
                } 
                // else {
                //     return response()->json(['status' => false, 'message' => 'data not found']);
                // }
            } else {
                $cartdata = Cart::where([['user_id', $userid], ['product_id', $productItem]])->first();
                if (!empty($cartdata)) {
                    $qty = $quaArray[$key] ?? '1';
                    $price = $qty * $product->s_price;
                    $cartupdate = Cart::where('id', $cartdata->id)->update([
                        'quantity' =>  $qty,
                        'price' => $price,

                    ]);
                    $newqty = Cart::where('id', $cartdata->id)->first();
                    $msg = 'Quantity Updated Successfully.';
                    // 'Quantity Updated Successfully. proid:' . $cartdata->product_id . ' qty ' . $newqty->quantity
                    // return response()->json(['status' => true, 'message' => $msg]);
                } 
                // else {
                //     return response()->json(['status' => false, 'message' => 'data not found']);
                // }
            }
           

        }
        return response()->json(['status' => true, 'message' => 'Cart Updated Successfully.']);
        } elseif ($request->device_id) {

            foreach($productArray as $key => $productItem){    

            $cart = GuestUserData::where([['device_id', $request->device_id], ['product_id', $productItem]])->first();
            if (empty($cart)) {
                return response()->json(['status' => false, 'message' => 'cart data not found']);
            }
            $product = Product::where('id', $cart->product_id)->first();
            if (empty($product)) {
                return response()->json(['status' => false, 'message' => 'product not found']);
            }
            if ($product->product_type == 'variants') {
                $cartdata = GuestUserData::where([['device_id', $request->device_id], ['product_id', $productItem], ['variation', $cart->variation]])->first();
                if (!empty($cartdata)) {
                    $productVariation = ProductVariants::where('id', $cart->variation)->first();
                    $qty = $quaArray[$key] ?? '1';
                    $price = $qty * $productVariation->variant_price;
                    $cartupdate = GuestUserData::where('id', $cart->id)->update([
                        'quantity' =>  $qty,
                        'price' => $price,

                    ]);
                    // return response()->json(['status' => true, 'message' => 'quantity updated']);
                } 
                // else {
                //     return response()->json(['status' => false, 'message' => 'data not found']);
                // }
            } else {
                $cartdata = GuestUserData::where([['device_id', $request->device_id], ['product_id', $productItem]])->first();
                if (!empty($cartdata)) {
                    $qty = $quaArray[$key] ?? '1';
                    $price = $qty * $product->s_price;
                    $cartupdate = GuestUserData::where('id', $cart->id)->update([
                        'quantity' =>  $qty,
                        'price' => $price,

                    ]);
                    // return response()->json(['status' => true, 'message' => 'quantity updated']);
                }
                //  else {
                //     return response()->json(['status' => false, 'message' => 'data not found']);
                // }
            }

        }
        return response()->json(['status' => true, 'message' => 'Cart Updated Successfully.']);
        } else {
            return response()->json(['status' => false, 'message' => 'user not found']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)

    {
        $user = auth()->guard('api')->user();
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => implode("", $validator->errors()->all())], 200);
        }

        if (!empty($user)) {
            $userid = $user->id;
            // return $userid;
            $cart = Cart::where([['user_id', $userid], ['product_id', $request->product_id]])->first();
            // return $cart;
            if (!empty($cart)) {
                $cart->delete();
                return response()->json(['status' => true, 'message' => "Removed Successfully"], 200);
            } else {
                return response()->json(['status' => false, 'message' => "cart not found"], 200);
            }
        } elseif ($request->device_id) {
            GuestUserData::where([['device_id', $request->device_id], ['product_id', $request->product_id]])->delete();
            return response()->json(['status' => true, 'message' => "Removed Successfully"], 200);
        } else {
            return response()->json(['status' => false, 'message' => "please enter device id or auth token"], 200);
        }
    }
}