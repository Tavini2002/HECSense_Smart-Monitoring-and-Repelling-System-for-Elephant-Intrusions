# Distance Calculation Accuracy & Calibration Guide

## Is the Distance Calculation Accurate?

**Short Answer:** The method is **theoretically correct**, but the accuracy depends on **proper calibration**. The current values are **placeholders** and need to be calibrated for your specific camera setup.

---

## Current Configuration

```python
ELEPHANT_WIDTH_M = 6.0      # Average elephant body length (meters)
FOCAL_LENGTH = 800          # ⚠️ PLACEHOLDER - Needs calibration!
```

### Issues with Current Setup:

1. **FOCAL_LENGTH = 800** is a **placeholder value**
   - This is NOT calibrated for your camera
   - Different cameras have different focal lengths
   - This will cause **inaccurate distance measurements**

2. **ELEPHANT_WIDTH_M = 6.0** is an **average**
   - Real elephants vary: 4.5m - 7.5m body length
   - Asian elephants: ~5-6m
   - African elephants: ~6-7m
   - This can cause ±10-20% error

3. **Bounding box width** depends on:
   - Elephant's angle/pose (side view vs front view)
   - Camera angle
   - Detection accuracy

---

## Accuracy Range

**Without Calibration:**
- ❌ **Very inaccurate** - Could be off by 50-100% or more
- Distance shown might be 20m when actual is 40m (or vice versa)

**With Proper Calibration:**
- ✅ **Moderately accurate** - Typically ±10-20% error
- At 20m actual distance, might show 18-22m
- At 50m actual distance, might show 45-55m

**Best Case (Perfect Calibration + Known Elephant Size):**
- ✅ **Good accuracy** - ±5-10% error
- Suitable for warning zones (20m, 50m thresholds)

---

## How to Calibrate for Accurate Distance

### Method 1: Using a Known Distance Frame

**Step 1:** Find a frame in your video where you know the actual distance
- Use a reference object (person, vehicle, known landmark)
- Or use GPS/mapping if available
- Or physically measure the distance

**Step 2:** Measure the elephant's bounding box width in pixels
```python
# In the video frame, note:
# - Actual distance to elephant (e.g., 30 meters)
# - Bounding box width in pixels (e.g., 240 pixels)
```

**Step 3:** Calculate the correct FOCAL_LENGTH
```python
# Formula: FOCAL_LENGTH = (pixel_width × actual_distance) / real_width
# Example:
# pixel_width = 240 pixels
# actual_distance = 30 meters
# real_width = 6.0 meters
# FOCAL_LENGTH = (240 × 30) / 6.0 = 1200
```

**Step 4:** Update the code
```python
FOCAL_LENGTH = 1200  # Your calibrated value
```

### Method 2: Using Camera Specifications

If you know your camera specs:

```python
# Focal length in pixels = (sensor_width_mm / pixel_width_mm) × focal_length_mm
# Or use camera's field of view (FOV) to calculate

# Example for common cameras:
# - Smartphone camera: ~1000-2000 pixels
# - Webcam: ~500-1000 pixels  
# - DSLR: ~2000-4000 pixels
```

### Method 3: Multiple Point Calibration

**Best Accuracy:** Calibrate using multiple known distances

1. Find 3-5 frames with known distances (e.g., 10m, 20m, 30m, 40m, 50m)
2. Measure bounding box width for each
3. Calculate FOCAL_LENGTH for each frame
4. Use the average value

```python
# Frame 1: 10m distance, 480px width → FOCAL_LENGTH = 800
# Frame 2: 20m distance, 240px width → FOCAL_LENGTH = 800
# Frame 3: 30m distance, 160px width → FOCAL_LENGTH = 800
# Average: 800 (consistent = good calibration!)
```

---

## Improving Accuracy Further

### 1. Adjust Elephant Width Based on Species

```python
# Asian Elephant (smaller)
ELEPHANT_WIDTH_M = 5.5

# African Elephant (larger)
ELEPHANT_WIDTH_M = 6.5

# Or use average
ELEPHANT_WIDTH_M = 6.0
```

### 2. Account for Elephant Pose

The bounding box width varies based on:
- **Side view** (best): Full body length visible
- **Front/back view** (worst): Only width visible, not length
- **Angled view**: Partial length

**Current limitation:** The code uses bounding box width, which assumes side view.

### 3. Use Multiple Measurements

Instead of single frame, average distance over multiple frames:
```python
# Average distance over last 5 frames for stability
distance_history = []
distance_history.append(distance_m)
if len(distance_history) > 5:
    distance_history.pop(0)
distance_m = sum(distance_history) / len(distance_history)
```

---

## Testing Accuracy

### Quick Test:

1. **Find a frame** where elephant is at known distance (e.g., 25 meters)
2. **Run detection** and note the calculated distance
3. **Compare:**
   - If calculated = 25m → ✅ Accurate!
   - If calculated = 50m → ❌ FOCAL_LENGTH too high (divide by 2)
   - If calculated = 12.5m → ❌ FOCAL_LENGTH too low (multiply by 2)

### Calibration Formula:

```python
# If actual distance = 25m, but code shows 50m:
# Current FOCAL_LENGTH = 800
# Correct FOCAL_LENGTH = 800 × (25 / 50) = 400

# If actual distance = 25m, but code shows 12.5m:
# Current FOCAL_LENGTH = 800
# Correct FOCAL_LENGTH = 800 × (25 / 12.5) = 1600
```

---

## Real-World Accuracy Expectations

| Scenario | Accuracy | Notes |
|----------|----------|-------|
| **Uncalibrated** | ±50-100% | Not usable |
| **Basic Calibration** | ±20-30% | Acceptable for zones |
| **Good Calibration** | ±10-15% | Good for warning system |
| **Perfect Calibration** | ±5-10% | Best possible with this method |

**For your warning zones:**
- **20m threshold**: ±2-3m error is acceptable (18-23m range)
- **50m threshold**: ±5-7m error is acceptable (45-55m range)

---

## Limitations of This Method

1. **Assumes side view** - Front/back view will be inaccurate
2. **Single elephant size** - Doesn't account for size variation
3. **No depth information** - Monocular camera limitation
4. **Camera angle matters** - Tilted camera affects accuracy
5. **Distance accuracy decreases** with distance (farther = less accurate)

---

## Recommendations

1. **✅ Calibrate FOCAL_LENGTH** using Method 1 (known distance frame)
2. **✅ Test with multiple distances** to verify accuracy
3. **✅ Adjust ELEPHANT_WIDTH_M** if you know the species
4. **✅ Use zone thresholds** (20m, 50m) - they account for some error
5. **⚠️ Don't rely on exact distance** - use zones instead

---

## Quick Calibration Script

Add this to your code temporarily to help calibrate:

```python
# Add after distance calculation:
if frame_count % 30 == 0:  # Print every 30 frames
    print(f"Frame {frame_count}: Distance = {distance_m:.1f}m, "
          f"Bbox width = {bbox_width_px}px, "
          f"FOCAL_LENGTH would be = {(distance_m * bbox_width_px) / ELEPHANT_WIDTH_M:.0f}")
```

This will help you find the right FOCAL_LENGTH value.

---

## Conclusion

**Current accuracy:** ❌ **Not accurate** - FOCAL_LENGTH needs calibration

**After calibration:** ✅ **Moderately accurate** - Good enough for zone-based warnings

**Best practice:** Calibrate using a known distance frame, then test and adjust as needed.




