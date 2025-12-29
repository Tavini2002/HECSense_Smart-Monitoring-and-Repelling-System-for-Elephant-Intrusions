# HEC-Sense AI Farm App

A comprehensive AI-powered elephant detection and monitoring system designed for human-elephant conflict (HEC) management. The system uses computer vision to detect elephants in real-time, estimates their distance from the camera, and provides intelligent alerts based on proximity zones.

## 🎯 Project Overview

HEC-Sense AI is a complete solution for monitoring elephant presence near farms and residential areas. It combines:

- **Real-time Detection**: YOLOv8-based elephant detection from video feeds or live camera
- **Distance Estimation**: Monocular camera-based distance calculation
- **Intelligent Alerting**: Zone-based color-coded warnings with TTS and audio alarms
- **Data Logging**: MySQL database integration for detection history and analytics
- **Web Dashboard**: Professional PHP-based dashboard for data visualization and management

## ✨ Features

### Core Detection Features
- 🐘 **Elephant Detection**: Real-time detection using YOLOv8 deep learning model
- 📏 **Distance Estimation**: Accurate distance calculation using focal length method
- 🎨 **Color-Coded Zones**: 
  - 🟢 **Safe Zone** (>130m): Green bounding box, no alerts
  - 🟠 **Warning Zone** (70-130m): Orange bounding box, continuous TTS warnings
  - 🔴 **Danger Zone** (<70m): Red bounding box, "Danger Danger" TTS + alarm sound
- 🔊 **Multi-Modal Alerts**:
  - Text-to-Speech (TTS) warnings with distance announcements
  - Audio alarms (firecrackers, bee sound, warning alarm)
  - Continuous looping in danger zone
- 📹 **Multiple Input Sources**: Video file upload or live camera feed

### Web Application (Streamlit)
- 🖥️ **User-Friendly Interface**: Modern web UI built with Streamlit
- ⚙️ **Configurable Parameters**: 
  - Adjustable confidence threshold (default: 10%)
  - Editable distance zone thresholds
  - Alarm sound selection
- 💾 **Database Integration**: Optional MySQL logging for all detections
- 📊 **Real-Time Monitoring**: Live video display with detection overlays

### PHP Dashboard
- 🔐 **Secure Login System**: Session-based authentication
- 📊 **Rich Analytics Dashboard**:
  - Overall statistics cards
  - Zone distribution pie chart
  - Daily detections line chart
  - Hourly detection patterns
  - Alert statistics
- 🔍 **Data Management**:
  - Browse and filter detections
  - View detection sessions
  - Alert logs
  - User management (admin)
- 🎨 **Professional UI**: Modern, responsive design with Bootstrap and Chart.js

## 📁 Project Structure

```
hec-sense-ai-app/
├── research/                          # Research and development scripts
│   ├── detect_elephant_distance.py    # Original research script with distance zones
│   ├── DISTANCE_ZONES_EXPLANATION.md  # Distance zone documentation
│   ├── DISTANCE_ACCURACY_CALIBRATION.md
│   ├── VIDEO_GENERATION_PROMPTS.md
│   ├── download_demo_videos.py
│   ├── yolov8s.pt                     # YOLOv8 model file
│   ├── *.mp3                          # Alarm sound effects
│   └── requirements.txt               # Research dependencies
│
├── source/                            # Main application source code
│   ├── main-app.py                    # Streamlit web application (MAIN APP)
│   ├── secondary-app.py              # Alternative implementation
│   ├── database.py                    # MySQL database helper functions
│   ├── setup_database.py             # Database setup script
│   ├── models/                        # YOLOv8 model files
│   │   ├── yolov8n.pt
│   │   └── yolov8s.pt
│   ├── assets/
│   │   └── alarm-sound-effects/       # Alarm sound files
│   ├── inputs/                        # Input video files
│   ├── outputs/                       # Processed video outputs
│   ├── requirements_streamlit.txt     # Python dependencies
│   ├── README_STREAMLIT.md            # Streamlit app documentation
│   └── README_DATABASE.md             # Database documentation
│
├── php-backend/                       # PHP dashboard backend
│   ├── index.php                      # Root redirect (login/dashboard)
│   ├── login.php                      # Login page
│   ├── logout.php                     # Logout handler
│   ├── dashboard.php                  # Main dashboard page
│   ├── detections.php                 # Detection browser
│   ├── sessions.php                   # Session management
│   ├── alerts.php                     # Alert logs
│   ├── users.php                      # User management (admin)
│   ├── setup_admin.php                # Admin user setup script
│   ├── config/
│   │   ├── config.php                 # Application configuration
│   │   └── database.php               # Database connection
│   ├── api/
│   │   ├── get_statistics.php         # Dashboard statistics API
│   │   └── get_detections.php         # Detection data API
│   ├── includes/
│   │   ├── navbar.php                 # Navigation bar
│   │   └── sidebar.php                # Sidebar menu
│   └── README.md                      # PHP backend documentation
│
└── README.md                          # This file
```

