"""
Database helper module for HEC-Sense AI
Handles MySQL connections and data operations.
"""

import mysql.connector
from mysql.connector import Error, pooling
import os
from datetime import datetime
from typing import Optional, Dict, List, Tuple
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Database Configuration
DB_CONFIG = {
    'host': os.getenv('MYSQL_HOST', 'localhost'),
    'port': int(os.getenv('MYSQL_PORT', 3306)),
    'user': os.getenv('MYSQL_USER', 'root'),
    'password': os.getenv('MYSQL_PASSWORD', ''),
    'database': os.getenv('MYSQL_DATABASE', 'hec_sense_ai_farm_app'),
    'autocommit': False
}

# Connection pool
_connection_pool = None

def init_connection_pool(pool_size=5):
    """Initialize MySQL connection pool."""
    global _connection_pool
    if _connection_pool is None:
        try:
            _connection_pool = pooling.MySQLConnectionPool(
                pool_name="hec_sense_pool",
                pool_size=pool_size,
                pool_reset_session=True,
                **DB_CONFIG
            )
            logger.info("✅ MySQL connection pool initialized")
            return True
        except Error as e:
            logger.error(f"❌ Error creating connection pool: {e}")
            return False
    return True

def get_connection():
    """Get a connection from the pool."""
    if _connection_pool is None:
        if not init_connection_pool():
            return None
    
    try:
        return _connection_pool.get_connection()
    except Error as e:
        logger.error(f"❌ Error getting connection: {e}")
        return None

def create_detection_session(session_name: str, source_type: str, source_path: str = None, 
                            camera_index: int = None, user_id: int = None) -> Optional[int]:
    """Create a new detection session and return session ID."""
    conn = get_connection()
    if not conn:
        return None
    
    try:
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO detection_sessions 
            (session_name, source_type, source_path, camera_index, user_id, status)
            VALUES (%s, %s, %s, %s, %s, 'active')
        """, (session_name, source_type, source_path, camera_index, user_id))
        
        session_id = cursor.lastrowid
        conn.commit()
        logger.info(f"✅ Created detection session: {session_id}")
        return session_id
    except Error as e:
        logger.error(f"❌ Error creating session: {e}")
        conn.rollback()
        return None
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def end_detection_session(session_id: int, status: str = 'completed'):
    """End a detection session."""
    conn = get_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        cursor.execute("""
            UPDATE detection_sessions 
            SET ended_at = NOW(), status = %s
            WHERE id = %s
        """, (status, session_id))
        
        conn.commit()
        logger.info(f"✅ Ended detection session: {session_id}")
        return True
    except Error as e:
        logger.error(f"❌ Error ending session: {e}")
        conn.rollback()
        return False
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def insert_detection(session_id: int, distance_meters: float, zone: str, 
                    confidence_score: float, bbox: Tuple[int, int, int, int] = None,
                    frame_number: int = None, video_timestamp: float = None,
                    alert_triggered: bool = False, alert_type: str = 'none') -> Optional[int]:
    """Insert an elephant detection record."""
    conn = get_connection()
    if not conn:
        return None
    
    try:
        cursor = conn.cursor()
        
        x1, y1, x2, y2 = bbox if bbox else (None, None, None, None)
        bbox_width = (x2 - x1) if (x1 and x2) else None
        bbox_height = (y2 - y1) if (y1 and y2) else None
        
        cursor.execute("""
            INSERT INTO elephant_detections 
            (session_id, distance_meters, zone, confidence_score,
             bounding_box_x1, bounding_box_y1, bounding_box_x2, bounding_box_y2,
             bounding_box_width, bounding_box_height,
             frame_number, video_timestamp, alert_triggered, alert_type)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (session_id, distance_meters, zone, confidence_score,
              x1, y1, x2, y2, bbox_width, bbox_height,
              frame_number, video_timestamp, alert_triggered, alert_type))
        
        detection_id = cursor.lastrowid
        
        # Update session total_detections
        cursor.execute("""
            UPDATE detection_sessions 
            SET total_detections = total_detections + 1
            WHERE id = %s
        """, (session_id,))
        
        conn.commit()
        return detection_id
    except Error as e:
        logger.error(f"❌ Error inserting detection: {e}")
        conn.rollback()
        return None
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def log_zone_transition(session_id: int, from_zone: str, to_zone: str, distance: float):
    """Log a zone transition."""
    conn = get_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO zone_transitions 
            (session_id, from_zone, to_zone, distance_at_transition)
            VALUES (%s, %s, %s, %s)
        """, (session_id, from_zone, to_zone, distance))
        
        conn.commit()
        return True
    except Error as e:
        logger.error(f"❌ Error logging zone transition: {e}")
        conn.rollback()
        return False
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def log_alert(session_id: int, alert_type: str, alert_message: str = None,
             distance_meters: float = None, zone: str = None, detection_id: int = None):
    """Log an alert event."""
    conn = get_connection()
    if not conn:
        return False
    
    try:
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO alerts_log 
            (session_id, detection_id, alert_type, alert_message, distance_meters, zone)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (session_id, detection_id, alert_type, alert_message, distance_meters, zone))
        
        conn.commit()
        return True
    except Error as e:
        logger.error(f"❌ Error logging alert: {e}")
        conn.rollback()
        return False
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def get_recent_detections(session_id: int = None, limit: int = 100) -> List[Dict]:
    """Get recent detections."""
    conn = get_connection()
    if not conn:
        return []
    
    try:
        cursor = conn.cursor(dictionary=True)
        if session_id:
            cursor.execute("""
                SELECT * FROM elephant_detections 
                WHERE session_id = %s 
                ORDER BY detection_timestamp DESC 
                LIMIT %s
            """, (session_id, limit))
        else:
            cursor.execute("""
                SELECT * FROM elephant_detections 
                ORDER BY detection_timestamp DESC 
                LIMIT %s
            """, (limit,))
        
        return cursor.fetchall()
    except Error as e:
        logger.error(f"❌ Error getting detections: {e}")
        return []
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

def get_session_statistics(session_id: int) -> Dict:
    """Get statistics for a session."""
    conn = get_connection()
    if not conn:
        return {}
    
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT 
                COUNT(*) as total_detections,
                AVG(distance_meters) as avg_distance,
                MIN(distance_meters) as min_distance,
                MAX(distance_meters) as max_distance,
                SUM(CASE WHEN zone = 'SAFE' THEN 1 ELSE 0 END) as safe_count,
                SUM(CASE WHEN zone = 'WARNING' THEN 1 ELSE 0 END) as warning_count,
                SUM(CASE WHEN zone = 'DANGER' THEN 1 ELSE 0 END) as danger_count
            FROM elephant_detections
            WHERE session_id = %s
        """, (session_id,))
        
        return cursor.fetchone() or {}
    except Error as e:
        logger.error(f"❌ Error getting statistics: {e}")
        return {}
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

