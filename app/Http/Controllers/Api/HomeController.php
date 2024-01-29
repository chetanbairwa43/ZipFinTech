<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Admin\VendorCollection;
use App\Http\Resources\Admin\SliderCollection;
use App\Http\Resources\Admin\LatestProductCollection;
use App\Http\Resources\Admin\CategoryCollection;
use App\Http\Controllers\Controller;
use App\Helper\ResponseBuilder;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Models\Category;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorProduct;
use Auth;

class HomeController extends Controller
{
    /**
     * Display home page .
     *
     * @return \Illuminate\Http\Response
     */
    public function home(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();

            $latitude = $user->latitude;
            $longitude = $user->longitude;

            $homePageData=[];
            
            
            if(!empty($latitude) && !empty($longitude)){
                $featuredStores = VendorProfile::featuredStoreWithDistance($latitude, $longitude,$limit=10);
            }else{
                $featuredStores=[];
            }

            $sliders=Slider::getActiveSliders();
            $getLatestProducts= VendorProduct::getBestFreshProducts();
            $category=Category::lastestCategory();
            
            $homePageData['sliderData']= new SliderCollection($sliders);
            $homePageData['BestFreshProduct']=new LatestProductCollection($getLatestProducts);
            $homePageData['latestCategory'] =new CategoryCollection($category);
            $homePageData['featuredStores']=new VendorCollection($featuredStores);

            return ResponseBuilder::success(trans('global.home_page_data'), $this->success,$homePageData);
          

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
    public function nearStores(Request $request)
    {
        try {
            
            $user = Auth::guard('api')->user();
            
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            
            $distance = 1;
            $page = ($request->pagination) ? $request->pagination : 10;
            
            if(!empty($latitude) && !empty($longitude)){
                $data = VendorProfile::storeDistance($latitude, $longitude, $distance, $page);
            }else{
                $data=[];
                return ResponseBuilder::success(trans('global.no_stores'), $this->success,$data);
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
    
    public function searchResult(Request $request){
        try {
            $user = Auth::guard('api')->user();
            
            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $distance = 10;
            $pagination = $request->pagination ?? 10;
            
            // Validation start
            $validSet = [
                'keyword' => 'required'
            ]; 
            
            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end
            
            $vendor_data = VendorProduct::getAllVendorsIdByProductName($request->keyword);
            
            $data = VendorProfile::getStoreCategoryWise($latitude, $longitude, $distance, $pagination, $vendor_data);
            
            if(empty($data)) {
                return ResponseBuilder::successWithPagination($data, [], trans('global.no_data_found'), $this->success);
            }
            $this->response = new VendorCollection($data);
            
            return ResponseBuilder::successWithPagination($data, $this->response, trans('global.all_stores'), $this->success);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
