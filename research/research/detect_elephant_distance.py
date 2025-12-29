import cv2
import threading
import time
from ultralytics import YOLO
import pyttsx3
import pygame

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH = "yolov8s.pt"
CONF_THRESHOLD = 0.10
VIDEO_SOURCE = "input-3.mp4"

ELEPHANT_WIDTH_M = 6.0      # average elephant body length (meters)

# ---- Calibration (IMPORTANT) ----
# FOCAL_LENGTH adjusted to double the distance measurements
# If system shows 50m, it will now show 100m (doubled)
FOCAL_LENGTH = 1600         # Doubled from 800 to double distance measurements

# Distance Zones (in meters)
# Based on wildlife safety recommendations:
# - Safe Zone (Green): >130m - Elephant is far, no immediate concern
# - Warning Zone (Orange): 70-130m - Elephant is approaching, caution needed
# - Danger Zone (Red): <70m - Elephant is very close, immediate threat
SAFE_ZONE_DISTANCE = 130.0     # meters - Green bounding box
WARNING_ZONE_DISTANCE = 70.0   # meters - Orange bounding box + TTS warning
DANGER_ZONE_DISTANCE = 70.0    # meters - Red bounding box + TTS + Firecracker alarm

# Display size
DISPLAY_WIDTH = 960
DISPLAY_HEIGHT = 540

# Alarm sound path (firecrackers)
# Try multiple possible paths
import os
_script_dir = os.path.dirname(os.path.abspath(__file__))
# First check research folder (where user has the file)
FIRECRACKER_SOUND = os.path.join(_script_dir, "1-firecrackers-sound-effect.mp3")
# Alternative: check source folder
FIRECRACKER_SOUND_ALT = os.path.join(_script_dir, "..", "source", "assets", "alarm-sound-effects", "1-firecrackers-sound-effect.mp3")

# ----------------------------
# Global State
# ----------------------------
_tts_engine = None
_audio_initialized = False
_last_warning_time = {}  # Track last warning time per elephant to avoid spam
_last_danger_time = {}
_warning_cooldown = 3.0  # seconds between warnings (allows TTS to finish and ensures continuous playback)
_danger_cooldown = 2.0   # seconds between danger alerts

# TTS Control
_tts_thread = None
_tts_stop_event = threading.Event()
_current_zone = None  # Track current zone to handle transitions

# ----------------------------
# Initialize TTS Engine
# ----------------------------
def init_tts():
    """Initialize text-to-speech engine."""
    global _tts_engine
    if _tts_engine is None:
        try:
            _tts_engine = pyttsx3.init()
            # Set speech rate (words per minute) - increased for faster speech
            _tts_engine.setProperty('rate', 220)
            # Set volume (0.0 to 1.0)
            _tts_engine.setProperty('volume', 0.9)
        except Exception as e:
            print(f"[WARNING] Could not initialize TTS: {e}")
    return _tts_engine

# ----------------------------
# Stop TTS
# ----------------------------
def stop_tts():
    """Stop any currently playing TTS."""
    global _tts_engine, _tts_stop_event
    try:
        _tts_stop_event.set()  # Signal to stop
        if _tts_engine is not None:
            try:
                _tts_engine.stop()
            except:
                pass
    except:
        pass

# ----------------------------
# Initialize Audio for Alarms
# ----------------------------
def init_audio():
    """Initialize pygame mixer for alarm sounds."""
    global _audio_initialized
    if not _audio_initialized:
        try:
            pygame.mixer.init(frequency=44100, size=-16, channels=2, buffer=512)
            _audio_initialized = True
        except Exception:
            pygame.mixer.init()
            _audio_initialized = True

