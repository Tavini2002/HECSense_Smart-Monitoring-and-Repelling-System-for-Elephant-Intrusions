import os
import time
import cv2
import threading
import gradio as gr
from ultralytics import YOLO

# Use pygame for controllable audio (playsound can't be force-stopped while playing)
import pygame

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH_DEFAULT = "models/yolov8s.pt"
ALARM_AUDIO_DIR = os.path.join("assets", "alarm-sound-effects")

ALARM_SOUND_MAP = {
    "Warning Alarm": "2-warning-alarm.mp3",
    "Firecrackers": "1-firecrackers-sound-effect.mp3",
    "Bee Sound": "0-bees-sound-effect.mp3",
}

# ----------------------------
# Global State
# ----------------------------
_yolo_model = None
_elephant_class_idx = None
_stop_event = threading.Event()

# Audio-related state
_audio_is_ready = False
_alarm_is_active = False
_alarm_file_in_use = None


# ----------------------------
# Helpers – Audio
# ----------------------------
def ensure_audio_initialized() -> None:
    """
    Initialize the pygame audio mixer once.
    """
    global _audio_is_ready
    if _audio_is_ready:
        return

    try:
        # Use common stable settings, fall back to defaults on failure.
        pygame.mixer.init(frequency=44100, size=-16, channels=2, buffer=512)
    except Exception:
        pygame.mixer.init()

    _audio_is_ready = True


def play_alarm_loop(sound_path: str, volume: float = 1.0) -> None:
    """
    Start or switch the alarm sound. Plays in a loop (non-blocking).
    """
    global _alarm_is_active, _alarm_file_in_use

    ensure_audio_initialized()

    # Reload only if not already playing this exact sound
    if (not _alarm_is_active) or (_alarm_file_in_use != sound_path):
        try:
            pygame.mixer.music.load(sound_path)
            pygame.mixer.music.set_volume(max(0.0, min(1.0, float(volume))))
            pygame.mixer.music.play(loops=-1)
            _alarm_is_active = True
            _alarm_file_in_use = sound_path
        except Exception as exc:
            print(f"[Alarm] Could not play '{sound_path}': {exc}")


def stop_alarm_immediately() -> None:
    """
    Force-stop any currently playing alarm sound.
    """
    global _alarm_is_active

    if not _audio_is_ready:
        return

    try:
        pygame.mixer.music.stop()
    except Exception:
        # Ignore any cleanup errors
        pass

    _alarm_is_active = False


# ----------------------------
# Helpers – Model & Detection
# ----------------------------
def get_model_and_elephant_id():
    """
    Load YOLO model on first call and cache the elephant class index.
    """
    global _yolo_model, _elephant_class_idx

    if _yolo_model is None:
        _yolo_model = YOLO(MODEL_PATH_DEFAULT)

        names = getattr(_yolo_model, "names", None) or getattr(_yolo_model.model, "names", None)

        if isinstance(names, dict):
            # Find key whose label is 'elephant'
            matching_keys = [k for k, v in names.items() if v == "elephant"]
            if not matching_keys:
                raise gr.Error("Could not find 'elephant' class in model.")
            _elephant_class_idx = matching_keys[0]
        elif isinstance(names, list):
            if "elephant" not in names:
                raise gr.Error("Could not find 'elephant' class in model.")
            _elephant_class_idx = names.index("elephant")
        else:
            raise gr.Error("Could not find 'elephant' class in model.")

    return _yolo_model, int(_elephant_class_idx)


def resolve_alarm_path(selection: str) -> str | None:
    """
    Map dropdown label to actual audio file path, return None if not found.
    """
    filename = ALARM_SOUND_MAP.get(selection)
    if not filename:
        return None

    audio_path = os.path.join(ALARM_AUDIO_DIR, filename)
    return audio_path if os.path.exists(audio_path) else None


def signal_stop_processing() -> None:
    """
    Set the flag to stop frame processing and halt any playing alarm.
    """
    _stop_event.set()
    stop_alarm_immediately()


