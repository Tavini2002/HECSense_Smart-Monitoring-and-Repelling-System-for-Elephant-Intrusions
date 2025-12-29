<?php
require_once 'config/config.php';
require_once 'config/database.php';

requireLogin();

$db = getDB();

// Get sessions with statistics
$stmt = $db->query("
    SELECT 
        ds.*,
        COUNT(ed.id) as detection_count,
        AVG(ed.distance_meters) as avg_distance,
        MIN(ed.distance_meters) as min_distance,
        MAX(ed.distance_meters) as max_distance,
        SUM(CASE WHEN ed.zone = 'SAFE' THEN 1 ELSE 0 END) as safe_count,
        SUM(CASE WHEN ed.zone = 'WARNING' THEN 1 ELSE 0 END) as warning_count,
        SUM(CASE WHEN ed.zone = 'DANGER' THEN 1 ELSE 0 END) as danger_count
    FROM detection_sessions ds
    LEFT JOIN elephant_detections ed ON ds.id = ed.session_id
    GROUP BY ds.id
    ORDER BY ds.started_at DESC
");
$sessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions - <?php echo APP_NAME; ?></title>
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
        .session-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-video me-2"></i>Detection Sessions</h2>
                
                <div class="table-container">
                    <?php if (empty($sessions)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                            No sessions found
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Session Name</th>
                                        <th>Type</th>
                                        <th>Started</th>
                                        <th>Ended</th>
                                        <th>Detections</th>
                                        <th>Zones</th>
                                        <th>Distance Range</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($sessions as $session): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($session['session_name']); ?></strong></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($session['source_type']); ?></span></td>
                                            <td><?php echo formatDate($session['started_at']); ?></td>
                                            <td><?php echo $session['ended_at'] ? formatDate($session['ended_at']) : '<span class="text-muted">-</span>'; ?></td>
                                            <td><strong><?php echo number_format($session['detection_count']); ?></strong></td>
                                            <td>
                                                <small>
                                                    <span class="badge badge-success">Safe: <?php echo $session['safe_count']; ?></span>
                                                    <span class="badge badge-warning">Warn: <?php echo $session['warning_count']; ?></span>
                                                    <span class="badge badge-danger">Danger: <?php echo $session['danger_count']; ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($session['min_distance']): ?>
                                                    <?php echo formatDistance($session['min_distance']); ?> - <?php echo formatDistance($session['max_distance']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $session['status'] == 'active' ? 'success' : ($session['status'] == 'completed' ? 'primary' : 'secondary'); ?>">
                                                    <?php echo strtoupper($session['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="detections.php?session_id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



