<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\Page;
use App\Http\Resources\Admin\PageResource;

class PageController extends Controller
{
    /**
     * Pages API
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function pages(Request $request) {
        try {
            // Validation start
            $validSet = [
                'slug' => 'required'
            ]; 

            $isInValid = $this->isValidPayload($request, $validSet);
            if($isInValid){
                return ResponseBuilder::error($isInValid, $this->badRequest);
            }
            // Validation end

            $data = Page::getPageBySlug($request->slug);
            $this->response = new PageResource($data);
            
            return ResponseBuilder::successMessage(trans('global.PAGE_DATA'), $this->success, $this->response); 

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
