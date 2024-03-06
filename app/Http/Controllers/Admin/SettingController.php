<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Tax;
use App\Helper\Helper;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['data'] = Setting::getAllSettingData();

        return view('admin.site-setting.index',$data);
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
        $request->validate([
            'admin_mail' => 'required | email',
            'referal_amount' => 'required',
            'surcharge' => 'required',
            'packing_charge' => 'required',
            'delivery_charge_1km' => 'required',
            'delivery_charge_2km' => 'required',
            'delivery_charge_3km' => 'required',
            'delivery_charge_4km' => 'required',
            'delivery_charge_5km' => 'required',
            'delivery_charge_per_km' => 'required',
        ]);
        
        $imagePath = config('app.logo');

        $data[] = [
            'logo_1' => $request->hasfile('logo_1') ? Helper::storeImage($request->file('logo_1'),$imagePath) : (isset($request->logo_1_old) ? $request->logo_1_old : ''),
            'logo_2' => $request->hasfile('logo_2') ? Helper::storeImage($request->file('logo_2'),$imagePath) : (isset($request->logo_2_old) ? $request->logo_2_old : ''),
            'admin_mail' => $request->admin_mail,
            'referal_amount' => $request->referal_amount,
            'surcharge' => $request->surcharge,
            'packing_charge' => $request->packing_charge,
            'delivery_charge_1km' => $request->delivery_charge_1km,
            'delivery_charge_2km' => $request->delivery_charge_2km,
            'delivery_charge_3km' => $request->delivery_charge_3km,
            'delivery_charge_4km' => $request->delivery_charge_4km,
            'delivery_charge_5km' => $request->delivery_charge_5km,
            'delivery_charge_per_km' => $request->delivery_charge_per_km,
            'tip_is_tax_free' => $request->tip_is_tax_free ? $request->tip_is_tax_free : 0,
            'cod' => $request->cod ? $request->cod : 0,
            'min_order_value' => $request->min_order_value,
        ];

        foreach ($data[0] as $key => $value) {
            Setting::updateOrCreate(
                [
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        return redirect()->back();
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

  
}
