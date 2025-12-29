import streamlit as st
import cv2
import numpy as np
import tempfile
import os
import threading
import time
from ultralytics import YOLO
import pygame
import pyttsx3
from database import (
    init_connection_pool, create_detection_session, end_detection_session,
    insert_detection, log_zone_transition, log_alert
)

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH_DEFAULT = "models/yolov8s.pt"
ALARM_AUDIO_DIR = os.path.join("assets", "alarm-sound-effects")
ELEPHANT_WIDTH_M = 6.0
FOCAL_LENGTH = 1600

# Distance Zones (defaults, can be edited in UI)
SAFE_ZONE_DISTANCE_DEFAULT = 130.0
WARNING_ZONE_DISTANCE_DEFAULT = 70.0
DANGER_ZONE_DISTANCE_DEFAULT = 70.0

# Alarm sound paths
_script_dir = os.path.dirname(os.path.abspath(__file__))
ALARM_SOUNDS = {
    "Firecrackers": {
        "file": "1-firecrackers-sound-effect.mp3",
        "paths": [
            os.path.join(_script_dir, "..", "research", "1-firecrackers-sound-effect.mp3"),
            os.path.join(_script_dir, "assets", "alarm-sound-effects", "1-firecrackers-sound-effect.mp3")
        ]
    },
    "Bee Sound": {
        "file": "0-bees-sound-effect.mp3",
        "paths": [
            os.path.join(_script_dir, "assets", "alarm-sound-effects", "0-bees-sound-effect.mp3"),
            os.path.join(_script_dir, "..", "research", "0-bees-sound-effect.mp3")
        ]
    },
    "Warning Alarm": {
        "file": "2-warning-alarm.mp3",
        "paths": [
            os.path.join(_script_dir, "assets", "alarm-sound-effects", "2-warning-alarm.mp3"),
            os.path.join(_script_dir, "..", "research", "2-warning-alarm.mp3")
        ]
    }
}

# ----------------------------
# Initialize Session State
# ----------------------------
if 'model' not in st.session_state:
    st.session_state.model = None
if 'elephant_id' not in st.session_state:
    st.session_state.elephant_id = None
if 'processing' not in st.session_state:
    st.session_state.processing = False
if 'stop_processing' not in st.session_state:
    st.session_state.stop_processing = False
if 'audio_initialized' not in st.session_state:
    st.session_state.audio_initialized = False
if 'tts_engine' not in st.session_state:
    st.session_state.tts_engine = None
if 'last_warning_time' not in st.session_state:
    st.session_state.last_warning_time = {}
if 'last_danger_time' not in st.session_state:
    st.session_state.last_danger_time = {}
if 'current_zone' not in st.session_state:
    st.session_state.current_zone = None
if 'warning_cooldown' not in st.session_state:
    st.session_state.warning_cooldown = 2.5  # Reduced for more frequent warnings
if 'danger_cooldown' not in st.session_state:
    st.session_state.danger_cooldown = 2.0
if 'safe_zone_distance' not in st.session_state:
    st.session_state.safe_zone_distance = SAFE_ZONE_DISTANCE_DEFAULT
if 'warning_zone_distance' not in st.session_state:
    st.session_state.warning_zone_distance = WARNING_ZONE_DISTANCE_DEFAULT
if 'danger_zone_distance' not in st.session_state:
    st.session_state.danger_zone_distance = DANGER_ZONE_DISTANCE_DEFAULT
if 'selected_alarm_sound' not in st.session_state:
    st.session_state.selected_alarm_sound = "Firecrackers"
if 'current_session_id' not in st.session_state:
    st.session_state.current_session_id = None
if 'frame_number' not in st.session_state:
    st.session_state.frame_number = 0
if 'db_enabled' not in st.session_state:
    st.session_state.db_enabled = True  # Enable database by default

# ----------------------------
# Helper Functions
# ----------------------------
def init_model():
    """Load YOLO model and find elephant class ID."""
    if st.session_state.model is None:
        try:
            # Check if model file exists
            if not os.path.exists(MODEL_PATH_DEFAULT):
                st.error(f"Model file not found at: {MODEL_PATH_DEFAULT}")
                return False
            
            st.session_state.model = YOLO(MODEL_PATH_DEFAULT)
            # Find elephant class ID
            elephant_id = None
            for k, v in st.session_state.model.names.items():
                if v == "elephant":
                    elephant_id = k
                    break
            if elephant_id is None:
                st.error("Elephant class not found in model!")
                return False
            st.session_state.elephant_id = elephant_id
            return True
        except Exception as e:
            st.error(f"Error loading model: {e}")
            import traceback
            st.error(traceback.format_exc())
            return False
    return True

