<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportQueries;
use App\Models\Awards;
use App\Models\UserAwards;

class SupportQueriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    $query = SupportQueries::query()
    ->join('users', 'support_queries.user_id', '=', 'users.id')
    ->select('support_queries.*', 'users.name', 'users.phone');

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where(function ($query_new) use ($data) {
                $query_new->where('fname', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->date_search){
            $data['date_search'] = $request->date_search;

            $query->wheredate('support_queries.created_at', $data['date_search']);
        }

        $data['items'] = $request->items ? $request->items : 10;

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);
            // return $data['data'];

        return view('admin.all-supports.supportqueries',$data);
    }

    // public function changeStatus($id, Request $request)
    // {
    //     try {
    //         $data= Transaction::where('id',$id)->first();
    //         // $data= Hsenotification::where('id',$id)->first();

    //         if($data) {
    //             $data->inspection_status = $data->inspection_status == 1 ? 0 : 1;
    //             $data->save();

    //             $vendor_awards =  Awards::where('id',3)->first();
    //             $awards = explode(',',$vendor_awards->vendor_id);
    //             if($data->inspection_status == true){
    //                 if(isset($data->user) && isset($data->user->vendor)) {
    //                     $awards[] = $data->user->vendor->id;
    //                 }
    //                 // $awards[] = $data->user_id;

    //                 $vendor_awards->vendor_id = implode(',',$awards);
    //                 $vendor_awards->save();
    //                 // $data->award_id = $awards->id;
    //                 // $userawards->save();
    //             }
    //             else {
    //                 if(isset($data->user) && isset($data->user->vendor)) {
    //                     if (($key = array_search($data->user->vendor->id, $awards)) !== false) {
    //                         unset($awards[$key]);
    //                     }
    //                 }
    //                 // if (($key = array_search($data->user_id, $awards)) !== false) {
    //                 //     unset($awards[$key]);
    //                 // }
    //                 $vendor_awards->vendor_id = implode(',',$awards);
    //                 $vendor_awards->save();
    //             }

    //             return response()->json(["success" => true, "status"=> $data->status]);
    //         }
    //         else {
    //             return response()->json(["success" => false]);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message'  => "Something went wrong, please try again!",
    //             'error_msg' => $e->getMessage(),
    //         ], 400);
    //     }
    // }
}
