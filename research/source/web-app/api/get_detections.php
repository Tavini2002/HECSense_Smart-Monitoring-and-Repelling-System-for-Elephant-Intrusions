<?php
/**
 * API Endpoint: Get Detection Data
 */
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/database.php';

requireLogin();

$db = getDB();
$response = ['success' => true, 'data' => []];

try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;
    
    $session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : null;
    $zone = isset($_GET['zone']) ? $_GET['zone'] : null;
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;

    $where = [];
    $params = [];

    if ($session_id) {
        $where[] = "ed.session_id = ?";
        $params[] = $session_id;
    }
    if ($zone) {
        $where[] = "ed.zone = ?";
        $params[] = $zone;
    }
    if ($date_from) {
        $where[] = "DATE(ed.detection_timestamp) >= ?";
        $params[] = $date_from;
    }
    if ($date_to) {
        $where[] = "DATE(ed.detection_timestamp) <= ?";
        $params[] = $date_to;
    }

    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM elephant_detections ed $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Get detections
    $stmt = $db->prepare("
        SELECT 
            ed.*,
            ds.session_name,
            ds.source_type
        FROM elephant_detections ed
        LEFT JOIN detection_sessions ds ON ed.session_id = ds.id
        $whereClause
        ORDER BY ed.detection_timestamp DESC
        LIMIT ? OFFSET ?
    ");
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $detections = $stmt->fetchAll();

    $response['data'] = [
        'detections' => $detections,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ];

} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>



