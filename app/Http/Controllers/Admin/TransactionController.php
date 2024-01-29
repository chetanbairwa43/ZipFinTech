<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\RequestMoney;
use App\Models\User;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $item = isset($request->items) ? $request->items : 10;
        $query = Transaction::with('user');

        if (isset($request->keyword)) {
            $query->whereHas('user', function ($uname) use ($request) {
                $uname->where('fname', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('lname', 'LIKE', '%' . $request->keyword . '%');
            });
        }
        if (isset($request->type)) {
            $query->where('transaction_type',$request->type);
        }

        $d['data'] = $query->latest()->paginate($item);

        return view('admin.transaction.index', $d);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $d['data']  = Transaction::where('id',$id)->first();
        return view('admin.transaction.show',$d);
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
    public function tDelete($id)
    {
        try {
            $data= Transaction::where('id',$id)->delete();
            if($data) {
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

    public function customerBalance(Request $request) {
        $items = isset($request->items)?$request->items : 10;
        $query =  User::with('transactions')->where('otp_verified',1);

        if(isset($request->keyword)){
            $query->where('fname','like',"%".$request->keyword."%")->orWhere('lname','like',"%".$request->keyword."%");
        }

        $d['data'] = $query->latest()->paginate($items);
        return view('admin.transaction.customerbalance',$d);
    }

    public function singleUserTransaction(Request $request,$id) {
        $items = isset($request->items)?$request->items : 10;
        try {
            $query = Transaction::where('user_id',$id);
            if(isset($request->type)){
                $query->where('transaction_type',$request->type);
            }
            $d['data'] = $query->latest()->paginate($items);
            $d['user'] = User::with('transactions')->where('id',$id)->first();
            return view('admin.transaction.single-user-transaction',$d);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function transactionUtilities(Request $request){

        $item = isset($request->items) ? $request->items : 10;

        $query = Transaction::with('user');
        if(isset($request->keyword)){
            $query->where(function($q) use($request) {
                $q->whereHas('user', function($subQuery) use($request) {
                    $subQuery->where('fname','LIKE',"%".$request->keyword."%")
                        ->orWhere('lname','LIKE',"%".$request->keyword."%")
                        ->orWhere('name','LIKE',"%".$request->keyword."%");
                });
            });
        }

        $data = $query->latest()->get();

        $filteredData = [
            'ebill' => $data->filter(fn ($q) => $q->transaction_about === 'Buy Electricity'),
            'buy_inter' => $data->filter(fn ($q) => $q->transaction_about === 'Buy Internet Data'),
            'buy_phon' => $data->filter(fn ($q) => $q->transaction_about === 'Buy Airtime'),
            'bill_pay' => $data->filter(fn ($q) => $q->transaction_about === 'Pay Bills'),
        ];
        foreach ($filteredData as $key => $filteredSet) {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $offset = ($currentPage - 1) * $item;

            $d[$key] = new LengthAwarePaginator(
                $filteredSet->slice($offset, $item),
                $filteredSet->count(),
                $item,
                $currentPage
            );
        }
        return view('admin.transaction.utilities-transaction', $d);
    }

    public function sendingMoney(Request $request){
        $item = isset($request->items) ? $request->items : 10;

        $query = Transaction::with('user','sender','receiver');
        if(isset($request->keyword)){
            $query->where(function($q) use($request) {
                $q->whereHas('user', function($subQuery) use($request) {
                    $subQuery->where('fname','LIKE',"%".$request->keyword."%")
                        ->orWhere('lname','LIKE',"%".$request->keyword."%")
                        ->orWhere('name','LIKE',"%".$request->keyword."%");
                });
            });
        }

        $data = $query->latest()->get();
        $filteredData = [
            'bill_pay' => $data->filter(fn ($q) => $q->user_type === 'otherusers'),
            'ebill' => $data->filter(fn ($q) => $q->user_type === 'ziptozip'),
        ];
        foreach ($filteredData as $key => $filteredSet) {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $offset = ($currentPage - 1) * $item;

            $d[$key] = new LengthAwarePaginator(
                $filteredSet->slice($offset, $item),
                $filteredSet->count(),
                $item,
                $currentPage
            );
        }

        return view('admin.transaction.sending-money', $d);
    }

    public function requestMoney(Request $request){

        $item = isset($request->items) ? $request->items : 10;

        $query = RequestMoney::with('payer', 'moneyResciever');

        if(isset($request->keyword)){
            $query->where(function($q) use($request) {
                $q->whereHas('payer', function($subQuery) use($request) {
                    $subQuery->where('fname','LIKE',"%".$request->keyword."%")
                        ->orWhere('lname','LIKE',"%".$request->keyword."%")
                        ->orWhere('name','LIKE',"%".$request->keyword."%");
                })
                ->orWhereHas('moneyResciever', function($subQuery) use($request) {
                    $subQuery->where('fname','LIKE',"%".$request->keyword."%")
                        ->orWhere('lname','LIKE',"%".$request->keyword."%")
                        ->orWhere('name','LIKE',"%".$request->keyword."%");
                });
            });
        }

        $data = $query->latest()->get();

        $filteredData = [
            'ztozreqest' => $data->filter(fn ($q) => $q->request_type === 'ziptozip'),
            'otherusers' => $data->filter(fn ($q) => $q->request_type === 'otherusers'),
        ];

        $d = [];
        foreach ($filteredData as $key => $filteredSet) {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $offset = ($currentPage - 1) * $item;

            $d[$key] = new LengthAwarePaginator(
                $filteredSet->slice($offset, $item),
                $filteredSet->count(),
                $item,
                $currentPage
            );
        }

        return view('admin.transaction.request-money', $d);

    }

    public function addBalance($id)
    {
        $data['data'] = User::where('id',$id)->first();
        return view('admin.add-balance.create',$data);
    }

     public function customerUpdateBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required',
            'transaction_type' => 'required',
            'transaction_about' => 'required'
        ]);

        $user = User::where('id',$id)->first();

        $data = Transaction::create(
            [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'phone'  => $user->phone,
                't_id'   => $user->unique_id,
                'transaction_type' => $request->transaction_type,
                // 'currency' => $request->currency,
                'transaction_about' => $request->transaction_about,
            ]
        );


        if($data)
        {
            return redirect()->route('admin.customer-balance');
        }
        else
        {
            return redirect()->back()->with('error', 'Something went Wrong, Please try again!');
        }
    }


}