def init_audio():
    """Initialize pygame mixer."""
    if not st.session_state.audio_initialized:
        try:
            pygame.mixer.init(frequency=44100, size=-16, channels=2, buffer=512)
            st.session_state.audio_initialized = True
        except:
            pygame.mixer.init()
            st.session_state.audio_initialized = True

def init_tts():
    """Initialize TTS engine."""
    if st.session_state.tts_engine is None:
        try:
            st.session_state.tts_engine = pyttsx3.init()
            st.session_state.tts_engine.setProperty('rate', 220)
            st.session_state.tts_engine.setProperty('volume', 0.9)
        except Exception as e:
            st.warning(f"Could not initialize TTS: {e}")

def get_zone_info(distance_m):
    """Get zone color and name based on distance."""
    safe_dist = st.session_state.safe_zone_distance
    warning_dist = st.session_state.warning_zone_distance
    
    if distance_m >= safe_dist:
        return (0, 255, 0), "SAFE", False, False  # Green
    elif distance_m >= warning_dist:
        return (0, 165, 255), "WARNING", True, False  # Orange
    else:
        return (0, 0, 255), "DANGER", False, True  # Red

def play_warning_speech(distance_m):
    """Play warning TTS in a separate thread."""
    def speak():
        try:
            # Create a new TTS engine instance for this call
            engine = pyttsx3.init()
            if engine:
                engine.setProperty('rate', 220)
                engine.setProperty('volume', 0.9)
                message = f"Warning, elephant is on {distance_m:.1f} meters"
                engine.say(message)
                engine.runAndWait()
                engine.stop()
        except Exception as e:
            if "run loop" not in str(e).lower():
                pass  # Silently ignore in thread
    
    # Play in separate thread to avoid blocking
    thread = threading.Thread(target=speak, daemon=True)
    thread.start()

def get_alarm_sound_path(sound_name):
    """Get the path to the selected alarm sound file."""
    if sound_name in ALARM_SOUNDS:
        for path in ALARM_SOUNDS[sound_name]["paths"]:
            if os.path.exists(path):
                return path
    return None

def play_danger_alert(distance_m):
    """Play danger TTS then selected alarm sound."""
    def alert_sequence():
        try:
            # Step 1: Play TTS first
            init_tts()
            if st.session_state.tts_engine:
                message = f"Danger Danger {distance_m:.1f} meters"
                st.session_state.tts_engine.say(message)
                st.session_state.tts_engine.runAndWait()
            
            # Step 2: Start selected alarm sound
            try:
                sound_path = get_alarm_sound_path(st.session_state.selected_alarm_sound)
                
                if sound_path:
                    init_audio()
                    pygame.mixer.music.load(sound_path)
                    pygame.mixer.music.set_volume(0.9)
                    pygame.mixer.music.play(loops=-1)
                else:
                    st.warning(f"Alarm sound file not found: {st.session_state.selected_alarm_sound}")
            except Exception as e:
                st.warning(f"Could not play alarm: {e}")
        except Exception as e:
            st.warning(f"Danger alert error: {e}")
    
    thread = threading.Thread(target=alert_sequence, daemon=True)
    thread.start()

def stop_alarm():
    """Stop firecracker alarm."""
    try:
        if st.session_state.audio_initialized:
            pygame.mixer.music.stop()
    except:
        pass