# ----------------------------
# Play Warning Speech
# ----------------------------
def play_warning_speech(distance_m, zone_name):
    """Play TTS warning in a separate thread to avoid blocking."""
    global _tts_thread, _tts_stop_event
    
    def speak():
        try:
            # Check if we should stop before starting
            if _tts_stop_event.is_set():
                return
            
            # Use a fresh engine instance to avoid conflicts
            engine = None
            try:
                engine = pyttsx3.init()
                if engine:
                    engine.setProperty('rate', 220)
                    engine.setProperty('volume', 0.9)
                    
                    # Check stop event before speaking
                    if not _tts_stop_event.is_set():
                        message = f"Warning, elephant is on {distance_m:.1f} meters"
                        engine.say(message)
                        # Use runAndWait but check stop event periodically isn't possible
                        # So we just let it finish, but stop will interrupt next call
                        engine.runAndWait()
            except Exception as e:
                # Only print if it's not a stop-related error
                if "run loop" not in str(e).lower():
                    print(f"[WARNING] TTS error: {e}")
            finally:
                if engine:
                    try:
                        engine.stop()
                    except:
                        pass
        except Exception as e:
            if "run loop" not in str(e).lower():
                print(f"[WARNING] TTS error: {e}")
    
    # Stop any existing TTS thread
    stop_tts()
    _tts_stop_event.clear()
    
    # Start new thread
    _tts_thread = threading.Thread(target=speak, daemon=True)
    _tts_thread.start()

# ----------------------------
# Play Danger Alert
# ----------------------------
def play_danger_alert(distance_m):
    """Play TTS 'danger danger' first, then start firecracker sound."""
    global _tts_thread, _tts_stop_event
    
    # Immediately stop warning TTS when entering danger zone
    stop_tts()
    
    def alert_sequence():
        """Play TTS first, then firecrackers."""
        try:
            # Step 1: Play TTS "Danger Danger" first and wait for it to finish
            engine = None
            try:
                engine = pyttsx3.init()
                if engine:
                    engine.setProperty('rate', 220)
                    engine.setProperty('volume', 0.9)
                    message = f"Danger Danger {distance_m:.1f} meters"
                    print(f"[INFO] Playing danger TTS: {message}")
                    engine.say(message)
                    engine.runAndWait()  # Wait for TTS to finish
                    print(f"[INFO] Danger TTS finished, starting firecrackers...")
            except Exception as e:
                if "run loop" not in str(e).lower():
                    print(f"[WARNING] TTS error: {e}")
            finally:
                if engine:
                    try:
                        engine.stop()
                    except:
                        pass
            
            # Step 2: After TTS finishes, start firecrackers
            try:
                # Try primary path first, then alternative
                sound_path = None
                if os.path.exists(FIRECRACKER_SOUND):
                    sound_path = FIRECRACKER_SOUND
                    print(f"[INFO] Found firecracker sound at: {FIRECRACKER_SOUND}")
                elif os.path.exists(FIRECRACKER_SOUND_ALT):
                    sound_path = FIRECRACKER_SOUND_ALT
                    print(f"[INFO] Found firecracker sound at: {FIRECRACKER_SOUND_ALT}")
                else:
                    print(f"[ERROR] Firecracker sound file not found!")
                    print(f"[ERROR] Checked: {FIRECRACKER_SOUND}")
                    print(f"[ERROR] Checked: {FIRECRACKER_SOUND_ALT}")
                
                if sound_path:
                    init_audio()
                    print(f"[INFO] Loading firecracker sound: {sound_path}")
                    pygame.mixer.music.load(sound_path)
                    pygame.mixer.music.set_volume(0.9)
                    print(f"[INFO] Playing firecracker alarm...")
                    pygame.mixer.music.play(loops=-1)  # Loop until stopped
                    print(f"[INFO] Firecracker alarm started!")
                else:
                    print(f"[ERROR] No valid sound path found!")
            except Exception as e:
                print(f"[ERROR] Could not play alarm sound: {e}")
                import traceback
                traceback.print_exc()
        except Exception as e:
            if "run loop" not in str(e).lower():
                print(f"[WARNING] Danger alert error: {e}")
    
    # Run the sequence in a thread (TTS first, then firecrackers)
    thread = threading.Thread(target=alert_sequence, daemon=True)
    thread.start()

