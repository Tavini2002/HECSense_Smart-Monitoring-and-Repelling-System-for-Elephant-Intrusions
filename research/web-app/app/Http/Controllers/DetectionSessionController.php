<?php

namespace App\Http\Controllers;

use App\Models\DetectionSession;
use App\Models\Detection;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DetectionSessionController extends Controller
{
    public function index()
    {
        $sessions = DetectionSession::orderBy('started_at', 'desc')
            ->paginate(20);
        
        return view('sessions.index', compact('sessions'));
    }

    public function show($id)
    {
        $session = DetectionSession::with(['detections', 'alerts', 'zoneTransitions', 'statistics'])
            ->findOrFail($id);
        
        // Get statistics for this session
        $stats = [
            'total_detections' => $session->detections->count(),
            'calm_count' => $session->detections->where('behavior', 'calm')->count(),
            'warning_count' => $session->detections->where('behavior', 'warning')->count(),
            'aggressive_count' => $session->detections->where('behavior', 'aggressive')->count(),
            'total_alerts' => $session->alerts->count(),
            'unique_elephants' => $session->detections->unique('track_id')->count(),
            'avg_speed' => $session->detections->avg('speed_kmph'),
            'max_speed' => $session->detections->max('speed_kmph'),
        ];
        
        // Get detections grouped by track ID
        $detectionsByTrack = $session->detections()
            ->select('track_id', 
                DB::raw('count(*) as count'),
                DB::raw('min(detected_at) as first_seen'),
                DB::raw('max(detected_at) as last_seen'),
                DB::raw('avg(speed_kmph) as avg_speed'),
                DB::raw('max(speed_kmph) as max_speed')
            )
            ->groupBy('track_id')
            ->orderBy('count', 'desc')
            ->get();
        
        // Get timeline data (detections per minute) - limit to prevent long load times
        $timelineData = $session->detections()
            ->select(
                DB::raw('DATE_FORMAT(detected_at, "%Y-%m-%d %H:%i") as minute'),
                DB::raw('count(*) as count'),
                DB::raw('sum(case when behavior = "aggressive" then 1 else 0 end) as aggressive_count'),
                DB::raw('sum(case when behavior = "warning" then 1 else 0 end) as warning_count'),
                DB::raw('sum(case when behavior = "calm" then 1 else 0 end) as calm_count')
            )
            ->groupBy('minute')
            ->orderBy('minute')
            ->limit(100)
            ->get();
        
        return view('sessions.show', compact('session', 'stats', 'detectionsByTrack', 'timelineData'));
    }

    public function destroy($id)
    {
        $session = DetectionSession::findOrFail($id);
        $session->delete();
        
        return redirect()->route('sessions.index')
            ->with('success', 'Session deleted successfully.');
    }

    public function ajax(Request $request)
    {
        $sessions = DetectionSession::query();
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $sessions->where('status', $request->status);
        }
        
        if ($request->has('source_type') && $request->source_type != '') {
            $sessions->where('source_type', $request->source_type);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $sessions->whereDate('started_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $sessions->whereDate('started_at', '<=', $request->date_to);
        }
        
        return $sessions->orderBy('started_at', 'desc')->paginate(20);
    }

    public function getMobileSessions(Request $request)
    {
        $sessions = DetectionSession::select('id', 'session_name', 'source_type', 'status', 'started_at', 'ended_at', 'total_frames')
            ->orderBy('started_at', 'desc')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    public function getMobileSession($id)
    {
        $session = DetectionSession::findOrFail($id);
        
        // Get detections count for statistics (without loading all detections)
        $detectionsCount = Detection::where('session_id', $id)->count();
        $calmCount = Detection::where('session_id', $id)->where('behavior', 'calm')->count();
        $warningCount = Detection::where('session_id', $id)->where('behavior', 'warning')->count();
        $aggressiveCount = Detection::where('session_id', $id)->where('behavior', 'aggressive')->count();
        $alertsCount = Alert::where('session_id', $id)->count();
        
        // Get unique elephants count
        $uniqueElephants = Detection::where('session_id', $id)
            ->select(DB::raw('COUNT(DISTINCT track_id) as count'))
            ->value('count') ?? 0;
        
        // Get average and max speed
        $avgSpeed = Detection::where('session_id', $id)->avg('speed_kmph');
        $maxSpeed = Detection::where('session_id', $id)->max('speed_kmph');
        
        // Get statistics
        $stats = [
            'total_detections' => $detectionsCount,
            'calm_count' => $calmCount,
            'warning_count' => $warningCount,
            'aggressive_count' => $aggressiveCount,
            'total_alerts' => $alertsCount,
            'unique_elephants' => $uniqueElephants,
            'avg_speed' => $avgSpeed ? round($avgSpeed, 2) : 0,
            'max_speed' => $maxSpeed ? round($maxSpeed, 2) : 0,
        ];
        
        // Get detections grouped by track ID
        $detectionsByTrack = Detection::where('session_id', $id)
            ->select('track_id', 
                DB::raw('count(*) as count'),
                DB::raw('min(detected_at) as first_seen'),
                DB::raw('max(detected_at) as last_seen'),
                DB::raw('avg(speed_kmph) as avg_speed'),
                DB::raw('max(speed_kmph) as max_speed')
            )
            ->groupBy('track_id')
            ->orderBy('count', 'desc')
            ->get();
        
        // Get timeline data
        $timelineData = Detection::where('session_id', $id)
            ->select(
                DB::raw('DATE_FORMAT(detected_at, "%Y-%m-%d %H:%i") as minute'),
                DB::raw('count(*) as count'),
                DB::raw('sum(case when behavior = "aggressive" then 1 else 0 end) as aggressive_count'),
                DB::raw('sum(case when behavior = "warning" then 1 else 0 end) as warning_count'),
                DB::raw('sum(case when behavior = "calm" then 1 else 0 end) as calm_count')
            )
            ->groupBy('minute')
            ->orderBy('minute')
            ->limit(100)
            ->get();
        
        // Return only necessary session fields
        return response()->json([
            'success' => true,
            'data' => [
                'session' => [
                    'id' => $session->id,
                    'session_name' => $session->session_name,
                    'source_type' => $session->source_type,
                    'source_path' => $session->source_path,
                    'status' => $session->status,
                    'started_at' => $session->started_at ? $session->started_at->toIso8601String() : null,
                    'ended_at' => $session->ended_at ? $session->ended_at->toIso8601String() : null,
                    'total_frames' => $session->total_frames,
                    'created_at' => $session->created_at ? $session->created_at->toIso8601String() : null,
                    'updated_at' => $session->updated_at ? $session->updated_at->toIso8601String() : null,
                ],
                'stats' => $stats,
                'detections_by_track' => $detectionsByTrack,
                'timeline_data' => $timelineData,
            ]
        ]);
    }
}