def process_frame(frame, conf_threshold, video_timestamp=None):
    """Process a single frame and return annotated frame."""
    if st.session_state.model is None or st.session_state.elephant_id is None:
        return frame
    
    # Increment frame number
    st.session_state.frame_number += 1
    
    # Run detection
    results = st.session_state.model.predict(frame, conf=conf_threshold, verbose=False)
    prediction = results[0]
    
    current_time = time.time()
    elephant_detected = False
    
    for box in prediction.boxes:
        if int(box.cls.item()) != st.session_state.elephant_id:
            continue
        
        elephant_detected = True
        x1, y1, x2, y2 = map(int, box.xyxy[0].tolist())
        conf = float(box.conf.item())
        
        bbox_width_px = max(1, x2 - x1)
        distance_m = (ELEPHANT_WIDTH_M * FOCAL_LENGTH) / bbox_width_px
        
        # Get zone info
        color_bgr, zone_name, should_warn, should_danger = get_zone_info(distance_m)
        
        # Handle warnings and alerts
        previous_zone = st.session_state.current_zone
        st.session_state.current_zone = zone_name
        
        # Log zone transition if zone changed
        if previous_zone and previous_zone != zone_name and st.session_state.db_enabled and st.session_state.current_session_id:
            log_zone_transition(
                st.session_state.current_session_id,
                previous_zone,
                zone_name,
                distance_m
            )
        
        if should_warn:
            if previous_zone == "DANGER":
                stop_alarm()
            
            if 'warning_zone' not in st.session_state.last_warning_time or \
               (current_time - st.session_state.last_warning_time['warning_zone']) >= st.session_state.warning_cooldown:
                play_warning_speech(distance_m)
                st.session_state.last_warning_time['warning_zone'] = current_time
                
                # Log alert to database
                if st.session_state.db_enabled and st.session_state.current_session_id:
                    log_alert(
                        st.session_state.current_session_id,
                        'warning_tts',
                        f"Warning, elephant is on {distance_m:.1f} meters",
                        distance_m,
                        zone_name
                    )
            stop_alarm()
            
            # Store detection in database
            if st.session_state.db_enabled and st.session_state.current_session_id:
                detection_id = insert_detection(
                    st.session_state.current_session_id,
                    distance_m,
                    zone_name,
                    conf,
                    (x1, y1, x2, y2),
                    st.session_state.frame_number,
                    video_timestamp,
                    alert_triggered=True,
                    alert_type='warning_tts'
                )
        
        elif should_danger:
            if 'warning_zone' in st.session_state.last_warning_time:
                del st.session_state.last_warning_time['warning_zone']
            
            detection_id = f"{x1}_{y1}"
            
            # Ensure alarm keeps playing continuously while in danger zone
            alarm_started = False
            try:
                init_audio()
                # Check if alarm is playing, if not start it
                if not pygame.mixer.music.get_busy():
                    # Start alarm sound (loops continuously)
                    sound_path = get_alarm_sound_path(st.session_state.selected_alarm_sound)
                    if sound_path:
                        pygame.mixer.music.load(sound_path)
                        pygame.mixer.music.set_volume(0.9)
                        pygame.mixer.music.play(loops=-1)  # Loop continuously
                        alarm_started = True
                        
                        # Log alarm start
                        if st.session_state.db_enabled and st.session_state.current_session_id:
                            log_alert(
                                st.session_state.current_session_id,
                                'alarm_sound',
                                f"Alarm started: {st.session_state.selected_alarm_sound}",
                                distance_m,
                                zone_name
                            )
            except Exception as e:
                pass
            
            # Play TTS with cooldown
            if detection_id not in st.session_state.last_danger_time or \
               (current_time - st.session_state.last_danger_time[detection_id]) >= st.session_state.danger_cooldown:
                # Play TTS in background
                def play_tts_only():
                    try:
                        init_tts()
                        if st.session_state.tts_engine:
                            message = f"Danger Danger {distance_m:.1f} meters"
                            st.session_state.tts_engine.say(message)
                            st.session_state.tts_engine.runAndWait()
                    except:
                        pass
                tts_thread = threading.Thread(target=play_tts_only, daemon=True)
                tts_thread.start()
                st.session_state.last_danger_time[detection_id] = current_time
                
                # Log danger TTS alert
                if st.session_state.db_enabled and st.session_state.current_session_id:
                    log_alert(
                        st.session_state.current_session_id,
                        'danger_tts',
                        f"Danger Danger {distance_m:.1f} meters",
                        distance_m,
                        zone_name
                    )
            
            # Store detection in database
            if st.session_state.db_enabled and st.session_state.current_session_id:
                db_detection_id = insert_detection(
                    st.session_state.current_session_id,
                    distance_m,
                    zone_name,
                    conf,
                    (x1, y1, x2, y2),
                    st.session_state.frame_number,
                    video_timestamp,
                    alert_triggered=True,
                    alert_type='alarm_sound' if alarm_started else 'danger_tts'
                )
        else:
            if previous_zone in ["WARNING", "DANGER"]:
                stop_alarm()
                # Log alarm stop
                if st.session_state.db_enabled and st.session_state.current_session_id:
                    log_alert(
                        st.session_state.current_session_id,
                        'stop_alarm',
                        "Alarm stopped - elephant in safe zone",
                        distance_m if 'distance_m' in locals() else None,
                        "SAFE"
                    )
            if 'warning_zone' in st.session_state.last_warning_time:
                del st.session_state.last_warning_time['warning_zone']
            stop_alarm()
            st.session_state.current_zone = "SAFE"
            
            # Store detection in database (safe zone)
            if st.session_state.db_enabled and st.session_state.current_session_id and elephant_detected:
                insert_detection(
                    st.session_state.current_session_id,
                    distance_m,
                    zone_name,
                    conf,
                    (x1, y1, x2, y2),
                    st.session_state.frame_number,
                    video_timestamp,
                    alert_triggered=False,
                    alert_type='none'
                )
        
        # Draw bounding box
        cv2.rectangle(frame, (x1, y1), (x2, y2), color_bgr, 3)
        
        # Draw label
        label_text = f"{zone_name} | {distance_m:.1f}m | {conf:.2f}"
        label_size, _ = cv2.getTextSize(label_text, cv2.FONT_HERSHEY_SIMPLEX, 0.7, 2)
        label_y = max(30, y1 - 10)
        
        cv2.rectangle(
            frame,
            (x1, label_y - label_size[1] - 5),
            (x1 + label_size[0] + 5, label_y + 5),
            (0, 0, 0),
            -1
        )
        
        cv2.putText(
            frame,
            label_text,
            (x1 + 2, label_y),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.7,
            color_bgr,
            2,
            cv2.LINE_AA
        )
    
    if not elephant_detected:
        stop_alarm()
        st.session_state.last_warning_time.clear()
        st.session_state.last_danger_time.clear()
        st.session_state.current_zone = None
    
    return frame

