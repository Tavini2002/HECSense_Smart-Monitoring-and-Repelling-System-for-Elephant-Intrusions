import os
import time
import cv2
import threading
import gradio as gr
from ultralytics import YOLO
import pygame

# ----------------------------
# Configurations
# ----------------------------
DEFAULT_MODEL_PATH = "models/yolov8s.pt"
ALARM_BASE_DIR = os.path.join("assets", "alarm-sound-effects")

SOUND_FILES = {
    "Warning Alarm": "2-warning-alarm.mp3",
    "Firecrackers": "1-firecrackers-sound-effect.mp3",
    "Bee Sound": "0-bees-sound-effect.mp3",
}

# ----------------------------
# Globals
# ----------------------------
_model = None
_elephant_id = None
_process_stop = threading.Event()

_audio_initialized = False
_alarm_playing = False
_current_sound_path = None

# ----------------------------
# Utilities
# ----------------------------
def _init_audio():
    global _audio_initialized
    if _audio_initialized:
        return
    try:
        pygame.mixer.init(frequency=44100, size=-16, channels=2, buffer=512)
    except Exception:
        pygame.mixer.init()
    _audio_initialized = True

def _load_model():
    global _model, _elephant_id
    if _model is None:
        _model = YOLO(DEFAULT_MODEL_PATH)
        names = getattr(_model, "names", None) or getattr(_model.model, "names", None)
        if isinstance(names, dict):
            _elephant_id = [k for k, v in names.items() if v == "elephant"][0]
        elif isinstance(names, list):
            _elephant_id = names.index("elephant")
        else:
            raise gr.Error("Could not find 'elephant' class in model.")
    return _model, int(_elephant_id)

def _resolve_sound_path(choice: str):
    fname = SOUND_FILES.get(choice)
    if not fname:
        return None
    p = os.path.join(ALARM_BASE_DIR, fname)
    return p if os.path.exists(p) else None

def _start_alarm(sound_path: str, volume: float = 1.0):
    global _alarm_playing, _current_sound_path
    _init_audio()
    if (not _alarm_playing) or (_current_sound_path != sound_path):
        try:
            pygame.mixer.music.load(sound_path)
            pygame.mixer.music.set_volume(max(0.0, min(1.0, volume)))
            pygame.mixer.music.play(loops=-1)
            _alarm_playing = True
            _current_sound_path = sound_path
        except Exception as e:
            print(f"[Alarm] Failed to play '{sound_path}': {e}")

def _stop_alarm():
    global _alarm_playing
    if not _audio_initialized:
        return
    try:
        pygame.mixer.music.stop()
    except Exception:
        pass
    _alarm_playing = False

def _stop_process():
    _process_stop.set()
    _stop_alarm()

# ----------------------------
# Main Stream Function
# ----------------------------
def stream_processed_frames(video_file, conf, alarm_on, alarm_choice, alarm_volume):
    global _process_stop
    _process_stop.clear()

    if video_file is None:
        raise gr.Error("Please upload or select a video.")
    video_path = video_file if isinstance(video_file, str) else video_file.get("path", None)
    if not video_path or not os.path.exists(video_path):
        raise gr.Error("Invalid video file path.")

    model, elephant_id = _load_model()
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise gr.Error("Could not open the selected video.")

    highlight_color = (0, 255, 255)
    sound_path = _resolve_sound_path(alarm_choice)

    try:
        while not _process_stop.is_set():
            ok, frame = cap.read()
            if not ok:
                break

            results = model.predict(frame, conf=conf, verbose=False)
            res = results[0]
            detected = any(int(b.cls.item()) == elephant_id for b in res.boxes)

            if alarm_on and detected and sound_path:
                _start_alarm(sound_path, volume=alarm_volume)
            else:
                _stop_alarm()

            for b in res.boxes:
                if int(b.cls.item()) != elephant_id:
                    continue
                x1, y1, x2, y2 = map(int, b.xyxy[0].tolist())
                conf_val = float(b.conf.item()) if b.conf is not None else 0.0
                cv2.rectangle(frame, (x1, y1), (x2, y2), highlight_color, 2)
                cv2.putText(frame, f"Elephant {conf_val:.2f}", (x1, max(20, y1 - 10)),
                            cv2.FONT_HERSHEY_SIMPLEX, 0.6, highlight_color, 2, cv2.LINE_AA)

            yield frame[:, :, ::-1]
    finally:
        cap.release()
        _stop_alarm()

