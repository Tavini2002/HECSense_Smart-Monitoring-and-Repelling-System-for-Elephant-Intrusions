import cv2
import time
import math
from ultralytics import YOLO

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH = "yolov8s.pt"     # COCO model
CONF_THRESHOLD = 0.35
VIDEO_SOURCE = "test-run-2.mp4"  # 0 for webcam OR "video.mp4"

ELEPHANT_LENGTH_M = 6.0       # avg adult elephant length (meters)

# Display size (window control)
DISPLAY_WIDTH = 960
DISPLAY_HEIGHT = 540

# ----------------------------
# Load YOLO model
# ----------------------------
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

# ----------------------------
# Video Capture
# ----------------------------
cap = cv2.VideoCapture(VIDEO_SOURCE)
if not cap.isOpened():
    raise RuntimeError("Cannot open video source")

prev_center = None
prev_time = None

# ----------------------------
# Main Loop
# ----------------------------
while True:
    ret, frame = cap.read()
    if not ret:
        break

    current_time = time.time()

    # Run detection on ORIGINAL frame
    results = model.predict(frame, conf=CONF_THRESHOLD, verbose=False)
    boxes = results[0].boxes

    for box in boxes:
        if int(box.cls.item()) != elephant_id:
            continue

        x1, y1, x2, y2 = map(int, box.xyxy[0])
        conf = float(box.conf.item())

        # Center of bounding box
        cx = (x1 + x2) // 2
        cy = (y1 + y2) // 2

        speed_kmph = 0.0

        if prev_center is not None and prev_time is not None:
            dx = cx - prev_center[0]
            dy = cy - prev_center[1]
            pixel_distance = math.sqrt(dx * dx + dy * dy)

            time_diff = current_time - prev_time
            if time_diff > 0:
                speed_px_per_sec = pixel_distance / time_diff

                bbox_width_px = max(1, x2 - x1)
                meters_per_pixel = ELEPHANT_LENGTH_M / bbox_width_px

                speed_mps = speed_px_per_sec * meters_per_pixel
                speed_kmph = speed_mps * 3.6

        prev_center = (cx, cy)
        prev_time = current_time

        # Draw bounding box
        cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)

        # Draw center point
        cv2.circle(frame, (cx, cy), 4, (255, 0, 0), -1)

        # Label
        cv2.putText(
            frame,
            f"Elephant {conf:.2f} | {speed_kmph:.1f} km/h",
            (x1, max(25, y1 - 10)),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.6,
            (0, 0, 255),
            2
        )

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

    cv2.imshow("Elephant Speed Estimation", display_frame)

    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

# ----------------------------
# Cleanup
# ----------------------------
cap.release()
cv2.destroyAllWindows()
