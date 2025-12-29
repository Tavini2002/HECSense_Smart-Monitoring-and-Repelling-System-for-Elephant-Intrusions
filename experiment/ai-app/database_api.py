"""
Database API Client for Elephant Detection System
Handles communication with Laravel backend API
"""

import requests
import json
from datetime import datetime
from typing import Optional, List, Dict, Any
import time

class DetectionAPIClient:
    """Client for interacting with Laravel detection API"""
    
    def __init__(self, base_url: str = "http://localhost:8000", enabled: bool = True):
        """
        Initialize API client
        
        Args:
            base_url: Base URL of Laravel API (default: http://localhost:8000)
            enabled: Whether database logging is enabled (default: True)
        """
        self.base_url = base_url.rstrip('/')
        self.api_base = f"{self.base_url}/api/detections"
        self.enabled = enabled
        self.session_id = None
        
    def create_session(self, session_name: Optional[str] = None, 
                      source_type: str = "camera",
                      source_path: Optional[str] = None,
                      confidence_threshold: float = 0.10) -> Optional[int]:
        """
        Create a new detection session
        
        Returns:
            Session ID if successful, None otherwise
        """
        if not self.enabled:
            return None
            
        try:
            response = requests.post(
                f"{self.api_base}/sessions",
                json={
                    'session_name': session_name,
                    'source_type': source_type,
                    'source_path': source_path,
                    'confidence_threshold': confidence_threshold
                },
                timeout=5
            )
            
            if response.status_code == 201:
                data = response.json()
                self.session_id = data.get('session_id')
                return self.session_id
            else:
                print(f"[API] Failed to create session: {response.status_code} - {response.text}")
                return None
        except Exception as e:
            print(f"[API] Error creating session: {e}")
            return None
    
    def update_session(self, status: str = "completed", total_frames: Optional[int] = None, 
                      notes: Optional[str] = None) -> bool:
        """
        Update session status
        
        Args:
            status: Session status (running, completed, stopped, error)
            total_frames: Total number of frames processed
            notes: Optional notes
        """
        if not self.enabled or not self.session_id:
            return False
            
        try:
            response = requests.put(
                f"{self.api_base}/sessions/{self.session_id}",
                json={
                    'status': status,
                    'total_frames': total_frames,
                    'notes': notes
                },
                timeout=5
            )
            
            return response.status_code == 200
        except Exception as e:
            print(f"[API] Error updating session: {e}")
            return False
    
    def store_detection(self, track_id: int, frame_number: int,
                       confidence: float, behavior: str,
                       bbox_x1: int, bbox_y1: int, bbox_x2: int, bbox_y2: int,
                       speed_kmph: float = 0.0, aggression_score: float = 0.0,
                       timestamp: Optional[float] = None,
                       alert_triggered: bool = False,
                       alert_type: Optional[str] = None,
                       detected_at: Optional[datetime] = None) -> bool:
        """
        Store a single detection
        
        Returns:
            True if successful, False otherwise
        """
        if not self.enabled or not self.session_id:
            return False
            
        try:
            data = {
                'session_id': self.session_id,
                'track_id': track_id,
                'frame_number': frame_number,
                'confidence': confidence,
                'behavior': behavior,
                'bbox_x1': bbox_x1,
                'bbox_y1': bbox_y1,
                'bbox_x2': bbox_x2,
                'bbox_y2': bbox_y2,
                'speed_kmph': speed_kmph,
                'aggression_score': aggression_score,
                'alert_triggered': alert_triggered,
            }
            
            if timestamp is not None:
                data['timestamp'] = timestamp
            
            if alert_type:
                data['alert_type'] = alert_type
            
            if detected_at:
                data['detected_at'] = detected_at.isoformat()
            
            response = requests.post(
                f"{self.api_base}/store",
                json=data,
                timeout=2
            )
            
            return response.status_code == 201
        except Exception as e:
            # Silently fail to avoid disrupting video processing
            return False
    
    def store_detections_batch(self, detections: List[Dict[str, Any]]) -> bool:
        """
        Store multiple detections in batch (more efficient)
        
        Args:
            detections: List of detection dictionaries
            
        Returns:
            True if successful, False otherwise
        """
        if not self.enabled or not self.session_id or not detections:
            return False
            
        try:
            # Prepare detections for API
            api_detections = []
            for det in detections:
                api_det = {
                    'track_id': det['track_id'],
                    'frame_number': det['frame_number'],
                    'confidence': det['confidence'],
                    'behavior': det['behavior'],
                    'bbox_x1': det['bbox_x1'],
                    'bbox_y1': det['bbox_y1'],
                    'bbox_x2': det['bbox_x2'],
                    'bbox_y2': det['bbox_y2'],
                    'speed_kmph': det.get('speed_kmph', 0.0),
                    'aggression_score': det.get('aggression_score', 0.0),
                }
                
                if 'timestamp' in det:
                    api_det['timestamp'] = det['timestamp']
                if 'alert_triggered' in det:
                    api_det['alert_triggered'] = det['alert_triggered']
                if 'alert_type' in det:
                    api_det['alert_type'] = det['alert_type']
                if 'detected_at' in det and det['detected_at']:
                    if isinstance(det['detected_at'], datetime):
                        api_det['detected_at'] = det['detected_at'].isoformat()
                    else:
                        api_det['detected_at'] = det['detected_at']
                
                api_detections.append(api_det)
            
            response = requests.post(
                f"{self.api_base}/store-batch",
                json={
                    'session_id': self.session_id,
                    'detections': api_detections
                },
                timeout=5
            )
            
            return response.status_code == 201
        except Exception as e:
            print(f"[API] Error storing batch detections: {e}")
            return False
    
    def store_alert(self, alert_type: str, message: Optional[str] = None,
                   track_id: Optional[int] = None,
                   distance_meters: Optional[float] = None,
                   zone_name: Optional[str] = None,
                   metadata: Optional[Dict] = None) -> bool:
        """
        Store an alert
        
        Args:
            alert_type: Type of alert (warning_tts, alarm_sound, danger_tts, etc.)
            message: Alert message
            track_id: Elephant track ID
            distance_meters: Distance in meters (if applicable)
            zone_name: Zone name (if applicable)
            metadata: Additional metadata as dictionary
        """
        if not self.enabled or not self.session_id:
            return False
            
        try:
            data = {
                'session_id': self.session_id,
                'alert_type': alert_type,
            }
            
            if message:
                data['message'] = message
            if track_id is not None:
                data['track_id'] = track_id
            if distance_meters is not None:
                data['distance_meters'] = distance_meters
            if zone_name:
                data['zone_name'] = zone_name
            if metadata:
                data['metadata'] = metadata
            
            response = requests.post(
                f"{self.api_base}/alerts",
                json=data,
                timeout=2
            )
            
            return response.status_code == 201
        except Exception as e:
            return False
    
    def store_zone_transition(self, from_zone: str, to_zone: str,
                             distance_meters: float,
                             track_id: Optional[int] = None) -> bool:
        """
        Store a zone transition
        """
        if not self.enabled or not self.session_id:
            return False
            
        try:
            data = {
                'session_id': self.session_id,
                'from_zone': from_zone,
                'to_zone': to_zone,
                'distance_meters': distance_meters,
            }
            
            if track_id is not None:
                data['track_id'] = track_id
            
            response = requests.post(
                f"{self.api_base}/zone-transitions",
                json=data,
                timeout=2
            )
            
            return response.status_code == 201
        except Exception as e:
            return False

