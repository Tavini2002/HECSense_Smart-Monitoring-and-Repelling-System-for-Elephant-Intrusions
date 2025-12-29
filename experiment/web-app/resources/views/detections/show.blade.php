@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
  <title>Detection Details</title>

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
                <h4 class="page-title">Detection Details</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('show.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('detections.index') }}">Detections</a></li>
                    <li class="breadcrumb-item active">Detection #{{ $detection->id }}</li>
                </ol>
            </div>
          </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Detection Information</h4>
                    <table class="table table-striped">
                        <tr>
                            <th>Detection ID:</th>
                            <td>{{ $detection->id }}</td>
                        </tr>
                        <tr>
                            <th>Session:</th>
                            <td>
                                <a href="{{ route('sessions.show', $detection->session_id) }}">
                                    Session #{{ $detection->session_id }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Track ID:</th>
                            <td><strong>#{{ $detection->track_id }}</strong></td>
                        </tr>
                        <tr>
                            <th>Frame Number:</th>
                            <td>{{ number_format($detection->frame_number) }}</td>
                        </tr>
                        <tr>
                            <th>Detected At:</th>
                            <td>{{ $detection->detected_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Behavior:</th>
                            <td>
                                @if($detection->behavior == 'calm')
                                    <span class="badge bg-success">Calm</span>
                                @elseif($detection->behavior == 'warning')
                                    <span class="badge bg-warning">Warning</span>
                                @else
                                    <span class="badge bg-danger">Aggressive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Speed:</th>
                            <td>{{ number_format($detection->speed_kmph, 2) }} km/h</td>
                        </tr>
                        <tr>
                            <th>Confidence:</th>
                            <td>{{ number_format($detection->confidence, 4) }}</td>
                        </tr>
                        <tr>
                            <th>Aggression Score:</th>
                            <td>{{ number_format($detection->aggression_score, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Alert Triggered:</th>
                            <td>
                                @if($detection->alert_triggered)
                                    <span class="badge bg-danger">Yes - {{ $detection->alert_type ?? 'N/A' }}</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Bounding Box Information</h4>
                    <table class="table table-striped">
                        <tr>
                            <th>Top-Left (X, Y):</th>
                            <td>({{ $detection->bbox_x1 }}, {{ $detection->bbox_y1 }})</td>
                        </tr>
                        <tr>
                            <th>Bottom-Right (X, Y):</th>
                            <td>({{ $detection->bbox_x2 }}, {{ $detection->bbox_y2 }})</td>
                        </tr>
                        <tr>
                            <th>Width:</th>
                            <td>{{ $detection->bbox_width }} px</td>
                        </tr>
                        <tr>
                            <th>Height:</th>
                            <td>{{ $detection->bbox_height }} px</td>
                        </tr>
                        <tr>
                            <th>Center (X, Y):</th>
                            <td>({{ $detection->center_x }}, {{ $detection->center_y }})</td>
                        </tr>
                        <tr>
                            <th>Area:</th>
                            <td>{{ number_format($detection->bbox_width * $detection->bbox_height) }} px²</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Track History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Track History (Same Elephant - Track ID #{{ $detection->track_id }})</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Frame #</th>
                                    <th>Detected At</th>
                                    <th>Behavior</th>
                                    <th>Speed (km/h)</th>
                                    <th>Confidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trackDetections as $trackDet)
                                <tr class="{{ $trackDet->id == $detection->id ? 'table-info' : '' }}">
                                    <td>{{ number_format($trackDet->frame_number) }}</td>
                                    <td>{{ $trackDet->detected_at->format('H:i:s') }}</td>
                                    <td>
                                        @if($trackDet->behavior == 'calm')
                                            <span class="badge bg-success">Calm</span>
                                        @elseif($trackDet->behavior == 'warning')
                                            <span class="badge bg-warning">Warning</span>
                                        @else
                                            <span class="badge bg-danger">Aggressive</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($trackDet->speed_kmph, 2) }}</td>
                                    <td>{{ number_format($trackDet->confidence, 4) }}</td>
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
    <script src="{{asset('assets')}}/js/app.js"></script>
@endsection

