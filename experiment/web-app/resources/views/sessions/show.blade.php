@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
  <title>Session Details</title>

  <link href="{{asset('assets')}}/plugins/chartjs/chart.min.js" rel="stylesheet" type="text/css" />
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
                <h4 class="page-title">Session Details</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('show.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sessions.index') }}">Sessions</a></li>
                    <li class="breadcrumb-item active">Session #{{ $session->id }}</li>
                </ol>
            </div>
          </div>
        </div>
    </div>

    <!-- Session Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Session Information</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>Session ID:</strong> {{ $session->id }}</p>
                            <p><strong>Session Name:</strong> {{ $session->session_name ?? 'N/A' }}</p>
                            <p><strong>Source Type:</strong> 
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $session->source_type)) }}</span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Status:</strong> 
                                @if($session->status == 'running')
                                    <span class="badge bg-success">Running</span>
                                @elseif($session->status == 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @elseif($session->status == 'stopped')
                                    <span class="badge bg-warning">Stopped</span>
                                @else
                                    <span class="badge bg-danger">Error</span>
                                @endif
                            </p>
                            <p><strong>Started At:</strong> {{ $session->started_at ? $session->started_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            <p><strong>Ended At:</strong> {{ $session->ended_at ? $session->ended_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Duration:</strong> 
                                @if($session->started_at && $session->ended_at)
                                    {{ gmdate('H:i:s', $session->duration) }}
                                @else
                                    N/A
                                @endif
                            </p>
                            <p><strong>Total Frames:</strong> {{ number_format($session->total_frames) }}</p>
                            <p><strong>Confidence Threshold:</strong> {{ number_format($session->confidence_threshold, 2) }}</p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>Source Path:</strong> 
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $session->source_path ?? 'N/A' }}">
                                    {{ $session->source_path ?? 'N/A' }}
                                </div>
                            </p>
                            @if($session->notes)
                                <p><strong>Notes:</strong> 
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $session->notes }}">
                                        {{ $session->notes }}
                                    </div>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3>{{ number_format($stats['total_detections']) }}</h3>
                    <p class="text-muted mb-0">Total Detections</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ number_format($stats['calm_count']) }}</h3>
                    <p class="text-muted mb-0">Calm</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ number_format($stats['warning_count']) }}</h3>
                    <p class="text-muted mb-0">Warning</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ number_format($stats['aggressive_count']) }}</h3>
                    <p class="text-muted mb-0">Aggressive</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Behavior Distribution</h4>
                    <div style="height: 300px; position: relative;">
                        <canvas id="behaviorChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Timeline</h4>
                    <div style="height: 300px; position: relative;">
                        <canvas id="timelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Elephants by Track -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Elephants Tracked</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <th style="min-width: 80px;">Track ID</th>
                                    <th style="min-width: 100px;">Detections</th>
                                    <th style="min-width: 90px;">First Seen</th>
                                    <th style="min-width: 90px;">Last Seen</th>
                                    <th style="min-width: 100px;">Avg Speed</th>
                                    <th style="min-width: 100px;">Max Speed</th>
                                    <th style="min-width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detectionsByTrack as $track)
                                <tr>
                                    <td><strong>#{{ $track->track_id }}</strong></td>
                                    <td>{{ number_format($track->count) }}</td>
                                    <td><small>{{ $track->first_seen ? \Carbon\Carbon::parse($track->first_seen)->format('H:i:s') : 'N/A' }}</small></td>
                                    <td><small>{{ $track->last_seen ? \Carbon\Carbon::parse($track->last_seen)->format('H:i:s') : 'N/A' }}</small></td>
                                    <td>{{ number_format($track->avg_speed, 2) }} <small>km/h</small></td>
                                    <td>{{ number_format($track->max_speed, 2) }} <small>km/h</small></td>
                                    <td>
                                        <a href="{{ route('detections.index', ['session_id' => $session->id, 'track_id' => $track->track_id]) }}" class="btn btn-sm btn-primary" title="View Detections">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
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
        new Chart(behaviorCtx, {
            type: 'doughnut',
            data: {
                labels: ['Calm', 'Warning', 'Aggressive'],
                datasets: [{
                    data: [
                        {{ $stats['calm_count'] }},
                        {{ $stats['warning_count'] }},
                        {{ $stats['aggressive_count'] }}
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Timeline Chart
        const timelineCtx = document.getElementById('timelineChart').getContext('2d');
        const timelineData = @json($timelineData);
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: timelineData.map(d => d.minute),
                datasets: [
                    {
                        label: 'Total',
                        data: timelineData.map(d => d.count),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)'
                    },
                    {
                        label: 'Aggressive',
                        data: timelineData.map(d => d.aggressive_count),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script src="{{asset('assets')}}/js/app.js"></script>
@endsection

