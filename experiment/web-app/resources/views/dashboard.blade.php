@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
  <title>Dashboard - Elephant Detection System</title>

  <link href="{{asset('assets')}}/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="{{asset('assets')}}/css/metismenu.min.css" rel="stylesheet" type="text/css">
  <link href="{{asset('assets')}}/css/icons.css" rel="stylesheet" type="text/css">
  <link href="{{asset('assets')}}/css/style.css" rel="stylesheet" type="text/css">
@endsection

@section('PageContent')

<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="page-title-box">
                <h4 class="page-title">🐘 {{ __('messages.elephant_detection_dashboard') }}</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('show.dashboard') }}">{{ __('messages.home') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('messages.dashboard') }}</li>
                </ol>
            </div>
          </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">{{ __('messages.total_sessions') }}</p>
                            <h4 class="mb-2">{{ number_format($totalSessions) }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold me-1"></i></span>
                                {{ __('messages.active') }}: {{ $activeSessions }}
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title text-primary rounded-3" style="background: transparent;">
                                <i class="mdi mdi-video font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">{{ __('messages.total_detections') }}</p>
                            <h4 class="mb-2">{{ number_format($totalDetections) }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-info me-2"><i class="mdi mdi-elephant font-size-18"></i></span>
                                {{ __('messages.all_time') }}
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title text-success rounded-3" style="background: transparent;">
                                <i class="mdi mdi-radar font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">{{ __('messages.total_alerts') }}</p>
                            <h4 class="mb-2">{{ number_format($totalAlerts) }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-danger me-2"><i class="mdi mdi-alert font-size-18"></i></span>
                                {{ __('messages.warnings_alarms') }}
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title text-danger rounded-3" style="background: transparent;">
                                <i class="mdi mdi-alert-circle font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">{{ __('messages.behavior_distribution') }}</p>
                            <h4 class="mb-2">
                                {{ __('messages.calm') }}: {{ $behaviorStats['calm'] ?? 0 }}<br>
                                {{ __('messages.warning') }}: {{ $behaviorStats['warning'] ?? 0 }}<br>
                                {{ __('messages.aggressive') }}: {{ $behaviorStats['aggressive'] ?? 0 }}
                            </h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title text-warning rounded-3" style="background: transparent;">
                                <i class="mdi mdi-chart-pie font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ __('messages.behavior_distribution_chart') }}</h4>
                    <div style="height: 250px;">
                        <canvas id="behaviorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ __('messages.sessions_per_day') }}</h4>
                    <div style="height: 250px;">
                        <canvas id="sessionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ __('messages.alert_types_distribution') }}</h4>
                    <div style="height: 250px;">
                        <canvas id="alertChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ __('messages.detections_per_hour') }}</h4>
                    <div style="height: 250px;">
                        <canvas id="detectionsPerHourChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sessions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">{{ __('messages.recent_detection_sessions') }}</h4>
                        <a href="{{ route('sessions.index') }}" class="btn btn-sm btn-primary">{{ __('messages.view_all') }}</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="min-width: 150px;">{{ __('messages.session_name') }}</th>
                                    <th style="min-width: 100px;">{{ __('messages.source') }}</th>
                                    <th style="min-width: 90px;">{{ __('messages.status') }}</th>
                                    <th style="min-width: 130px;">{{ __('messages.started_at') }}</th>
                                    <th style="min-width: 80px;">{{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSessions as $session)
                                <tr>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $session->session_name ?? 'N/A' }}">
                                            {{ $session->session_name ? \Illuminate\Support\Str::limit($session->session_name, 35) : 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $session->source_type)) }}</span>
                                    </td>
                                    <td>
                                        @if($session->status == 'running')
                                            <span class="badge bg-success">{{ __('messages.running') }}</span>
                                        @elseif($session->status == 'completed')
                                            <span class="badge bg-primary">{{ __('messages.completed') }}</span>
                                        @elseif($session->status == 'stopped')
                                            <span class="badge bg-warning">{{ __('messages.stopped') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('messages.error') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $session->started_at ? $session->started_at->format('M d, Y H:i') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('sessions.show', $session->id) }}" class="btn btn-sm btn-primary" title="{{ __('messages.view') }}">
                                            <i class="mdi mdi-eye"></i> {{ __('messages.view') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">{{ __('messages.no_sessions_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('FooterAssets')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('assets')}}/plugins/chartjs/chart.min.js"></script>
    
    <script>
        // Behavior Chart
        const behaviorCtx = document.getElementById('behaviorChart').getContext('2d');
        const behaviorLabels = @json([__('messages.calm'), __('messages.warning'), __('messages.aggressive')]);
        const behaviorData = {
            labels: behaviorLabels,
            datasets: [{
                label: '{{ __('messages.detections_label') }}',
                data: [
                    {{ $behaviorStats['calm'] ?? 0 }},
                    {{ $behaviorStats['warning'] ?? 0 }},
                    {{ $behaviorStats['aggressive'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#2c3749'
            }]
        };
        new Chart(behaviorCtx, {
            type: 'doughnut',
            data: behaviorData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Sessions Chart
        const sessionsCtx = document.getElementById('sessionsChart').getContext('2d');
        const sessionsData = {
            labels: {!! json_encode($sessionsPerDay->pluck('date')) !!},
            datasets: [{
                label: '{{ __('messages.sessions_label') }}',
                data: {!! json_encode($sessionsPerDay->pluck('count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };
        new Chart(sessionsCtx, {
            type: 'bar',
            data: sessionsData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Alert Chart
        const alertCtx = document.getElementById('alertChart').getContext('2d');
        const alertLabels = {!! json_encode(array_keys($alertTypes)) !!};
        const alertValues = {!! json_encode(array_values($alertTypes)) !!};
        
        // Dynamic color assignment for alert types
        const alertColors = [
            'rgba(255, 99, 132, 0.6)',   // warning_tts - red/pink
            'rgba(54, 162, 235, 0.6)',   // alarm_sound - blue
            'rgba(255, 206, 86, 0.6)',   // orange
            'rgba(75, 192, 192, 0.6)',   // teal
            'rgba(153, 102, 255, 0.6)'   // purple
        ];
        
        new Chart(alertCtx, {
            type: 'pie',
            data: {
                labels: alertLabels,
                datasets: [{
                    data: alertValues,
                    backgroundColor: alertColors.slice(0, alertLabels.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Detections Per Hour Chart
        const detectionsHourCtx = document.getElementById('detectionsPerHourChart').getContext('2d');
        
        // Show only every 2 hours on x-axis for better readability
        const hourLabels = {!! json_encode($detectionsPerHour->pluck('hour')) !!};
        const hourData = {!! json_encode($detectionsPerHour->pluck('count')) !!};
        
        new Chart(detectionsHourCtx, {
            type: 'bar',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: '{{ __('messages.detections_label') }}',
                    data: hourData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            maxTicksLimit: 12  // Show max 12 labels (every 2 hours)
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <!-- App js -->
    <script src="{{asset('assets')}}/js/app.js"></script>
@endsection