# ----------------------------
# Play Danger TTS in Background (while firecrackers are playing)
# ----------------------------
def play_danger_tts_background(distance_m):
    """Play 'danger danger' TTS in background thread."""
    def speak_danger():
        try:
            engine = None
            try:
                engine = pyttsx3.init()
                if engine:
                    engine.setProperty('rate', 220)
                    engine.setProperty('volume', 0.9)
                    message = f"Danger Danger {distance_m:.1f} meters"
                    engine.say(message)
                    engine.runAndWait()
            except Exception as e:
                if "run loop" not in str(e).lower():
                    print(f"[WARNING] Danger TTS error: {e}")
            finally:
                if engine:
                    try:
                        engine.stop()
                    except:
                        pass
        except Exception as e:
            if "run loop" not in str(e).lower():
                print(f"[WARNING] Danger TTS background error: {e}")
    
    thread = threading.Thread(target=speak_danger, daemon=True)
    thread.start()

# ----------------------------
# Stop Alarm
# ----------------------------
def stop_alarm():
    """Stop any playing alarm sound."""
    try:
        if _audio_initialized:
            if pygame.mixer.music.get_busy():
                print(f"[INFO] Stopping firecracker alarm...")
            pygame.mixer.music.stop()
    except Exception as e:
        print(f"[WARNING] Error stopping alarm: {e}")

# ----------------------------
# Get Zone Info Based on Distance
# ----------------------------
def get_zone_info(distance_m):
    """
    Determine zone color, name, and whether to trigger alerts based on distance.
    Returns: (color_bgr, zone_name, should_warn, should_danger_alert)
    """
    if distance_m >= SAFE_ZONE_DISTANCE:
        return (0, 255, 0), "SAFE", False, False  # Green
    elif distance_m >= WARNING_ZONE_DISTANCE:
        return (0, 165, 255), "WARNING", True, False  # Orange
    else:
        return (0, 0, 255), "DANGER", False, True  # Red

# ----------------------------
# Load model
# ----------------------------
model = YOLO(MODEL_PATH)

# Find elephant class ID
elephant_id = None
for k, v in model.names.items():
    if v == "elephant":
        elephant_id = k
        break

if elephant_id is None:
    raise RuntimeError("Elephant class not found")

print(f"[INFO] Elephant class ID: {elephant_id}")
print(f"[INFO] Distance Zones:")
print(f"  - Safe Zone (Green): >{SAFE_ZONE_DISTANCE}m")
print(f"  - Warning Zone (Orange): {WARNING_ZONE_DISTANCE}m - {SAFE_ZONE_DISTANCE}m")
print(f"  - Danger Zone (Red): <{WARNING_ZONE_DISTANCE}m")

# ----------------------------
# Video Capture
# ----------------------------
cap = cv2.VideoCapture(VIDEO_SOURCE)
if not cap.isOpened():
    raise RuntimeError("Cannot open video source")

