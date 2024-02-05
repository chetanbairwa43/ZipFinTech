<?php

namespace App\Helper;
use DB;
use App\BookingActivity;
use App\Models\LogActivity;
use App\Models\ProductAttribute;
use App\Models\VenderPlan;
use App\Models\User;
use App\Models\UserDeviceToken;
use Image as Img;
// use Intervention\Image\Image;
use Request;


class Helper
{

  
  public static  function FatoohpaymetMethod()
  {
    $curl = curl_init();
    // $urlAPI =env("FATOORAH_API_URL", "");
    // dd($urlAPI);
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://apitest.myfatoorah.com/v2/InitiatePayment",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "InvoiceAmount": 100,
    "CurrencyIso": "kwd",
    "ImageUrl": "https://dirise.eoxyslive.com/assets/images/brand/logo.png"
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        // 'Authorization: Bearer 2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM'
        'Authorization: Bearer '.env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM").''
    ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }
  
  public static  function yearfm($months)
    {
      $str = '';

      if(($y = round(bcdiv($months, 12))))
      {
        $str .= "$y Year".($y-1 ? 's' : null);
      }
      if(($m = round($months % 12)))
      {
        $str .= ($y ? ' ' : null)."$m Month".($m-1 ? 's' : null);
      }
      return empty($str) ? false : $str;
    }
    public static function imageUpload($image,$path){

        $extention = $image->getClientOriginalExtension();
        $filename = time().'.'.$extention;
        $image->move($path, $filename);
        return $filename;
	}

  public static function withdarawStatus()
  {
    return $status=[
      '0' => 'pending',
      '1' => 'approved',
      '2' => 'rejected',
    ];
  }


  public static function initiatePaymentFatoorah($postFields){
    $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
    $apiURL = env("FATOORAH_API_URL", "https://apitest.myfatoorah.com");
    $json = Helper::callAPI("$apiURL/v2/InitiatePayment", $apiKey, $postFields);
    return $json->Data->PaymentMethods;
	}

  public static function executePaymentFatoorah($postFields) {
    $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
    $apiURL = env("FATOORAH_API_URL", "https://apitest.myfatoorah.com");
    $json = Helper::callAPI("$apiURL/v2/ExecutePayment", $apiKey, $postFields);
    return $json->Data;
}
//GET Banks Endpoint

public static function getBanks(){
    $curl = curl_init();
    $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/GetBanks',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS =>'{
      "SupplierName": "Omi",
      "Mobile": "Yadav",
      "Email": "omi@yopmail.com",
      "CommissionValue": 0,
      "CommissionPercentage": 0,
      "IsActive": true
    }',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);

}

//------------------------------------------------------------------------------
/*
 * Call API Endpoint Function
 */

 public static function callAPI($endpointURL, $apiKey, $postFields = [], $requestType = 'POST') {
  $curl = curl_init($endpointURL);
  curl_setopt_array($curl, array(
      CURLOPT_CUSTOMREQUEST  => $requestType,
      CURLOPT_POSTFIELDS     => json_encode($postFields),
      CURLOPT_HTTPHEADER     => array("Authorization: Bearer 2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM", 'Content-Type: application/json'),
      CURLOPT_RETURNTRANSFER => true,
  ));
  

  $response = curl_exec($curl);
  $curlErr  = curl_error($curl);
  curl_close($curl);

  if ($curlErr) {
      //Curl is not working in your server
      die("Curl Error: $curlErr");
  }

  $error = Helper::handleError($response);
  if ($error) {
      die("Error: $error");
  }

  return json_decode($response);
}

public static function MyFatoorahCheckPayment($postFields = []) {

  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  $curl = curl_init('https://apitest.myfatoorah.com/v2/getPaymentStatus');
  curl_setopt_array($curl, array(
      CURLOPT_CUSTOMREQUEST  => 'POST',
      CURLOPT_POSTFIELDS     => json_encode($postFields),
      CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
      CURLOPT_RETURNTRANSFER => true,
  ));

  $response = curl_exec($curl);
  $curlErr  = curl_error($curl);
  curl_close($curl);

  if ($curlErr) {
      //Curl is not working in your server
      die("Curl Error: $curlErr");
  }

  $error = Helper::handleError($response);
  if ($error) {
      die("Error: $error");
  }

  return json_decode($response);
}
//------------------------------------------------------------------------------
/*
 * Handle Endpoint Errors Function 
 */

