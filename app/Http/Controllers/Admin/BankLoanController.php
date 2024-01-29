<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BankLoan;

class BankLoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = BankLoan::query()->join('users', 'bank_loans.user_id', '=', 'users.id')->select('bank_loans.*', 'users.fname', 'users.lname');
        // ->join('users','bank_loans.user_id','=','users.id',);

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where('name', 'like', '%'.$data['keyword'].'%');
            // $query->where('users.fname', 'like', '%'.$data['keyword'].'%');
        }

         if(isset($request->is_approved)){
            $query->where('is_approved', $request->is_approved);
        }


        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        // $query->orderBy('created_at', 'DESC');
        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);
        // $data['data'] = $query->paginate($data['items']);
        return view('admin.bankloan.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // return dafadaf;
        $data['value'] = BankLoan::where('id',$id)->first();
        return view('admin.bankloan.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }
    public function loanStatusUpdate(Request $request)
    {
        $value  = BankLoan::findOrFail($request->id);
        $value->loan_amount        = $request->input('loan_amount');
        $value->loan_duration      = $request->input('loan_duration');
        $value->flat_fee           = $request->input('flat_fee');
        $value->fee_type           = $request->input('fee_type');
        $value->repayment_method   = $request->input('repayment_method');
        $value->repayment_date     = $request->input('repayment_date');
        $value->warning_date       = $request->input('warning_date');
        $value->collection_method  = $request->input('collection_method');
        $value->repayment_schedule = $request->input('repayment_schedule');
        $value->is_approved        = $request->input('is_approved');
        $value->save();
        return redirect()->back();
    }
}
