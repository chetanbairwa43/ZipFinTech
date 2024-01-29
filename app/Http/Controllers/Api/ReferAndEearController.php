<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\Setting;
use Auth;
class ReferAndEearController extends Controller
{
    public function referAndeEarn(){
        try {

            $user = Auth::guard('api')->user(); 
            $referAmount = Setting::getDataByKey('referal_amount');
            $data['referCode'] = $user->referal_code ?? '';
            $data['referAmount'] = $referAmount->value ?? 0;
  
           return ResponseBuilder::success(trans('global.refer_data'), $this->success,$data);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
