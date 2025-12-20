import cv2
from ultralytics import YOLO

# ----------------------------
# Configuration
# ----------------------------
MODEL_PATH = "yolov8s.pt"
CONF_THRESHOLD = 0.35
VIDEO_SOURCE = "input-2.mp4"

ELEPHANT_WIDTH_M = 6.0      # average elephant body length (meters)

# ---- Calibration (IMPORTANT) ----
FOCAL_LENGTH = 800          # <-- adjust once using a known-distance frame

# Display size
DISPLAY_WIDTH = 960
DISPLAY_HEIGHT = 540

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

    for box in boxes:
        if int(box.cls.item()) != elephant_id:
            continue

        x1, y1, x2, y2 = map(int, box.xyxy[0])
        conf = float(box.conf.item())

        bbox_width_px = max(1, x2 - x1)

        # Distance estimation
        distance_m = (ELEPHANT_WIDTH_M * FOCAL_LENGTH) / bbox_width_px

        # Draw bounding box
        cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)

        # Label
        cv2.putText(
            frame,
            f"Elephant {conf:.2f} | {distance_m:.1f} m",
            (x1, max(25, y1 - 10)),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.6,
            (0, 0, 255),
            2
        )

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
cv2.destroyAllWindows()
