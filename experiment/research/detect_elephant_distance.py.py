import cv2
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
    return (real_width_m * focal_length) / bbox_width_px


model = YOLO(MODEL_PATH)

elephant_id = next((k for k, v in model.names.items() if v == "elephant"), None)
if elephant_id is None:
    raise RuntimeError("Elephant class not found")

cap = cv2.VideoCapture(VIDEO_SOURCE)

while True:
    ret, frame = cap.read()
    if not ret:
        break

    results = model.predict(frame, conf=CONF_THRESHOLD, verbose=False)

    for box in results[0].boxes:
        if int(box.cls.item()) != elephant_id:
            continue

        x1, y1, x2, y2 = map(int, box.xyxy[0])
        confidence = float(box.conf.item())

        bbox_width = max(1, x2 - x1)
        distance = estimate_distance(
            ELEPHANT_WIDTH_M, FOCAL_LENGTH, bbox_width
        )

        cv2.rectangle(frame, (x1, y1), (x2, y2), BOX_COLOR, 2)
        cv2.putText(
            frame,
            f"Elephant {confidence:.2f} | {distance:.1f} m",
            (x1, max(25, y1 - 10)),
            cv2.FONT_HERSHEY_SIMPLEX,
            0.6,
            TEXT_COLOR,
            2
        )

    frame = cv2.resize(frame, (DISPLAY_WIDTH, DISPLAY_HEIGHT))
    cv2.imshow("Elephant Distance Estimation", frame)

    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

cap.release()
cv2.destroyAllWindows()
