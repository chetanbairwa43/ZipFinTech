<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Webhook;
use App\Models\WebhookDetails;
use Auth;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user = Auth::user()->first();
        // $user = User::create([
        //     'user_id' => Auth::user()->id,
        //     'business' => $request->business,
        //     'virtualAccount' => $request->virtualAccount,
        //     'sessionId' => $request->sessionId,
        //     'senderAccountName' => $request->senderAccountName,
        //     'senderAccountNumber' => $request->senderAccountNumber,
        //     'senderBankName'    => $request->senderBankName,
        //     'sourceCurrency'    => $request->sourceCurrency,
        //     'sourceAmount'  => $request->sourceAmount,
        //     'description'   => $request->description,
        //     'amountReceived' => $request->amountReceived,
        //     'fee' => $request->fee,
        //     'customerName' => $request->customerName,
        //     'settlementDestination' => $request->settlementDestination,
        //     'status' => $request->status,
        //     'initiatedAt' => $request->initiatedAt,
        //     'reference' => $request->reference
        // ]);
        return view('admin.webhook.pay');
    }

    public function handle(Request $request)
    {
        // Handle the incoming webhook data
        // $data = $request->all();

        $webData = WebhookDetails::create([
            'user_id' => 1,
            // 'user_id' => Auth::user()->id,
            'trans_response' => 12312
            // 'trans_response' => json_encode($data)
        ]);
        // $webData->save();
        
        // Example: Log the data
        // \Log::info('Webhook received:', $data);

        // Return a response (if required)
        return response()->json(['message' => 'Webhook received'], 200);
    }
    
}
