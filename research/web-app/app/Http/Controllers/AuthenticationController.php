<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthenticationController extends Controller
{
    function index(){
        
        return view("index");
    
    }

    function login(Request $request){

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->route('show.dashboard');

        }

        return redirect()->back()->with([
            'error' => 'Invalid username or password; Please check your credentials and try again.',
        ]);


    }

    function logout(Request $request){

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("index");

    }
}
