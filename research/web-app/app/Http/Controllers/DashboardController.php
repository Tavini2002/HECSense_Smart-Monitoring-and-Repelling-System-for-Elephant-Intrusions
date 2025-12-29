<?php

namespace App\Http\Controllers;

use App\Models\DetectionSession;
use App\Models\Detection;
use App\Models\Alert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        // Get overall statistics (optimized with single queries)
        $totalSessions = DetectionSession::count();
        $activeSessions = DetectionSession::where('status', 'running')->count();
        $totalDetections = Detection::count();
        $totalAlerts = Alert::count();
        
        // Get recent sessions (limit to 5 for faster load)
        $recentSessions = DetectionSession::select('id', 'session_name', 'source_type', 'status', 'started_at', 'ended_at', 'total_frames')
            ->orderBy('started_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get behavior statistics (last 30 days) - ensure all behaviors are present
        $behaviorStatsRaw = Detection::select('behavior', DB::raw('count(*) as count'))
            ->where('detected_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('behavior')
            ->pluck('count', 'behavior')
            ->toArray();
        
        // Ensure all behaviors are present with default 0
        $behaviorStats = [
            'calm' => $behaviorStatsRaw['calm'] ?? 0,
            'warning' => $behaviorStatsRaw['warning'] ?? 0,
            'aggressive' => $behaviorStatsRaw['aggressive'] ?? 0,
        ];
        
        // Get sessions per day (last 7 days) - ensure all days are present
        $sessionsPerDayRaw = DetectionSession::select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('started_at', '>=', Carbon::now()->subDays(7))
            ->whereNotNull('started_at')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        // Build array for all 7 days
        $sessionsPerDay = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $sessionsPerDay->push([
                'date' => Carbon::parse($date)->format('M d, Y'),
                'count' => $sessionsPerDayRaw[$date] ?? 0
            ]);
        }
        
        // Get detections per hour (last 24 hours) - ensure all hours are present
        $detectionsPerHourRaw = Detection::select(
                DB::raw('HOUR(detected_at) as hour'),
                DB::raw('count(*) as count')
            )
            ->where('detected_at', '>=', Carbon::now()->subDay())
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
        
        // Build array for all 24 hours
        $detectionsPerHour = collect();
        for ($i = 0; $i < 24; $i++) {
            $detectionsPerHour->push([
                'hour' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00',
                'count' => $detectionsPerHourRaw[$i] ?? 0
            ]);
        }
        
        // Get alert types distribution (last 30 days)
        $alertTypes = Alert::select('alert_type', DB::raw('count(*) as count'))
            ->where('triggered_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('alert_type')
            ->pluck('count', 'alert_type')
            ->toArray();
        
        return view('dashboard', compact(
            'totalSessions',
            'activeSessions',
            'totalDetections',
            'totalAlerts',
            'recentSessions',
            'behaviorStats',
            'sessionsPerDay',
            'detectionsPerHour',
            'alertTypes'
        ));
    }

    public function getMobileDashboardStats(Request $request)
    {
        // Get overall statistics
        $totalSessions = DetectionSession::count();
        $activeSessions = DetectionSession::where('status', 'running')->count();
        $totalDetections = Detection::count();
        $totalAlerts = Alert::count();
        
        // Get recent sessions (limit to 5)
        $recentSessions = DetectionSession::select('id', 'session_name', 'source_type', 'status', 'started_at', 'ended_at', 'total_frames')
            ->orderBy('started_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get behavior statistics (last 30 days)
        $behaviorStatsRaw = Detection::select('behavior', DB::raw('count(*) as count'))
            ->where('detected_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('behavior')
            ->pluck('count', 'behavior')
            ->toArray();
        
        $behaviorStats = [
            'calm' => $behaviorStatsRaw['calm'] ?? 0,
            'warning' => $behaviorStatsRaw['warning'] ?? 0,
            'aggressive' => $behaviorStatsRaw['aggressive'] ?? 0,
        ];
        
        // Get sessions per day (last 7 days)
        $sessionsPerDayRaw = DetectionSession::select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('started_at', '>=', Carbon::now()->subDays(7))
            ->whereNotNull('started_at')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        $sessionsPerDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $sessionsPerDay[] = [
                'date' => Carbon::parse($date)->format('M d, Y'),
                'count' => $sessionsPerDayRaw[$date] ?? 0
            ];
        }
        
        // Get detections per hour (last 24 hours)
        $detectionsPerHourRaw = Detection::select(
                DB::raw('HOUR(detected_at) as hour'),
                DB::raw('count(*) as count')
            )
            ->where('detected_at', '>=', Carbon::now()->subDay())
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
        
        $detectionsPerHour = [];
        for ($i = 0; $i < 24; $i++) {
            $detectionsPerHour[] = [
                'hour' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00',
                'count' => $detectionsPerHourRaw[$i] ?? 0
            ];
        }
        
        // Get alert types distribution (last 30 days)
        $alertTypes = Alert::select('alert_type', DB::raw('count(*) as count'))
            ->where('triggered_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('alert_type')
            ->pluck('count', 'alert_type')
            ->toArray();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_sessions' => $totalSessions,
                'active_sessions' => $activeSessions,
                'total_detections' => $totalDetections,
                'total_alerts' => $totalAlerts,
                'behavior_stats' => $behaviorStats,
                'sessions_per_day' => $sessionsPerDay,
                'detections_per_hour' => $detectionsPerHour,
                'alert_types' => $alertTypes,
                'recent_sessions' => $recentSessions,
            ]
        ]);
    }
}
