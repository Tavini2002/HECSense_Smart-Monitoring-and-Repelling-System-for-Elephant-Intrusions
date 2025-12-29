import cv2
import time
import math
import numpy as np
from collections import deque
from ultralytics import YOLO

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH = "yolov8s.pt"     # COCO model
CONF_THRESHOLD = 0.10
VIDEO_SOURCE = "input-1.mp4"  # 0 for webcam OR "video.mp4"

ELEPHANT_LENGTH_M = 6.0       # avg adult elephant length (meters)

# Aggression detection thresholds
SPEED_THRESHOLD_AGGRESSIVE = 5.0   # km/h - above this is aggressive
SPEED_THRESHOLD_CALM = 2.0         # km/h - below this is calm (2-5 km/h is warning)

# State persistence thresholds (prevent flickering)
AGGRESSIVE_CONFIRMATION_COUNT = 8  # Need 8+ consecutive aggressive/warning frames to mark as aggressive
ACCELERATION_THRESHOLD = 5.0      # m/s² - high acceleration indicates aggression
DIRECTION_CHANGE_THRESHOLD = 45.0 # degrees - sudden direction changes
TRAJECTORY_WINDOW = 10           # number of frames to track for trajectory analysis

# Tracking parameters
MAX_TRACKING_DISTANCE = 200  # pixels - maximum distance to match a detection to an existing track
TRACKING_TIMEOUT = 30  # frames - remove track if not seen for this many frames
MIN_IOU_FOR_MATCH = 0.1  # minimum IoU to consider a match (helps with overlapping detections)

# Display size (window control)
DISPLAY_WIDTH = 960
DISPLAY_HEIGHT = 540

# ----------------------------
# Trajectory Tracker Class
# ----------------------------
class TrajectoryTracker:
    def __init__(self, max_history=20):
        self.max_history = max_history
        self.positions = deque(maxlen=max_history)
        self.times = deque(maxlen=max_history)
        self.speeds = deque(maxlen=max_history)
        
    def add_point(self, center, timestamp, speed):
        """Add a new tracking point"""
        self.positions.append(center)
        self.times.append(timestamp)
        self.speeds.append(speed)
    
    def get_direction_changes(self):
        """Calculate number of significant direction changes"""
        if len(self.positions) < 3:
            return 0
        
        direction_changes = 0
        angles = []
        
        for i in range(1, len(self.positions)):
            dx = self.positions[i][0] - self.positions[i-1][0]
            dy = self.positions[i][1] - self.positions[i-1][1]
            angle = math.degrees(math.atan2(dy, dx))
            angles.append(angle)
        
        for i in range(1, len(angles)):
            angle_diff = abs(angles[i] - angles[i-1])
            # Normalize angle difference to 0-180 range
            if angle_diff > 180:
                angle_diff = 360 - angle_diff
            if angle_diff > DIRECTION_CHANGE_THRESHOLD:
                direction_changes += 1
        
        return direction_changes
    
    def get_average_speed(self):
        """Get average speed over trajectory window"""
        if len(self.speeds) == 0:
            return 0.0
        return sum(self.speeds) / len(self.speeds)
    
    def get_speed_variance(self):
        """Calculate speed variance (higher = more erratic)"""
        if len(self.speeds) < 2:
            return 0.0
        avg = self.get_average_speed()
        variance = sum((s - avg) ** 2 for s in self.speeds) / len(self.speeds)
        return variance
    
    def get_trajectory_straightness(self):
        """Calculate how straight the trajectory is (0-1, 1 = perfectly straight)"""
        if len(self.positions) < 3:
            return 0.5
        
        # Calculate total distance vs straight-line distance
        total_distance = 0
        for i in range(1, len(self.positions)):
            dx = self.positions[i][0] - self.positions[i-1][0]
            dy = self.positions[i][1] - self.positions[i-1][1]
            total_distance += math.sqrt(dx*dx + dy*dy)
        
        if total_distance == 0:
            return 0.5
        
        # Straight line distance from first to last
        dx = self.positions[-1][0] - self.positions[0][0]
        dy = self.positions[-1][1] - self.positions[0][1]
        straight_distance = math.sqrt(dx*dx + dy*dy)
        
        if straight_distance == 0:
            return 0.0
        
        return straight_distance / total_distance
    
    def get_movement_toward_camera(self, bbox_sizes):
        """Detect if elephant is moving toward camera (bbox getting larger)"""
        if len(bbox_sizes) < 3:
            return False
        
        # Calculate average size change rate
        recent_sizes = list(bbox_sizes)[-5:] if len(bbox_sizes) >= 5 else list(bbox_sizes)
        if len(recent_sizes) < 2:
            return False
        
        # Calculate trend (increasing size = moving closer)
        size_diffs = [recent_sizes[i] - recent_sizes[i-1] for i in range(1, len(recent_sizes))]
        avg_growth = sum(size_diffs) / len(size_diffs)
        
        # Significant growth indicates charging toward camera
        return avg_growth > 10  # pixels per frame threshold

