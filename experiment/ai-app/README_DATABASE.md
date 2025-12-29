# Database Integration Setup Guide

This guide explains how to connect the Elephant Detection PyQt5 app to the Laravel backend database.

## Prerequisites

1. Laravel backend must be running (default: http://localhost:8000)
2. Database migrations must be run in Laravel
3. Python `requests` library must be installed

## Installation

### 1. Install Python Dependencies

```bash
cd research/ai-app
pip install requests
```

### 2. Run Laravel Migrations

```bash
cd research/web-app
php artisan migrate
```

This will create all the necessary database tables:
- `detection_sessions`
- `detections`
- `alerts`
- `zone_transitions`
- `elephant_statistics`

### 3. Configure API URL (Optional)

By default, the app connects to `http://localhost:8000`. To change this, set environment variable:

**Windows:**
```cmd
set DB_API_URL=http://your-server:8000
python elephant_detection_app.py
```

**Linux/Mac:**
```bash
export DB_API_URL=http://your-server:8000
python elephant_detection_app.py
```

Or disable database logging:
```bash
set DB_ENABLED=false
python elephant_detection_app.py
```

## How It Works

1. **Session Creation**: When you start detection, a session is created in the database
2. **Detection Storage**: Detections are stored in batches (every 10 detections) for performance
3. **Alert Logging**: Alerts (warnings and alarms) are logged to the database
4. **Session Completion**: When detection stops, the session status is updated

## API Endpoints

The following API endpoints are used:

- `POST /api/detections/sessions` - Create session
- `PUT /api/detections/sessions/{id}` - Update session
- `POST /api/detections/store` - Store single detection
- `POST /api/detections/store-batch` - Store multiple detections (batch)
- `POST /api/detections/alerts` - Store alert
- `POST /api/detections/zone-transitions` - Store zone transition

## Database Status in UI

The UI shows database status in the Settings panel:
- ✅ **Green**: Database logging enabled and connected
- ❌ **Gray**: Database logging disabled

## Troubleshooting

1. **"Database API client not found"**: Make sure `database_api.py` is in the same directory as `elephant_detection_app.py`

2. **Connection errors**: 
   - Verify Laravel is running: `php artisan serve`
   - Check API URL is correct
   - Check Laravel logs: `storage/logs/laravel.log`

3. **No data in dashboard**:
   - Run migrations: `php artisan migrate`
   - Check database connection in Laravel `.env` file
   - Verify API routes are registered

4. **Performance issues**:
   - Detections are batched (every 10) to improve performance
   - Alerts have 5-second cooldown to prevent spam
   - Database errors won't crash the app (fail silently)

## Testing

To test the connection:

1. Start Laravel server: `php artisan serve`
2. Run the PyQt5 app: `python elephant_detection_app.py`
3. Start detection on a video
4. Check Laravel dashboard at `http://localhost:8000/dashboard`
5. View sessions at `http://localhost:8000/sessions`

