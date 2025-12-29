<?php
/**
 * API Endpoint: Get Dashboard Statistics
 */
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$db = getDB();
$response = ['success' => true, 'data' => []];

try {
    // Overall Statistics
    $stmt = $db->query("
        SELECT 
            COUNT(DISTINCT ds.id) as total_sessions,
            COUNT(ed.id) as total_detections,
            AVG(ed.distance_meters) as avg_distance,
            MIN(ed.distance_meters) as min_distance,
            MAX(ed.distance_meters) as max_distance,
            SUM(CASE WHEN ed.zone = 'SAFE' THEN 1 ELSE 0 END) as safe_count,
            SUM(CASE WHEN ed.zone = 'WARNING' THEN 1 ELSE 0 END) as warning_count,
            SUM(CASE WHEN ed.zone = 'DANGER' THEN 1 ELSE 0 END) as danger_count,
            COUNT(DISTINCT DATE(ed.detection_timestamp)) as active_days
        FROM detection_sessions ds
        LEFT JOIN elephant_detections ed ON ds.id = ed.session_id
    ");
    $response['data']['overall'] = $stmt->fetch();

    // Zone Distribution (for pie chart)
    $stmt = $db->query("
        SELECT 
            zone,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM elephant_detections), 2) as percentage
        FROM elephant_detections
        GROUP BY zone
        ORDER BY zone
    ");
    $response['data']['zone_distribution'] = $stmt->fetchAll();

    // Daily Detection Count (for line chart)
    $stmt = $db->query("
        SELECT 
            DATE(detection_timestamp) as date,
            COUNT(*) as count,
            SUM(CASE WHEN zone = 'SAFE' THEN 1 ELSE 0 END) as safe,
            SUM(CASE WHEN zone = 'WARNING' THEN 1 ELSE 0 END) as warning,
            SUM(CASE WHEN zone = 'DANGER' THEN 1 ELSE 0 END) as danger
        FROM elephant_detections
        WHERE detection_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(detection_timestamp)
        ORDER BY date ASC
    ");
    $response['data']['daily_detections'] = $stmt->fetchAll();

    // Hourly Detection Pattern (last 7 days)
    $stmt = $db->query("
        SELECT 
            HOUR(detection_timestamp) as hour,
            COUNT(*) as count
        FROM elephant_detections
        WHERE detection_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY HOUR(detection_timestamp)
        ORDER BY hour ASC
    ");
    $response['data']['hourly_pattern'] = $stmt->fetchAll();

    // Recent Sessions
    $stmt = $db->query("
        SELECT 
            ds.id,
            ds.session_name,
            ds.source_type,
            ds.started_at,
            ds.ended_at,
            ds.status,
            ds.total_detections,
            COUNT(ed.id) as actual_detections
        FROM detection_sessions ds
        LEFT JOIN elephant_detections ed ON ds.id = ed.session_id
        GROUP BY ds.id
        ORDER BY ds.started_at DESC
        LIMIT 10
    ");
    $response['data']['recent_sessions'] = $stmt->fetchAll();

    // Alert Statistics
    $stmt = $db->query("
        SELECT 
            alert_type,
            COUNT(*) as count
        FROM alerts_log
        GROUP BY alert_type
    ");
    $response['data']['alert_stats'] = $stmt->fetchAll();

} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>



