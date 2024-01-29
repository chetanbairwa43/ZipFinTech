<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Helper\ResponseBuilder;
use App\Http\Resources\Admin\FaqCollection;

class FaqController extends Controller
{
    public function list(Request $request) {
        try {
            $query = Faq::where('status',1);

            if($request->keyword){
                $data['keyword'] = $request->keyword;
                
                $query->where(function ($query_new) use ($data) {
                    $query_new->where('question', 'like', '%'.$data['keyword'].'%')
                    ->orwhere('answer', 'like', '%'.$data['keyword'].'%');
                });
            }

            $data = $query->orderBy('created_at','DESC')->get();

            $this->response = new FaqCollection($data);

            return ResponseBuilder::successMessage(trans('global.FAQ_LIST'), $this->success, $this->response); 

        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
