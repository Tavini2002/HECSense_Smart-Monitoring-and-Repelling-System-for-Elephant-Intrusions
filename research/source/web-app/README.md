# HEC-Sense AI Farm App - PHP Backend Dashboard

Professional PHP-based dashboard for viewing and analyzing elephant detection data.

## Features

- 🔐 **Secure Login System** - Session-based authentication
- 📊 **Rich Dashboard** - Statistics, charts, and visualizations
- 📈 **Charts & Graphs**:
  - Zone Distribution (Pie Chart)
  - Daily Detections (Line Chart)
  - Hourly Detection Pattern (Bar Chart)
  - Alert Statistics (Doughnut Chart)
- 🔍 **Detection Browser** - Filter and search detections
- 📹 **Session Management** - View detection sessions
- 🔔 **Alert Logs** - View all alerts and warnings
- 👥 **User Management** - Admin panel for user management

## Installation

### 1. Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### 2. Database Setup

First, run the Python database setup script:
```bash
cd ../source
python setup_database.py
```

### 3. Configure Database

Edit `config/database.php` with your MySQL credentials:
```php
private $host = 'localhost';
private $db_name = 'hec_sense_ai_farm_app';
private $username = 'root';
private $password = 'your_password';
```

### 4. Create Admin User

Run the setup script to create default admin user:
```bash
php setup_admin.php
```

Default credentials:
- Username: `admin`
- Password: `admin`

**⚠️ IMPORTANT: Change the password after first login!**

### 5. Start Web Server

#### Option 1: PHP Built-in Server
```bash
cd php-backend
php -S localhost:8000
```

#### Option 2: Apache/Nginx
Configure your web server to point to the `php-backend` directory.

### 6. Access Dashboard

Open your browser and navigate to:
```
http://localhost:8000/login.php
```

## File Structure

```
php-backend/
├── config/
│   ├── database.php      # Database connection
│   └── config.php        # Application configuration
├── api/
│   ├── get_statistics.php    # Dashboard statistics API
│   └── get_detections.php    # Detection data API
├── includes/
│   ├── navbar.php        # Navigation bar component
│   └── sidebar.php       # Sidebar component
├── dashboard.php         # Main dashboard page
├── detections.php        # Detection browser page
├── sessions.php          # Session management page
├── alerts.php            # Alert logs page
├── users.php             # User management (admin only)
├── login.php             # Login page
├── logout.php            # Logout handler
└── setup_admin.php       # Admin user setup script
```

## Pages

### Dashboard (`dashboard.php`)
- Overview statistics
- Zone distribution pie chart
- Daily detections line chart
- Hourly detection pattern
- Alert statistics
- Recent sessions table

### Detections (`detections.php`)
- Browse all detections
- Filter by session, zone, date range
- Pagination support
- Detailed detection information

### Sessions (`sessions.php`)
- View all detection sessions
- Session statistics
- Session details

### Alerts (`alerts.php`)
- View all alerts and warnings
- Filter by alert type
- Alert timeline

### Users (`users.php`) - Admin Only
- User management
- Create/edit users
- Role management

## API Endpoints

### GET `/api/get_statistics.php`
Returns dashboard statistics including:
- Overall statistics
- Zone distribution
- Daily detections
- Hourly pattern
- Recent sessions
- Alert statistics

### GET `/api/get_detections.php`
Returns detection data with pagination:
- Query parameters: `page`, `limit`, `session_id`, `zone`, `date_from`, `date_to`

## Security Features

- Session-based authentication
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- Session timeout (1 hour)
- Role-based access control

## Customization

### Change Colors
Edit CSS variables in each page:
```css
:root {
    --primary: #667eea;
    --secondary: #764ba2;
}
```

### Modify Session Timeout
Edit `config/config.php`:
```php
define('SESSION_TIMEOUT', 3600); // seconds
```

## Troubleshooting

### Database Connection Error
- Check MySQL credentials in `config/database.php`
- Verify MySQL server is running
- Check database name matches: `hec_sense_ai_farm_app`

### Login Not Working
- Run `php setup_admin.php` to create admin user
- Check database has `users` table
- Verify password hash is set correctly

### Charts Not Loading
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check database has detection data

## Support

For issues or questions, check:
- Database setup: `source/README_DATABASE.md`
- Python app: `source/README_STREAMLIT.md`

