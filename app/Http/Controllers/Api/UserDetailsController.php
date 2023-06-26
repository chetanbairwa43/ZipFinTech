<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Helper\ResponseBuilder;

class UserDetailsController extends Controller
{
    // public function asdaf()
    // {
    //     echo "Hello world";
    // }
    
    public function details(Request $request)
    {
        $today = Carbon::now()->format('Y/m/d');
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required | email | unique:users',
            'phone' => 'required | digits:10 | integer',
            'username' => 'required | unique:users',
            'date_of_birth' => 'before:'.$today.'| date_format:Y/m/d',
        ]);
        try{
            if($validate->fails()) {
                return ResponseBuilder::error($validate->errors()->first(), $this->badRequest);
            }
            
            if(User::where('email',$request->email && 'phone',$request->phone)->first())
            {
                return response([
                    'message'=>'User already exists',
                    'status'=>'failed'
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'purpose_Category' => $request->purpose_Category,
                'date_of_birth' => $request->date_of_birth,
                'username' => $request->username,
                'residential_address' => $request->residential_address,
            ]);
            return ResponseBuilder::successMessage('User Details', $this->success, $user);
        }catch(exception $e){
            return ResponseBuilder::error($e->Message(), $this->badRequest);
        }
    }
}