# ----------------------------
# Aggression Classifier
# ----------------------------
class AggressionClassifier:
    def __init__(self):
        self.trackers = {}  # Track multiple elephants by ID
        self.bbox_sizes = {}  # Track bounding box sizes
        # State persistence tracking
        self.aggressive_count = {}  # Count consecutive aggressive/warning detections per elephant
        self.confirmed_states = {}  # Currently confirmed state (calm/aggressive/warning)
        
    def classify(self, box_id, center, bbox_size, speed_kmph, timestamp):
        """Classify elephant behavior as calm or aggressive"""
        
        # Initialize tracker for this elephant if needed
        if box_id not in self.trackers:
            self.trackers[box_id] = TrajectoryTracker(max_history=TRAJECTORY_WINDOW)
            self.bbox_sizes[box_id] = deque(maxlen=TRAJECTORY_WINDOW)
        
        tracker = self.trackers[box_id]
        tracker.add_point(center, timestamp, speed_kmph)
        self.bbox_sizes[box_id].append(bbox_size)
        
        # Need at least 3 points for reliable classification
        if len(tracker.positions) < 3:
            return "warning", 0.5  # Not enough data
        
        # Get average speed over trajectory window (smoothed, not one-shot)
        avg_speed = tracker.get_average_speed()
        
        # Additional smoothing: use median of recent speeds to filter outliers
        # This prevents single-frame errors from affecting classification
        if len(tracker.speeds) >= 3:
            recent_speeds = sorted(list(tracker.speeds)[-5:])  # Last 5 speeds, sorted
            median_speed = recent_speeds[len(recent_speeds) // 2]
            # Use weighted average: 70% median (more stable), 30% average (responsive)
            smoothed_speed = 0.7 * median_speed + 0.3 * avg_speed
        else:
            smoothed_speed = avg_speed
        
        # Cap smoothed speed at reasonable maximum
        smoothed_speed = min(smoothed_speed, 40.0)
        
        # DETECT CURRENT FRAME STATE (not final classification yet)
        # Speed > 5 km/h = aggressive, Speed < 2 km/h = calm, 2-5 km/h = warning
        if smoothed_speed > SPEED_THRESHOLD_AGGRESSIVE:
            current_frame_state = "aggressive"
            aggression_score = 1.0
        elif smoothed_speed < SPEED_THRESHOLD_CALM:
            current_frame_state = "calm"
            aggression_score = 0.0
        else:
            current_frame_state = "warning"
            aggression_score = 0.5
        
        # Initialize counters if needed
        if box_id not in self.aggressive_count:
            self.aggressive_count[box_id] = 0.0
            self.confirmed_states[box_id] = "warning"  # Start with neutral state
        
        # COUNTER-BASED STATE PERSISTENCE LOGIC
        # Only track aggressive/warning - calm frames DO NOT reset aggressive_count
        if current_frame_state == "aggressive" or current_frame_state == "warning":
            # Both aggressive and warning frames count as 1.0 (same weight)
            self.aggressive_count[box_id] += 1.0
        # Calm frames: do nothing - don't increment, don't reset
        # This allows aggressive_count to persist and only reset when track is removed
        
        # STATE CONFIRMATION LOGIC (counter-based)
        confirmed_state = self.confirmed_states.get(box_id, "calm")  # Default to calm
        
        # Check if we have enough consecutive aggressive/warning detections
        # Warning and aggressive both contribute 1.0 to the count (same weight)
        if self.aggressive_count[box_id] >= AGGRESSIVE_CONFIRMATION_COUNT:
            # Once aggressive threshold is reached (8 frames), mark as aggressive
            confirmed_state = "aggressive"
        elif confirmed_state == "aggressive":
            # Once aggressive, stay aggressive (don't flip back to calm)
            # Keep the aggressive state until track is removed
            pass
        else:
            # Not confirmed aggressive yet (count < 8) - show current frame state
            if current_frame_state == "aggressive":
                confirmed_state = "aggressive"  # Show aggressive immediately when speed > 5
            elif current_frame_state == "warning":
                confirmed_state = "warning"
            else:
                confirmed_state = "calm"
        
        # Update confirmed state
        self.confirmed_states[box_id] = confirmed_state
        
        # Return confirmed state (can be aggressive, warning, or calm)
        if confirmed_state == "aggressive":
            return "aggressive", 1.0
        elif confirmed_state == "warning":
            return "warning", 0.5
        else:
            return "calm", 0.0

# ----------------------------
# Main Execution (only runs when script is executed directly)
# ----------------------------
if __name__ == "__main__":
    # Load YOLO model
    model = YOLO(MODEL_PATH)

    # Get elephant class ID
    elephant_id = None
    for k, v in model.names.items():
        if v == "elephant":
            elephant_id = k
            break

    if elephant_id is None:
        raise RuntimeError("Elephant class not found in YOLO model")

    print(f"[INFO] Elephant class ID: {elephant_id}")
    print(f"[INFO] Aggression thresholds:")
    print(f"  - Aggressive speed: >{SPEED_THRESHOLD_AGGRESSIVE} km/h")
    print(f"  - Calm speed: <{SPEED_THRESHOLD_CALM} km/h")
    print(f"[INFO] State persistence:")
    print(f"  - Aggressive confirmation: {AGGRESSIVE_CONFIRMATION_COUNT} consecutive frames")

    # ----------------------------
    # Video Capture
    # ----------------------------
    cap = cv2.VideoCapture(VIDEO_SOURCE)
    if not cap.isOpened():
        raise RuntimeError("Cannot open video source")

    # Get video FPS for better time calculations
    fps = cap.get(cv2.CAP_PROP_FPS)
    if fps <= 0:
        fps = 30.0  # Default FPS

    print(f"[INFO] Video FPS: {fps}")
    print(f"[INFO] Using YOLOv8 built-in tracker (BoT-SORT algorithm)")

    frame_count = 0
    classifier = AggressionClassifier()

    # ----------------------------
    # Main Loop
    # ----------------------------
    while True:
        ret, frame = cap.read()
        if not ret:
            break
        
        frame_count += 1
        current_time = time.time()
        
        # Use frame count for more consistent timing if video FPS is known
        frame_time = frame_count / fps if fps > 0 else current_time
        
        # Run detection and tracking on ORIGINAL frame (YOLOv8 built-in tracker)
        results = model.track(frame, conf=CONF_THRESHOLD, persist=True, verbose=False)
        boxes = results[0].boxes
        
        # Extract elephant detections with track IDs from YOLOv8 tracker
        current_track_ids = set()  # Track IDs seen in this frame for cleanup
        
        for box in boxes:
            if int(box.cls.item()) != elephant_id:
                continue
            
            # Get track ID from YOLOv8 tracker (None if not tracked)
            track_id = None
            if box.id is not None:
                track_id = int(box.id.item())
            
            # Skip if no track ID (shouldn't happen with persist=True, but just in case)
            if track_id is None:
                continue
            
            current_track_ids.add(track_id)
            
            x1, y1, x2, y2 = map(int, box.xyxy[0])
            conf = float(box.conf.item())
            cx = (x1 + x2) // 2
            cy = (y1 + y2) // 2
            center = (cx, cy)
            
            # Use YOLOv8's track ID directly as box_id
            box_id = track_id
            
            # Bounding box size (area)
            bbox_width = x2 - x1
            bbox_height = y2 - y1
            bbox_size = bbox_width * bbox_height
            
            # Calculate speed using trajectory tracker (handles frame-to-frame speed)
            speed_kmph = 0.0
            
            # Get previous position from trajectory tracker if available
            if box_id in classifier.trackers and len(classifier.trackers[box_id].positions) > 0:
                prev_center = classifier.trackers[box_id].positions[-1]
                
                # Calculate pixel movement
                dx = cx - prev_center[0]
                dy = cy - prev_center[1]
                pixel_distance = math.sqrt(dx * dx + dy * dy)
                
                # Get time difference from tracker
                if len(classifier.trackers[box_id].times) > 0:
                    prev_time = classifier.trackers[box_id].times[-1]
                    time_diff = current_time - prev_time
                    
                    # Only calculate if time difference is reasonable (0.01 to 0.5 seconds)
                    if 0.01 <= time_diff <= 0.5:
                        speed_px_per_sec = pixel_distance / time_diff
                        
                        # Better scale estimation: use average of width and height
                        bbox_avg_size = (bbox_width + bbox_height) / 2.0
                        bbox_avg_size = max(20, bbox_avg_size)  # Minimum 20 pixels
                        
                        # Estimate scale: assume elephant is 6m
                        meters_per_pixel = ELEPHANT_LENGTH_M / bbox_avg_size
                        
                        # Calculate speed in m/s, then convert to km/h
                        speed_mps = speed_px_per_sec * meters_per_pixel
                        speed_kmph = speed_mps * 3.6
                        
                        # Cap speed at reasonable maximum
                        speed_kmph = min(speed_kmph, 40.0)
                        
                        # Filter out very small movements (likely noise)
                        if speed_kmph < 0.5:
                            speed_kmph = 0.0
            
            # Classify behavior (use actual time for persistence)
            behavior, aggression_score = classifier.classify(
                box_id, center, bbox_size, speed_kmph, current_time
            )
            
            # Get aggressive count for debugging
            aggressive_count = classifier.aggressive_count.get(box_id, 0.0)
            
            # Determine color based on behavior (bounding box colors)
            if behavior == "calm":
                color = (0, 255, 0)  # Green (BGR format)
                status_text = "CALM"
            elif behavior == "aggressive":
                color = (0, 0, 255)  # Red (BGR format)
                status_text = "AGGRESSIVE"
            else:  # warning
                color = (0, 165, 255)  # Orange for warning (BGR format)
                status_text = "WARNING"
            
            # Draw bounding box with appropriate color (thicker line for visibility)
            cv2.rectangle(frame, (x1, y1), (x2, y2), color, 3)
            
            # Label with only mood/status text (bigger text)
            label_y = max(40, y1 - 10)
            label_text = status_text
            
            # Get text size for larger font
            font_scale = 1.2
            thickness = 3
            (text_width, text_height), baseline = cv2.getTextSize(
                label_text, cv2.FONT_HERSHEY_SIMPLEX, font_scale, thickness
            )
            
            # Draw background rectangle with same color as bounding box (with padding)
            padding = 5
            cv2.rectangle(
                frame,
                (x1, label_y - text_height - padding),
                (x1 + text_width + padding * 2, label_y + baseline + padding),
                color,  # Same color as bounding box
                -1  # Filled rectangle
            )
            
            # Draw text in white for better contrast
            cv2.putText(
                frame,
                label_text,
                (x1 + padding, label_y),
                cv2.FONT_HERSHEY_SIMPLEX,
                font_scale,
                (255, 255, 255),  # White text (BGR format)
                thickness
            )
            
            # Print debug counts to console
            print(f"Frame {frame_count} | Elephant {box_id}: Agg={int(aggressive_count)} | State={behavior} | Speed={speed_kmph:.1f} km/h")
        
        # Clean up classifier data for tracks that are no longer in the video
        # (YOLOv8 tracker handles track persistence, we just clean up our classifier state)
        boxes_to_remove = [box_id for box_id in classifier.trackers.keys() if box_id not in current_track_ids]
        for box_id in boxes_to_remove:
            if box_id in classifier.trackers:
                del classifier.trackers[box_id]
            if box_id in classifier.bbox_sizes:
                del classifier.bbox_sizes[box_id]
            if box_id in classifier.aggressive_count:
                del classifier.aggressive_count[box_id]
            if box_id in classifier.confirmed_states:
                del classifier.confirmed_states[box_id]
        
        # ----------------------------
        # Resize ONLY for display
        # ----------------------------
        h, w = frame.shape[:2]
        scale = min(DISPLAY_WIDTH / w, DISPLAY_HEIGHT / h)
        
        display_frame = cv2.resize(
            frame,
            (int(w * scale), int(h * scale)),
            interpolation=cv2.INTER_AREA
        )
        
        cv2.imshow("Elephant Aggression Detection", display_frame)
        
        if cv2.waitKey(1) & 0xFF == ord("q"):
            break

    # ----------------------------
    # Cleanup
    # ----------------------------
    cap.release()
    cv2.destroyAllWindows()
    print("[INFO] Detection finished")
