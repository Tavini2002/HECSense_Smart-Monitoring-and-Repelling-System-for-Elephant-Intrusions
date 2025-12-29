<?php
require_once 'config/config.php';
require_once 'config/database.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --light: #f9fafb;
        }
        
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        
        .sidebar .nav-link {
            color: #4b5563;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        .stat-card.primary { border-left-color: var(--primary); }
        .stat-card.success { border-left-color: var(--success); }
        .stat-card.warning { border-left-color: var(--warning); }
        .stat-card.danger { border-left-color: var(--danger); }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        .loading {
            text-align: center;
            padding: 40px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-elephant me-2"></i><?php echo APP_NAME; ?>
            </span>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <nav class="nav flex-column mt-3">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-chart-line me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="detections.php">
                        <i class="fas fa-search me-2"></i>Detections
                    </a>
                    <a class="nav-link" href="sessions.php">
                        <i class="fas fa-video me-2"></i>Sessions
                    </a>
                    <a class="nav-link" href="alerts.php">
                        <i class="fas fa-bell me-2"></i>Alerts
                    </a>
                    <?php if (isAdmin()): ?>
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-line me-2"></i>Dashboard</h2>
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4" id="statsCards">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card primary">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Detections</h6>
                                    <h2 class="mb-0" id="totalDetections">-</h2>
                                </div>
                                <div class="stat-icon" style="background: var(--primary);">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Safe Zone</h6>
                                    <h2 class="mb-0" id="safeCount">-</h2>
                                </div>
                                <div class="stat-icon" style="background: var(--success);">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Warning Zone</h6>
                                    <h2 class="mb-0" id="warningCount">-</h2>
                                </div>
                                <div class="stat-icon" style="background: var(--warning);">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card danger">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Danger Zone</h6>
                                    <h2 class="mb-0" id="dangerCount">-</h2>
                                </div>
                                <div class="stat-icon" style="background: var(--danger);">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Zone Distribution</h5>
                            <canvas id="zonePieChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Daily Detections (Last 30 Days)</h5>
                            <canvas id="dailyLineChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Hourly Detection Pattern</h5>
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5 class="mb-3"><i class="fas fa-bell me-2"></i>Alert Statistics</h5>
                            <canvas id="alertChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Sessions -->
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-list me-2"></i>Recent Sessions</h5>
                    <div class="table-responsive">
                        <table class="table table-hover" id="sessionsTable">
                            <thead>
                                <tr>
                                    <th>Session Name</th>
                                    <th>Type</th>
                                    <th>Started</th>
                                    <th>Detections</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="sessionsTableBody">
                                <tr>
                                    <td colspan="5" class="loading">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let zonePieChart, dailyLineChart, hourlyChart, alertChart;

        async function loadDashboardData() {
            try {
                const response = await fetch('api/get_statistics.php');
                const result = await response.json();

                if (result.success) {
                    updateStats(result.data);
                    updateCharts(result.data);
                    updateSessionsTable(result.data.recent_sessions);
                } else {
                    console.error('Error loading data:', result.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function updateStats(data) {
            const overall = data.overall || {};
            document.getElementById('totalDetections').textContent = overall.total_detections || 0;
            document.getElementById('safeCount').textContent = overall.safe_count || 0;
            document.getElementById('warningCount').textContent = overall.warning_count || 0;
            document.getElementById('dangerCount').textContent = overall.danger_count || 0;
        }

        function updateCharts(data) {
            // Zone Distribution Pie Chart
            const zoneData = data.zone_distribution || [];
            if (zonePieChart) zonePieChart.destroy();
            
            zonePieChart = new Chart(document.getElementById('zonePieChart'), {
                type: 'pie',
                data: {
                    labels: zoneData.map(z => z.zone),
                    datasets: [{
                        data: zoneData.map(z => z.count),
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Daily Detections Line Chart
            const dailyData = data.daily_detections || [];
            if (dailyLineChart) dailyLineChart.destroy();
            
            dailyLineChart = new Chart(document.getElementById('dailyLineChart'), {
                type: 'line',
                data: {
                    labels: dailyData.map(d => new Date(d.date).toLocaleDateString()),
                    datasets: [
                        {
                            label: 'Safe',
                            data: dailyData.map(d => d.safe),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Warning',
                            data: dailyData.map(d => d.warning),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Danger',
                            data: dailyData.map(d => d.danger),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Hourly Pattern Chart
            const hourlyData = data.hourly_pattern || [];
            if (hourlyChart) hourlyChart.destroy();
            
            hourlyChart = new Chart(document.getElementById('hourlyChart'), {
                type: 'bar',
                data: {
                    labels: hourlyData.map(h => h.hour + ':00'),
                    datasets: [{
                        label: 'Detections',
                        data: hourlyData.map(h => h.count),
                        backgroundColor: '#667eea',
                        borderColor: '#764ba2',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Alert Statistics Chart
            const alertData = data.alert_stats || [];
            if (alertChart) alertChart.destroy();
            
            alertChart = new Chart(document.getElementById('alertChart'), {
                type: 'doughnut',
                data: {
                    labels: alertData.map(a => a.alert_type.replace('_', ' ').toUpperCase()),
                    datasets: [{
                        data: alertData.map(a => a.count),
                        backgroundColor: ['#667eea', '#f59e0b', '#ef4444', '#10b981'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function updateSessionsTable(sessions) {
            const tbody = document.getElementById('sessionsTableBody');
            if (!sessions || sessions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No sessions found</td></tr>';
                return;
            }

            tbody.innerHTML = sessions.map(session => `
                <tr>
                    <td>${session.session_name || 'N/A'}</td>
                    <td><span class="badge bg-secondary">${session.source_type}</span></td>
                    <td>${new Date(session.started_at).toLocaleString()}</td>
                    <td><strong>${session.actual_detections || 0}</strong></td>
                    <td><span class="badge bg-${session.status === 'active' ? 'success' : 'secondary'}">${session.status}</span></td>
                </tr>
            `).join('');
        }

        function refreshData() {
            document.getElementById('sessionsTableBody').innerHTML = `
                <tr>
                    <td colspan="5" class="loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            `;
            loadDashboardData();
        }

        // Load data on page load
        loadDashboardData();
        
        // Auto-refresh every 30 seconds
        setInterval(loadDashboardData, 30000);
    </script>
</body>
</html>



