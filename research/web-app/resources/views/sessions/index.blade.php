@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
  <title>Detection Sessions</title>

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
                <h4 class="page-title">{{ __('messages.detection_sessions_page') }}</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('show.dashboard') }}">{{ __('messages.home') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('messages.sessions') }}</li>
                </ol>
            </div>
          </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('messages.all_detection_sessions') }}</h4>
                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered table-striped nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('messages.session_name') }}</th>
                                    <th>{{ __('messages.source_type') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.started_at') }}</th>
                                    <th>{{ __('messages.ended_at') }}</th>
                                    <th>{{ __('messages.duration') }}</th>
                                    <th>{{ __('messages.total_frames') }}</th>
                                    <th>{{ __('messages.confidence') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                <tr>
                                    <td>{{ $session->id }}</td>
                                    <td>{{ $session->session_name ?? __('messages.n_a') }}</td>
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
                                    <td>{{ $session->started_at ? $session->started_at->format('Y-m-d H:i:s') : __('messages.n_a') }}</td>
                                    <td>{{ $session->ended_at ? $session->ended_at->format('Y-m-d H:i:s') : __('messages.n_a') }}</td>
                                    <td>
                                        @if($session->started_at && $session->ended_at)
                                            {{ gmdate('H:i:s', $session->duration) }}
                                        @else
                                            {{ __('messages.n_a') }}
                                        @endif
                                    </td>
                                    <td>{{ number_format($session->total_frames) }}</td>
                                    <td>{{ number_format($session->confidence_threshold, 2) }}</td>
                                    <td>
                                        <a href="{{ route('sessions.show', $session->id) }}" class="btn btn-sm btn-primary">
                                            <i class="mdi mdi-eye"></i> {{ __('messages.view') }}
                                        </a>
                                        <form action="{{ route('sessions.destroy', $session->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('messages.are_you_sure') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="mdi mdi-delete"></i> {{ __('messages.delete') }}
                                            </button>
                                        </form>
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
    <script src="{{asset('assets')}}/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                responsive: true,
                order: [[4, 'desc']] // Sort by started_at descending
            });
        });
    </script>

    <script src="{{asset('assets')}}/js/app.js"></script>
@endsection

