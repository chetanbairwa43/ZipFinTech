<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductCollection;
use App\Http\Resources\Admin\ProductResource;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the Products.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        try {
            $data = Product::getAllActiveProduct($request->keyword);
            if(count($data) > 0) {
                $this->response = new ProductCollection($data);
                return ResponseBuilder::success(trans('global.all_products'),$this->success,$this->response);
            }
            return ResponseBuilder::success(trans('global.no_products'),$this->success,[]);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }

    /**
     * Display a single Product.
     *
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        try {
            $data = Product::getActiveProductDetailsByID($id);

            if(empty($data)) {
                return ResponseBuilder::error(trans('global.no_product'),$this->badRequest);
            }

            $this->response = new ProductResource($data);
            return ResponseBuilder::success(trans('global.product'),$this->success,$this->response);

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
