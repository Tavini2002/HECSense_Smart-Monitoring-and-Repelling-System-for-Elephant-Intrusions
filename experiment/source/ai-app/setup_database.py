"""
Database Setup Script for HEC-Sense AI Elephant Detection System
Creates MySQL database and all required tables for storing detection data.
"""

import mysql.connector
from mysql.connector import Error
import os
from datetime import datetime

# Database Configuration
DB_CONFIG = {
    'host': os.getenv('MYSQL_HOST', 'localhost'),
    'port': int(os.getenv('MYSQL_PORT', 3306)),
    'user': os.getenv('MYSQL_USER', 'root'),
    'password': os.getenv('MYSQL_PASSWORD', ''),
    'database': None  # Will be created
}

DATABASE_NAME = 'hec_sense_ai_farm_app'

def create_database_connection(include_db=False):
    """Create a connection to MySQL server."""
    config = DB_CONFIG.copy()
    if include_db:
        config['database'] = DATABASE_NAME
    else:
        config.pop('database', None)
    
    try:
        connection = mysql.connector.connect(**config)
        if connection.is_connected():
            return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

def create_database():
    """Create the database if it doesn't exist."""
    connection = create_database_connection(include_db=False)
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        cursor.execute(f"CREATE DATABASE IF NOT EXISTS {DATABASE_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
        print(f"✅ Database '{DATABASE_NAME}' created or already exists")
        return True
    except Error as e:
        print(f"❌ Error creating database: {e}")
        return False
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def create_tables():
    """Create all required tables."""
    connection = create_database_connection(include_db=True)
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        
        # Table 1: users (for future PHP dashboard login)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(255),
                role ENUM('admin', 'user', 'viewer') DEFAULT 'user',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """)
        print("✅ Table 'users' created")
        
        # Table 2: detection_sessions (tracks each video/camera session)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS detection_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_name VARCHAR(255),
                source_type ENUM('video_upload', 'camera', 'file') NOT NULL,
                source_path VARCHAR(500),
                camera_index INT,
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ended_at TIMESTAMP NULL,
                total_detections INT DEFAULT 0,
                status ENUM('active', 'completed', 'stopped') DEFAULT 'active',
                user_id INT,
                notes TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_started_at (started_at),
                INDEX idx_status (status),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """)
        print("✅ Table 'detection_sessions' created")
        
        # Table 3: elephant_detections (main detection data)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS elephant_detections (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                detection_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                distance_meters DECIMAL(10, 2) NOT NULL,
                zone ENUM('SAFE', 'WARNING', 'DANGER') NOT NULL,
                confidence_score DECIMAL(5, 4) NOT NULL,
                bounding_box_x1 INT,
                bounding_box_y1 INT,
                bounding_box_x2 INT,
                bounding_box_y2 INT,
                bounding_box_width INT,
                bounding_box_height INT,
                frame_number BIGINT,
                video_timestamp DECIMAL(10, 3),
                alert_triggered BOOLEAN DEFAULT FALSE,
                alert_type ENUM('none', 'warning_tts', 'danger_tts', 'alarm_sound') DEFAULT 'none',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES detection_sessions(id) ON DELETE CASCADE,
                INDEX idx_session_id (session_id),
                INDEX idx_detection_timestamp (detection_timestamp),
                INDEX idx_zone (zone),
                INDEX idx_distance (distance_meters),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """)
        print("✅ Table 'elephant_detections' created")
        
        # Table 4: zone_transitions (tracks when elephant moves between zones)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS zone_transitions (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                from_zone ENUM('SAFE', 'WARNING', 'DANGER') NOT NULL,
                to_zone ENUM('SAFE', 'WARNING', 'DANGER') NOT NULL,
                transition_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                distance_at_transition DECIMAL(10, 2) NOT NULL,
                FOREIGN KEY (session_id) REFERENCES detection_sessions(id) ON DELETE CASCADE,
                INDEX idx_session_id (session_id),
                INDEX idx_transition_timestamp (transition_timestamp),
                INDEX idx_from_zone (from_zone),
                INDEX idx_to_zone (to_zone)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """)
        print("✅ Table 'zone_transitions' created")
        
        # Table 5: alerts_log (logs all alerts and warnings)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS alerts_log (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                detection_id BIGINT,
                alert_type ENUM('warning_tts', 'danger_tts', 'alarm_sound', 'stop_alarm') NOT NULL,
                alert_message TEXT,
                distance_meters DECIMAL(10, 2),
                zone ENUM('SAFE', 'WARNING', 'DANGER'),
                triggered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES detection_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (detection_id) REFERENCES elephant_detections(id) ON DELETE SET NULL,
                INDEX idx_session_id (session_id),
                INDEX idx_alert_type (alert_type),
                INDEX idx_triggered_at (triggered_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """)
        print("✅ Table 'alerts_log' created")
        
        # Table 6: system_settings (stores app configuration)
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
                description TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                updated_by INT,
                FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_setting_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """)
        print("✅ Table 'system_settings' created")
        
        # Insert default system settings
        default_settings = [
            ('safe_zone_distance', '130.0', 'number', 'Safe zone distance threshold in meters'),
            ('warning_zone_distance', '70.0', 'number', 'Warning zone distance threshold in meters'),
            ('danger_zone_distance', '70.0', 'number', 'Danger zone distance threshold in meters'),
            ('elephant_width_m', '6.0', 'number', 'Average elephant width in meters for distance calculation'),
            ('focal_length', '1600', 'number', 'Camera focal length in pixels'),
            ('default_confidence_threshold', '0.10', 'number', 'Default confidence threshold for detection'),
            ('warning_cooldown', '2.5', 'number', 'Cooldown time between warning TTS in seconds'),
            ('danger_cooldown', '2.0', 'number', 'Cooldown time between danger alerts in seconds'),
            ('default_alarm_sound', 'Firecrackers', 'string', 'Default alarm sound selection')
        ]
        
        for key, value, stype, desc in default_settings:
            cursor.execute("""
                INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description)
                VALUES (%s, %s, %s, %s)
            """, (key, value, stype, desc))
        
        print("✅ Default system settings inserted")
        
        # Create a default admin user (password: admin123 - should be changed!)
        # Password hash for 'admin123' using bcrypt would be set here
        # For now, we'll skip this and let PHP dashboard handle user creation
        
        connection.commit()
        print("\n✅ All tables created successfully!")
        return True
        
    except Error as e:
        print(f"❌ Error creating tables: {e}")
        connection.rollback()
        return False
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def verify_database():
    """Verify database structure."""
    connection = create_database_connection(include_db=True)
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()
        
        print(f"\n📊 Database '{DATABASE_NAME}' contains {len(tables)} tables:")
        for table in tables:
            cursor.execute(f"SELECT COUNT(*) FROM {table[0]}")
            count = cursor.fetchone()[0]
            print(f"   - {table[0]}: {count} records")
        
        return True
    except Error as e:
        print(f"❌ Error verifying database: {e}")
        return False
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def main():
    """Main setup function."""
    print("=" * 60)
    print("HEC-Sense AI - MySQL Database Setup")
    print("=" * 60)
    print()
    
    # Get database credentials
    print("Database Configuration:")
    print(f"  Host: {DB_CONFIG['host']}")
    print(f"  Port: {DB_CONFIG['port']}")
    print(f"  User: {DB_CONFIG['user']}")
    print(f"  Database: {DATABASE_NAME}")
    print()
    
    # Test connection
    print("Testing MySQL connection...")
    test_conn = create_database_connection(include_db=False)
    if not test_conn:
        print("❌ Failed to connect to MySQL server!")
        print("\nPlease check:")
        print("  1. MySQL server is running")
        print("  2. Credentials are correct (set via environment variables or edit script)")
        print("  3. User has CREATE DATABASE privileges")
        return
    
    print("✅ Connected to MySQL server")
    test_conn.close()
    
    # Create database
    print("\nCreating database...")
    if not create_database():
        return
    
    # Create tables
    print("\nCreating tables...")
    if not create_tables():
        return
    
    # Verify
    print("\nVerifying database structure...")
    verify_database()
    
    print("\n" + "=" * 60)
    print("✅ Database setup completed successfully!")
    print("=" * 60)
    print("\nNext steps:")
    print("  1. Update main-app.py to use MySQL connection")
    print("  2. Set environment variables for MySQL credentials:")
    print("     export MYSQL_HOST=localhost")
    print("     export MYSQL_USER=root")
    print("     export MYSQL_PASSWORD=your_password")
    print("  3. Build PHP dashboard to view the data")

if __name__ == "__main__":
    main()

