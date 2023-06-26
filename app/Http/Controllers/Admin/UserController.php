<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserReferal;
use App\Models\Role;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Permission;
use App\Models\DriverProfile;
use App\Models\VendorProfile;
use App\Models\VendorAvailability;
use App\Helper\Helper;
use App\Models\StaffPermissions;
use Validator;
use Hash;
use DB;
use Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::query();
        if(Auth::user()->roles->contains('5')){
            $query->where('id',Auth::user()->id);
        }

        $keyword = $request->input('keyword', '');
        $query->where(function ($query1) use ($keyword) {
            $query1->where('name', 'like', '%'.$keyword.'%')
            ->orwhere('email', 'like', '%'.$keyword.'%')
            ->orwhere('phone', 'like', '%'.$keyword.'%');
        });
        
        if(isset($request->role)){
            $requestRole = $request->role;
            $query->whereHas('roles', function($q) use ($requestRole)
                            {
                                $q->where('id', $requestRole);
                            });
        }
        
        if(isset($request->status)){
            $query->where('status', $request->status);
        }

        if(isset($request->items)){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }
        
        $data['roles'] = Role::pluck('name','id');
        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.user.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
        $data['range'] = Helper::deliveryRange();
        $data['roles'] = Role::all()->pluck('name', 'id');
        $data['banks'] = Bank::allActiveBanks();
        $data['staffPermissonsArray']= $this->staffPermissonsArray();
       
        return view('admin.user.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'nullable | string',
            'email' => 'nullable | email',
            'phone' => 'required | digits:10 | integer | unique:users,phone,'.$request->id,
            'profileImage' => 'mimes:jpeg,png,jpg',
            'role' => 'required | array',
        ];

        if(in_array(1,$request->role) || in_array(5,$request->role)) {
            if(isset($request->password)) {
                $rules['password'] = [' required_if:id,NULL', 'regex:/^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%]).*$/', 'string', 'min:8'];
            }
        }

        if(in_array(3,$request->role)) {
            $rules['dob'] = 'required';
            $rules['driverAadhar'] = 'required | unique:driver_profiles,aadhar_no,'.$request->driver_id;
            $rules['driverPanCard'] = 'required | unique:driver_profiles,pan_no,'.$request->driver_id;
            $rules['driverVehicle'] = 'required | unique:driver_profiles,vehicle_no,'.$request->driver_id;
            $rules['driverDrivingLicence'] = 'required | unique:driver_profiles,licence_no,'.$request->driver_id;
            $rules['driverStatement'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['driverPanImage'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['driverLicenceFront'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['driverLicenceBack'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['driverAadharFront'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['driverAadharBack'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
        }

        if(in_array(4,$request->role)) {
            $rules['aadharNumber'] = 'required | unique:vendor_profiles,aadhar_no,'.$request->vendor_id;
            $rules['panCardNumber'] = 'required | unique:vendor_profiles,pan_no,'.$request->vendor_id;
            $rules['deliveryRange'] = 'required';
            $rules['admin_commission'] = 'required';
            $rules['bankStatement'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['panCardImage'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['aadharCardFront'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['aadharCardBack'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['storeImage'] = 'required_if:id,NULL | mimes:jpeg,png,jpg';
            $rules['store_name'] = 'required | string';
            $rules['store_latitude'] = 'required | string';
            $rules['store_longitude'] = 'required | string';
            $rules['store_location'] = 'required | string';
            $rules['store_address'] = 'required | string';
        }

        $request->validate($rules);

        $imagePath = config('app.profile_image');

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => isset($request->password) ? Hash::make($request->password) : '',
            'profile_image' => $request->hasfile('profileImage') ? Helper::storeImage($request->file('profileImage'), $imagePath, $request->profileImageOld) : (isset($request->profileImageOld) ? $request->profileImageOld : ''),
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'referal_code' => Helper::generateReferCode(),
        ];

        if(in_array(3,$request->role)){
            $data['is_driver'] = 1;
            $data['is_driver_online'] = $request->driverMode ? $request->driverMode : 0;
            $data['as_driver_verified'] = $request->driverVerify ? $request->driverVerify : 0;
        }

        if(in_array(4,$request->role)) {
            $data['is_vendor'] = 1;
            $data['delivery_range'] = $request->deliveryRange ? $request->deliveryRange : 0;
            $data['is_vendor_online'] = $request->storeOpen ? $request->storeOpen : 0;
            $data['self_delivery'] = $request->self_delivery ? $request->self_delivery : 0;
            $data['admin_commission'] = $request->admin_commission ? $request->admin_commission : 0;
            $data['as_vendor_verified'] = $request->vendorVerify ? $request->vendorVerify : 0;
            $data['featured_store'] = $request->featured_store ? $request->featured_store : 0;
        }

        if(!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $userData = User::updateOrCreate(['id' => $request->id,],$data);

        if(($userData->wasChanged('as_driver_verified')) && ($request->driverVerify)){
            $data = trans('notifications.DRIVER_ACCOUNT_APPROVE');
            $userId = $userData->id;
            $title = 'Your account approved';
            $notification_type = 'account_approve_driver';
            Helper::pushNotification($data,$userId,$title, '', $notification_type);
        }

        if(($userData->wasChanged('as_vendor_verified')) && ($request->vendorVerify)){
            $data = trans('notifications.VENDOR_ACCOUNT_APPROVE');
            $userId = $userData->id;
            $title = 'Your account approved';
            $notification_type = 'account_approve_vendor';
            Helper::pushNotification($data,$userId,$title, '', $notification_type);
        }

        $user_role = $userData->roles()->sync($request->role);
       
        if(in_array(3,$request->role))
        {
            $imagePath = config('app.driver_document');

            $driverData = DriverProfile::updateOrCreate(
            [
                'user_id' => $request->id,
            ],
            [
                'user_id' => $userData->id,
                'dob' => $request->dob,
                'aadhar_no' => $request->driverAadhar,
                'pan_no' => $request->driverPanCard,
                'vehicle_no' => $request->driverVehicle,
                'licence_no' => $request->driverDrivingLicence,
                'bank_statement' => $request->hasfile('driverStatement') ? Helper::storeImage($request->file('driverStatement'), $imagePath, $request->driverStatementOld) : (isset($request->driverStatementOld) ? $request->driverStatementOld : ''),
                'pan_card_image' => $request->hasfile('driverPanImage') ? Helper::storeImage($request->file('driverPanImage'), $imagePath, $request->driverPanImageOld) : (isset($request->driverPanImageOld) ? $request->driverPanImageOld : ''),
                'licence_front_image' => $request->hasfile('driverLicenceFront') ? Helper::storeImage($request->file('driverLicenceFront'), $imagePath, $request->driverLicenceFrontOld) : (isset($request->driverLicenceFrontOld) ? $request->driverLicenceFrontOld : ''),
                'licence_back_image' => $request->hasfile('driverLicenceBack') ? Helper::storeImage($request->file('driverLicenceBack'), $imagePath, $request->driverLicenceBackOld) : (isset($request->driverLicenceBackOld) ? $request->driverLicenceBackOld : ''),
                'aadhar_front_image' => $request->hasfile('driverAadharFront') ? Helper::storeImage($request->file('driverAadharFront'), $imagePath, $request->driverAadharFrontOld) : (isset($request->driverAadharFrontOld) ? $request->driverAadharFrontOld : ''),
                'aadhar_back_image' => $request->hasfile('driverAadharBack') ? Helper::storeImage($request->file('driverAadharBack'), $imagePath, $request->driverAadharBackOld) : (isset($request->driverAadharBackOld) ? $request->driverAadharBackOld : ''),
                'status' => $request->driverVerify ? $request->driverVerify : 0,
            ]);
        }

        if(in_array(4,$request->role))
        {
            $imagePath = config('app.vendor_document');

            $vendorData = VendorProfile::updateOrCreate(
            [
                'user_id' => $request->id,
            ],
            [
                'user_id' => $userData->id,
                'aadhar_no' => $request->aadharNumber,
                'pan_no' => $request->panCardNumber,
                'store_name' => $request->store_name,
                'location' => $request->store_location,
                'address' => $request->store_address,
                'lat' => $request->store_latitude,
                'long' => $request->store_longitude,
                'store_image' => $request->hasfile('storeImage') ? Helper::storeImage($request->file('storeImage'), $imagePath, $request->storeImageOld) : (isset($request->storeImageOld) ? $request->storeImageOld : ''),
                'bank_statement' => $request->hasfile('bankStatement') ? Helper::storeImage($request->file('bankStatement'), $imagePath, $request->bankStatementOld) : (isset($request->bankStatementOld) ? $request->bankStatementOld : ''),
                'pan_card_image' => $request->hasfile('panCardImage') ? Helper::storeImage($request->file('panCardImage'), $imagePath, $request->panCardImageOld) : (isset($request->panCardImageOld) ? $request->panCardImageOld : ''),
                'aadhar_front_image' => $request->hasfile('aadharCardFront') ? Helper::storeImage($request->file('aadharCardFront'), $imagePath, $request->aadharCardFrontOld) : (isset($request->aadharCardFrontOld) ? $request->aadharCardFrontOld : ''),
                'aadhar_back_image' => $request->hasfile('aadharCardBack') ? Helper::storeImage($request->file('aadharCardBack'), $imagePath, $request->aadharCardBackOld) : (isset($request->aadharCardBackOld) ? $request->aadharCardBackOld : ''),
                'status' => $request->vendorVerify ? $request->vendorVerify : 0,
            ]);

            $data = array();
            
            for ($i=1; $i <= 7; $i++) { 
                $data[] = [
                    'week_day' => $i, 
                    'start_time' => !empty($request->start_time[$i]) ? $request->start_time[$i] : '09:00', 
                    'end_time' => !empty($request->end_time[$i]) ? $request->end_time[$i] : '17:00',
                    'status' => isset($request->weekday[$i]) ? $request->weekday[$i] : 0,
                ]
                + (!empty($request->id) ? ['user_id' => $request->id] : ['user_id' => $userData->id])
                + (!empty($request->vendor_available_id) ? ['id' => $request->vendor_available_id[$i-1]] : []);
            }

            VendorAvailability::upsert($data, ['id','user_id','week_day'],['start_time','end_time','status']);
        }

        if(in_array(3,$request->role) || in_array(4,$request->role)) {
            if((isset($request->bank)) && (isset($request->account_name)) && (isset($request->account_no)) && (isset($request->ifsc_code))) {
                $bankAccount = BankAccount::updateOrCreate(
                [
                    'id' => $request->bank_account_id,
                ],
                [
                    'bank_id' => $request->bank,
                    'user_id' => isset($request->id) ? $request->id : $userData->id,
                    'account_name' => $request->account_name,
                    'account_no' => $request->account_no,
                    'ifsc_code' => $request->ifsc_code,
                ]);
            }
        }

        if(in_array(5,$request->role)){
           /**Store staff permissions */
            if($request->staff_permissions){
                $getPermissions=StaffPermissions::where('user_id',$userData->id)->delete();
                foreach($request->staff_permissions as $item){
                    StaffPermissions::create([
                    'user_id'=> $userData->id,
                    'staff_permission'=>$item,
                    ]);
                }
           }
        }

        if($userData || $driverData || $vendorData) {
            return redirect()->route('admin.users.index');
        }
        else {
            return redirect()->back()->with('error', 'Something Went Wrong');
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
        $data['data'] = User::where('id',$id)->with('driver','vendor','vendor_availability')->first();
        if(!$data['data']) {
            return redirect()->back()->with('error', 'Invalid User');
        }
        $data['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
        return view('admin.user.show',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = User::where('id',$id)->with('driver','vendor','vendor_availability')->first();
        if(!$data['data']) {
            return redirect()->back()->with('error', 'Invalid User');
        }
        $data['range'] = Helper::deliveryRange();
        $data['roles'] = Role::all()->pluck('name', 'id');
        $data['data']->load('roles');
        $data['banks'] = Bank::allActiveBanks();
        $data['data']->staffPermissions;
        $data['staffPermissionsData'] = $data['data']->staffPermissions->pluck('staff_permission')->toArray();
        $data['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
        $data['staffPermissonsArray']= $this->staffPermissonsArray();

        return view('admin.user.create',$data);
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
            $data= User::where('id',$id)->delete();
            // $user_role = DB::table('role_user')->where('user_id',$id)->delete();
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

    public function changeStatus($id, Request $request)
    {
        try {
            $data= User::where('id',$id)->first();
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

    public function addFund($id, Request $request)
    {
        $data = User::where('id', $id)->first();
        if(!$data) {
            return redirect()->back()->with('error', 'Something Went Wrong!');
        }
        $data->wallet_balance += $request->amount;
        $data->save();
        return redirect()->route('admin.users.index');
    }

    public function revokeFund($id, Request $request)
    {
        $data = User::where('id', $id)->first();
        if(!$data) {
            return redirect()->back()->with('error', 'Something Went Wrong!');
        }
        $data->wallet_balance -= $request->amount;
        $data->save();
        return redirect()->route('admin.users.index');
    }

    /**
     * Display a listing of the User Referals.
     *
     * @return \Illuminate\Http\Response
     */
    public function userReferal(Request $request)
    {
        $query = UserReferal::query()->join('users', 'user_referals.user_id', '=', 'users.id')
                        ->join('users as referred_user', 'user_referals.referred_user_id', '=', 'referred_user.id')
                        ->select('user_referals.*', 'users.name as user_name', 'referred_user.name as referred_user_name', 'referred_user.referal_code as referred_user_code');
        
        if(isset($request->keyword)){
            $data['keyword'] = $request->keyword;
    
            $query->where(function ($query_new) use ($data) {
                $query_new->where('users.name', 'like', '%'.$data['keyword'].'%')
                ->orwhere('referred_user.name', 'like', '%'.$data['keyword'].'%')
                ->orwhere('referred_user.referal_code', 'like', '%'.$data['keyword'].'%');
            });
        }

        if(isset($request->items)){
            $data['items'] = $request->items;
        }
        else{
            $data['items'] = 10;
        }

        $data['data'] = $query->orderBy('created_at','DESC')->paginate($data['items']);

        return view('admin.user.user-referal', $data);
    }
}
