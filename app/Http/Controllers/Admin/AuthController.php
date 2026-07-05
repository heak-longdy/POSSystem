<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1], $request->remember)) {
            Session::flash('status', true);
            return request()->returnUrl ? redirect()->to(request()->returnUrl) : redirect()->route('admin-dashboard');
        } else {
            return Redirect::back()->with('status', false);
        }
    }

    public function signOut()
    {
        Auth::logout();
        session()->forget('current_admin_login');
        return redirect()->route('admin-login');
    }
}