## 🚀 Quick Start

### Prerequisites

- **Python 3.8+**
- **MySQL 5.7+** or **MariaDB 10.3+**
- **PHP 7.4+** (for dashboard)
- **Webcam** (optional, for live detection)
- **Git** (optional)

### Step 1: Clone Repository

```bash
git clone <repository-url>
cd hec-sense-ai-app
```

### Step 2: Install Python Dependencies

```bash
cd source
pip install -r requirements_streamlit.txt
```

**Required packages:**
- `streamlit` - Web framework
- `opencv-python` - Computer vision
- `ultralytics` - YOLOv8 model
- `pyttsx3` - Text-to-speech
- `pygame` - Audio playback
- `mysql-connector-python` - Database connectivity
- `python-dotenv` - Environment variables

### Step 3: Download YOLOv8 Model

The model file should be in `source/models/yolov8s.pt`. If missing:

```bash
# The model will auto-download on first run, or download manually:
# Place yolov8s.pt in source/models/ directory
```

### Step 4: Set Up MySQL Database

1. **Install MySQL** (if not installed):
   - Windows: Download from [MySQL website](https://dev.mysql.com/downloads/)
   - Linux: `sudo apt-get install mysql-server`
   - macOS: `brew install mysql`

2. **Start MySQL Server**:
   ```bash
   # Windows: Start MySQL service from Services
   # Linux: sudo systemctl start mysql
   # macOS: brew services start mysql
   ```

3. **Create Database and Tables**:
   ```bash
   cd source
   python setup_database.py
   ```

   This will:
   - Create database `hec_sense_ai_farm_app`
   - Create all required tables
   - Set up default system settings

4. **Configure Database Credentials** (Optional):
   
   Create `.env` file in `source/` directory:
   ```bash
   MYSQL_HOST=localhost
   MYSQL_PORT=3306
   MYSQL_USER=root
   MYSQL_PASSWORD=your_password
   MYSQL_DATABASE=hec_sense_ai_farm_app
   ```

   Or set environment variables:
   ```bash
   export MYSQL_HOST=localhost
   export MYSQL_USER=root
   export MYSQL_PASSWORD=your_password
   ```

### Step 5: Run Streamlit Application

```bash
cd source
streamlit run main-app.py
```

The app will open in your browser at `http://localhost:8501`

**First-time setup:**
1. The model will auto-load on startup
2. Enable "Database Storage" in sidebar (optional)
3. Upload a video or select camera
4. Click "Play Detection" or "Start Camera"

### Step 6: Set Up PHP Dashboard (Optional)

1. **Install PHP** (if not installed):
   - Windows: Download from [PHP website](https://windows.php.net/download/)
   - Linux: `sudo apt-get install php php-mysql`
   - macOS: `brew install php`

2. **Configure Database Connection**:
   
   Edit `php-backend/config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'hec_sense_ai_farm_app';
   private $username = 'root';
   private $password = 'your_password';
   ```

3. **Create Admin User**:
   ```bash
   cd php-backend
   php setup_admin.php
   ```

   Default credentials:
   - Username: `admin`
   - Password: `admin`
   
   **⚠️ IMPORTANT: Change password after first login!**

4. **Start PHP Server**:
   ```bash
   cd php-backend
   php -S localhost:8000
   ```

5. **Access Dashboard**:
   
   Open browser: `http://localhost:8000/`
   
   Login with:
   - Username: `admin`
   - Password: `admin`

## 📖 Detailed Setup Instructions

### Python Application Setup

#### 1. Virtual Environment (Recommended)

```bash
cd source
python -m venv venv

# Windows
venv\Scripts\activate

# Linux/macOS
source venv/bin/activate

pip install -r requirements_streamlit.txt
```

#### 2. Model Files

Ensure you have YOLOv8 model files:
- `source/models/yolov8s.pt` (recommended, more accurate)
- `source/models/yolov8n.pt` (faster, less accurate)

The app will auto-download if missing, or download manually from [Ultralytics](https://github.com/ultralytics/ultralytics).

#### 3. Alarm Sound Files

Place alarm sound files in one of these locations:
- `source/assets/alarm-sound-effects/`
- `research/` (root level)

Required files:
- `1-firecrackers-sound-effect.mp3` (default danger alarm)
- `0-bees-sound-effect.mp3` (optional)
- `2-warning-alarm.mp3` (optional)

#### 4. TTS Engine Setup

**Windows**: Built-in (SAPI5) - No setup needed

**Linux**: Install `espeak`:
```bash
sudo apt-get install espeak espeak-data libespeak1 libespeak-dev
```

**macOS**: Built-in - No setup needed

### MySQL Database Setup

#### Database Schema

The database includes 6 main tables:

1. **users** - User accounts for PHP dashboard
2. **detection_sessions** - Tracks each video/camera session
3. **elephant_detections** - Main detection records with distance, zone, confidence
4. **zone_transitions** - Logs when elephants move between zones
5. **alerts_log** - Records all alerts and warnings triggered
6. **system_settings** - Stores application configuration

#### Manual Database Setup

If you prefer to set up manually:

```sql
CREATE DATABASE hec_sense_ai_farm_app;

USE hec_sense_ai_farm_app;

-- Run the SQL from setup_database.py or see README_DATABASE.md
```

### PHP Backend Setup

#### Requirements

- PHP 7.4 or higher
- MySQL extension enabled
- Web server (Apache/Nginx) or PHP built-in server

#### Configuration Files

1. **Database Configuration** (`php-backend/config/database.php`):
   ```php
   private $host = 'localhost';
   private $db_name = 'hec_sense_ai_farm_app';
   private $username = 'root';
   private $password = 'your_password';
   ```

2. **Application Configuration** (`php-backend/config/config.php`):
   ```php
   define('BASE_URL', 'http://localhost:8000');
   define('APP_NAME', 'HEC-Sense AI Dashboard');
   define('SESSION_TIMEOUT', 3600); // 1 hour
   ```

#### Using Apache/Nginx

**Apache**: Point DocumentRoot to `php-backend/` directory

**Nginx**: Configure server block:
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/hec-sense-ai-app/php-backend;
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## 🎮 Usage Guide

### Streamlit Application

#### Basic Workflow

1. **Start Application**:
   ```bash
   cd source
   streamlit run main-app.py
   ```

2. **Load Model** (auto-loads on startup):
   - Model loads automatically
   - Check sidebar for model status

3. **Choose Input Source**:
   - **Video Upload Tab**: Click "Browse files" and select video
   - **Camera Tab**: Select camera index (usually 0)

4. **Configure Settings** (Sidebar):
   - **Confidence Threshold**: Default 10% (0.10)
   - **Distance Zones**: 
     - Safe Zone: >130m (default)
     - Warning Zone: 70-130m (default)
     - Danger Zone: <70m (default)
   - **Alarm Sound**: Select from dropdown
   - **Database Storage**: Toggle to enable/disable

5. **Start Detection**:
   - **Video**: Click "Play Detection" button
   - **Camera**: Click "Start Camera" button

6. **Monitor Detection**:
   - Watch real-time video with bounding boxes
   - Colors indicate zones:
     - 🟢 Green = Safe (>130m)
     - 🟠 Orange = Warning (70-130m)
     - 🔴 Red = Danger (<70m)
   - Listen for TTS warnings and alarms

7. **Stop Detection**:
   - Click "Stop Detection" or "Stop Camera" button

#### Advanced Features

- **Session Management**: Click "Start New Session" to create a new database session
- **Real-time Zone Display**: Current zone status shown below video
- **Distance Display**: Distance shown on bounding boxes

### PHP Dashboard

#### Login

1. Navigate to `http://localhost:8000/`
2. Enter credentials:
   - Username: `admin`
   - Password: `admin`
3. Click "Login"

#### Dashboard Overview

The main dashboard shows:
- **Statistics Cards**: Total detections, active sessions, alerts
- **Zone Distribution**: Pie chart showing safe/warning/danger zone counts
- **Daily Detections**: Line chart of detections over time
- **Hourly Pattern**: Bar chart showing detection times
- **Alert Statistics**: Doughnut chart of alert types
- **Recent Sessions**: Table of latest detection sessions

#### Navigation

- **Dashboard**: Main overview page
- **Detections**: Browse and filter all detections
- **Sessions**: View detection sessions
- **Alerts**: View alert logs
- **Users**: User management (admin only)

#### Filtering Data

In **Detections** page:
- Filter by session
- Filter by zone (Safe/Warning/Danger)
- Filter by date range
- Search by detection ID

## 🔧 Configuration

### Distance Zones

Default zone thresholds:
- **Safe Zone**: >130 meters (Green)
- **Warning Zone**: 70-130 meters (Orange)
- **Danger Zone**: <70 meters (Red)

**To modify in Streamlit app:**
- Edit values in sidebar "Distance Zones" section
- Changes apply immediately

**To modify in research script:**
Edit `research/detect_elephant_distance.py`:
```python
SAFE_ZONE_DISTANCE = 130.0
WARNING_ZONE_DISTANCE = 70.0
DANGER_ZONE_DISTANCE = 70.0
```

### Focal Length Calibration

The distance calculation uses:
```python
distance_m = (ELEPHANT_WIDTH_M * FOCAL_LENGTH) / bbox_width_px
```

**Current settings:**
- `ELEPHANT_WIDTH_M = 6.0` meters (average elephant body length)
- `FOCAL_LENGTH = 1600` (calibrated for doubled distance)

**To recalibrate:**
1. Capture frame with elephant at known distance
2. Measure pixel width of bounding box
3. Calculate: `FOCAL_LENGTH = (pixel_width × actual_distance) / 6.0`
4. Update `FOCAL_LENGTH` in code

### Alarm Sounds

Available alarm sounds:
- **Firecrackers** (default): `1-firecrackers-sound-effect.mp3`
- **Bee Sound**: `0-bees-sound-effect.mp3`
- **Warning Alarm**: `2-warning-alarm.mp3`

**To add custom alarm:**
1. Place `.mp3` file in `source/assets/alarm-sound-effects/`
2. Update `ALARM_SOUNDS` dictionary in `main-app.py`

### TTS Settings

**Warning Message**: "Warning, elephant is on [distance] meters"
- Repeats every 2.5 seconds in warning zone

**Danger Message**: "Danger Danger [distance] meters"
- Plays once when entering danger zone, then alarm starts

**To modify TTS speed:**
Edit `play_warning_speech()` and `play_danger_alert()` functions in `main-app.py`:
```python
engine.setProperty('rate', 200)  # Speed (words per minute)
```

## 🐛 Troubleshooting

### Python Application Issues

#### Model Not Loading
- **Error**: "Model file not found"
- **Solution**: 
  - Check `source/models/yolov8s.pt` exists
  - Model will auto-download on first run
  - Ensure internet connection for download

#### Camera Not Working
- **Error**: "Cannot open camera"
- **Solution**:
  - Try different camera index (0, 1, 2, etc.)
  - Check camera permissions
  - Ensure camera is not used by another application

#### TTS Not Working
- **Error**: "TTS engine not available"
- **Solution**:
  - **Windows**: Should work automatically
  - **Linux**: Install `espeak`: `sudo apt-get install espeak`
  - **macOS**: Should work automatically

#### Audio Not Playing
- **Error**: "Alarm sound not found"
- **Solution**:
  - Check sound files exist in `source/assets/alarm-sound-effects/` or `research/`
  - Verify file names match exactly
  - Check file format is MP3

#### Database Connection Error
- **Error**: "Cannot connect to MySQL"
- **Solution**:
  - Verify MySQL server is running
  - Check credentials in `.env` or environment variables
  - Ensure database `hec_sense_ai_farm_app` exists
  - Run `python setup_database.py` to create database

### PHP Dashboard Issues

#### Cannot Access Login Page
- **Error**: "404 Not Found" or blank page
- **Solution**:
  - Verify PHP server is running: `php -S localhost:8000`
  - Check you're in `php-backend/` directory
  - Ensure `index.php` exists

#### Login Not Working
- **Error**: "Invalid credentials"
- **Solution**:
  - Run `php setup_admin.php` to create admin user
  - Check database has `users` table
  - Verify password hash in database

#### Database Connection Error
- **Error**: "Database connection failed"
- **Solution**:
  - Check `config/database.php` credentials
  - Verify MySQL server is running
  - Ensure database `hec_sense_ai_farm_app` exists
  - Check user has proper permissions

#### Charts Not Loading
- **Error**: Blank charts or JavaScript errors
- **Solution**:
  - Check browser console for errors
  - Verify API endpoints are accessible
  - Ensure database has detection data
  - Check internet connection (for Chart.js CDN)

### Performance Issues

#### Slow Detection
- **Solution**:
  - Use `yolov8n.pt` (nano) instead of `yolov8s.pt` (small)
  - Reduce video resolution
  - Lower confidence threshold
  - Use GPU acceleration (if available)

#### High Memory Usage
- **Solution**:
  - Process videos in chunks
  - Limit frame buffer size
  - Close unused sessions

## 📊 PHP Backend Explanation

### Architecture

The PHP backend is a traditional server-side application with:

- **MVC-like Structure**: Separation of concerns
- **Session-based Authentication**: Secure user login
- **RESTful API**: JSON endpoints for data
- **Responsive UI**: Bootstrap-based frontend

### Key Components

#### 1. Authentication System

**Files:**
- `login.php` - Login form and authentication logic
- `logout.php` - Session destruction
- `config/config.php` - Session configuration

**Features:**
- Password hashing (bcrypt)
- Session timeout (1 hour)
- Role-based access control
- Secure session management

#### 2. Dashboard Pages

**Main Pages:**
- `dashboard.php` - Overview with charts and statistics
- `detections.php` - Detection browser with filters
- `sessions.php` - Session management
- `alerts.php` - Alert logs
- `users.php` - User management (admin only)

**Common Features:**
- Responsive design
- Real-time data updates
- Filtering and search
- Pagination

#### 3. API Endpoints

**Location**: `php-backend/api/`

**Endpoints:**
- `get_statistics.php` - Dashboard statistics (GET)
- `get_detections.php` - Detection data with pagination (GET)

**Response Format**: JSON

**Example Request:**
```javascript
fetch('/api/get_statistics.php')
  .then(response => response.json())
  .then(data => console.log(data));
```

#### 4. Database Integration

**Connection**: `config/database.php`
- PDO-based connection
- Prepared statements (SQL injection prevention)
- Error handling

**Data Flow:**
1. PHP queries MySQL database
2. Data formatted as JSON
3. Frontend JavaScript fetches and displays

#### 5. Security Features

- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: `htmlspecialchars()` on output
- **Session Security**: Secure session configuration
- **Password Security**: Bcrypt hashing
- **Access Control**: Role-based permissions

### Data Flow

```
User → Login → Session Created → Dashboard → API Call → Database Query → JSON Response → Chart Update
```

### Customization

#### Change Theme Colors

Edit CSS in each page:
```css
:root {
    --primary: #667eea;
    --secondary: #764ba2;
}
```

#### Modify Session Timeout

Edit `config/config.php`:
```php
define('SESSION_TIMEOUT', 3600); // seconds
```

#### Add New Chart

1. Create API endpoint in `api/`
2. Add JavaScript fetch in dashboard
3. Create Chart.js instance
4. Update data on page load

## 🔬 Research Folder

The `research/` folder contains:

- **Original Research Script**: `detect_elephant_distance.py`
  - Standalone script for testing distance zones
  - Command-line interface
  - Video file processing

- **Documentation**:
  - `DISTANCE_ZONES_EXPLANATION.md` - Zone system details
  - `DISTANCE_ACCURACY_CALIBRATION.md` - Calibration guide
  - `VIDEO_GENERATION_PROMPTS.md` - Video generation tips

- **Demo Videos**: Test videos for development

- **Model Files**: YOLOv8 model weights

**Usage:**
```bash
cd research
python detect_elephant_distance.py
```

## 📝 Technology Stack

### Python Application
- **Streamlit** - Web framework
- **OpenCV** - Computer vision
- **Ultralytics YOLOv8** - Object detection
- **PyTTSx3** - Text-to-speech
- **Pygame** - Audio playback
- **MySQL Connector** - Database connectivity

### PHP Dashboard
- **PHP 7.4+** - Server-side language
- **MySQL** - Database
- **Bootstrap 5** - CSS framework
- **Chart.js** - Data visualization
- **Font Awesome** - Icons

### Database
- **MySQL 5.7+** / **MariaDB 10.3+**

## 📄 License

[Specify your license here]

## 🤝 Contributing

[Add contribution guidelines if applicable]

## 📧 Support

For issues or questions:
- Check documentation in respective folders
- Review troubleshooting section
- Check GitHub issues (if applicable)

## 🎯 Future Enhancements

- [ ] Mobile app integration
- [ ] Email/SMS alerts
- [ ] Multi-camera support
- [ ] Cloud deployment
- [ ] Advanced analytics
- [ ] Machine learning model retraining
- [ ] Real-time streaming support
- [ ] Export reports (PDF/CSV)

---

**Built with ❤️ for Human-Elephant Conflict Management**


