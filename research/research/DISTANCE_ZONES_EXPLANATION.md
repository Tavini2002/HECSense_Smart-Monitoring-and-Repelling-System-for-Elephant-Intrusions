# Distance Calculation Method & Zone System

## Recommended Distance Calculation Method

### **Focal Length Method (Currently Implemented)**

**Why this method is perfect for your system:**
- ✅ Works with a single camera (no additional hardware needed)
- ✅ Already implemented and tested in your codebase
- ✅ Cost-effective and practical
- ✅ Good accuracy when properly calibrated
- ✅ Real-time performance

**How it works:**
```
Distance (meters) = (Real Object Width × Focal Length) / Pixel Width in Image
```

**Formula used:**
```python
distance_m = (ELEPHANT_WIDTH_M * FOCAL_LENGTH) / bbox_width_px
```

Where:
- `ELEPHANT_WIDTH_M = 6.0` meters (average elephant body length)
- `FOCAL_LENGTH = 800` (needs calibration for your specific camera)
- `bbox_width_px` = width of bounding box in pixels

### Alternative Methods (Not Recommended for Your Use Case)

1. **Stereo Vision** - Requires two cameras, complex calibration
2. **LiDAR/Depth Sensors** - Expensive hardware
3. **Deep Learning Depth Estimation** - Requires training data and more computation

## Distance Zones Configuration

Based on wildlife safety recommendations and human-elephant conflict management:

### 🟢 **SAFE ZONE (Green)**
- **Distance:** > 130 meters
- **Color:** Green bounding box
- **Action:** No alerts, elephant is far away
- **Rationale:** At this distance, there's no immediate threat

### 🟠 **WARNING ZONE (Orange)**
- **Distance:** 70 - 130 meters
- **Color:** Orange bounding box
- **Action:** 
  - TTS Warning: "Warning, elephant is on X.X meters"
  - Warning repeats every 3 seconds (cooldown)
  - Continuous distance announcements while in warning zone
- **Rationale:** Elephant is approaching, caution needed

### 🔴 **DANGER ZONE (Red)**
- **Distance:** < 70 meters
- **Color:** Red bounding box
- **Action:**
  - TTS Alert: "X.X meters. Danger!"
  - Firecracker alarm sound (loops continuously)
  - Alert repeats every 2 seconds (cooldown)
- **Rationale:** Elephant is very close, immediate threat - requires immediate action

## Adjusting Distance Thresholds

You can modify the thresholds in `detect_elephant_distance.py`:

```python
SAFE_ZONE_DISTANCE = 130.0     # meters - Green bounding box
WARNING_ZONE_DISTANCE = 70.0   # meters - Orange bounding box + TTS warning
DANGER_ZONE_DISTANCE = 70.0    # meters - Red bounding box + TTS + Firecracker alarm
```

**Current Configuration:**
- **Safe Zone:** > 130 meters (Green)
- **Warning Zone:** 70 - 130 meters (Orange)
- **Danger Zone:** < 70 meters (Red)

**Recommendations for different scenarios:**
- **Urban/Residential areas:** Lower thresholds (40m warning, 25m danger)
- **Wildlife reserves:** Current thresholds (70m warning, 70m danger)
- **Agricultural fields:** Can use higher thresholds (100m warning, 50m danger)

## Calibration Guide

### Step 1: Capture Reference Frame
1. Place an object of known size (or elephant at known distance) in frame
2. Measure the actual distance to the object
3. Note the pixel width of the object in the image

### Step 2: Calculate Focal Length
```python
FOCAL_LENGTH = (pixel_width × actual_distance) / real_object_width
```

### Step 3: Update Configuration
Update `FOCAL_LENGTH` in the script with your calculated value.

## Demo Videos

### Where to Get Test Videos:

1. **Pixabay** (Free stock videos)
   - URL: https://pixabay.com/videos/search/elephant/
   - Download free HD/4K elephant videos

2. **NESTLER Wild Animal Recognition Dataset**
   - URL: https://zenodo.org/records/15804949
   - Contains elephant videos from Uganda

3. **YouTube** (with permission)
   - Search for "elephant wildlife video"
   - Download using tools like `yt-dlp` or `youtube-dl`

4. **Roboflow Elephant Detection Dataset**
   - URL: https://universe.roboflow.com/roboflow-universe-projects/elephant-detection-cxnt1

### Creating Synthetic Test Videos:

If you have access to 3D software or game engines, you can create test scenarios with known distances for calibration.

## Installation Requirements

```bash
pip install opencv-python ultralytics pyttsx3 pygame
```

**Note:** 
- `pyttsx3` requires system TTS engine:
  - Windows: Built-in (SAPI5)
  - Linux: Requires `espeak` or `festival`
  - macOS: Built-in

## Usage

```bash
cd research
python detect_elephant_distance.py
```

Make sure:
- Video file path is correct in `VIDEO_SOURCE`
- Model file `yolov8s.pt` exists
- Firecracker sound file path is correct (if using alarm)

## Features Implemented

✅ Color-coded bounding boxes (Green/Orange/Red)
✅ TTS warnings for warning and danger zones
✅ Firecracker alarm for danger zone
✅ Cooldown system to prevent alert spam
✅ Real-time distance estimation
✅ Zone-based visual feedback

