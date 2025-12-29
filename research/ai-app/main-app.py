import sys
import cv2
import time
import math
import os
import threading
from queue import Queue, Empty
from PyQt5.QtWidgets import (
    QApplication, QMainWindow, QWidget, QVBoxLayout, QHBoxLayout,
    QPushButton, QLabel, QSlider, QCheckBox, QFileDialog, QRadioButton,
    QButtonGroup, QGroupBox, QMessageBox, QLineEdit
)
from PyQt5.QtCore import Qt, QTimer, QThread, pyqtSignal, pyqtSlot
from PyQt5.QtGui import QImage, QPixmap
import numpy as np
from ultralytics import YOLO
import pygame
from datetime import datetime

# Import database API client
try:
    from database_api import DetectionAPIClient
    DB_API_AVAILABLE = True
except ImportError:
    DB_API_AVAILABLE = False
    print("[WARNING] database_api.py not found. Database logging disabled.")

# Import from elephant-aggression-detection.py
# Note: Using importlib since filename has hyphens
import importlib.util

# Get the directory of this script
_script_dir = os.path.dirname(os.path.abspath(__file__))
_detection_script_path = os.path.join(_script_dir, "elephant-aggression-detection.py")

spec = importlib.util.spec_from_file_location("elephant_aggression_detection", _detection_script_path)
elephant_detection_module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(elephant_detection_module)

TrajectoryTracker = elephant_detection_module.TrajectoryTracker
AggressionClassifier = elephant_detection_module.AggressionClassifier
ELEPHANT_LENGTH_M = elephant_detection_module.ELEPHANT_LENGTH_M

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH = os.path.join(_script_dir, "yolov8s.pt")
DEFAULT_CONF_THRESHOLD = 0.10

# Alarm sound paths
ALARM_SOUND_PATH = os.path.join(_script_dir, "assets", "warning-sound.mp3")
WARNING_SOUND_PATH = os.path.join(_script_dir, "assets", "warning-sound-only.mp3")

# Database API Configuration
DB_API_URL = os.getenv("DB_API_URL", "http://localhost:8000")  # Laravel API URL
DB_ENABLED = os.getenv("DB_ENABLED", "true").lower() == "true"  # Enable/disable database logging


# ----------------------------
# Audio Manager
# ----------------------------
class AudioManager:
    """Manages alarm sound playback"""
    def __init__(self):
        self.audio_initialized = False
        self.alarm_playing = False
        self.warning_playing = False
        self.last_aggressive_time = 0
        self.last_warning_time = 0
        self.aggressive_cooldown = 2.0  # seconds
        self.warning_cooldown = 2.5  # seconds
        
    def init_audio(self):
        """Initialize pygame mixer"""
        if not self.audio_initialized:
            try:
                pygame.mixer.init(frequency=44100, size=-16, channels=2, buffer=512)
                self.audio_initialized = True
            except:
                try:
                    pygame.mixer.init()
                    self.audio_initialized = True
                except Exception as e:
                    print(f"Warning: Could not initialize audio: {e}")
                    self.audio_initialized = False
    
    def play_aggressive_alarm(self):
        """Play aggressive alarm sound"""
        if not self.audio_initialized:
            self.init_audio()
            if not self.audio_initialized:
                return
        
        current_time = time.time()
        if current_time - self.last_aggressive_time < self.aggressive_cooldown:
            return
        
        try:
            if os.path.exists(ALARM_SOUND_PATH):
                self.stop_alarm()  # Stop any current sound
                pygame.mixer.music.load(ALARM_SOUND_PATH)
                pygame.mixer.music.set_volume(0.9)
                pygame.mixer.music.play(loops=-1)  # Loop continuously
                self.alarm_playing = True
                self.last_aggressive_time = current_time
        except Exception as e:
            print(f"Error playing alarm: {e}")
    
    def play_warning_sound(self):
        """Play warning sound"""
        if not self.audio_initialized:
            self.init_audio()
            if not self.audio_initialized:
                return
        
        current_time = time.time()
        if current_time - self.last_warning_time < self.warning_cooldown:
            return
        
        try:
            if os.path.exists(WARNING_SOUND_PATH):
                # Only play warning if aggressive alarm is not playing
                if not self.alarm_playing:
                    pygame.mixer.music.load(WARNING_SOUND_PATH)
                    pygame.mixer.music.set_volume(0.7)
                    pygame.mixer.music.play()
                    self.warning_playing = True
                    self.last_warning_time = current_time
        except Exception as e:
            print(f"Error playing warning sound: {e}")
    
    def stop_alarm(self):
        """Stop all alarm sounds"""
        if self.audio_initialized:
            try:
                pygame.mixer.music.stop()
            except:
                pass
        self.alarm_playing = False
        self.warning_playing = False


