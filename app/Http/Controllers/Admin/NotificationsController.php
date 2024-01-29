<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\UserDeviceToken;
use Auth;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $d['title'] = "Notifications";
        $d['buton_name'] = "Send Notification";
         $pagination=10;
        if(isset($_GET['paginate'])){
            $pagination=$_GET['paginate'];
        }
         $q=Notifications::select('*');
            if($request->search){
                $q->where('title', 'like', "%$request->search%");  
            }
             $d['notification']=$q->paginate($pagination)->withQueryString();
        
        return view('admin/notifications/index',$d);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $d['title'] = "Send Notification";
        return view('admin/notifications/add',$d);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $Notification = Notifications::updateOrCreate(['id' => $request->id], [
            'title'         => $request->title,
            'arab_title'         => $request->arab_title,
            'user_id'       => 0,
            'body'          => $request->body,
            'arab_body'          => $request->arab_body,
            'type'          => 'promotional',
        ]);
         if ($files    =    $request->file('image')) {
            $name    =    uniqid() . $files->getClientOriginalName();
            $url = url('notification-image/'.$name);
            $files->move('notification-image/', $name);
        }
        $Notification->update([
            'image' => $name ?? ''
        ]);
        //firebase token
        // $firebaseToken = ['c_RBk1QsSg2iYcxjTE5X_A:APA91bFIcqLao-hkc4xn5PWsslMp8Q-MTVuhluRnSLjnx4_nhMDkoRA0jEg-WTBfzaO3AdJ1kYkqgZNj1bthGa1YPFYpE9Ou8AhjextMxT-vmf70OoEByJXmkoPYNQk2e8Hv_ztabF-V'];
        $firebaseToken =UserDeviceToken::whereNotNull('device_id')
                    ->distinct('device_id')
                    ->pluck('device_id')
                    ->all();
        $SERVER_API_KEY = env('NOTIFICATION_SERVER_KEY');
        $this->sendNOtification($firebaseToken,$SERVER_API_KEY,$request->title,$request->body,$url);
        return redirect('/dashboard/notifications')->with('status', 'your data is updated');
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
        $notification = Notifications::findOrFail($id);
        $notification->delete();
        return back();
    }

    public function sendNOtification($firebaseToken,$SERVER_API_KEY,$title,$body,$url){
        
        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
                "image" =>$url,
               // "image" =>"https://ps.w.org/wp-notification-bell/assets/icon-256x256.png",
            ]
        ];
        $dataString = json_encode($data);
    

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
      
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        //dd($response);
    }
}

