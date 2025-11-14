<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MobileUser;

class MobileUserController extends Controller
{
    public function showMobileUsers()
    {
        return view('mobile-users');
    }

    /**
     * DataTables AJAX: lists mobile users with search + pagination
     */
    public function processMobileUsersAjax(Request $request)
    {
        // Base totals (before filtering)
        $recordsTotal = DB::table('mobile_users')->count();

        // Build filtered query
        $query = DB::table('mobile_users');

        // DataTables global search param: 'search.value'
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');

            // Only columns that exist in your schema
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', $search . '%')
                  ->orWhere('email', 'like', $search . '%')
                  ->orWhere('phone_number', 'like', $search . '%')
                  ->orWhere('gender', 'like', $search . '%')
                  ->orWhere('status', 'like', $search . '%');
            });
        }

        // Count after filtering
        $recordsFiltered = $query->count();

        // Pagination (DataTables: 'start' = offset, 'length' = limit)
        $limit  = (int) $request->input('length', 10);
        $offset = (int) $request->input('start', 0);

        // Optional ordering (defaults to id DESC)
        $users = $query->orderBy('id', 'DESC')
                       ->skip($offset)
                       ->take($limit)
                       ->get();

        // Prepare rows (only existing fields)
        $data = [];
        foreach ($users as $u) {
            $data[] = [
                'id'          => $u->id,
                'full_name'   => $u->full_name,
                'email'       => $u->email,
                'phone_number'=> $u->phone_number,
                'gender'      => $u->gender,   // enum: male|female|other (nullable)
                'dob'         => $u->dob,
                'status'      => $u->status,   // enum: pending|approved
                'created_at'  => $u->created_at,
                'updated_at'  => $u->updated_at,
            ];
        }

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Approve a user
     */
    public function approveUser($id)
    {
        $user = DB::table('mobile_users')->where('id', $id)->first();

        if ($user && $user->status !== 'approved') {
            DB::table('mobile_users')->where('id', $id)->update(['status' => 'approved']);
            return response()->json(['success' => true, 'message' => 'User approved successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'User is already approved or does not exist.']);
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        $user = MobileUser::find($id);

        if ($user) {
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'User not found.']);
    }
}