# ----------------------------
# Streamlit UI
# ----------------------------
st.set_page_config(
    page_title="HEC-Sense AI - Elephant Detection",
    page_icon="🐘",
    layout="wide"
)

st.title("🐘 HEC-Sense AI - Elephant Detection System")
st.markdown("Real-time elephant detection with distance-based warning zones")

# Initialize database connection pool
if st.session_state.db_enabled:
    try:
        init_connection_pool()
    except Exception as e:
        st.warning(f"⚠️ Database connection failed: {e}. Running without database storage.")
        st.session_state.db_enabled = False

# Auto-load model on startup
if st.session_state.model is None:
    with st.spinner("Loading YOLO model..."):
        if init_model():
            st.session_state.model_loaded = True
        else:
            st.session_state.model_loaded = False

# Sidebar Configuration
with st.sidebar:
    st.header("⚙️ Configuration")
    
    # Model status
    if st.session_state.model is not None:
        st.success(f"✅ Model loaded (Elephant ID: {st.session_state.elephant_id})")
    else:
        st.error("❌ Model not loaded")
        if st.button("🔄 Reload Model", type="primary"):
            with st.spinner("Loading YOLO model..."):
                if init_model():
                    st.success("Model loaded successfully!")
                    st.rerun()
                else:
                    st.error("Failed to load model!")
    
    st.divider()
    
    # Detection settings
    conf_threshold = st.slider(
        "Confidence Threshold",
        min_value=0.05,
        max_value=0.95,
        value=0.10,
        step=0.05
    )
    
    st.divider()
    
    # Editable Distance Zones
    st.header("📍 Distance Zones Configuration")
    
    safe_dist = st.number_input(
        "🟢 Safe Zone Distance (meters)",
        min_value=10.0,
        max_value=500.0,
        value=st.session_state.safe_zone_distance,
        step=5.0,
        help="Distance threshold for Safe Zone (Green). Elephant is considered safe if distance > this value.",
        key="safe_zone_input"
    )
    st.session_state.safe_zone_distance = safe_dist
    
    warning_dist = st.number_input(
        "🟠 Warning Zone Distance (meters)",
        min_value=5.0,
        max_value=200.0,
        value=st.session_state.warning_zone_distance,
        step=5.0,
        help="Distance threshold for Warning Zone (Orange). Warning zone is between this value and Safe Zone.",
        key="warning_zone_input"
    )
    st.session_state.warning_zone_distance = warning_dist
    
    danger_dist = st.number_input(
        "🔴 Danger Zone Distance (meters)",
        min_value=5.0,
        max_value=200.0,
        value=st.session_state.danger_zone_distance,
        step=5.0,
        help="Distance threshold for Danger Zone (Red). Elephant is in danger zone if distance < this value.",
        key="danger_zone_input"
    )
    st.session_state.danger_zone_distance = danger_dist
    
    # Display current zone ranges (updates dynamically)
    st.info(f"""
    **Current Zones:**
    - 🟢 Safe: > {safe_dist}m
    - 🟠 Warning: {warning_dist}m - {safe_dist}m
    - 🔴 Danger: < {warning_dist}m
    """)
    
    st.divider()
    
    # Audio settings
    enable_audio = st.checkbox("Enable Audio Alerts", value=True)
    
    st.divider()
    
    # Alarm sound selection
    st.header("🔊 Alarm Sound")
    sound_options = list(ALARM_SOUNDS.keys())
    current_index = 0
    if st.session_state.selected_alarm_sound in sound_options:
        current_index = sound_options.index(st.session_state.selected_alarm_sound)
    
    selected_sound = st.selectbox(
        "Select Alarm Sound",
        options=sound_options,
        index=current_index,
        help="Choose the alarm sound to play in danger zone"
    )
    st.session_state.selected_alarm_sound = selected_sound
    
    if st.button("Stop All Audio"):
        stop_alarm()
        st.session_state.last_warning_time.clear()
        st.session_state.last_danger_time.clear()

