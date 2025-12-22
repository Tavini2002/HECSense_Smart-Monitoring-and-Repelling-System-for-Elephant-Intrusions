import cv2
import time
from ultralytics import YOLO

MODEL_PATH = "yolov8s.pt"
VIDEO_SOURCE = "input-2.mp4"

CONF_THRESHOLD = 0.35
ELEPHANT_WIDTH_M = 6.0
FOCAL_LENGTH = 800

DISPLAY_WIDTH = 960
DISPLAY_HEIGHT = 540

BOX_COLOR = (0, 0, 255)
TEXT_COLOR = (0, 0, 255)


def estimate_distance(real_width_m, focal_length, bbox_width_px):
    if bbox_width_px <= 0:
        return None
    return round((real_width_m * focal_length) / bbox_width_px, 1)


def draw_detection(frame, x1, y1, x2, y2, label):
    cv2.rectangle(frame, (x1, y1), (x2, y2), BOX_COLOR, 2)
    cv2.putText(
        frame,
        label,
        (x1, max(25, y1 - 10)),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.6,
        TEXT_COLOR,
        2
    )


def resize_for_display(frame):
    h, w = frame.shape[:2]
    scale = min(DISPLAY_WIDTH / w, DISPLAY_HEIGHT / h)
    return cv2.resize(
        frame,
        (int(w * scale), int(h * scale)),
        interpolation=cv2.INTER_AREA
    )


model = YOLO(MODEL_PATH)
elephant_id = next((k for k, v in model.names.items() if v == "elephant"), None)

cap = cv2.VideoCapture(VIDEO_SOURCE)

prev_time = time.time()

while True:
    ret, frame = cap.read()
    if not ret:
        break

    # FPS calculation
    current_time = time.time()
    fps = 1 / (current_time - prev_time)
    prev_time = current_time

    results = model.predict(frame, conf=CONF_THRESHOLD, verbose=False)

    for box in results[0].boxes:
        if int(box.cls.item()) != elephant_id:
            continue

        x1, y1, x2, y2 = map(int, box.xyxy[0])
        confidence = float(box.conf.item())

        distance = estimate_distance(
            ELEPHANT_WIDTH_M,
            FOCAL_LENGTH,
            max(1, x2 - x1)
        )

        label = f"Elephant {confidence:.2f} | {distance} m"
        draw_detection(frame, x1, y1, x2, y2, label)

    # FPS overlay
    cv2.putText(
        frame,
        f"FPS: {int(fps)}",
        (15, 30),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.8,
        (0, 255, 0),
        2
    )

    display_frame = resize_for_display(frame)
    cv2.imshow("Elephant Distance Estimation", display_frame)

    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

cap.release()
cv2.destroyAllWindows()
