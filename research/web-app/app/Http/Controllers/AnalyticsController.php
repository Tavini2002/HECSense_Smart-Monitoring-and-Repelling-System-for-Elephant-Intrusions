<?php

namespace App\Http\Controllers;

use App\Models\Detection;
use App\Models\DetectionSession;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('analytics.index');
    }

    public function getBehaviorChart(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);
        
        $data = Detection::select('behavior', DB::raw('count(*) as count'))
            ->where('detected_at', '>=', $startDate)
            ->groupBy('behavior')
            ->get();
        
        return response()->json($data);
    }

    public function getSpeedChart(Request $request)
    {
        $sessionId = $request->input('session_id');
        $trackId = $request->input('track_id');
        
        $query = Detection::select('detected_at', 'speed_kmph', 'behavior')
            ->orderBy('detected_at');
        
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }
        
        if ($trackId) {
            $query->where('track_id', $trackId);
        }
        
        $data = $query->limit(1000)->get();
        
        return response()->json($data);
    }

    public function getTimelineChart(Request $request)
    {
        $days = $request->input('days', 7);
        $startDate = Carbon::now()->subDays($days);
        
        $data = Detection::select(
                DB::raw('DATE(detected_at) as date'),
                DB::raw('count(*) as total'),
                DB::raw('sum(case when behavior = "aggressive" then 1 else 0 end) as aggressive'),
                DB::raw('sum(case when behavior = "warning" then 1 else 0 end) as warning'),
                DB::raw('sum(case when behavior = "calm" then 1 else 0 end) as calm')
            )
            ->where('detected_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json($data);
    }

    public function getAlertChart(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);
        
        $data = Alert::select('alert_type', DB::raw('count(*) as count'))
            ->where('triggered_at', '>=', $startDate)
            ->groupBy('alert_type')
            ->get();
        
        return response()->json($data);
    }

    public function getSessionsChart(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);
        
        $data = DetectionSession::select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('count(*) as count'),
                DB::raw('sum(case when status = "completed" then 1 else 0 end) as completed'),
                DB::raw('sum(case when status = "stopped" then 1 else 0 end) as stopped')
            )
            ->where('started_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json($data);
    }
}

