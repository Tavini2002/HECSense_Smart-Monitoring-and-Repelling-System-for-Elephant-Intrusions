# Video Generation Prompts for Elephant Detection Testing

## Purpose
Generate test videos showing an elephant approaching from far away to near, for testing the distance-based warning system.

---

## Prompt for Runway Gen-3 / Gen-2

```
A single African elephant walking on a dirt road in a natural wildlife setting, mountains and trees in the background. The elephant starts far in the distance (approximately 100 meters away) and slowly walks directly toward the camera, getting progressively closer. The camera remains stationary, positioned at eye level. Natural daylight, clear visibility. The elephant should be clearly visible throughout, getting larger as it approaches. Shot from the front/side angle showing the elephant's full body. No other animals or people in frame. Realistic wildlife documentary style.
```

**Alternative (Shorter):**
```
Single elephant walking on a road, starting far away and approaching the camera. Natural wildlife setting with mountains in background. Stationary camera, clear daylight. Elephant gets progressively closer and larger in frame.
```

---

## Prompt for Pika AI

```
A realistic wildlife video: one African elephant walking on a rural dirt road, starting 100 meters away and walking directly toward a stationary camera. The elephant gradually approaches, getting larger in the frame. Natural mountain landscape background with trees. Bright daylight, clear visibility. Documentary style, no other animals. The elephant's full body should be visible throughout the approach.
```

---

## Prompt for Kling AI

```
Wildlife documentary style: A single elephant walking on a dirt path in a natural environment. The elephant begins far in the distance (about 100 meters) and slowly walks toward the camera, getting closer and larger. Mountains and forest visible in the background. Stationary camera position at ground level. Natural lighting, clear day. Only one elephant visible, no other wildlife. The elephant should be clearly identifiable and visible throughout the entire sequence as it approaches.
```

---

## Prompt for Stable Video Diffusion / AnimateDiff

```
A single African elephant walking on a dirt road in a wildlife reserve. The elephant starts far away (approximately 100 meters distance) and walks directly toward the camera, gradually getting closer and larger. Natural mountain landscape with trees in the background. Stationary camera at eye level. Bright natural daylight. Documentary film style. Only one elephant in the frame. The elephant's body should be fully visible and clearly identifiable throughout the approach sequence.
```

---

## Prompt for Luma AI Dream Machine

```
Wildlife scene: One elephant walking on a rural road, starting from far distance (around 100 meters) and approaching a stationary camera. The elephant moves slowly and steadily closer, growing larger in the frame. Natural setting with mountains and vegetation in background. Clear daylight, realistic wildlife documentary style. Single elephant only, full body visible. The camera remains fixed as the elephant approaches from far to near.
```

---

## Prompt for Meta Make-A-Video

```
A realistic wildlife video showing a single African elephant walking on a dirt road. The elephant begins far in the distance (about 100 meters away) and walks directly toward the camera, getting progressively closer and larger. Natural mountain and forest background. Stationary camera position. Bright natural lighting. Documentary style footage. Only one elephant visible. The elephant should remain clearly visible throughout as it approaches from far to near.
```

---

## Key Elements to Include in Any Model:

1. **Single elephant** - Only one animal
2. **Distance progression** - Starts far (100m) and approaches camera
3. **Stationary camera** - Camera doesn't move
4. **Natural setting** - Road/path with mountains/forest background
5. **Clear visibility** - Good lighting, elephant clearly visible
6. **Full body visible** - Elephant's entire body should be in frame
7. **Realistic style** - Documentary/wildlife footage style

---

## Tips for Best Results:

- **Duration**: Request 10-15 seconds for a smooth approach
- **Frame rate**: 24-30 fps for smooth motion
- **Resolution**: 720p or 1080p minimum
- **Aspect ratio**: 16:9 (standard video format)
- **Camera angle**: Eye level or slightly elevated, not too high or low
- **Movement speed**: Slow, steady walk (not running)

---

## Example Variations:

### For Testing Different Distances:
```
[Same as above] The elephant should be clearly visible at approximately 60 meters, then 40 meters, then 20 meters, then 10 meters from the camera.
```

### For Different Environments:
```
[Same as above] Set in a rural agricultural area with fields and farm buildings in the background.
```

### For Different Times:
```
[Same as above] Golden hour lighting, early morning or late afternoon.
```

---

## Post-Generation Notes:

After generating the video:
1. Save it as `input-test.mp4` in the research folder
2. Update `VIDEO_SOURCE` in `detect_elephant_distance.py` to use the new video
3. The system should detect the elephant and show:
   - Green box when far (>50m)
   - Orange box with warnings when 20-50m
   - Red box with danger alerts when <20m