# ----------------------------
# Database Worker Thread
# ----------------------------
class DatabaseWorker(QThread):
    """Separate thread for database operations to avoid blocking video processing or UI"""
    
    def __init__(self, db_client):
        super().__init__()
        self.db_client = db_client
        self.queue = Queue()
        self.running = False
        
    def add_task(self, task_type, **kwargs):
        """Add a database task to the queue"""
        if self.db_client and self.db_client.enabled:
            self.queue.put((task_type, kwargs))
    
    def stop(self):
        """Stop the worker thread"""
        self.running = False
        # Add a None task to wake up the thread
        self.queue.put(None)
    
    def run(self):
        """Process database tasks from queue"""
        self.running = True
        
        while self.running:
            try:
                # Get task from queue (timeout to allow checking self.running)
                try:
                    task = self.queue.get(timeout=0.1)
                except Empty:
                    continue
                
                if task is None:
                    break
                
                task_type, kwargs = task
                
                # Process different task types
                if task_type == "store_batch":
                    self.db_client.store_detections_batch(kwargs.get('detections', []))
                elif task_type == "store_alert":
                    self.db_client.store_alert(
                        alert_type=kwargs.get('alert_type'),
                        message=kwargs.get('message'),
                        track_id=kwargs.get('track_id'),
                        distance_meters=kwargs.get('distance_meters'),
                        zone_name=kwargs.get('zone_name'),
                        metadata=kwargs.get('metadata')
                    )
                elif task_type == "update_session":
                    self.db_client.update_session(
                        status=kwargs.get('status'),
                        total_frames=kwargs.get('total_frames'),
                        notes=kwargs.get('notes')
                    )
                elif task_type == "create_session":
                    session_id = self.db_client.create_session(
                        session_name=kwargs.get('session_name'),
                        source_type=kwargs.get('source_type'),
                        source_path=kwargs.get('source_path'),
                        confidence_threshold=kwargs.get('confidence_threshold')
                    )
                    # Store session_id in db_client if created successfully
                    if session_id:
                        self.db_client.session_id = session_id
                
                self.queue.task_done()
                
            except Exception as e:
                print(f"[DB Worker] Error processing task: {e}")
                # Continue processing other tasks even if one fails
                continue


