<?php

namespace App\Http\Controllers;

use App\Models\Detection;
use App\Models\DetectionSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Detection::with('session');
        
        // Apply filters
        if ($request->has('session_id') && $request->session_id != '') {
            $query->where('session_id', $request->session_id);
        }
        
        if ($request->has('behavior') && $request->behavior != '') {
            $query->where('behavior', $request->behavior);
        }
        
        if ($request->has('track_id') && $request->track_id != '') {
            $query->where('track_id', $request->track_id);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }
        
        $detections = $query->orderBy('detected_at', 'desc')->paginate(50);
        $sessions = DetectionSession::orderBy('started_at', 'desc')->get();
        
        return view('detections.index', compact('detections', 'sessions'));
    }

    public function show($id)
    {
        $detection = Detection::with(['session'])->findOrFail($id);
        
        // Get other detections from the same track in the same session
        $trackDetections = Detection::where('session_id', $detection->session_id)
            ->where('track_id', $detection->track_id)
            ->orderBy('frame_number')
            ->get();
        
        return view('detections.show', compact('detection', 'trackDetections'));
    }

    public function ajax(Request $request)
    {
        $query = Detection::with('session');
        
        // Apply filters
        if ($request->has('session_id') && $request->session_id != '') {
            $query->where('session_id', $request->session_id);
        }
        
        if ($request->has('behavior') && $request->behavior != '') {
            $query->where('behavior', $request->behavior);
        }
        
        if ($request->has('track_id') && $request->track_id != '') {
            $query->where('track_id', $request->track_id);
        }
        
        return $query->orderBy('detected_at', 'desc')->paginate(50);
    }

    public function getMobileDetections(Request $request)
    {
        $query = Detection::select(
            'id',
            'session_id',
            'track_id',
            'frame_number',
            'detected_at',
            'confidence',
            'behavior',
            'speed_kmph',
            'aggression_score',
            'alert_triggered',
            'alert_type'
        );
        
        // Apply filters
        if ($request->has('session_id') && $request->session_id != '') {
            $query->where('session_id', $request->session_id);
        }
        
        if ($request->has('behavior') && $request->behavior != '') {
            $query->where('behavior', $request->behavior);
        }
        
        if ($request->has('track_id') && $request->track_id != '') {
            $query->where('track_id', $request->track_id);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('detected_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('detected_at', '<=', $request->date_to);
        }

        // Load session name separately to avoid N+1
        $detections = $query->orderBy('detected_at', 'desc')->paginate(30);
        
        // Get session IDs and load sessions
        $sessionIds = $detections->pluck('session_id')->unique();
        $sessions = DetectionSession::whereIn('id', $sessionIds)
            ->select('id', 'session_name', 'source_type')
            ->get()
            ->keyBy('id');
        
        // Format the response
        $formattedData = $detections->map(function ($detection) use ($sessions) {
            $session = $sessions->get($detection->session_id);
            return [
                'id' => $detection->id,
                'session_id' => $detection->session_id,
                'session_name' => $session->session_name ?? 'Unknown Session',
                'track_id' => $detection->track_id,
                'frame_number' => $detection->frame_number,
                'detected_at' => $detection->detected_at ? $detection->detected_at->toIso8601String() : null,
                'confidence' => $detection->confidence ? (float)$detection->confidence : 0,
                'behavior' => $detection->behavior,
                'speed_kmph' => $detection->speed_kmph ? (float)$detection->speed_kmph : 0,
                'aggression_score' => $detection->aggression_score ? (float)$detection->aggression_score : 0,
                'alert_triggered' => $detection->alert_triggered ?? false,
                'alert_type' => $detection->alert_type,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $formattedData,
                'current_page' => $detections->currentPage(),
                'last_page' => $detections->lastPage(),
                'per_page' => $detections->perPage(),
                'total' => $detections->total(),
                'next_page_url' => $detections->nextPageUrl(),
                'prev_page_url' => $detections->previousPageUrl(),
            ]
        ]);
    }
}

