<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Support\Language;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1], $request->remember)) {
            $locale = Language::resolve(Auth::user()->language_preference);
            Session::put('locale', $locale);
            Session::put('language', $locale);
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
