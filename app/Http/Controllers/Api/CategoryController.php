<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CategoryCollection;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Resources\Admin\VendorCollection;
use App\Http\Resources\Admin\LatestProductCollection;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\Category;
use App\Models\VendorProduct;
use App\Models\User;
use App\Models\VendorProfile;
use Auth;
use Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $pagination = isset($request->pagination) ? $request->pagination : 12;
            $data = Category::where('status', 1)->paginate($pagination);
            if(count($data) > 0) {
                $this->response = new CategoryCollection($data);
                return ResponseBuilder::successWithPagination($data, $this->response, trans('global.all_categories'), $this->success);
            }
            return ResponseBuilder::successWithPagination($data, [], trans('global.no_categories'), $this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display a single Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function view($id, Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $distance = 10;
            $vendor_data = VendorProduct::where('category_id', $id)->distinct()->pluck('vendor_id');

            $data = VendorProfile::getStoreCategoryWise($latitude, $longitude, $distance, $request->pagination, $vendor_data);
            
            if(empty($data)) {
                return ResponseBuilder::successWithPagination($data, [], trans('global.no_stores'), $this->success);
            }
            $this->response = new VendorCollection($data);
            
            return ResponseBuilder::successWithPagination($data, $this->response, trans('global.all_stores'), $this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display a single Category on Single Store Page.
     *
     * @return \Illuminate\Http\Response
     */
    public function singleCategory(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            // Validation start
            $validSet = [
                'vendor_id' => 'required | integer',
                'category_id' => 'required | integer',
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
          
            // Validation end

            $vendor_data = VendorProduct::where('category_id', $request->category_id)->where('vendor_id', $request->vendor_id)->paginate(10);

            if(empty($vendor_data)) {
                return ResponseBuilder::successWithPagination($vendor_data, [], trans('global.no_stores'), $this->success);
            }
            $this->response = new LatestProductCollection($vendor_data);
            
            return ResponseBuilder::successWithPagination($vendor_data, $this->response, trans('global.all_stores'), $this->success);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
