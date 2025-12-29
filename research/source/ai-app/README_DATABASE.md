# MySQL Database Integration - HEC-Sense AI

This document explains how to set up and use the MySQL database integration for storing elephant detection data.

## Database Schema

The database consists of 6 main tables:

1. **users** - User accounts for PHP dashboard (future)
2. **detection_sessions** - Tracks each video/camera session
3. **elephant_detections** - Main detection records with distance, zone, confidence
4. **zone_transitions** - Logs when elephants move between zones
5. **alerts_log** - Records all alerts and warnings triggered
6. **system_settings** - Stores application configuration

## Setup Instructions

### 1. Install MySQL Server

Make sure MySQL is installed and running on your system.

### 2. Set Environment Variables

Create a `.env` file in the `source` directory (or set environment variables):

```bash
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=your_password
MYSQL_DATABASE=hec_sense_ai_farm_app
```

Or export them in your shell:
```bash
export MYSQL_HOST=localhost
export MYSQL_USER=root
export MYSQL_PASSWORD=your_password
```

### 3. Run Database Setup Script

```bash
cd source
python setup_database.py
```

This will:
- Create the database `hec_sense_ai_farm_app`
- Create all required tables
- Insert default system settings
- Verify the database structure

### 4. Install Python Dependencies

```bash
pip install -r requirements_streamlit.txt
```

This includes:
- `mysql-connector-python` - MySQL database connector
- `python-dotenv` - Environment variable management

## Usage in Streamlit App

The database integration is automatically enabled in `main-app.py`. 

### Features:

- **Automatic Session Tracking**: Each video upload or camera session creates a detection session
- **Real-time Data Storage**: Every detection is stored with:
  - Timestamp
  - Distance from camera
  - Zone (SAFE/WARNING/DANGER)
  - Confidence score
  - Bounding box coordinates
  - Frame number
  
- **Alert Logging**: All alerts (TTS warnings, danger alerts, alarm sounds) are logged
- **Zone Transitions**: Tracks when elephants move between zones

### Database Toggle

You can enable/disable database storage from the Streamlit sidebar:
- Go to "💾 Database" section
- Toggle "Enable Database Storage"

## Database Tables Structure

### detection_sessions
- `id` - Session ID
- `session_name` - Name of the session
- `source_type` - video_upload, camera, or file
- `source_path` - Path to video file (if applicable)
- `camera_index` - Camera index (if applicable)
- `started_at` - Session start time
- `ended_at` - Session end time
- `total_detections` - Count of detections
- `status` - active, completed, or stopped

### elephant_detections
- `id` - Detection ID
- `session_id` - Foreign key to detection_sessions
- `detection_timestamp` - When detection occurred
- `distance_meters` - Distance from camera
- `zone` - SAFE, WARNING, or DANGER
- `confidence_score` - Detection confidence (0-1)
- `bounding_box_*` - Bounding box coordinates
- `frame_number` - Frame number in video
- `video_timestamp` - Timestamp in video (seconds)
- `alert_triggered` - Whether alert was triggered
- `alert_type` - Type of alert

### zone_transitions
- `id` - Transition ID
- `session_id` - Foreign key to detection_sessions
- `from_zone` - Previous zone
- `to_zone` - New zone
- `transition_timestamp` - When transition occurred
- `distance_at_transition` - Distance when transition happened

### alerts_log
- `id` - Alert log ID
- `session_id` - Foreign key to detection_sessions
- `detection_id` - Foreign key to elephant_detections
- `alert_type` - warning_tts, danger_tts, alarm_sound, stop_alarm
- `alert_message` - Alert message text
- `distance_meters` - Distance when alert triggered
- `zone` - Zone when alert triggered
- `triggered_at` - When alert was triggered

## PHP Dashboard Integration

The database is designed to work with a PHP-based dashboard. The `users` table is ready for authentication, and all detection data is stored in a format that can be easily queried and displayed.

### Example Queries for PHP Dashboard

```sql
-- Get recent detections
SELECT * FROM elephant_detections 
ORDER BY detection_timestamp DESC 
LIMIT 100;

-- Get session statistics
SELECT 
    COUNT(*) as total_detections,
    AVG(distance_meters) as avg_distance,
    SUM(CASE WHEN zone = 'DANGER' THEN 1 ELSE 0 END) as danger_count
FROM elephant_detections
WHERE session_id = ?;

-- Get zone transitions for a session
SELECT * FROM zone_transitions 
WHERE session_id = ?
ORDER BY transition_timestamp DESC;

-- Get alerts for a session
SELECT * FROM alerts_log 
WHERE session_id = ?
ORDER BY triggered_at DESC;
```

## Troubleshooting

### Connection Errors

If you get connection errors:
1. Check MySQL server is running: `mysql -u root -p`
2. Verify credentials in `.env` or environment variables
3. Check user has CREATE DATABASE privileges
4. Check firewall/network settings

### Database Not Storing Data

1. Check "Enable Database Storage" is checked in sidebar
2. Verify database connection in logs
3. Check MySQL server logs for errors
4. Ensure tables were created successfully

### Performance Issues

For high-frequency detections:
- Consider batch inserts instead of individual inserts
- Add database indexes (already included in schema)
- Monitor database connection pool size

## Next Steps

1. Build PHP dashboard to visualize the data
2. Add user authentication
3. Create reports and analytics
4. Export detection data to CSV/JSON
5. Set up automated backups

