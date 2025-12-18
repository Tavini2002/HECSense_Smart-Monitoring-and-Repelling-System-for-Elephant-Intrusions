@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
    <title>Message Requests</title>

    {{-- DataTable styles --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/responsive.bootstrap4.min.css') }}">

    {{-- Core UI styles --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/metismenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        #messageTable tbody tr:hover {
            background-color: #3f4f69;
            cursor: pointer;
        }
    </style>
@endsection


@section('PageContent')
<div class="container-fluid">

    {{-- Page header --}}
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="page-title">Requested Messages</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Organ App</li>
                    <li class="breadcrumb-item active">Messages</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Notifications --}}
    <div class="row">
        <div class="col-12">

            @if ($errors->any())
                <div class="alert alert-danger text-center" style="background:red;color:#fff;font-size:18px;">
                    <button type="button" class="close text-white" data-dismiss="alert">&times;</button>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger text-center" style="background:red;color:#fff;font-size:18px;">
                    <button type="button" class="close text-white" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success text-center" style="background:green;color:#fff;font-size:18px;">
                    <button type="button" class="close text-white" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

        </div>
    </div>

    {{-- Data table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="messageTable"
                           class="table table-bordered nowrap"
                           style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Organ</th>
                                <th>Blood Group</th>
                                <th>Requested User</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('FooterAssets')

    {{-- Core scripts --}}
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/metismenu.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('assets/js/waves.min.js') }}"></script>

    {{-- DataTables --}}
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/responsive.bootstrap4.min.js') }}"></script>

    {{-- App --}}
    <script src="{{ asset('assets/js/app.js') }}"></script>

    {{-- Table initialization --}}
    <script>
        $(function () {
            $('#messageTable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('process.messages.ajax') }}",
                    method: "POST",
                    data: function (payload) {
                        payload._token = "{{ csrf_token() }}";
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'organ_name', name: 'organ_name' },
                    { data: 'blood_type', name: 'blood_type' },
                    { data: 'requested_by', name: 'requested_by' },
                    { data: 'message', name: 'message' }
                ]
            });
        });
    </script>

@endsection
