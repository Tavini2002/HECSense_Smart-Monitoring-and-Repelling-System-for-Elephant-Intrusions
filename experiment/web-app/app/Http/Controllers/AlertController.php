<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DetectionSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function getMobileAlerts(Request $request)
    {
        $query = Alert::select(
            'id',
            'session_id',
            'track_id',
            'alert_type',
            'message',
            'triggered_at',
            'distance_meters',
            'zone_name'
        );
        
        // Apply filters
        if ($request->has('session_id') && $request->session_id != '') {
            $query->where('session_id', $request->session_id);
        }
        
        if ($request->has('alert_type') && $request->alert_type != '') {
            $query->where('alert_type', $request->alert_type);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('triggered_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('triggered_at', '<=', $request->date_to);
        }

        // Load session name separately to avoid N+1
        $alerts = $query->orderBy('triggered_at', 'desc')->paginate(30);
        
        // Get session IDs and load sessions
        $sessionIds = $alerts->pluck('session_id')->unique();
        $sessions = DetectionSession::whereIn('id', $sessionIds)
            ->select('id', 'session_name', 'source_type')
            ->get()
            ->keyBy('id');
        
        // Format the response
        $formattedData = $alerts->map(function ($alert) use ($sessions) {
            $session = $sessions->get($alert->session_id);
            return [
                'id' => $alert->id,
                'session_id' => $alert->session_id,
                'session_name' => $session->session_name ?? 'Unknown Session',
                'track_id' => $alert->track_id,
                'alert_type' => $alert->alert_type,
                'message' => $alert->message,
                'triggered_at' => $alert->triggered_at ? $alert->triggered_at->toIso8601String() : null,
                'distance_meters' => $alert->distance_meters ? (float)$alert->distance_meters : null,
                'zone_name' => $alert->zone_name,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $formattedData,
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'per_page' => $alerts->perPage(),
                'total' => $alerts->total(),
                'next_page_url' => $alerts->nextPageUrl(),
                'prev_page_url' => $alerts->previousPageUrl(),
            ]
        ]);
    }

    public function getRecentAggressiveAlerts(Request $request)
    {
        // Get aggressive alerts from the last 60 seconds (increased window)
        $since = Carbon::now()->subSeconds(60);
        
        // Check for both 'alarm_sound' and any alert with aggressive-related message
        $alerts = Alert::where(function($query) {
                $query->where('alert_type', 'alarm_sound')
                      ->orWhere('alert_type', 'danger_tts')
                      ->orWhere(function($q) {
                          $q->where('message', 'like', '%Aggressive%')
                            ->orWhere('message', 'like', '%aggressive%');
                      });
            })
            ->where('triggered_at', '>=', $since)
            ->orderBy('triggered_at', 'desc')
            ->limit(5)
            ->get();
        
        if ($alerts->isEmpty()) {
            return response()->json([
                'success' => true,
                'has_aggressive_alert' => false,
                'data' => null
            ]);
        }
        
        // Get the most recent alert
        $mostRecent = $alerts->first();
        $session = DetectionSession::find($mostRecent->session_id);
        
        return response()->json([
            'success' => true,
            'has_aggressive_alert' => true,
            'data' => [
                'id' => $mostRecent->id,
                'session_id' => $mostRecent->session_id,
                'session_name' => $session->session_name ?? 'Unknown Session',
                'track_id' => $mostRecent->track_id,
                'message' => $mostRecent->message ?? 'Aggressive elephant detected - Alarm triggered',
                'triggered_at' => $mostRecent->triggered_at->toIso8601String(),
            ]
        ]);
    }
}