# ----------------------------
# Video Processing Thread
# ----------------------------
class VideoProcessor(QThread):
    """Separate thread for video processing to avoid blocking UI"""
    frame_ready = pyqtSignal(np.ndarray)  # Signal to send processed frame
    stats_ready = pyqtSignal(dict)  # Signal to send statistics
    
    def __init__(self, model, elephant_id, classifier, conf_threshold, db_worker=None):
        super().__init__()
        self.model = model
        self.elephant_id = elephant_id
        self.classifier = classifier
        self.conf_threshold = conf_threshold
        self.db_worker = db_worker  # Database worker thread for async operations
        self.cap = None
        self.running = False
        self.frame_count = 0
        self.skip_frames = 3  # Skip first few frames to allow tracker to initialize
        self.last_frame_shape = None  # Track frame dimensions
        self.use_fresh_tracking = False  # Flag to use fresh tracking for new videos
        self.fresh_tracking_frame_count = 0  # Count frames processed in fresh tracking mode
        
        # Batch storage for detections (store every N frames for performance)
        self.detections_buffer = []
        self.batch_size = 10  # Store every 10 detections in batch
        self.source_path = None
        self.source_type = "camera"
        
    def set_video_source(self, source):
        """Set video source (file path or camera index)"""
        if self.cap is not None:
            self.cap.release()
        self.cap = cv2.VideoCapture(source)
        if not self.cap.isOpened():
            return False
        
        # Determine source type and path
        if isinstance(source, str) and os.path.exists(source):
            self.source_type = "video_upload"
            self.source_path = os.path.basename(source)
        else:
            self.source_type = "camera"
            self.source_path = f"Camera {source}"
        
        # Reset frame skip counter and tracking state when opening new video
        self.skip_frames = 5  # Skip more frames to ensure tracker resets
        self.last_frame_shape = None
        self.use_fresh_tracking = True  # Start with fresh tracking (persist=False) for first few frames
        self.fresh_tracking_frame_count = 0  # Reset fresh tracking counter
        self.detections_buffer = []  # Clear buffer
        
        return True
    
    def set_confidence(self, conf):
        """Update confidence threshold"""
        self.conf_threshold = conf
    
    def stop(self):
        """Stop processing"""
        self.running = False
    
    def run(self):
        """Main processing loop"""
        if self.cap is None:
            return
        
        self.running = True
        self.frame_count = 0
        
        # Create database session if enabled (async via worker)
        if self.db_worker:
            session_name = f"{self.source_type} - {self.source_path}"
            self.db_worker.add_task(
                "create_session",
                session_name=session_name,
                source_type=self.source_type,
                source_path=self.source_path,
                confidence_threshold=self.conf_threshold
            )
        
        # Get video FPS
        self.video_fps = self.cap.get(cv2.CAP_PROP_FPS)
        if self.video_fps <= 0:
            self.video_fps = 30.0
        fps = self.video_fps  # Keep for compatibility
        
        while self.running:
            ret, frame = self.cap.read()
            if not ret:
                # End of video or camera disconnected
                break
            
            self.frame_count += 1
            current_time = time.time()
            
            # Check if frame dimensions changed (new video source)
            current_frame_shape = frame.shape[:2]
            if self.last_frame_shape is not None and current_frame_shape != self.last_frame_shape:
                # Frame dimensions changed - reset skip counter and use fresh tracking
                self.skip_frames = 5
                self.use_fresh_tracking = True
                self.fresh_tracking_frame_count = 0
                print(f"[INFO] Frame dimensions changed from {self.last_frame_shape} to {current_frame_shape}, resetting tracker")
            self.last_frame_shape = current_frame_shape
            
            # Skip first few frames after dimension change or new video to allow tracker to initialize
            if self.skip_frames > 0:
                self.skip_frames -= 1
                # Use predict() for first frame to avoid tracking state issues, then skip subsequent frames
                if self.skip_frames == 4:  # First frame - use predict to initialize
                    try:
                        results = self.model.predict(frame, conf=self.conf_threshold, verbose=False)
                        # Don't process boxes here, just initialize
                    except:
                        pass
                # Skip these frames entirely during initialization
                continue
            
            # Use fresh tracking (persist=False) for first few frames after new video/dimension change
            # This prevents dimension mismatch errors by not reusing old tracker state
            if self.use_fresh_tracking:
                try:
                    # Use persist=False for first frames after reset - this creates fresh tracker state
                    results = self.model.track(frame, conf=self.conf_threshold, persist=False, verbose=False)
                    boxes = results[0].boxes
                    self.fresh_tracking_frame_count += 1
                    # After 5 successful frames, switch to persist=True to maintain tracking within the video
                    if self.fresh_tracking_frame_count >= 5:
                        self.use_fresh_tracking = False
                        print("[INFO] Switched to persistent tracking mode")
                except Exception as e:
                    # If tracking fails, fall back to predict
                    print(f"Tracking error (using predict): {e}")
                    try:
                        results = self.model.predict(frame, conf=self.conf_threshold, verbose=False)
                        boxes = results[0].boxes
                    except Exception as e2:
                        print(f"Detection also failed (skipping frame): {e2}")
                        continue
            else:
                # Normal tracking with persist=True (maintains track IDs across frames)
                try:
                    results = self.model.track(frame, conf=self.conf_threshold, persist=True, verbose=False)
                    boxes = results[0].boxes
                except Exception as e:
                    # If tracking fails, check if it's a dimension mismatch
                    error_str = str(e)
                    if "Assertion failed" in error_str or "size()" in error_str:
                        # Dimension mismatch - switch to fresh tracking
                        print(f"Tracking dimension mismatch error (switching to fresh tracking): {e}")
                        self.use_fresh_tracking = True
                        self.fresh_tracking_frame_count = 0
                        self.skip_frames = 3
                        continue
                    else:
                        # Other error - try predict() as fallback
                        print(f"Tracking error (fallback to detect): {e}")
                        try:
                            results = self.model.predict(frame, conf=self.conf_threshold, verbose=False)
                            boxes = results[0].boxes
                        except Exception as e2:
                            print(f"Detection also failed (skipping frame): {e2}")
                            continue
            
            # Extract elephant detections with track IDs
            current_track_ids = set()
            detection_stats = {"calm": 0, "warning": 0, "aggressive": 0}
            has_aggressive = False
            has_warning = False
            
            for box in boxes:
                if int(box.cls.item()) != self.elephant_id:
                    continue
                
                # Get track ID (should always be present when using track())
                track_id = None
                if box.id is not None:
                    try:
                        track_id = int(box.id.item())
                    except:
                        track_id = None
                
                # Skip if no track ID (shouldn't happen with track(), but handle gracefully)
                if track_id is None:
                    continue
                
                current_track_ids.add(track_id)
                
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                conf = float(box.conf.item())
                cx = (x1 + x2) // 2
                cy = (y1 + y2) // 2
                center = (cx, cy)
                box_id = track_id
                
                # Bounding box size
                bbox_width = x2 - x1
                bbox_height = y2 - y1
                bbox_size = bbox_width * bbox_height
                
                # Calculate speed
                speed_kmph = 0.0
                if box_id in self.classifier.trackers and len(self.classifier.trackers[box_id].positions) > 0:
                    prev_center = self.classifier.trackers[box_id].positions[-1]
                    dx = cx - prev_center[0]
                    dy = cy - prev_center[1]
                    pixel_distance = math.sqrt(dx * dx + dy * dy)
                    
                    if len(self.classifier.trackers[box_id].times) > 0:
                        prev_time = self.classifier.trackers[box_id].times[-1]
                        time_diff = current_time - prev_time
                        
                        if 0.01 <= time_diff <= 0.5:
                            speed_px_per_sec = pixel_distance / time_diff
                            bbox_avg_size = (bbox_width + bbox_height) / 2.0
                            bbox_avg_size = max(20, bbox_avg_size)
                            meters_per_pixel = ELEPHANT_LENGTH_M / bbox_avg_size
                            speed_mps = speed_px_per_sec * meters_per_pixel
                            speed_kmph = speed_mps * 3.6
                            speed_kmph = min(speed_kmph, 40.0)
                            if speed_kmph < 0.5:
                                speed_kmph = 0.0
                
                # Classify behavior
                behavior, aggression_score = self.classifier.classify(
                    box_id, center, bbox_size, speed_kmph, current_time
                )
                
                # Update statistics
                detection_stats[behavior] = detection_stats.get(behavior, 0) + 1
                if behavior == "aggressive":
                    has_aggressive = True
                elif behavior == "warning":
                    has_warning = True
                
                # Determine color based on behavior
                if behavior == "calm":
                    color = (0, 255, 0)  # Green
                    status_text = "CALM"
                elif behavior == "aggressive":
                    color = (0, 0, 255)  # Red
                    status_text = "AGGRESSIVE"
                else:  # warning
                    color = (0, 165, 255)  # Orange
                    status_text = "WARNING"
                
                # Draw bounding box
                cv2.rectangle(frame, (x1, y1), (x2, y2), color, 3)
                
                # Draw label
                label_y = max(40, y1 - 10)
                label_text = status_text
                font_scale = 1.2
                thickness = 3
                (text_width, text_height), baseline = cv2.getTextSize(
                    label_text, cv2.FONT_HERSHEY_SIMPLEX, font_scale, thickness
                )
                
                padding = 5
                cv2.rectangle(
                    frame,
                    (x1, label_y - text_height - padding),
                    (x1 + text_width + padding * 2, label_y + baseline + padding),
                    color,
                    -1
                )
                
                cv2.putText(
                    frame,
                    label_text,
                    (x1 + padding, label_y),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    font_scale,
                    (255, 255, 255),
                    thickness
                )
                
                # Store detection in database buffer (for batch insertion)
                if self.db_worker:
                    # Calculate video timestamp if FPS is available
                    video_timestamp = None
                    if hasattr(self, 'video_fps') and self.video_fps > 0:
                        video_timestamp = self.frame_count / self.video_fps
                    
                    detection_data = {
                        'track_id': track_id,
                        'frame_number': self.frame_count,
                        'confidence': conf,
                        'behavior': behavior,
                        'bbox_x1': x1,
                        'bbox_y1': y1,
                        'bbox_x2': x2,
                        'bbox_y2': y2,
                        'speed_kmph': speed_kmph,
                        'aggression_score': aggression_score,
                        'alert_triggered': False,  # Alerts are stored separately
                        'alert_type': None,
                        'detected_at': datetime.fromtimestamp(current_time),
                        'timestamp': video_timestamp,
                    }
                    self.detections_buffer.append(detection_data)
                    
                    # Store batch when buffer is full (async via worker)
                    if len(self.detections_buffer) >= self.batch_size:
                        self.db_worker.add_task("store_batch", detections=self.detections_buffer.copy())
                        self.detections_buffer = []  # Clear buffer
            
            # Clean up old tracks
            boxes_to_remove = [box_id for box_id in self.classifier.trackers.keys() if box_id not in current_track_ids]
            for box_id in boxes_to_remove:
                if box_id in self.classifier.trackers:
                    del self.classifier.trackers[box_id]
                if box_id in self.classifier.bbox_sizes:
                    del self.classifier.bbox_sizes[box_id]
                if box_id in self.classifier.aggressive_count:
                    del self.classifier.aggressive_count[box_id]
                if box_id in self.classifier.confirmed_states:
                    del self.classifier.confirmed_states[box_id]
            
            # Send frame and stats to UI
            self.frame_ready.emit(frame)
            stats_with_frame = {
                **detection_stats, 
                "frame": self.frame_count, 
                "has_aggressive": has_aggressive, 
                "has_warning": has_warning
            }
            self.stats_ready.emit(stats_with_frame)
            
            # Control frame rate (approximately 30 FPS)
            time.sleep(1.0 / fps if fps > 0 else 0.03)
        
        # Flush remaining detections buffer (async via worker)
        if self.db_worker and self.detections_buffer:
            self.db_worker.add_task("store_batch", detections=self.detections_buffer.copy())
            self.detections_buffer = []
        
        # Update session status in database (async via worker)
        if self.db_worker:
            self.db_worker.add_task(
                "update_session",
                status="completed",
                total_frames=self.frame_count
            )
        
        # Cleanup
        if self.cap is not None:
            self.cap.release()


