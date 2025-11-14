<?php

namespace App\Http\Controllers\API;

use App\Models\MobileUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1) Validate only the fields you actually use
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:mobile_users,email',
            'password'     => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'gender'       => 'required|string|in:Male,Female',
            'dob'          => 'required|date',
            // removed: blood_type, organ, allergies
        ]);

        // 2) Create the user (hash the password)
        $user = MobileUser::create([
            'full_name'    => $validated['full_name'],
            'email'        => $validated['email'],
            'password'     => $validated['password'],
            'phone_number' => $validated['phone_number'] ?? null,
            'gender'       => $validated['gender'],
            'dob'          => $validated['dob'],
            'status'       => 'pending',
        ]);

        // 3) Return success
        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }


    public function login(Request $request)
    {
        // Validate request data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
    
        // Attempt to find the user by email
        $user = MobileUser::where('email', $request->email)->first();
    
        // Check if the user exists and the plain-text password matches
        if ($user && $request->password === $user->password) {
            // Check if user status is approved
            if ($user->status !== 'approved') {
                return response()->json([
                    'message' => 'Your account is pending approval',
                    'status' => 'pending',
                ], 200);
            }
    
            // Password matches and user is approved; return success with selected user details
            return response()->json([
                'message' => 'Login successful',
                'status' => 'approved',
                'data' => [
                    'id' => $user->id, // Include the user ID in the response
                    'email' => $user->email, // Include email
                    'full_name' => $user->full_name, // Include full name
                ],
            ], 200);
        }
    
        // Invalid credentials
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
   
}
