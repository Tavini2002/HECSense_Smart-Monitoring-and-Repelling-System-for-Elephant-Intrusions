<?php
require_once 'config/config.php';
require_once 'config/database.php';

requireLogin();

$db = getDB();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Get alerts
$stmt = $db->prepare("
    SELECT 
        al.*,
        ds.session_name,
        ed.distance_meters,
        ed.zone
    FROM alerts_log al
    LEFT JOIN detection_sessions ds ON al.session_id = ds.id
    LEFT JOIN elephant_detections ed ON al.detection_id = ed.id
    ORDER BY al.triggered_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$alerts = $stmt->fetchAll();

// Get total count
$countStmt = $db->query("SELECT COUNT(*) FROM alerts_log");
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #667eea; --secondary: #764ba2; }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .sidebar .nav-link { color: #4b5563; padding: 12px 20px; border-radius: 8px; margin: 5px 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
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
                <h2 class="mb-4"><i class="fas fa-bell me-2"></i>Alerts & Warnings</h2>
                
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Alert Log (<?php echo number_format($total); ?> total)</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Alert Type</th>
                                    <th>Message</th>
                                    <th>Session</th>
                                    <th>Distance</th>
                                    <th>Zone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alerts)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                            No alerts found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($alerts as $alert): ?>
                                        <tr>
                                            <td><?php echo formatDate($alert['triggered_at']); ?></td>
                                            <td>
                                                <?php
                                                $typeColors = [
                                                    'warning_tts' => 'warning',
                                                    'danger_tts' => 'danger',
                                                    'alarm_sound' => 'danger',
                                                    'stop_alarm' => 'success'
                                                ];
                                                $color = $typeColors[$alert['alert_type']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?>">
                                                    <?php echo str_replace('_', ' ', strtoupper($alert['alert_type'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($alert['alert_message'] ?? '-'); ?></td>
                                            <td><small><?php echo htmlspecialchars($alert['session_name'] ?? 'N/A'); ?></small></td>
                                            <td><?php echo $alert['distance_meters'] ? formatDistance($alert['distance_meters']) : '-'; ?></td>
                                            <td><?php echo $alert['zone'] ? getZoneBadge($alert['zone']) : '-'; ?></td>
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
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                                <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
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



