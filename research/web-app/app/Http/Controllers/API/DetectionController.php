<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DetectionSession;
use App\Models\Detection;
use App\Models\Alert;
use App\Models\ZoneTransition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DetectionController extends Controller
{
    /**
     * Create or update a detection session
     */
    public function createSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_name' => 'nullable|string|max:255',
            'source_type' => 'required|in:video_upload,camera,stream',
            'source_path' => 'nullable|string|max:500',
            'confidence_threshold' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $session = DetectionSession::create([
            'session_name' => $request->session_name ?? 'Session ' . Carbon::now()->format('Y-m-d H:i:s'),
            'source_type' => $request->source_type,
            'source_path' => $request->source_path,
            'started_at' => Carbon::now(),
            'status' => 'running',
            'confidence_threshold' => $request->confidence_threshold ?? 0.10,
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'message' => 'Session created successfully'
        ], 201);
    }

    /**
     * Update session status (end/stop session)
     */
    public function updateSession(Request $request, $id)
    {
        $session = DetectionSession::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:running,completed,stopped,error',
            'total_frames' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $updateData = [];
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
            if ($request->status == 'completed' || $request->status == 'stopped' || $request->status == 'error') {
                $updateData['ended_at'] = Carbon::now();
            }
        }
        
        if ($request->has('total_frames')) {
            $updateData['total_frames'] = $request->total_frames;
        }
        
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        $session->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Session updated successfully'
        ]);
    }

    /**
     * Store a detection
     */
    public function storeDetection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:detection_sessions,id',
            'track_id' => 'required|integer',
            'frame_number' => 'required|integer',
            'detected_at' => 'nullable|date',
            'timestamp' => 'nullable|numeric',
            'confidence' => 'required|numeric|min:0|max:1',
            'behavior' => 'required|in:calm,warning,aggressive',
            'speed_kmph' => 'nullable|numeric|min:0',
            'aggression_score' => 'nullable|numeric|min:0|max:1',
            'bbox_x1' => 'required|integer',
            'bbox_y1' => 'required|integer',
            'bbox_x2' => 'required|integer',
            'bbox_y2' => 'required|integer',
            'alert_triggered' => 'nullable|boolean',
            'alert_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $detection = Detection::create([
            'session_id' => $request->session_id,
            'track_id' => $request->track_id,
            'frame_number' => $request->frame_number,
            'detected_at' => $request->detected_at ? Carbon::parse($request->detected_at) : Carbon::now(),
            'timestamp' => $request->timestamp,
            'confidence' => $request->confidence,
            'behavior' => $request->behavior,
            'speed_kmph' => $request->speed_kmph ?? 0,
            'aggression_score' => $request->aggression_score ?? 0,
            'bbox_x1' => $request->bbox_x1,
            'bbox_y1' => $request->bbox_y1,
            'bbox_x2' => $request->bbox_x2,
            'bbox_y2' => $request->bbox_y2,
            'bbox_width' => $request->bbox_x2 - $request->bbox_x1,
            'bbox_height' => $request->bbox_y2 - $request->bbox_y1,
            'center_x' => ($request->bbox_x1 + $request->bbox_x2) / 2,
            'center_y' => ($request->bbox_y1 + $request->bbox_y2) / 2,
            'alert_triggered' => $request->alert_triggered ?? false,
            'alert_type' => $request->alert_type,
        ]);

        // Update session total_frames if needed
        $session = DetectionSession::find($request->session_id);
        if ($session && $request->frame_number > $session->total_frames) {
            $session->update(['total_frames' => $request->frame_number]);
        }

        return response()->json([
            'success' => true,
            'detection_id' => $detection->id,
            'message' => 'Detection stored successfully'
        ], 201);
    }

    /**
     * Store multiple detections (batch insert for performance)
     */
    public function storeDetectionsBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:detection_sessions,id',
            'detections' => 'required|array|min:1',
            'detections.*.track_id' => 'required|integer',
            'detections.*.frame_number' => 'required|integer',
            'detections.*.confidence' => 'required|numeric',
            'detections.*.behavior' => 'required|in:calm,warning,aggressive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $sessionId = $request->session_id;
        $detections = [];
        $now = Carbon::now();
        $maxFrame = 0;

        foreach ($request->detections as $det) {
            $bboxWidth = $det['bbox_x2'] - $det['bbox_x1'];
            $bboxHeight = $det['bbox_y2'] - $det['bbox_y1'];
            
            $detections[] = [
                'session_id' => $sessionId,
                'track_id' => $det['track_id'],
                'frame_number' => $det['frame_number'],
                'detected_at' => isset($det['detected_at']) ? Carbon::parse($det['detected_at']) : $now,
                'timestamp' => $det['timestamp'] ?? null,
                'confidence' => $det['confidence'],
                'behavior' => $det['behavior'],
                'speed_kmph' => $det['speed_kmph'] ?? 0,
                'aggression_score' => $det['aggression_score'] ?? 0,
                'bbox_x1' => $det['bbox_x1'],
                'bbox_y1' => $det['bbox_y1'],
                'bbox_x2' => $det['bbox_x2'],
                'bbox_y2' => $det['bbox_y2'],
                'bbox_width' => $bboxWidth,
                'bbox_height' => $bboxHeight,
                'center_x' => ($det['bbox_x1'] + $det['bbox_x2']) / 2,
                'center_y' => ($det['bbox_y1'] + $det['bbox_y2']) / 2,
                'alert_triggered' => $det['alert_triggered'] ?? false,
                'alert_type' => $det['alert_type'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            if ($det['frame_number'] > $maxFrame) {
                $maxFrame = $det['frame_number'];
            }
        }

        Detection::insert($detections);

        // Update session total_frames
        $session = DetectionSession::find($sessionId);
        if ($session && $maxFrame > $session->total_frames) {
            $session->update(['total_frames' => $maxFrame]);
        }

        return response()->json([
            'success' => true,
            'count' => count($detections),
            'message' => 'Detections stored successfully'
        ], 201);
    }

    /**
     * Store an alert
     */
    public function storeAlert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:detection_sessions,id',
            'alert_type' => 'required|in:warning_tts,alarm_sound,danger_tts,stop_alarm,zone_transition',
            'message' => 'nullable|string',
            'track_id' => 'nullable|integer',
            'distance_meters' => 'nullable|numeric',
            'zone_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $alert = Alert::create([
            'session_id' => $request->session_id,
            'track_id' => $request->track_id,
            'alert_type' => $request->alert_type,
            'message' => $request->message,
            'triggered_at' => $request->triggered_at ? Carbon::parse($request->triggered_at) : Carbon::now(),
            'distance_meters' => $request->distance_meters,
            'zone_name' => $request->zone_name,
            'metadata' => $request->metadata ?? null,
        ]);

        return response()->json([
            'success' => true,
            'alert_id' => $alert->id,
            'message' => 'Alert stored successfully'
        ], 201);
    }

    /**
     * Store a zone transition
     */
    public function storeZoneTransition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:detection_sessions,id',
            'from_zone' => 'required|string',
            'to_zone' => 'required|string',
            'distance_meters' => 'required|numeric',
            'track_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $transition = ZoneTransition::create([
            'session_id' => $request->session_id,
            'track_id' => $request->track_id,
            'from_zone' => $request->from_zone,
            'to_zone' => $request->to_zone,
            'distance_meters' => $request->distance_meters,
            'transitioned_at' => $request->transitioned_at ? Carbon::parse($request->transitioned_at) : Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'transition_id' => $transition->id,
            'message' => 'Zone transition stored successfully'
        ], 201);
    }
}

