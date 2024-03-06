<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserReferal;
use App\Models\Role;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Permission;
use App\Models\DriverProfile;
use App\Models\VirtualAccounts;
use App\Models\VendorProfile;
use App\Models\VendorAvailability;
use App\Models\AfricaVerification;
use App\Helper\Helper;
use App\Models\StaffPermissions;
use App\Models\UserAddress;
use App\Models\CardHolderDetails;
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
        } else {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('id', 1);
            });
        }

        $keyword = $request->input('keyword', '');
        $query->where(function ($query1) use ($keyword) {
            $query1->where('name', 'like', '%'.$keyword.'%')
            ->orWhere('fname', 'like', '%'.$keyword.'%')
            ->orWhere('lname', 'like', '%'.$keyword.'%')
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
        // $data['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
        // $data['range'] = Helper::deliveryRange();
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
            'fname'        => 'nullable | string',
            'lname'        => 'nullable | string',
            // 'email'        => 'nullable | email',
            // 'phone'        => 'required | integer | unique:users,phone,'.$request->id,
            // 'bvn'          => 'required | digits:11 | integer | unique:users,bvn,'.$request->id,
            'profileImage' => 'mimes:jpeg,png,jpg',
            'role'         => 'required | array',
        ];

        if(in_array(1,$request->role) || in_array(5,$request->role)) {
            if(isset($request->password)) {
                $rules['password'] = [' required_if:id,NULL', 'regex:/^.*(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%]).*$/', 'string', 'min:8'];
            }
        }
        $request->validate($rules);

        $imagePath = config('app.profile_image');

        $data = [
            'fname'         => ucfirst($request->fname),
            'lname'         => ucfirst($request->lname),
            // 'email'         => isset($request->email) ? $request->email : '',
            // 'phone'         => isset($request->phone) ? $request->phone : '',
            // 'dob'           => $request->dob,
            'password'      => isset($request->password) ? Hash::make($request->password) : '',
            'profile_image' => $request->hasfile('profileImage') ? Helper::storeImage($request->file('profileImage'), $imagePath, $request->profileImageOld) : (isset($request->profileImageOld) ? $request->profileImageOld : ''),
            // 'bvn'           => $request->bvn,
            'created_origin'=> 'ZIP admin',
            // 'latitude' => $request->latitude,
            // 'longitude' => $request->longitude,
            'referal_code' => Helper::generateReferCode(),
        ];
        // $verifitionData =  Helper::bvnVerification($request->bvn ,$data['email'] , $request->dob , $request->fname ,  $request->lname , $request->selfie);
        // $verifitionData =  json_decode($verifitionData,true);

        // if($verifitionData['verificationStatus'] == 'VERIFIED'){
        //   $data->verification_image  = $request->selfie;
        //   $data->is_africa_verifed  = $request->verificationStatus == 'VERIFIED' ? true : false ;
        //   $data->save();
        // }
    
        $users = User::where('id', $request->id)->first();

        if(!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $kycVerification = [
            'firstName' => $request->fname,
            'lastName' => $request->lname,
            'email' => $users->email,
            'bvn' => $users->bvn
        ];
        // dd($kycVerification);
        // $verifitionData =  Helper::fincraVerification(json_encode($kycVerification));
        // $verifitionData =  json_decode($verifitionData,true);
        // $verifitionData = $verifitionData['data']['accountNumber'];
        // // return $verifitionData;
        // $accountInformation = [
        //     'accountNumber'=>$verifitionData,
        //     'accountName' => $request->fname. $request->lname,
        //     'bankName' => 'PROVIDUS BANK',
        //     'reference' => 'b2a6277a-cbc9-4ab6-b1f4-46879f003cfb',
         
        // ];  

        $userData = User::updateOrCreate(['id' => $request->id,],$data);
        // $virtualAccounts = VirtualAccounts::create([
        //     'user_id'            => $userData->id,
        //     'accountType'        => 'individual',
        //     'currency'           => 'NGN',
        //     // 'business'           => '64529bd2bfdf28e7c18aa9da',
        //     'business'           => '645f9404bc81847c40e448a0',
        //     'business_id'        => '653a39a673cbd51d3d521db1',
        //     'accountNumber'      => $verifitionData,
        //     'creation_origin'    => $request->creation_origin ?? 'ZIP admin',
        //     'KYCInformation'     => json_encode($kycVerification),
        //     'accountInformation' => json_encode($accountInformation),
        // ]);
        // // $data->json_decode->KYCInformation;
        // // $data->json_decode->accountInformation;
        // // $jsonCode= json_encode($data);
        // $virtualAccounts->save();
       

        // if($verifitionData['verificationStatus'] == 'VERIFIED'){
        //   $data->verification_image  = $request->selfie;
        //   $data->is_africa_verifed  = $request->verificationStatus == 'VERIFIED' ? true : false ;
        //   $data->save();
        // }

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
        $data['data'] = User::where('id',$id)->first();
        $data['virtual'] = VirtualAccounts::where('user_id',$id)->first();
        $data['fincraUser'] = AfricaVerification::where('user_id',$id)->first();   
        $data['address'] = UserAddress::where('user_id',$id)->first();
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
        $data['data'] = User::where('id',$id)->first();
        $data['virtualAccount'] = json_decode(VirtualAccounts::where('user_id',$id)->first(),true);
        // $data['africaUser'] = AfricaVerification::where('user_id',$id)->first();
        // if(!$data['data']) {
        //     return redirect()->back()->with('error', 'Invalid User');
        // }
        // $data['range'] = Helper::deliveryRange();
        $data['roles'] = Role::all()->pluck('name', 'id');
        // $data['data']->load('roles');
        $data['banks'] = Bank::allActiveBanks();
        // $data['data']->staffPermissions;
        // $data['staffPermissionsData'] = $data['data']->staffPermissions->pluck('staff_permission')->toArray();
        // $data['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
        // $data['staffPermissonsArray']= $this->staffPermissonsArray();

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
            $cardData= CardHolderDetails::where('user_id',$id)->delete();
            $virtualAccount= VirtualAccounts::where('user_id',$id)->delete();
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
    public function fincraUser(Request $request){
        $query = VirtualAccounts::query();

        if (isset($request->keyword)) {
            $query->whereHas('users', function ($uname) use ($request) {
                $uname->where('fname', 'LIKE', '%' . $request->keyword . '%')
                ->orWhere('lname', 'LIKE', '%' . $request->keyword . '%');
            });
        }
        if($request->date_search){
            $data['date_search'] = $request->date_search;

            $query->wheredate('virtual_accounts.created_at', $data['date_search']);
        }
            if(isset($request->card_type)){
            $query->where('card_type', $request->card_type);
        }
            if(isset($request->creation_origin)){
            $query->where('creation_origin', $request->creation_origin);
        }
        if (isset($request->type)) {
            $query->where('transaction_type',$request->type);
        }
        $d['verifitionData'] =  Helper::fincraUsers();
        $d['verifitionData'] =  json_decode($d['verifitionData'],true);

        $d['usersNotFound'] = [];
        foreach($d['verifitionData']['data']['results'] as $key => $value) {
            $fincrauserEmail = (isset($value['KYCInformation']['email']) ? $value['KYCInformation']['email'] : '');
            $usersFound = User::where('email', $fincrauserEmail)->first(); 
            if(!$usersFound){
                $d['usersNotFound'][] = $value;
            }
        }
        $d['FincrausersNotFound'] = [];
        foreach($d['verifitionData']['data']['results'] as $key => $values) {
            $fincrauserEmails = (isset($values['KYCInformation']['email']) ? $values['KYCInformation']['email'] : '');
            $usersEmailFound = User::where('email',$fincrauserEmail)->get(); 
            if($usersEmailFound !=$fincrauserEmails){
            $d['FincrausersNotFound'] = $usersEmailFound;
            }
        }
      
        $item = isset($request->items) ? $request->items : 10;
        $d['data'] = $query->latest()->paginate($item);
        return view('admin.user.user-audit', $d);
    }
    public function getBeneficiariesApiData(Request $request){
        // $requestData = '64529bd2bfdf28e7c18aa9da';
        $requestData = '645f9404bc81847c40e448a0';
        $beneficiaries = Helper::fincrabeneficiaries($requestData,1,$request->items);
        $collection = json_decode($beneficiaries, true);
        $collection = $collection['data']['results'];
        $d['beneficiaries'] = $collection;
   
        return view('admin.user.fincra-beneficiaries', $d);
    }
    
}
