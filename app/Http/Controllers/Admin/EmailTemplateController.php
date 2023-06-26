<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $q = EmailTemplate::query();

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $q->where(function ($query) use ($data) {
                $query->where('from_name', 'like', '%'.$data['keyword'].'%')
                ->orwhere('from_email', 'like', '%'.$data['keyword'].'%')
                ->orwhere('email_category', 'like', '%'.$data['keyword'].'%')
                ->orwhere('email_subject', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->status){
            $data['status'] = $request->status;

            if($request->status == 'active'){
                $q->where('status', '=', 1);
            }
            else {
                $q->where('status', '=', 0);
            }
        }

        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $q->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.email-template.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.email-template.create');
        
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
            'from_name' => 'required | string',
            'from_email' => 'required | email',
            'email_category' => 'required',
            'email_subject' => 'required',
            'content' => 'required',
            'status' => 'required'
        ]);

        $data = EmailTemplate::updateOrCreate(
            [
                'id' => $request->id,
            ],
            [
                'from_name' => $request->from_name,
                'from_email' => $request->from_email,
                'email_category' => $request->email_category,
                'email_subject' => $request->email_subject,
                'email_content' => $request->content,
                'status' => $request->status,
            ]
        );

        if($data)
        {
            return redirect()->route('admin.email-templates.index');
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
        $data['data'] = EmailTemplate::where('id',$id)->first();

        return view('admin.email-template.show',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = EmailTemplate::where('id', $id)->first();
        return view('admin.email-template.create',$data);
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
            $data= EmailTemplate::where('id',$id)->first();
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
            $data= EmailTemplate::where('id',$id)->first();
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