 public static function handleError($response) {

  $json = json_decode($response);
  if (isset($json->IsSuccess) && $json->IsSuccess == true) {
      return null;
  }

  //Check for the errors
  if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
      $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
      $blogDatas = array_column($errorsObj, 'Error', 'Name');

      $error = implode(', ', array_map(function ($k, $v) {
                  return "$k: $v";
              }, array_keys($blogDatas), array_values($blogDatas)));
  } else if (isset($json->Data->ErrorMessage)) {
      $error = $json->Data->ErrorMessage;
  }

  if (empty($error)) {
      $error = (isset($json->Message)) ? $json->Message : (!empty($response) ? $response : 'API key or API URL is not correct');
  }

  return $error;
}

  public static function tapPaymentCreateToken($cardNumber,$exp_month,$exp_year,$cvc,$customerName){

    $client = new \GuzzleHttp\Client();
    
    $response = $client->request('POST', 'https://api.tap.company/v2/tokens', [
      'body' => '{"card":{"number":'.$cardNumber.',"exp_month":'.$exp_month.',"exp_year":'.$exp_year.',"cvc":'.$cvc.',"name":"'.$customerName.'","address":{"country":"Kuwait","line1":"Salmiya, 21","city":"Kuwait city","street":"Salim","avenue":"Gulf"}},"client_ip":"192.168.1.20"}',
      'headers' => [
        'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
        'accept' => 'application/json', 
        'content-type' => 'application/json',
      ],
    ]);
    
    return json_decode($response->getBody(),true);
}


    public static function createPayment($amount,$customerName,$customerEmail,$customerPhone,$token){

      $client = new \GuzzleHttp\Client();

      $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
        'body' => 
        '{"amount":'.$amount.',"currency":"KWD","customer_initiated":true,"threeDSecure":true,"save_card":false,"description":"Test Description","metadata":{"udf1":"Metadata 1"},"reference":{"transaction":"txn_01","order":"ord_01"},"receipt":{"email":true,"sms":true},"customer":{"first_name":"'.$customerName.'","middle_name":"test","last_name":"test","email":"'.$customerEmail.'","phone":{"country_code":965,"number":51234567}},"source":{"id":"src_all"},"post":{"url":"https://dirise.eoxyslive.com/api/tap-payment"},"redirect":{"url":"https://dirise.eoxyslive.com/api/tap-payment"}}',
        'headers' => [
          'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
          'accept' => 'application/json',
          'content-type' => 'application/json',
        ],
      ]);

      return json_decode($response->getBody(),true);
	}
    public static function checkPaymentStatus($tapID){
      
      $client = new \GuzzleHttp\Client();
      
      $response = $client->request('GET', 'https://api.tap.company/v2/charges/'.$tapID.'', [
        'headers' => [
          'Authorization' => 'Bearer sk_test_XKokBfNWv6FIYuTMg5sLPjhJ',
          'accept' => 'application/json',
        ],
      ]);
      
      return json_decode($response->getBody(),true);
	}

    public static function saveVarints($request, $pid){
        $products = Product::where('parent_id', '=', $pid)->get();
        foreach ($products as $pk => $pv) {
          if ($pv->id != null) {
            DB::table('products_variants')->where('p_id', '=', $pv->id)->delete();
            Product::destroy($pv->id);
          }
        }
        $user = Auth::guard('api')->user();
        $parent_product = Product::where('id', '=', $pid)->first();
        $variant_sku = $request->variant_sku;
        $variant_price = $request->variant_price;
        $variant_stock = $request->variant_stock;
        $variant = $request->variant_value;
        if(!empty($variant_sku)){
          foreach ($variant_sku as $k => $v) {
            $product = Product::create([
              'vendor_id'         => $parent_product->vendor_id,
              'product_type'    => $parent_product->product_type,
              'pname'             => $parent_product->pname,
              'cat_id'             => $parent_product->cat_id,
              'cat_id_2'             => $parent_product->cat_id_2,
              'cat_id_3'             => $parent_product->cat_id_3,
              'sku_id'            =>  $variant_sku[$k][0],
              'commission'        => $parent_product->commission,
              'p_price'           => $parent_product->p_price,
              's_price'           => $variant_price[$k][0],
              'tax_apply'       => $parent_product->tax_apply,
              'tax_type'       => $parent_product->tax,
              'short_description' => $parent_product->short_description,
              'long_description' => $parent_product->content,
              'offer_start_date'    => $parent_product->discount_start,
              'offer_end_date'          => $parent_product->discount_end,
              'offer_discount'          => $parent_product->offer_discount,
              'in_stock'          => $variant_stock[$k][0],
              'shipping_type'    => $parent_product->shipping_type,
              'shipping_charge'    => $parent_product->shipping_price,
              'meta_title'         => $parent_product->meta_title,
              'meta_keyword'          => $parent_product->meta_keyword,
              'meta_description'      => $parent_product->meta_description,
              'parent_id'             => $parent_product->id,
              'arab_pname'          => $parent_product->arab_pname,
              'arab_short_description'          => $parent_product->arab_short_description,
              'arab_long_description'          => $parent_product->arab_long_description,
              'stock_alert'            => $request->stock_alert
            ]);
            $thumb = [];
            $i = 0;
            $updateVariants = [
              'parent_id' => $pid,
              'p_id' => $product->id,
              'variant_id' => $variant[$k][0], //$variant->variant_id,
              'variant_value' => $variant[$k][0], //$variant->id,
              'variant_sku' => $variant_sku[$k][0],
              'variant_price' => $variant_price[$k][0],
              'variant_stock' => $variant_stock[$k][0],
            ];
    
            if (isset($request->file('variant_images')[$k]) && $request->file('variant_images')[$k]) {
              $files = $request->file('variant_images')[$k];
              if (isset($files)) {
                foreach ($files as $file) {
                  $name = uniqid() . $file->getClientOriginalName();
                  $file->move('products/gallery', $name);
                  $thumb[$i++] = $name;
                }
                $product->gallery_image = $thumb;
                $product->update();
                $updateVariants['variant_images'] = '["' . implode(",", $thumb) . '"]';
              }
            } else {
              $prv_img = $request->prv_img[$k];
              $updateVariants['variant_images'] = implode(',', $prv_img);
            }
            $variant_values = json_decode($variant[$k][0]);
            $variant_value = [];
            $products_variants_id = DB::table('products_variants')->insertGetId($updateVariants);
            foreach ($variant_values as $key => $value) {
              $variant_values = DB::table('attributes_value')->where('id', '=', $value)->first();
              $variant_value[$key] = $variant_values->attr_value_name;
              $pa = ProductAttribute::firstOrNew(
                array(
                  'product_id' => $pid,
                  'attr_value_id' => $value,
                )
              );
              $pa->attr_id = $variant_values->attr_id;
              $pa->save();
              DB::table('variations')->insert([
                'product_id' => $product->id,
                'parent_id' => $pid,
                'variant_id' => $products_variants_id,
                'attribute_id' => $variant_values->attr_id, //$variant->variant_id,
                'attribute_term_id' => $value, //$variant->id,
              ]);
            }
          }
        }
	}

    public static function multiImageUpload($images,$path){

        $multi = [];

            foreach ($images as $img) {
                $name = $img->getClientOriginalName();
                $img->move($path, $name);
                  $multi[] =  $name;
            }
            return $multi;
        
    }
    public static function addToLog($subject,$type='')
    {
        $log = [];
        $log['subject'] = $subject;
        $log['url'] = Request::fullUrl();
        $log['method'] = Request::method();
        $log['type'] = $type;
        $log['ip'] = Request::ip();
        $log['agent'] = Request::header('user-agent');
        $log['user_id'] = auth()->check() ? auth()->user()->id : 1;
        LogActivity::create($log);
    }


    public static function logActivityLists()
    {
        $pagination=10;
        $logs = LogActivity::latest();
        $log_pagination = $logs->paginate($pagination)->withQueryString();
        return $log_pagination;
    }

   public static  function generateBarcodeNumber()
     {
        $number = mt_rand(1000000000, 9999999999); 
        if ($number) {
            return $number;
            // return generateBarcodeNumber();
        }

       
    }
    public static function dayFromNumber($day)
    {

        $days = [
           '1' => 'Monday',
           '2' => 'Tuesday',
           '3' => 'Wednesday',
           '4' => 'Thursday',
           '5' => 'Friday',
           '6' => 'Saturday',
           '7' => 'Sunday'
        ];

        return $days[$day];

    }

    public static function imageThumbnail($imageUrl, $destinationPath, $height, $width, $old_image = null)
    {   
        try {
            if (!empty($old_image)) {
                if (File::exists($destinationPath.'/'.$old_image)) {
                    unlink($destinationPath.'/'.$old_image);
                }
            }

            $imageContent = file_get_contents($imageUrl);
            $imageName = time() . '_thumbnail.jpg';

            file_put_contents($destinationPath . '/' . $imageName, $imageContent);

            $img = Img::make($destinationPath . '/' . $imageName);
            $img->resize($height, $width, function ($const) {
                $const->aspectRatio();
            })->save();

            return $imageName;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function productImageThumbnail($imageUrl, $destinationPath, $height, $width, $old_image = null)
    {   
        try {
            if (!empty($old_image)) {
                $oldImagePath = $destinationPath . '/' . $old_image;
                if (File::exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageContent = file_get_contents($imageUrl);
            $imageName = time() . '_thumbnail.jpg';
            $thumbnailPath = $destinationPath . '/' . $imageName;

            file_put_contents($thumbnailPath, $imageContent);

            $img = Img::make($thumbnailPath);
            $img->resize($height, $width, function ($const) {
                $const->aspectRatio();
            })->save($thumbnailPath, 90); // Specify the quality and format (if needed)

            return $imageName;
        } catch (\Exception $e) {
            return 0;
        }
    }


    public static function fedexAcessToken() {
      try {
          $curl = curl_init();
          curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://apis-sandbox.fedex.com/oauth/token',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id='.env('FEDEX_API_KEY').'&client_secret='.env('FEDEX_CLIENT_SECRET'),
          CURLOPT_HTTPHEADER => array(
              'Content-Type: application/x-www-form-urlencoded',
              'Cookie: ak_bmsc=AA2596F0778E8478F8AC7136CA598F81~000000000000000000000000000000~YAAQPagRYA91xdKJAQAAxxoq/RTPxl0NE3BX44o0QwxmkJRurancikmyYE4d02tl0sH/bQ8QuTyvHsSinHd/j3l8IXxuuPl0W79ATCFPP64OTyy4zSM+mf7vKxo9MzUA8wK7WaonYdNdLFoffMq0Xh4xQSM6alAVVtOvwA2Z0OACz+aK8TN7pXDIdoWAJHITx9y94IHinmmLnG3QMut5j7DS4eFvle/SUdeQtwS9B0kXEvKGrrHNSuaKUB2M/o3fpSe9KAIh20DYImo4gJLglvN0o3b9OiS/Wt9HxfmQa+2IJ+46/1psMkMk/poOR9aL7ANsc0AnaRlnrCUSn1P14XCvw482MhTnuGpQaNebA4+9SQW1VSPvFbTkgBcV; bm_sv=74E1FF1F55C46B458F8A5503DB233A88~YAAQPagRYAJ2xdKJAQAA0T8q/RQSQlEG/C6WsS0Zs9uCkW6eOVrdDysnR4KQVpAFNn8UuCwWVJqB8sLXNnwO9r/HNVokvRW9I17SKlnCEixj3APlnzhxkgpkiZ9fcD2mA/RcvmYQupPBe0sClPX4DrFwv/CqyLccCHihku+dpMv3wvWYvom7wmxYTNHh2ZiBQUKImyxZ7kCqF2yLGaNE9SsSt2fTYS9YvSzpjZMkwwwD4lTKGIbXTS190FgTIE8=~1'
          ),
          ));

          $response = curl_exec($curl);

          curl_close($curl);
          $data = json_decode($response);
          return $data->access_token;

      } catch (\Exception $e) {
          return $e->getMessage();
      }

  }

  public static function createSupplier(){
    $curl = curl_init();
    $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/CreateSupplier',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "SupplierName": "Lavi",
        "Mobile": "7665025654",
        "Email": "lavi@yopmail.com",
        "CommissionValue": 0,
        "CommissionPercentage": 0,
        "IsActive": true
      }',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
  }  

  public static function editSupplier(){
    $curl = curl_init();
    $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/EditSupplier',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "SupplierCode": 3,
        "SupplierName": "Chetan",
        "Mobile": "7340479434",
        "Email": "chetan@yopmail.com",
        "CommissionValue": 0,
        "CommissionPercentage": 0,
        "DepositTerms": ""
      }',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
  }

  public static function getSuppliers(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/GetSuppliers',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);
  }

  public static function getSupplierDetails(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com//v2/GetSupplierDetails',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);
  }

  public static function getSupplierDocuments(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com//v2/GetSupplierDocuments',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);

  }

  public static function getSupplierDashboard(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com//v2/GetSupplierDashboard',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);

  }

  public static function getSupplierDeposit(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com//v2/GetSupplierDeposits',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);

  }

  public static function uploadSupplierDocument(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/UploadSupplierDocument',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => array('FileUpload'=> new CURLFILE('/C:/Users/HP/Downloads/download.pdf'),'FileType' => '5','ExpireDate' => '2023-09-29T03:04:00','SupplierCode' => '2'),
    CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);
  }

  public static function transferBalance(){

  $curl = curl_init();
  $apiKey = env("FATOORAH_API_KEY", "2g82LAFrZDbJVQ7TmSV3LxkKybngYho5gfyKxaIfdIQVup7pZBe87nS8GaLAZ-LvBBQ9aK54lDj7mQphJ5l9v9ykxcRkOA5P9H9sfbuhtxBZaWZRLDUnYOMxYpuaGjRhxDqzecf8clMuaVxp_cfbv9_bEqQ5yEVBG5QWytbthuGXXdTdrm9nuAfLDWyV2Sryn6fGfscS2VuOgx30x2elF9PsSJf8IItdrDbsei0OHsryVZOuQirw3qbSUyMFMtThamc5Xu02vF5PLAjWdVQSaij2TuRP4cr3PIMNOPoOKVu3FaS9umx23ZbGRrpp6ZA-GRWFvtJEWSuZFDjEFFKOMYiamZ0FoTheTI1wwa1NMsgvs6Na5MajrBbz0kumgR89B66znVYiAEiHjybBdJL8UNB5A8ItK73P8riTWiT2JwwzzC_EQuvM-g5lcHC9mFXqtDskfYCKXEiE-vZ-uhoq-iZ19etY4FmxNWBBvgKdVLuX514C52iuAxS5_tOnLA46kDKFx6GeIRbCG2hfAE56jLvKnx-bo4NJ2ns-Lo1q_t0j7Lbqy2mpG2aAkFaJL5YlKapk3_omWX7uR2hMMoLq4Z6gTwzeKl_Opzb4EOGzw3gIVpbyPUNs6AGbeEAoXEHMlH47gI3Do1tH57__HTAUeCbRSuD-zYrhouKrMembVUqIQ7iM");
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/TransferBalance',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
    "SupplierCode": 3,
    "TransferAmount": 0,
    "TransferType": "pull",
    "InternalNotes": "withdraw from the supplier 1 to the vendor."
  }',
  CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return json_decode($response);

  }

  
  public static function getRecurringPayment(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/GetRecurringPayment',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
  }

  public static function cancelRecurringPayment(){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/CancelRecurringPayment',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
  }

  public static function resumeRecurringPayment(){

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/ResumeRecurringPayment',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response);
  }

  public static function fireBasePushNotification($user_id, $notification_title, $notification_body)
    {
      try {
        $getToken = UserDeviceToken::getUserById($user_id);
        $firebaseToken[]= $getToken->device_id;
        
        $SERVER_API_KEY = env('NOTIFICATION_SERVER_KEY');
        
        
        $data = [
          "registration_ids" => $firebaseToken,
          "notification" => [
            "title" => $notification_title,
            "body"  => strip_tags($notification_body),
            ]
          ];
          
          $dataString = json_encode($data);
          
          $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
          ];
          
          $ch = curl_init();
          
          curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
          
            $response = curl_exec($ch);
            return $response;

            } catch (\Exception $e) {
                return false;
            }
    }

    public static function stationDelivery($postData){
      $curl = curl_init();
      $postDataArray = is_object($postData) ? $postData->toArray() : $postData;

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://stationx.app:3001/tp/api/v1/order/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => !empty($postDataArray) ? json_encode($postDataArray) : '{}',
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer TJNFyUnn6KPm1dC8PaQQDzP0bSN4jTm4GAY4ax31c4ZeUyHHDw5TZ0B18XU8JGab',
          // 'Cookie: userId=s%3AOwcd7hfkhnf9B5y2CHltov_4u7NmgJYe.usBGaRdUV9xQ7hDdk97axJJ245LUCD8IUGe9U6O0KM8'
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      return json_decode($response, true);

    }

 
    public static function refund($postFields)
    {
      $curl = curl_init();
      $apiUrl = env('FATOORAH_API_URL');
      $apiKey = env('FATOORAH_API_KEY');
      curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl.'/v2/MakeRefund',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postFields),
        CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer '.$apiKey,
          'Content-Type: application/json',
          'Cookie: ApplicationGatewayAffinity=3ef0c0508ad415fb05a4ff3f87fb97da; ApplicationGatewayAffinityCORS=3ef0c0508ad415fb05a4ff3f87fb97da'
        ),
      ));
  
      $response = curl_exec($curl);
  
      curl_close($curl);
      return json_decode($response, true);
    }

    public static function getRefundStatus(){

      $curl = curl_init();
      $apiUrl = env('FATOORAH_API_URL');
      $apiKey = env('FATOORAH_API_KEY');
      curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl.'/v2/GetRefundStatus',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('KeyType' => 'InvoiceId','Key' => '3376565'),
        CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Content-Type: application/json',
          'Authorization: Bearer '.$apiKey,
          'Cookie: ApplicationGatewayAffinity=3ef0c0508ad415fb05a4ff3f87fb97da; ApplicationGatewayAffinityCORS=3ef0c0508ad415fb05a4ff3f87fb97da'
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      return $response;

    }


}