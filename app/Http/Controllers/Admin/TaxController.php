<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tax;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $q = Tax::query();

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $q->where('title', 'like', '%'.$data['keyword'].'%');
        }

        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $q->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.tax.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.tax.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required | string',
            'tax_percent' => 'required | numeric'
        ]);

        $data = Tax::updateOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'title' => $request->title,
                'tax_percent' => $request->tax_percent,
                'status' => $request->status
            ]
        );

        $result = $data->update();

        if($result)
        {
            return redirect()->route('admin.taxes.index');
        }
        else
        {
            return redirect()->back()->with('error', 'Something went Wrong, Please try again!');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = Tax::where('id', $id)->first();
        return view('admin.tax.create',$data);
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
    public function destroy($id)
    {
        try {
            $data= Tax::where('id',$id)->first();
            $result = $data->delete();
            if($result) {
                return response()->json(["success" => true]);
            }
            else {
                return response()->json(["success" => false]);
            }
        }  catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message'  => "Something went wrong, please try again!",
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Change the specified resource status from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id, Request $request)
    {
        try {
            $data= Tax::where('id',$id)->first();
            if($data) {
                $data->status = $data->status == 1 ? 0 : 1;
                $data->save();
                return response()->json(["success" => true, "status"=> $data->status]);
            }
            else {
                return response()->json(["success" => false]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message'  => "Something went wrong, please try again!",
                'error_msg' => $e->getMessage(),
            ], 400);
        }
    }
}