# ----------------------------
# Main Loop
# ----------------------------
while True:
    ret, frame = cap.read()
    if not ret:
        break

    results = model.predict(frame, conf=CONF_THRESHOLD, verbose=False)
    boxes = results[0].boxes

    current_time = time.time()
    elephant_count = 0
    
    for box in boxes:
        if int(box.cls.item()) != elephant_id:
            continue

        elephant_count += 1
        x1, y1, x2, y2 = map(int, box.xyxy[0])
        conf = float(box.conf.item())

        bbox_width_px = max(1, x2 - x1)

        # Distance estimation using focal length method
        # Formula: distance = (real_width × focal_length) / pixel_width
        distance_m = (ELEPHANT_WIDTH_M * FOCAL_LENGTH) / bbox_width_px

        # Get zone information based on distance
        color_bgr, zone_name, should_warn, should_danger = get_zone_info(distance_m)
        
        # Create unique ID for this detection (using rounded bounding box center for stability)
        center_x = (x1 + x2) // 2
        center_y = (y1 + y2) // 2
        # Round to nearest 50 pixels to create stable ID that doesn't change with small movements
        stable_x = (center_x // 50) * 50
        stable_y = (center_y // 50) * 50
        detection_id = f"{stable_x}_{stable_y}"

        # Handle warnings and alerts with cooldown to avoid spam
        # Track zone transitions to stop warnings immediately when entering danger
        previous_zone = _current_zone
        _current_zone = zone_name
        
        if should_warn:
            # If transitioning from danger to warning, stop danger alerts first
            if previous_zone == "DANGER":
                stop_tts()
                stop_alarm()
            
            # Play warning speech continuously while in warning zone - NO firecrackers
            # Use a global warning time to ensure continuous playback
            if 'warning_zone' not in _last_warning_time or \
               (current_time - _last_warning_time['warning_zone']) >= _warning_cooldown:
                # Clear stop event and play warning
                _tts_stop_event.clear()
                print(f"[INFO] Warning zone: Elephant at {distance_m:.1f}m - Playing warning TTS")
                play_warning_speech(distance_m, zone_name.lower())
                _last_warning_time['warning_zone'] = current_time
            
            # Stop any firecrackers if in warning zone (should only play in danger zone)
            stop_alarm()
        
        elif should_danger:
            # Immediately stop warning TTS when entering danger zone
            if previous_zone == "WARNING" or previous_zone != "DANGER":
                stop_tts()
            
            # Clear warning zone tracking when entering danger zone
            if 'warning_zone' in _last_warning_time:
                del _last_warning_time['warning_zone']
            
            # Play danger alert - ensure firecrackers keep playing
            if detection_id not in _last_danger_time or \
               (current_time - _last_danger_time[detection_id]) >= _danger_cooldown:
                # Start firecrackers if not already playing
                if not _audio_initialized or not pygame.mixer.music.get_busy():
                    print(f"[INFO] Triggering danger alert for elephant at {distance_m:.1f}m")
                    play_danger_alert(distance_m)
                else:
                    # Firecrackers already playing, just play TTS in background
                    print(f"[INFO] Firecrackers already playing, playing danger TTS in background...")
                    play_danger_tts_background(distance_m)
                _last_danger_time[detection_id] = current_time
        else:
            # In safe zone - stop everything
            if previous_zone in ["WARNING", "DANGER"]:
                stop_tts()
            if 'warning_zone' in _last_warning_time:
                del _last_warning_time['warning_zone']
            stop_alarm()
            _current_zone = "SAFE"

        # Draw bounding box with zone-based color
        cv2.rectangle(frame, (x1, y1), (x2, y2), color_bgr, 3)

        # Draw zone label with background for better visibility
        label_text = f"{zone_name} | {distance_m:.1f}m | {conf:.2f}"
        label_size, _ = cv2.getTextSize(label_text, cv2.FONT_HERSHEY_SIMPLEX, 0.7, 2)
        label_y = max(30, y1 - 10)
        
        # Draw background rectangle for text
        cv2.rectangle(
            frame,
            (x1, label_y - label_size[1] - 5),
            (x1 + label_size[0] + 5, label_y + 5),
            (0, 0, 0),
            -1
        )
        
        # Draw text
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
    
    # Clean up old warning times (keep only recent detections)
    if elephant_count == 0:
        # No elephants detected, stop everything
        stop_tts()
        stop_alarm()
        _current_zone = None
        # Clean up old entries
        _last_warning_time.clear()
        _last_danger_time.clear()

    # ----------------------------
    # Resize for display
    # ----------------------------
    h, w = frame.shape[:2]
    scale = min(DISPLAY_WIDTH / w, DISPLAY_HEIGHT / h)

    display_frame = cv2.resize(
        frame,
        (int(w * scale), int(h * scale)),
        interpolation=cv2.INTER_AREA
    )

    cv2.imshow("Elephant Distance Estimation", display_frame)

    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

# ----------------------------
# Cleanup
# ----------------------------
cap.release()
stop_tts()
stop_alarm()
cv2.destroyAllWindows()
print("[INFO] Processing complete.")