# ----------------------------
# Gradio UI
# ----------------------------
with gr.Blocks(title="Real-time Elephant Detection (Stoppable Alarm)", 
               css="""
               body, .gradio-container {
                   background-color: #0f0c29 !important;
                   background: linear-gradient(135deg, #0f0c29, #302b63, #24243e) !important;
                   color: #f0f0f0;
                   font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
               }

               /* Buttons */
               .gr-button.primary {
                   background: linear-gradient(135deg, #ff7e5f, #feb47b) !important;
                   color: white !important;
                   font-weight: bold;
                   border-radius: 14px;
                   box-shadow: 0 0 12px rgba(255,126,95,0.6);
                   transition: all 0.3s ease;
               }
               .gr-button.primary:hover {
                   box-shadow: 0 0 28px rgba(255,126,95,0.9);
                   transform: scale(1.05);
               }

               .gr-button.stop {
                   background: linear-gradient(135deg, #ff4b1f, #ff9068) !important;
                   color: white !important;
                   font-weight: bold;
                   border-radius: 14px;
                   box-shadow: 0 0 12px rgba(255,75,31,0.6);
                   transition: all 0.3s ease;
               }
               .gr-button.stop:hover {
                   box-shadow: 0 0 28px rgba(255,75,31,0.9);
                   transform: scale(1.05);
               }

               /* Sliders */
               .gr-slider input[type="range"] {
                   -webkit-appearance: none;
                   width: 100%;
                   height: 10px;
                   background: linear-gradient(90deg, #ff7e5f, #feb47b);
                   border-radius: 6px;
               }
               .gr-slider input[type="range"]::-webkit-slider-thumb {
                   -webkit-appearance: none;
                   width: 20px;
                   height: 20px;
                   background: #fff;
                   border-radius: 50%;
                   border: none;
                   cursor: pointer;
                   box-shadow: 0 0 12px #ff7e5f;
                   transition: box-shadow 0.3s ease;
               }
               .gr-slider input[type="range"]::-webkit-slider-thumb:hover {
                   box-shadow: 0 0 24px #ff7e5f;
               }
               .gr-slider > label {
                   color: #f0f0f0 !important;
               }

               /* Dropdowns */
               .gr-dropdown {
                   background: linear-gradient(135deg, #434343, #000000);
                   color: #f0f0f0 !important;
                   border-radius: 12px;
                   padding: 5px;
               }
               .gr-dropdown select {
                   background: transparent;
                   color: #f0f0f0 !important;
               }
               """) as demo:

    # ------------------------
    # Modern Gradient Header
    # ------------------------
    gr.Markdown("""
    <div style="text-align:center; padding:25px; background: linear-gradient(135deg, #ff7e5f, #feb47b);
                border-radius:16px; box-shadow:0 0 20px rgba(0,0,0,0.4);">
        <h1 style="color:white; font-family: 'Verdana', sans-serif; margin:0;">🐘 HEC-Sense AI System</h1>
        <p style="color:#f9f9f9; font-size:16px; margin-top:8px;">
            Upload your video and enable alarm detection. Start detection and stop anytime to halt the process.
        </p>
    </div>
    """)

    # ------------------------
    # Inputs
    # ------------------------
    with gr.Row():
        file_in = gr.File(label="Choose Video", file_types=["video"], file_count="single")
        conf = gr.Slider(0.05, 0.95, value=0.35, step=0.05, label="Confidence")

    with gr.Row():
        alarm_on = gr.Checkbox(label="Alarm ON", value=False)
        alarm_choice = gr.Dropdown(
            choices=list(SOUND_FILES.keys()),
            value="Warning Alarm",
            label="Alarm Sound"
        )
        alarm_volume = gr.Slider(0.0, 1.0, value=0.9, step=0.05, label="Alarm Volume")

    # ------------------------
    # Output
    # ------------------------
    live_out = gr.Image(label="Live Output", streaming=True, height=480)

    # ------------------------
    # Buttons
    # ------------------------
    with gr.Row():
        start_btn = gr.Button("🚀 Start Detection", variant="primary")
        stop_btn = gr.Button("🛑 Stop", variant="stop")

    start_btn.click(fn=stream_processed_frames,
                    inputs=[file_in, conf, alarm_on, alarm_choice, alarm_volume],
                    outputs=live_out)
    stop_btn.click(fn=lambda: _stop_process(), inputs=[], outputs=[])

if __name__ == "__main__":
    demo.launch()