# Main Content
tab1, tab2 = st.tabs(["📹 Video Upload", "📷 Camera"])

with tab1:
    st.header("Upload Video")
    uploaded_file = st.file_uploader(
        "Choose a video file",
        type=['mp4', 'avi', 'mov', 'mkv'],
        help="Upload a video file to analyze"
    )
    
    if uploaded_file is not None:
        # Save uploaded file to temporary location
        tfile = tempfile.NamedTemporaryFile(delete=False, suffix='.mp4')
        tfile.write(uploaded_file.read())
        video_path = tfile.name
        
        col1, col2, col3 = st.columns([1, 1, 2])
        
        with col1:
            play_button = st.button("▶️ Play Detection", type="primary", use_container_width=True)
        
        with col2:
            stop_button = st.button("⏹️ Stop", use_container_width=True)
        
        if stop_button:
            st.session_state.stop_processing = True
            st.session_state.processing = False
            stop_alarm()
            
            # End detection session
            if st.session_state.db_enabled and st.session_state.current_session_id:
                end_detection_session(st.session_state.current_session_id, 'stopped')
                st.session_state.current_session_id = None
        
        if play_button:
            # Try to load model if not loaded
            if st.session_state.model is None:
                with st.spinner("Loading model..."):
                    if not init_model():
                        st.error("❌ Failed to load model! Please check if models/yolov8s.pt exists in the source folder.")
                        st.stop()
                    else:
                        st.success("✅ Model loaded!")
                        st.rerun()
            
            if st.session_state.model is not None:
                st.session_state.processing = True
                st.session_state.stop_processing = False
                st.session_state.frame_number = 0
                
                # Create detection session
                if st.session_state.db_enabled:
                    session_name = f"Video: {uploaded_file.name}"
                    st.session_state.current_session_id = create_detection_session(
                        session_name=session_name,
                        source_type='video_upload',
                        source_path=uploaded_file.name
                    )
                    if st.session_state.current_session_id:
                        st.success(f"✅ Session started (ID: {st.session_state.current_session_id})")
                
                # Video processing
                cap = cv2.VideoCapture(video_path)
                if not cap.isOpened():
                    st.error("Could not open video file!")
                else:
                    fps = cap.get(cv2.CAP_PROP_FPS)
                    video_placeholder = st.empty()
                    
                    try:
                        while st.session_state.processing and not st.session_state.stop_processing:
                            ret, frame = cap.read()
                            if not ret:
                                break
                            
                            # Calculate video timestamp
                            video_timestamp = st.session_state.frame_number / fps if fps > 0 else None
                            
                            # Process frame
                            processed_frame = process_frame(frame.copy(), conf_threshold, video_timestamp)
                            
                            # Convert BGR to RGB for Streamlit
                            processed_frame_rgb = cv2.cvtColor(processed_frame, cv2.COLOR_BGR2RGB)
                            
                            # Display frame with fixed size to fit window
                            # Resize frame to fit screen (max width 1280px, max height 720px, maintain aspect ratio)
                            h, w = processed_frame_rgb.shape[:2]
                            max_width = 1280
                            max_height = 720
                            
                            # Calculate scale to fit both width and height constraints
                            scale_w = max_width / w if w > max_width else 1.0
                            scale_h = max_height / h if h > max_height else 1.0
                            scale = min(scale_w, scale_h)  # Use the smaller scale to fit both dimensions
                            
                            if scale < 1.0:
                                new_w = int(w * scale)
                                new_h = int(h * scale)
                                processed_frame_rgb = cv2.resize(processed_frame_rgb, (new_w, new_h), interpolation=cv2.INTER_AREA)
                            
                            video_placeholder.image(processed_frame_rgb, channels="RGB", width='content')
                            
                            # Small delay to control playback speed
                            time.sleep(0.03)  # ~30 FPS
                    
                    except Exception as e:
                        st.error(f"Error processing video: {e}")
                    finally:
                        cap.release()
                        st.session_state.processing = False
                        stop_alarm()
                        
                        # End detection session
                        if st.session_state.db_enabled and st.session_state.current_session_id:
                            end_detection_session(st.session_state.current_session_id, 'completed')
                            st.session_state.current_session_id = None
                        
                        st.success("Video processing completed!")

