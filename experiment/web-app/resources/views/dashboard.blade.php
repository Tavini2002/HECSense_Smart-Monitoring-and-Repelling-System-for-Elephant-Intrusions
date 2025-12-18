@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
    <title>Dashboard</title>

    {{-- DataTables styles --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/responsive.bootstrap4.min.css') }}">

    {{-- Core styles --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/metismenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endsection

@section('PageContent')
<div class="container-fluid">

    {{-- Page heading --}}
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="page-title mb-0">Dashboard</h4>
            </div>
        </div>
    </div>

    {{-- Dashboard placeholder --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-2">Welcome to the Dashboard</h5>
                    <p class="mb-0">
                        This area can be customized with widgets, charts, or reports.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('FooterAssets')

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- DataTables scripts --}}
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    {{-- Application script --}}
    <script src="{{ asset('assets/js/app.js') }}"></script>

@endsection
