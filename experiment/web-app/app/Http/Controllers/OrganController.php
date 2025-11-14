<?php

namespace App\Http\Controllers;

use App\Models\Organ;
use App\Models\Message;
use App\Models\OrganRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class OrganController extends Controller
{
    function showOrgans(){

        return view("organ");
        
    }

    function showOrganRequests(){

        return view("organ-request");
    }

    public function processOrgansAjax(Request $request) {
        // Query to fetch organ data
        $query = Organ::query();
    
        // Search logic
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('organ_name', 'like', $searchValue . '%')
                  ->orWhere('donor_name', 'like', $searchValue . '%')
                  ->orWhere('blood_type', 'like', $searchValue . '%')
                  ->orWhere('organ_condition', 'like', $searchValue . '%')
                  ->orWhere('organ_type', 'like', $searchValue . '%')
                  ->orWhere('donor_age', 'like', $searchValue . '%')
                  ->orWhere('donor_gender', 'like', $searchValue . '%');
            });
        }
    
        // Get total count before pagination
        $total = $query->count();
    
        // Implement pagination
        $limit = (int) $request->input('length', 10); // Default to 10 if not provided
        $offset = (int) $request->input('start', 0); // Default to 0 if not provided
    
        $organs = $query->orderBy('id', 'DESC')
                        ->skip($offset)
                        ->take($limit)
                        ->get();
    
        // Prepare data for DataTables
        $data = [];
        foreach ($organs as $organ) {
            $data[] = [
                'id' => $organ->id,
                'organ_name' => $organ->organ_name,
                'blood_type' => $organ->blood_type,
                'donor_name' => $organ->donor_name,
                'donor_age' => $organ->donor_age,  // Added donor_age
                'donor_gender' => $organ->donor_gender, // Added donor_gender
                'organ_type' => $organ->organ_type,
                'organ_condition' => $organ->organ_condition,
            ];
        }
    
        // Prepare response for DataTables
        $response = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ];
    
        return response()->json($response);
    }
    
    
    public function addOrgan(Request $request) {

        // Validate incoming request
        $validatedData = $request->validate([
            'organ_name' => 'required|string|max:255',
            'blood_type' => 'required|string|max:3',
            'donor_name' => 'required|string|max:255',
            'donor_age' => 'required|integer|min:0',
            'donor_gender' => 'required|in:male,female',
            'organ_type' => 'required|string|max:255',
            'organ_condition' => 'required|string|in:Fresh,Stored,Preserved',
        ]);
    
        // Create a new organ record
        $organ = new Organ();
        $organ->organ_name = $request->input('organ_name');
        $organ->blood_type = $request->input('blood_type');
        $organ->donor_name = $request->input('donor_name');
        $organ->donor_age = $request->input('donor_age');
        $organ->donor_gender = $request->input('donor_gender');
        $organ->organ_type = $request->input('organ_type');
        $organ->organ_condition = $request->input('organ_condition');
    
        // Save the organ record
        $organ->save();
    
        // Optionally, return a response or redirect as needed
        return redirect()->route('show.organs', ['id' => $organ->id])->with('success', 'Organ added successfully.');
    }
    

    public function deleteOrgan($id) {
        $organ = Organ::find($id);
        
        if ($organ) {
            $organ->delete();
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false, 'message' => 'Organ not found']);
    }


    // Process the organ requests (for DataTables)
    public function processOrganRequestsAjax(Request $request)
    {
        // Fetch organ requests along with organ and user details
        $query = OrganRequest::with('organ', 'user'); // Assuming OrganRequest has relationships with Organ and User models

        // Search logic (if required)
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('organ', function ($subQuery) use ($searchValue) {
                    $subQuery->where('organ_name', 'like', '%' . $searchValue . '%');
                })->orWhereHas('user', function ($subQuery) use ($searchValue) {
                    $subQuery->where('full_name', 'like', '%' . $searchValue . '%')
                            ->orWhere('phone_number', 'like', '%' . $searchValue . '%');
                });
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Pagination setup
        $limit = (int) $request->input('length', 10); // Default to 10 if not provided
        $offset = (int) $request->input('start', 0); // Default to 0 if not provided

        // Fetch the paginated result
        $organRequests = $query->skip($offset)
                            ->take($limit)
                            ->orderBy('id', 'DESC')
                            ->get();

        // Prepare data for DataTables
        $data = [];
        foreach ($organRequests as $organRequest) {  // Renamed variable to $organRequest
            $data[] = [
                'id' => $organRequest->id,
                'organ_name' => $organRequest->organ->organ_name,
                'blood_type' => $organRequest->organ->blood_type,
                'requested_by' => $organRequest->user->full_name,
                'phone_number' => $organRequest->user->phone_number,
                'status' => $organRequest->status,
            ];
        }

        // Prepare response for DataTables
        $response = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ];

        return response()->json($response);
    }

    // Accept the organ request
    public function acceptOrganRequest($id)
    {
        $organRequest = OrganRequest::find($id);
        if ($organRequest) {
            $organRequest->status = 'approved';
            $organRequest->save();

            return response()->json(['success' => true, 'message' => 'Organ request accepted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Organ request not found.']);
    }

    // Reject the organ request
    public function rejectOrganRequest($id)
    {
        $organRequest = OrganRequest::find($id);
        if ($organRequest) {
            $organRequest->status = 'rejected';
            $organRequest->save();

            return response()->json(['success' => true, 'message' => 'Organ request rejected successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Organ request not found.']);
    }

    public function showMessages()
    {
        // Fetch messages ordered by id in descending order
        $messages = Message::with('user') // Assuming there is a relationship with User model
                            ->orderBy('id', 'desc')
                            ->get();
    
        // Return the view with the messages data
        return view('messages', ['messages' => $messages]);
    }

    public function processMessagesAjax(Request $request)
    {
        $query = Message::with('user'); // Fetch messages with user relationship
        
        // Optional search logic
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('organ_name', 'like', '%' . $searchValue . '%')
                  ->orWhere('blood_type', 'like', '%' . $searchValue . '%')
                  ->orWhere('message', 'like', '%' . $searchValue . '%')
                  ->orWhereHas('user', function($subQuery) use ($searchValue) {
                      $subQuery->where('full_name', 'like', '%' . $searchValue . '%');
                  });
            });
        }
    
        // Get total count before pagination
        $total = $query->count();
    
        // Pagination and ordering
        $limit = (int) $request->input('length', 10);
        $offset = (int) $request->input('start', 0);
    
        // Fetch the data
        $messages = $query->skip($offset)
                          ->take($limit)
                          ->orderBy('id', 'desc')
                          ->get();
    
        // Prepare data for DataTable
        $data = [];
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->id,
                'organ_name' => $message->organ_name,
                'blood_type' => $message->blood_type,
                'requested_by' => $message->user->full_name, // Full Name of the user
                'message' => $message->message,
            ];
        }
    
        // Return JSON response
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }
    
    
}
