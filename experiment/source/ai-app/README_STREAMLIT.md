# HEC-Sense AI - Streamlit Web Application

A web-based interface for real-time elephant detection with distance-based warning zones.

## Features

- 📹 **Video Upload**: Upload and analyze video files
- 📷 **Live Camera**: Real-time detection from webcam
- 🎯 **Distance Zones**: Color-coded zones (Green/Orange/Red)
- 🔊 **Audio Alerts**: TTS warnings and firecracker alarms
- ⚙️ **Configurable**: Adjustable confidence threshold

## Installation

1. Install dependencies:
```bash
pip install -r requirements_streamlit.txt
```

2. Make sure you have:
   - Model file: `models/yolov8s.pt`
   - Firecracker sound: `research/1-firecrackers-sound-effect.mp3` or `assets/alarm-sound-effects/1-firecrackers-sound-effect.mp3`

## Running the Application

```bash
cd source
streamlit run streamlit_app.py
```

The app will open in your browser at `http://localhost:8501`

## Usage

1. **Load Model**: Click "Load Model" button in the sidebar
2. **Choose Input**:
   - **Video Upload Tab**: Upload a video file and click "Play Detection"
   - **Camera Tab**: Select camera and click "Start Camera"
3. **Configure**: Adjust confidence threshold in sidebar
4. **Monitor**: Watch real-time detection with zone colors and distance

## Distance Zones

- 🟢 **Safe Zone (>130m)**: Green bounding box, no alerts
- 🟠 **Warning Zone (70-130m)**: Orange bounding box, TTS warnings
- 🔴 **Danger Zone (<70m)**: Red bounding box, "Danger Danger" TTS + Firecracker alarm

## Troubleshooting

- **Model not loading**: Check if `models/yolov8s.pt` exists
- **Camera not working**: Try different camera index (0, 1, 2, etc.)
- **Audio not playing**: Check if sound files exist in correct paths
- **TTS errors**: Install system TTS engine (Windows: built-in, Linux: `espeak`)