with tab2:
    st.header("Live Camera Feed")
    
    # Camera selection
    camera_option = st.radio(
        "Select Camera",
        ["Use Default Camera (0)", "Custom Camera Index"],
        horizontal=True
    )
    
    camera_index = 0
    if camera_option == "Custom Camera Index":
        camera_index = st.number_input("Camera Index", min_value=0, max_value=10, value=0)
    
    col1, col2 = st.columns([1, 1])
    
    with col1:
        start_camera = st.button("📷 Start Camera", type="primary", use_container_width=True)
    
    with col2:
        stop_camera = st.button("⏹️ Stop Camera", use_container_width=True)
    
    if stop_camera:
        st.session_state.stop_processing = True
        st.session_state.processing = False
        stop_alarm()
        
        # End detection session
        if st.session_state.db_enabled and st.session_state.current_session_id:
            end_detection_session(st.session_state.current_session_id, 'stopped')
            st.session_state.current_session_id = None
    
    if start_camera:
        # Try to load model if not loaded
        if st.session_state.model is None:
            with st.spinner("Loading model..."):
                if not init_model():
                    st.error("❌ Failed to load model! Please check if models/yolov8s.pt exists in the source folder.")
                    st.stop()
                else:
                    st.success("✅ Model loaded!")
                    st.rerun()
        
        if st.session_state.model is not None:
            st.session_state.processing = True
            st.session_state.stop_processing = False
            st.session_state.frame_number = 0
            
            # Create detection session
            if st.session_state.db_enabled:
                session_name = f"Camera {camera_index}"
                st.session_state.current_session_id = create_detection_session(
                    session_name=session_name,
                    source_type='camera',
                    camera_index=camera_index
                )
                if st.session_state.current_session_id:
                    st.success(f"✅ Session started (ID: {st.session_state.current_session_id})")
            
            cap = cv2.VideoCapture(camera_index)
            if not cap.isOpened():
                st.error(f"Could not open camera {camera_index}!")
            else:
                video_placeholder = st.empty()
                
                try:
                    while st.session_state.processing and not st.session_state.stop_processing:
                        ret, frame = cap.read()
                        if not ret:
                            st.warning("Failed to read from camera")
                            break
                        
                        # Process frame (no video timestamp for live camera)
                        processed_frame = process_frame(frame.copy(), conf_threshold, None)
                        
                        # Convert BGR to RGB for Streamlit
                        processed_frame_rgb = cv2.cvtColor(processed_frame, cv2.COLOR_BGR2RGB)
                        
                        # Display frame with fixed size to fit window
                        # Resize frame to fit screen (max width 1280px, max height 720px, maintain aspect ratio)
                        h, w = processed_frame_rgb.shape[:2]
                        max_width = 1280
                        max_height = 720
                        
                        # Calculate scale to fit both width and height constraints
                        scale_w = max_width / w if w > max_width else 1.0
                        scale_h = max_height / h if h > max_height else 1.0
                        scale = min(scale_w, scale_h)  # Use the smaller scale to fit both dimensions
                        
                        if scale < 1.0:
                            new_w = int(w * scale)
                            new_h = int(h * scale)
                            processed_frame_rgb = cv2.resize(processed_frame_rgb, (new_w, new_h), interpolation=cv2.INTER_AREA)
                        
                        video_placeholder.image(processed_frame_rgb, channels="RGB", width='content')
                        
                        # Small delay
                        time.sleep(0.03)
                
                except Exception as e:
                    st.error(f"Error processing camera feed: {e}")
                finally:
                    cap.release()
                    st.session_state.processing = False
                    stop_alarm()
                    
                    # End detection session
                    if st.session_state.db_enabled and st.session_state.current_session_id:
                        end_detection_session(st.session_state.current_session_id, 'stopped')
                        st.session_state.current_session_id = None
                    
                    st.info("Camera feed stopped")


