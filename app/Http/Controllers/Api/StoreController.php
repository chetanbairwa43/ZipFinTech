<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\VendorResource;
use App\Http\Resources\Admin\LatestProductCollection;
use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorProfile;

class StoreController extends Controller
{
    public function storeDetails($id, Request $request){
        try {
            if(empty($id)){
                return ResponseBuilder::error(trans('global.store_id'),$this->badRequest);
            }
            
            $storeDetail=VendorProfile::getDataById($id);
            
            if(empty($storeDetail)){
                return ResponseBuilder::error(trans('global.invalid_store_id'),$this->badRequest);
            }

            $pagination = isset($request->pagination) ? $request->pagination : 10;
            $getVendorProducts=VendorProduct::getLatestProductsByVendor($storeDetail->user_id, $pagination, $request->keyword);
            $data=[];
            $data['storeDetails'] = new VendorResource($storeDetail);
            $data['LatestProducts']=new LatestProductCollection($getVendorProducts);

            return ResponseBuilder::successWithPagination($getVendorProducts, $data, trans('global.store_details'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
