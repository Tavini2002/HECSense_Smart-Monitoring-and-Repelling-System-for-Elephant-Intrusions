<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class SettingController extends Controller
{
    function showSettings(){

        $usernames = User::pluck('username');

        return view("settings", ['username' => $usernames]);
        
    }

    function updateUsername(Request $request){

        $request->validate([
            'username' => 'required|string|max:255',
        ]);
    
        $user = Auth::user();
        $user->username = $request->input('username');
        $user->save();
    
        // Log out the user
        Auth::logout();

        // Redirect to the login page with a success message
        return redirect()->route('index')->with('success', 'Username updated successfully. Please log in with your new username.');

    }

    function updatePassword(Request $request){
        
         // Validate the request
         $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8',
        ], [
            'new_password.confirmed' => 'The new password and confirmation password do not match.',
        ]);
        
    
        // Get the authenticated user
        $user = Auth::user();
    
        // Check if the current password is correct
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
    
        // Update the password
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // Log out the user
        Auth::logout();

        // Redirect to the login page with a success message
        return redirect()->route('index')->with('success', 'Password updated successfully. Please log in with your new password.');
        

    }
}
