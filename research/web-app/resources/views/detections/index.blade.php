@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
  <title>Detections</title>

  <!-- DataTables -->
  <link href="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="{{asset('assets')}}/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

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
                <h4 class="page-title">Elephant Detections</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('show.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Detections</li>
                </ol>
            </div>
          </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('messages.filter') }}</h4>
                    <form method="GET" action="{{ route('detections.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="session_id" class="form-control">
                                    <option value="">{{ __('messages.all_sessions') }}</option>
                                    @foreach($sessions as $sess)
                                        <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>
                                            Session #{{ $sess->id }} - {{ $sess->session_name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="behavior" class="form-control">
                                    <option value="">{{ __('messages.all_behaviors') }}</option>
                                    <option value="calm" {{ request('behavior') == 'calm' ? 'selected' : '' }}>{{ __('messages.calm') }}</option>
                                    <option value="warning" {{ request('behavior') == 'warning' ? 'selected' : '' }}>{{ __('messages.warning') }}</option>
                                    <option value="aggressive" {{ request('behavior') == 'aggressive' ? 'selected' : '' }}>{{ __('messages.aggressive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="track_id" class="form-control" placeholder="{{ __('messages.track_id') }}" value="{{ request('track_id') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
                                <a href="{{ route('detections.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Detections Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('messages.all_detections') }}</h4>
                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered table-striped nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('messages.session') }}</th>
                                    <th>{{ __('messages.track_id') }}</th>
                                    <th>{{ __('messages.frame_number') }}</th>
                                    <th>{{ __('messages.detected_at') }}</th>
                                    <th>{{ __('messages.behavior') }}</th>
                                    <th>{{ __('messages.speed_kmph') }}</th>
                                    <th>{{ __('messages.confidence') }}</th>
                                    <th>{{ __('messages.alert') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detections as $detection)
                                <tr>
                                    <td>{{ $detection->id }}</td>
                                    <td>
                                        <a href="{{ route('sessions.show', $detection->session_id) }}">
                                            Session #{{ $detection->session_id }}
                                        </a>
                                    </td>
                                    <td><strong>#{{ $detection->track_id }}</strong></td>
                                    <td>{{ number_format($detection->frame_number) }}</td>
                                    <td>{{ $detection->detected_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @if($detection->behavior == 'calm')
                                            <span class="badge bg-success">{{ __('messages.calm') }}</span>
                                        @elseif($detection->behavior == 'warning')
                                            <span class="badge bg-warning">{{ __('messages.warning') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('messages.aggressive') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($detection->speed_kmph, 2) }}</td>
                                    <td>{{ number_format($detection->confidence, 4) }}</td>
                                    <td>
                                        @if($detection->alert_triggered)
                                            <span class="badge bg-danger">{{ __('messages.yes') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('messages.no') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('detections.show', $detection->id) }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-eye"></i> {{ __('messages.view') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $detections->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('FooterAssets')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                responsive: true,
                order: [[4, 'desc']], // Sort by detected_at descending
                pageLength: 50
            });
        });
    </script>

    <script src="{{asset('assets')}}/js/app.js"></script>
@endsection