# ----------------------------
# Main Video Processing
# ----------------------------
def process_video_stream(video_file, conf_threshold, alarm_enabled, alarm_label, alarm_volume):
    """
    Generator that yields processed frames for Gradio's streaming Image component.
    """
    global _stop_event
    _stop_event.clear()

    if video_file is None:
        raise gr.Error("Please upload or select a video.")

    # Gradio File component returns a dict; but also handle raw string for robustness
    video_path = video_file if isinstance(video_file, str) else video_file.get("path", None)

    if not video_path or not os.path.exists(video_path):
        raise gr.Error("Invalid video file path.")

    model, elephant_id = get_model_and_elephant_id()
    capture = cv2.VideoCapture(video_path)

    if not capture.isOpened():
        raise gr.Error("Could not open the selected video.")

    bbox_color_red = (0, 0, 255)
    alarm_path = resolve_alarm_path(alarm_label)

    try:
        while not _stop_event.is_set():
            success, frame = capture.read()
            if not success:
                break

            # Run detection
            results = model.predict(frame, conf=conf_threshold, verbose=False)
            prediction = results[0]

            # Check if any detection corresponds to elephant class
            elephant_detected = any(int(box.cls.item()) == elephant_id for box in prediction.boxes)

            # Alarm control logic
            if alarm_enabled and elephant_detected and alarm_path:
                play_alarm_loop(alarm_path, volume=alarm_volume)
            else:
                stop_alarm_immediately()

            # Draw bounding boxes for elephants only
            for box in prediction.boxes:
                if int(box.cls.item()) != elephant_id:
                    continue

                x1, y1, x2, y2 = map(int, box.xyxy[0].tolist())
                confidence_value = float(box.conf.item()) if box.conf is not None else 0.0

                cv2.rectangle(frame, (x1, y1), (x2, y2), bbox_color_red, 2)
                cv2.putText(
                    frame,
                    f"Elephant {confidence_value:.2f}",
                    (x1, max(20, y1 - 10)),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.6,
                    bbox_color_red,
                    2,
                    cv2.LINE_AA,
                )

            # Convert BGR to RGB for Gradio
            yield frame[:, :, ::-1]
    finally:
        capture.release()
        stop_alarm_immediately()


# ----------------------------
# Gradio Interface
# ----------------------------
with gr.Blocks(title="Real-time Elephant Detection (Stoppable Alarm)") as demo:
    gr.Markdown(
        """
    # 🐘 HEC-Sense AI System
    Upload a video, enable alarm if desired, and start detection.  
    Use **Stop** to immediately stop both detection and sound.
    """
    )

    with gr.Row():
        video_input = gr.File(label="Choose Video", file_types=["video"], file_count="single")
        confidence_slider = gr.Slider(
            minimum=0.05,
            maximum=0.95,
            value=0.35,
            step=0.05,
            label="Confidence",
        )

    with gr.Row():
        alarm_toggle = gr.Checkbox(label="Alarm ON", value=False)
        alarm_dropdown = gr.Dropdown(
            choices=list(ALARM_SOUND_MAP.keys()),
            value="Warning Alarm",
            label="Alarm Sound",
        )
        alarm_volume_slider = gr.Slider(
            minimum=0.0,
            maximum=1.0,
            value=0.9,
            step=0.05,
            label="Alarm Volume",
        )

    video_output = gr.Image(label="Live Output", streaming=True, height=480)

    with gr.Row():
        start_button = gr.Button("🚀 Start Detection", variant="primary")
        stop_button = gr.Button("🛑 Stop", variant="stop")

    # Bind actions
    start_button.click(
        fn=process_video_stream,
        inputs=[video_input, confidence_slider, alarm_toggle, alarm_dropdown, alarm_volume_slider],
        outputs=video_output,
    )

    stop_button.click(fn=lambda: signal_stop_processing(), inputs=[], outputs=[])


if __name__ == "__main__":
    demo.launch()
