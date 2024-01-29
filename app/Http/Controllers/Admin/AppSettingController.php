<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class AppSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['data'] = Setting::pluck('value','key');


        return view('admin.app-setting.index',$data);
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
        // return $request;
        $request->validate([
            'app_version' => 'required',
            'maintenance_mode' => 'required',
            'force_update' => 'required',
        ]);

        $data = [
            'app_version'      => $request->app_version,
            'maintenance_mode' => $request->maintenance_mode,
            'force_update'     => $request->force_update,
            'business_id'      => $request->business_id,
            'public_key'       => $request->public_key,
            'secret_key'       => $request->secret_key,
            'api_Key'          => $request->api_Key,
            'test_token'       => $request->test_token,
            'mobile_number'    => $request->mobile_number ?? NULL,
            'landline_number'  => $request->landline_number ?? NULL,
            'support_email'    => $request->support_email ?? NULL,
            'whatsapp_number'  => $request->whatsapp_number ?? NULL,
            'payout_fee'  => $request->payout_fee ?? NULL,
            'cashout_fee'  => $request->cashout_fee ?? NULL,
            'cashin_fee'  => $request->cashin_fee ?? NULL,
            'service_fee'  => $request->service_fee ?? NULL,
            'bridgeCard_fee'  => $request->bridgeCard_fee ?? NULL,
            'cardCreation_fee'  => $request->cardCreation_fee ?? NULL,
            'bridgeCard_fxrate_fee' => $request->bridgeCard_fxrate_fee ?? NULL,
        ];

        foreach ($data as $key => $value) {
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