# ----------------------------
# Main Window
# ----------------------------
class ElephantDetectionUI(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("🐘 Elephant Aggression Detection System")
        self.setGeometry(100, 100, 1200, 800)
        
        # Initialize model and classifier
        self.model = None
        self.elephant_id = None
        self.classifier = None
        self.processor = None
        
        # UI state
        self.conf_threshold = DEFAULT_CONF_THRESHOLD
        self.alarm_enabled = True
        self.current_source = None
        self.is_processing = False
        
        # Audio manager
        self.audio_manager = AudioManager()
        
        # Database API client and worker
        self.db_client = None
        self.db_worker = None
        if DB_API_AVAILABLE and DB_ENABLED:
            try:
                self.db_client = DetectionAPIClient(base_url=DB_API_URL, enabled=DB_ENABLED)
                self.db_worker = DatabaseWorker(self.db_client)
                self.db_worker.start()  # Start the database worker thread
                print(f"[DB] Database API client initialized: {DB_API_URL}")
            except Exception as e:
                print(f"[DB] Failed to initialize API client: {e}")
                self.db_client = None
                self.db_worker = None
        
        # Load model
        self.load_model()
        
        # Initialize classifier
        if self.model is not None:
            self.classifier = AggressionClassifier()
        
        # Setup UI
        self.init_ui()
        
    def load_model(self):
        """Load YOLO model and find elephant class ID"""
        try:
            if not os.path.exists(MODEL_PATH):
                QMessageBox.critical(self, "Error", f"Model file not found: {MODEL_PATH}")
                return False
            
            self.model = YOLO(MODEL_PATH)
            
            # Find elephant class ID
            self.elephant_id = None
            for k, v in self.model.names.items():
                if v == "elephant":
                    self.elephant_id = k
                    break
            
            if self.elephant_id is None:
                QMessageBox.critical(self, "Error", "Elephant class not found in model!")
                return False
            
            print(f"[INFO] Elephant class ID: {self.elephant_id}")
            return True
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Failed to load model: {e}")
            return False
    
    def init_ui(self):
        """Initialize the UI"""
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        main_layout = QHBoxLayout(central_widget)
        
        # Left sidebar for controls
        sidebar = self.create_sidebar()
        main_layout.addWidget(sidebar, stretch=0)
        
        # Right side for video display
        video_layout = QVBoxLayout()
        
        # Video display label
        self.video_label = QLabel()
        self.video_label.setText("No video source selected")
        self.video_label.setAlignment(Qt.AlignCenter)
        self.video_label.setMinimumSize(800, 600)
        self.video_label.setStyleSheet("background-color: black; color: white; font-size: 16px;")
        video_layout.addWidget(self.video_label)
        
        # Statistics label
        self.stats_label = QLabel("Frame: 0 | Calm: 0 | Warning: 0 | Aggressive: 0")
        self.stats_label.setStyleSheet("font-size: 12px; padding: 5px; background-color: #f0f0f0;")
        video_layout.addWidget(self.stats_label)
        
        main_layout.addLayout(video_layout, stretch=1)
    
    def create_sidebar(self):
        """Create the sidebar with controls"""
        sidebar = QWidget()
        sidebar.setFixedWidth(300)
        sidebar_layout = QVBoxLayout(sidebar)
        sidebar_layout.setSpacing(10)
        
        # Model status
        status_group = QGroupBox("Model Status")
        status_layout = QVBoxLayout()
        if self.model is not None:
            status_label = QLabel(f"✅ Model loaded\nElephant ID: {self.elephant_id}")
        else:
            status_label = QLabel("❌ Model not loaded")
        status_layout.addWidget(status_label)
        status_group.setLayout(status_layout)
        sidebar_layout.addWidget(status_group)
        
        # Source selection
        source_group = QGroupBox("Video Source")
        source_layout = QVBoxLayout()
        
        self.source_radio_group = QButtonGroup()
        self.radio_file = QRadioButton("Browse Video File")
        self.radio_camera = QRadioButton("Use Camera")
        self.radio_file.setChecked(True)
        self.source_radio_group.addButton(self.radio_file, 0)
        self.source_radio_group.addButton(self.radio_camera, 1)
        
        source_layout.addWidget(self.radio_file)
        source_layout.addWidget(self.radio_camera)
        
        self.browse_button = QPushButton("Browse...")
        self.browse_button.clicked.connect(self.browse_video)
        source_layout.addWidget(self.browse_button)
        
        source_group.setLayout(source_layout)
        sidebar_layout.addWidget(source_group)
        
        # Control buttons
        control_group = QGroupBox("Controls")
        control_layout = QVBoxLayout()
        
        self.start_button = QPushButton("▶ Start Detection")
        self.start_button.clicked.connect(self.start_detection)
        self.start_button.setStyleSheet("background-color: #4CAF50; color: white; font-weight: bold; padding: 10px; border-radius: 5px;")
        control_layout.addWidget(self.start_button)
        
        self.stop_button = QPushButton("⏹ Stop")
        self.stop_button.clicked.connect(self.stop_detection)
        self.stop_button.setEnabled(False)
        self.stop_button.setStyleSheet("background-color: #f44336; color: white; font-weight: bold; padding: 10px; border-radius: 5px;")
        control_layout.addWidget(self.stop_button)
        
        control_group.setLayout(control_layout)
        sidebar_layout.addWidget(control_group)
        
        # Settings
        settings_group = QGroupBox("Settings")
        settings_layout = QVBoxLayout()
        
        # Confidence threshold
        conf_label = QLabel("Confidence Threshold:")
        settings_layout.addWidget(conf_label)
        
        conf_layout = QHBoxLayout()
        self.conf_slider = QSlider(Qt.Horizontal)
        self.conf_slider.setMinimum(5)
        self.conf_slider.setMaximum(95)
        self.conf_slider.setValue(int(DEFAULT_CONF_THRESHOLD * 100))
        self.conf_slider.valueChanged.connect(self.on_conf_changed)
        conf_layout.addWidget(self.conf_slider)
        self.conf_value_label = QLabel(f"{DEFAULT_CONF_THRESHOLD:.2f}")
        self.conf_value_label.setMinimumWidth(50)
        self.conf_value_label.setAlignment(Qt.AlignRight)
        conf_layout.addWidget(self.conf_value_label)
        settings_layout.addLayout(conf_layout)
        
        # Alarm checkbox
        self.alarm_checkbox = QCheckBox("Alarm On")
        self.alarm_checkbox.setChecked(True)
        self.alarm_checkbox.stateChanged.connect(self.on_alarm_toggled)
        settings_layout.addWidget(self.alarm_checkbox)
        
        # Database status
        if self.db_worker and self.db_client and self.db_client.enabled:
            db_status_label = QLabel("✅ Database Logging: Enabled")
            db_status_label.setStyleSheet("color: green; font-size: 10px;")
            settings_layout.addWidget(db_status_label)
        else:
            db_status_label = QLabel("❌ Database Logging: Disabled")
            db_status_label.setStyleSheet("color: gray; font-size: 10px;")
            settings_layout.addWidget(db_status_label)
        
        settings_group.setLayout(settings_layout)
        sidebar_layout.addWidget(settings_group)
        
        sidebar_layout.addStretch()
        
        return sidebar
    
    def browse_video(self):
        """Browse for video file"""
        file_path, _ = QFileDialog.getOpenFileName(
            self,
            "Select Video File",
            "",
            "Video Files (*.mp4 *.avi *.mov *.mkv);;All Files (*)"
        )
        if file_path:
            self.current_source = file_path
            self.video_label.setText(f"Selected: {os.path.basename(file_path)}")
    
    def on_conf_changed(self, value):
        """Handle confidence threshold change"""
        self.conf_threshold = value / 100.0
        self.conf_value_label.setText(f"{self.conf_threshold:.2f}")
        if self.processor is not None:
            self.processor.set_confidence(self.conf_threshold)
    
    def on_alarm_toggled(self, state):
        """Handle alarm checkbox toggle"""
        self.alarm_enabled = (state == Qt.Checked)
        if not self.alarm_enabled:
            self.audio_manager.stop_alarm()
    
    def start_detection(self):
        """Start video processing"""
        if self.model is None or self.elephant_id is None:
            QMessageBox.warning(self, "Error", "Model not loaded!")
            return
        
        # Determine source
        if self.radio_file.isChecked():
            if not self.current_source:
                QMessageBox.warning(self, "Error", "Please select a video file first!")
                return
            source = self.current_source
        else:
            source = 0  # Default camera
        
        # Reset classifier for new session (fresh start)
        self.classifier = AggressionClassifier()
        
        self.processor = VideoProcessor(self.model, self.elephant_id, self.classifier, self.conf_threshold, self.db_worker)
        self.processor.frame_ready.connect(self.update_frame)
        self.processor.stats_ready.connect(self.update_stats)
        self.processor.finished.connect(self.on_processing_finished)
        
        # Set video source
        if not self.processor.set_video_source(source):
            QMessageBox.warning(self, "Error", f"Could not open video source: {source}")
            return
        
        # Start processing
        self.processor.start()
        self.is_processing = True
        self.start_button.setEnabled(False)
        self.stop_button.setEnabled(True)
        self.browse_button.setEnabled(False)
        self.radio_file.setEnabled(False)
        self.radio_camera.setEnabled(False)
    
    def stop_detection(self):
        """Stop video processing"""
        if self.processor is not None:
            self.processor.stop()
            self.processor.wait()  # Wait for thread to finish
            
            # Update session status to stopped (async via worker)
            if self.db_worker:
                self.db_worker.add_task(
                    "update_session",
                    status="stopped",
                    total_frames=self.processor.frame_count
                )
            
            self.processor = None
        
        self.is_processing = False
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.browse_button.setEnabled(True)
        self.radio_file.setEnabled(True)
        self.radio_camera.setEnabled(True)
        self.audio_manager.stop_alarm()
        
        self.video_label.setText("Detection stopped")
        self.stats_label.setText("Frame: 0 | Calm: 0 | Warning: 0 | Aggressive: 0")
    
    @pyqtSlot(np.ndarray)
    def update_frame(self, frame):
        """Update video display with new frame"""
        # Convert BGR to RGB
        frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        
        # Resize if needed (maintain aspect ratio, max 800x600)
        h, w = frame_rgb.shape[:2]
        max_w, max_h = 800, 600
        scale = min(max_w / w, max_h / h)
        
        if scale < 1.0:
            new_w = int(w * scale)
            new_h = int(h * scale)
            frame_rgb = cv2.resize(frame_rgb, (new_w, new_h), interpolation=cv2.INTER_AREA)
        
        # Convert to QImage
        q_image = QImage(frame_rgb.data, frame_rgb.shape[1], frame_rgb.shape[0], 
                        frame_rgb.strides[0], QImage.Format_RGB888)
        
        # Display
        pixmap = QPixmap.fromImage(q_image)
        self.video_label.setPixmap(pixmap)
    
    @pyqtSlot(dict)
    def update_stats(self, stats):
        """Update statistics display"""
        frame = stats.get("frame", 0)
        calm = stats.get("calm", 0)
        warning = stats.get("warning", 0)
        aggressive = stats.get("aggressive", 0)
        
        self.stats_label.setText(
            f"Frame: {frame} | Calm: {calm} | Warning: {warning} | Aggressive: {aggressive}"
        )
        
        # Handle alarm and store alerts in database
        if stats.get("has_aggressive", False):
            # Always store aggressive alerts in database (even if alarm is disabled)
            if self.db_worker:
                self.db_worker.add_task(
                    "store_alert",
                    alert_type="alarm_sound",
                    message="Aggressive elephant detected - Alarm triggered"
                )
            # Play alarm sound only if enabled
            if self.alarm_enabled:
                self.audio_manager.play_aggressive_alarm()
        elif stats.get("has_warning", False):
            # Store warning alerts in database
            if self.db_worker:
                self.db_worker.add_task(
                    "store_alert",
                    alert_type="warning_tts",
                    message="Warning - Elephant moving at warning speed"
                )
            # Play warning sound only if enabled
            if self.alarm_enabled:
                self.audio_manager.play_warning_sound()
        else:
            if self.alarm_enabled:
                self.audio_manager.stop_alarm()
    
    def on_processing_finished(self):
        """Handle processing finished"""
        self.is_processing = False
        self.start_button.setEnabled(True)
        self.stop_button.setEnabled(False)
        self.browse_button.setEnabled(True)
        self.radio_file.setEnabled(True)
        self.radio_camera.setEnabled(True)
        self.audio_manager.stop_alarm()
        
        # Session already updated in processor thread, but ensure it's marked as completed (async)
        if self.processor and self.db_worker:
            self.db_worker.add_task("update_session", status="completed")
    
    def closeEvent(self, event):
        """Handle window close event"""
        self.stop_detection()
        
        # Stop database worker thread
        if self.db_worker:
            self.db_worker.stop()
            self.db_worker.wait()
        
        event.accept()


# ----------------------------
# Main Entry Point
# ----------------------------
def main():
    app = QApplication(sys.argv)
    app.setStyle("Fusion")  # Modern look
    
    window = ElephantDetectionUI()
    window.show()
    
    sys.exit(app.exec_())


if __name__ == "__main__":
    main()

