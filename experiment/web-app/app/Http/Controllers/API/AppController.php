<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organ;
use App\Models\OrganRequest;
use Carbon\Carbon;
use App\Models\MobileUser;
use App\Models\Message;

class AppController extends Controller
{
    public function getOrgans()
    {
        $organs = Organ::select('id', 'organ_name', 'blood_type')->get();
        return response()->json($organs);
    }

    public function getOrganById($id)
    {
        // Fetch organ details by ID, excluding created_at and updated_at
        $organ = Organ::select(
            'id',
            'organ_name',
            'blood_type',
            'donor_name',
            'donor_age',
            'donor_gender',
            'organ_type',
            'organ_condition'
        )->find($id);

        if ($organ) {
            return response()->json(['success' => true, 'data' => $organ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Organ not found'], 404);
        }
    }

    public function requestOrgan(Request $request)
    {
        // Validate the input data
        $request->validate([
            'organ_id' => 'required|exists:organs,id',
            'user_id' => 'required|exists:mobile_users,id', // Adjust 'mobile_users' based on your user table name
        ]);

        try {
            // Create a new organ request with status 'pending'
            $organRequest = OrganRequest::create([
                'organ_id' => $request->input('organ_id'),
                'user_id' => $request->input('user_id'),
                'date' => Carbon::now()->toDateString(),
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organ request created successfully',
                'data' => $organRequest
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create organ request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkOrganRequest(Request $request)
    {
        // Validate the input data
        $request->validate([
            'organ_id' => 'required|exists:organs,id',
            'user_id' => 'required|exists:mobile_users,id', // Adjust based on your user table name
        ]);

        try {
            // Fetch the organ request with the given organ_id and user_id
            $organRequest = OrganRequest::where('organ_id', $request->input('organ_id'))
                                        ->where('user_id', $request->input('user_id'))
                                        ->first();

            if ($organRequest) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'status' => $organRequest->status, // Return the status of the request
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'status' => null,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check organ request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMatchingOrgans(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:mobile_users,id', // Validate that user_id exists in the database
        ]);
    
        try {
            // Fetch the user's blood type and organ based on user_id
            $user = MobileUser::find($validated['user_id']);
    
            if (!$user || !$user->blood_type || !$user->organ) {
                return response()->json(['error' => 'User blood type or organ not found'], 404);
            }
    
            // Query matching organs based on user's blood type and organ
            $matchingOrgans = Organ::where('blood_type', $user->blood_type)
                ->where('organ_name', $user->organ)
                ->get();
    
            return response()->json($matchingOrgans, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch matching organs'], 500);
        }
    }

    public function getUserRequests(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mobile_users,id',
        ]);

        try {
            $userRequests = OrganRequest::with('organ') // Load organ details
                ->where('user_id', $request->input('user_id'))
                ->get();

            return response()->json(['success' => true, 'data' => $userRequests], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch requests', 'error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mobile_users,id', // Ensure user exists
            'organ_name' => 'required|string',
            'blood_type' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            // Create the new message
            $message = Message::create([
                'user_id' => $request->input('user_id'),
                'organ_name' => $request->input('organ_name'),
                'blood_type' => $request->input('blood_type'),
                'message' => $request->input('message'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


}
