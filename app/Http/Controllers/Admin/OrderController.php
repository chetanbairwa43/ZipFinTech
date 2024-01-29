<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Helper;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSignUp;
use App\Mail\OrderMail;
use App\Models\EmailTemplate;
use App\Models\OrderNote;
use App\Models\Setting;
use App\Models\OrderDetail;
use PDF;
use Storage;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Order::query()->join('users', 'orders.user_id', '=', 'users.id')
                        ->join('users as vendor', 'orders.vendor_id', '=', 'vendor.id')
                        ->select('orders.*', 'users.name as user_name', 'vendor.name as vendor_name');

        if($request->keyword){
            $data['keyword'] = $request->keyword;

            $query->where(function ($query_new) use ($data) {
                $query_new->where('users.name', 'like', '%'.$data['keyword'].'%')
                ->orwhere('vendor.name', 'like', '%'.$data['keyword'].'%');
            });
        }

        if($request->status){
            $data['status'] = $request->status;

            $query->where('orders.status', $request->status);
        }

        if($request->items){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);
        $data['orderStatus'] = Helper::orderStatus();

        return view('admin.order.index',$data);
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
        $data['data'] = Order::where('id', $id)->with('orderItem','orderItem.products','coupon')->first();
        $data['orderStatus'] = Helper::orderStatus();
        return view('admin.order.show',$data);
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

    /**
     * Change Order Status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeOrderStatus($id, Request $request)
    {
        $data = Order::findOrFail($id);
        $data->status = $request->order_status;
        $result = $data->save();

        $result1 = $this->createOrderLog($id, $request->order_status, $request->note);
            
        $order_detail = OrderDetail::where('order_id', $id)->get();
        $order_items = '';
        foreach ($order_detail as $items) {
        $order_items .= '<tr style="border-collapse: collapse;border-bottom: 1px solid #eaedf1; "><td><h6 style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53; margin: 10px 0px;">' . $items->products->name . ' </h6></td>
                            <td><h6 align="center" style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53; margin: 10px 0px; align: center;">' . $items->item_qty .' x '. $items->qty. ' </h6></td>
                            <td><h6 align="center" style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53; margin: 10px 0px; align: center;">₹ ' . $items->price . ' </h6></td>
                            <td><h6 align="right" style="font-size: 15px; font-family: \'Raleway\', sans-serif; font-weight: 400; color:#4c4c53;  align: right; margin: 10px 0px;">₹ ' . $items->price * $items->qty . '</h6></td>
                        </tr>';
        }
        $mail_category = Helper::orderStatus()[$request->order_status];

        if($mail_category != 'Order Placed') {
            $setting_data = Setting::getAllSettingData();
            $img = url('/'.config('app.logo').'/'.$setting_data['logo_1']);
            $mail_data = EmailTemplate::getMailByMailCategory(strtolower($mail_category));

            if(isset($mail_data)) {
                if((strtolower($mail_category) == 'accepted') || (strtolower($mail_category) == 'reject') || (strtolower($mail_category) == 'pickup') || (strtolower($mail_category) == 'return request')) {
                    $arr1 = array('{image}', '{order_number}', '{order_date}', '{products_list}', '{sub_total}', '{surcharge}', '{tax}', '{coupon_code}', '{coupon}', '{delivery_charge}', '{packing_fee}', '{tip_amount}', '{payment_mode}', '{grand_total}', '{shipping_address}', '{phone}', '{email}');
                    $arr2 = array($img, $id, $data->created_at->format('d F Y'), $order_items, $data->item_total, $data->surcharge, $data->tax, isset($data->coupon->coupon_code) ? $data->coupon->coupon_code : '', isset($data->coupon->discounted_price) ? $data->coupon->discounted_price : '', $data->delivery_charges, $data->packing_fee, $data->tip_amount, isset($data->order_type) ? ($data->order_type == 'O' ? 'Online' : ($data->order_type == 'C' ? 'COD' : '')) : '', $data->grand_total, '', $data->user->phone, $data->user->email);

                    $msg = $mail_data->email_content;
                    $msg = str_replace($arr1, $arr2, $msg);
                }
                if(strtolower($mail_category) == 'delivered') {
                    $arr1 = array('{image}', '{name}');
                    $arr2 = array($img, $data->user->name);

                    $msg = $mail_data->email_content;
                    $msg = str_replace($arr1, $arr2, $msg);
                }
                if(strtolower($mail_category) == 'refund') {
                    $arr1 = array('{image}', '{order_number}', '{order_date}');
                    $arr2 = array($img, $id, $data->created_at->format('d F Y'));

                    $msg = $mail_data->email_content;
                    $msg = str_replace($arr1, $arr2, $msg);
                }

                $config = ['from_email' => isset($mail_data->from_email) ? $mail_data->from_email : env('MAIL_FROM_ADDRESS'),
                    'name' => isset($mail_data->from_email) ? $mail_data->from_email : env('MAIL_FROM_NAME'),
                    'subject' => $mail_data->email_subject, 
                    'message' => $msg,
                ];

                Mail::to($setting_data['admin_mail'])->send(new OrderMail($config));
            }
        }

        return redirect()->back();
    }

    /**
     * Create and Download Invoice PDF
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice($id)
    {
        $data['data'] = Order::where('id', $id)->with('orderItem','orderItem.products','coupon')->first();
        $logo = Setting::getDataByKey('logo_1');
        $data['logo'] = config('app.logo').'/'.$logo->value;
        $pdf = PDF::loadView('invoice/order-invoice', $data);
        $filename = 'invoice-'.$id.'-'.time().'.pdf';
        Storage::put('orderInvoice/'.$filename, $pdf->output());
        return $pdf->download($filename);
    }
}
