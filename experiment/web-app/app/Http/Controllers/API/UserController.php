<?php

namespace App\Http\Controllers\API;

use App\Models\MobileUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function getProfile(Request $request)
    {
        // Retrieve user_id from query parameters directly
        $userId = $request->query('user_id');
    
        // Log the user ID to check if it's received correctly
        Log::info('User ID received from request:', ['userId' => $userId]);
    
        // Check if userId is available
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }
    
        // Fetch the user's profile data using the provided user_id
        $user = MobileUser::select('full_name', 'email', 'phone_number', 'blood_type', 'organ')
            ->where('id', $userId)
            ->first();
    
        if ($user) {
            return response()->json(['success' => true, 'data' => $user]);
        } else {
            Log::error('User not found in database.', ['userId' => $userId]);
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mobile_users,id',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        // Retrieve the user
        $user = MobileUser::find($request->user_id);

        // Verify the old password
        if ($user->password !== $request->old_password) {
            return response()->json(['success' => false, 'message' => 'Old password is incorrect.'], 400);
        }

        // Update to the new password
        $user->password = $request->new_password;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }
    
}
