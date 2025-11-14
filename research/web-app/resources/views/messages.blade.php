@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')

<title>Requested Messages</title>

<!-- DataTables -->
<link href="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets')}}/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

<link href="{{asset('assets')}}/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="{{asset('assets')}}/css/metismenu.min.css" rel="stylesheet" type="text/css">
<link href="{{asset('assets')}}/css/icons.css" rel="stylesheet" type="text/css">
<link href="{{asset('assets')}}/css/style.css" rel="stylesheet" type="text/css">

<style>
    #datatable tbody tr:hover{
        background-color: #3f4f69; /* Change the background color on hover */
        cursor: pointer; /* Change the cursor to pointer to indicate the row is clickable */
    }
</style>

@endsection

@section('PageContent')

<div class="container-fluid">
    <div class="page-title-box">

        <div class="row align-items-center ">
          <div class="col-md-8">
            <div class="page-title-box">
                <h4 class="page-title">Requested Messages</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a>Organ App</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#">Messages</a>
                    </li>
                </ol>
            </div>

        </div>
    </div>
    <!-- end page-title -->

    <div class="row">
        <div class="col-12">

            @if ($errors->any())
                <div class="alert alert-danger" style="background-color: red; color: white; font-size: 18px; text-align: center;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color:white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger" style="background-color: red; color: white; font-size: 18px; text-align: center;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color:white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('error') }}
                </div>
            @endif
            
            @if (session('success'))
                <div class="alert alert-success" style="background-color: green; color: white; font-size: 18px; text-align: center;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color:white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('success') }}
                </div>
            @endif


            <div class="card">
                <div class="card-body">
                    <table id="datatable" class="table table-bordered display nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Message ID</th>
                                <th>Organ Name</th>
                                <th>Blood Type</th>
                                <th>Requested By</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        <!-- end col -->
    </div>
    <!-- end row -->
</div>

@endsection

@section('FooterAssets')

 <!-- jQuery  -->
 <script src="{{asset('assets')}}/js/jquery.min.js"></script>
 <script src="{{asset('assets')}}/js/bootstrap.bundle.min.js"></script>
 <script src="{{asset('assets')}}/js/metismenu.min.js"></script>
 <script src="{{asset('assets')}}/js/jquery.slimscroll.js"></script>
 <script src="{{asset('assets')}}/js/waves.min.js"></script>

 <!-- DataTables -->
 <script src="{{asset('assets')}}/plugins/datatables/jquery.dataTables.min.js"></script>
 <script src="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.js"></script>
 <!-- Responsive examples -->
 <script src="{{asset('assets')}}/plugins/datatables/dataTables.responsive.min.js"></script>
 <script src="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.js"></script>

 <!-- App js -->
 <script src="{{asset('assets')}}/js/app.js"></script>

<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('process.messages.ajax') }}", // Update route for fetching messages
                "type": "POST",
                "data": function (d) {
                    d._token = "{{ csrf_token() }}"; // Include CSRF token
                }
            },
            "scrollX": true,
            "columns": [
                { "data": "id", "name": "id" },
                { "data": "organ_name", "name": "organ_name" },
                { "data": "blood_type", "name": "blood_type" },
                { "data": "requested_by", "name": "requested_by" }, // Full Name of the person requesting
                { "data": "message", "name": "message" }
            ]
        });
    });
</script>

@endsection
