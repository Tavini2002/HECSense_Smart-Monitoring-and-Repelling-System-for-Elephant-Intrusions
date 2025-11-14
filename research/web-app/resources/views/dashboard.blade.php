@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
  <title>Dashboard</title>

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
                <h4 class="page-title">Dashboard</h4>
            </div>
          </div>
        </div>
    </div>

    <!-- Empty Dashboard Content -->
    <div class="row">
        <div class="col-12">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="text-muted">Welcome to your Dashboard</h5>
                    <p class="mb-0">You can add new widgets, analytics, or reports here.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('FooterAssets')

    <!-- Required datatable js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.js"></script>

    <!-- App js -->
    <script src="{{asset('assets')}}/js/app.js"></script>

@endsection
