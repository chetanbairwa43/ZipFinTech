<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class DeleteUserController extends Controller
{
    public function userDelete()
    {
        return view('auth.userdelete');
    }

    public function deleteUser(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
    
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
             
            $user->delete();
    
            // return redirect()->back()->withSuccess('User Account is Deleted Successfully!!!');
            return redirect()->back()->with('success', 'User Account is Deleted Successfully!!!');
        } else {
            // return redirect()->back()->withSuccess('Invalid credentials or user not found');
            return redirect()->back()->with('error', 'Invalid credentials or user not found');
        }
    }
    
}
