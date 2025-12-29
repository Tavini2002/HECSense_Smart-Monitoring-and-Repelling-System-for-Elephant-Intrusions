<?php
require_once 'config/config.php';
require_once 'config/database.php';

requireLogin();

$db = getDB();

// Get filter parameters
$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : null;
$zone = isset($_GET['zone']) ? $_GET['zone'] : null;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;

// Get sessions for filter dropdown
$sessionsStmt = $db->query("SELECT id, session_name FROM detection_sessions ORDER BY started_at DESC LIMIT 100");
$sessions = $sessionsStmt->fetchAll();

// Build query
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
$totalPages = ceil($total / $limit);

// Get detections
$offset = ($page - 1) * $limit;
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detections - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .sidebar .nav-link { color: #4b5563; padding: 12px 20px; border-radius: 8px; margin: 5px 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
        .filter-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-search me-2"></i>Detections</h2>
                
                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Session</label>
                                <select name="session_id" class="form-select">
                                    <option value="">All Sessions</option>
                                    <?php foreach($sessions as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php echo $session_id == $s['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['session_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Zone</label>
                                <select name="zone" class="form-select">
                                    <option value="">All Zones</option>
                                    <option value="SAFE" <?php echo $zone == 'SAFE' ? 'selected' : ''; ?>>Safe</option>
                                    <option value="WARNING" <?php echo $zone == 'WARNING' ? 'selected' : ''; ?>>Warning</option>
                                    <option value="DANGER" <?php echo $zone == 'DANGER' ? 'selected' : ''; ?>>Danger</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-2"></i>Filter</button>
                                <a href="detections.php" class="btn btn-secondary"><i class="fas fa-redo me-2"></i>Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Results -->
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Detection Records (<?php echo number_format($total); ?> total)</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Session</th>
                                    <th>Distance</th>
                                    <th>Zone</th>
                                    <th>Confidence</th>
                                    <th>Alert</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detections)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                            No detections found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($detections as $det): ?>
                                        <tr>
                                            <td><?php echo formatDate($det['detection_timestamp']); ?></td>
                                            <td>
                                                <small><?php echo htmlspecialchars($det['session_name'] ?? 'N/A'); ?></small><br>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($det['source_type'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td><strong><?php echo formatDistance($det['distance_meters']); ?></strong></td>
                                            <td><?php echo getZoneBadge($det['zone']); ?></td>
                                            <td><?php echo number_format($det['confidence_score'], 2); ?></td>
                                            <td>
                                                <?php if ($det['alert_triggered']): ?>
                                                    <span class="badge bg-danger"><?php echo htmlspecialchars($det['alert_type']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&session_id=<?php echo $session_id ?? ''; ?>&zone=<?php echo $zone ?? ''; ?>&date_from=<?php echo $date_from ?? ''; ?>&date_to=<?php echo $date_to ?? ''; ?>">Previous</a>
                                </li>
                                <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&session_id=<?php echo $session_id ?? ''; ?>&zone=<?php echo $zone ?? ''; ?>&date_from=<?php echo $date_from ?? ''; ?>&date_to=<?php echo $date_to ?? ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&session_id=<?php echo $session_id ?? ''; ?>&zone=<?php echo $zone ?? ''; ?>&date_from=<?php echo $date_from ?? ''; ?>&date_to=<?php echo $date_to ?? ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



