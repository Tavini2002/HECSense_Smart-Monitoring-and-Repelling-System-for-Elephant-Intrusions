@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')

  <title>Mobile Users</title>

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
                <h4 class="page-title">{{ __('messages.mobile_users') }}</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('show.dashboard') }}">{{ __('messages.home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#">{{ __('messages.user_accounts') }}</a>
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
                        <th>{{ __('messages.action') }}</th>
                        <th>{{ __('messages.full_name') }}</th>
                        <th>{{ __('messages.email') }}</th>
                        <th>{{ __('messages.phone_number') }}</th>
                        <th>{{ __('messages.gender') }}</th>
                        <th>{{ __('messages.date_of_birth') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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

     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 
     <script src="{{asset('assets')}}/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
 
     <!-- Required datatable js -->
     <script src="{{asset('assets')}}/plugins/datatables/jquery.dataTables.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.js"></script>
     <!-- Buttons examples -->
     <script src="{{asset('assets')}}/plugins/datatables/dataTables.buttons.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/buttons.bootstrap4.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/jszip.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/pdfmake.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/vfs_fonts.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/buttons.html5.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/buttons.print.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/buttons.colVis.min.js"></script>
     <!-- Responsive examples -->
     <script src="{{asset('assets')}}/plugins/datatables/dataTables.responsive.min.js"></script>
     <script src="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.js"></script>

    <!-- App js -->
    <script src="{{asset('assets')}}/js/app.js"></script>
    

    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 6000); // 6 seconds
        });
    </script>

<script>
// Make sure every AJAX call carries the CSRF token
$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
});

// SweetAlert confirm + call approve
function confirmApprove(id){
  Swal.fire({
    title: 'Approve this user?',
    icon: 'question',
    showCancelButton: true
  }).then(res => {
    if(!res.isConfirmed) return;

    $.post("{{ route('approve.user', ':id') }}".replace(':id', id))
      .done(resp => {
        if(resp.success){
          $('#datatable').DataTable().ajax.reload(null, false);
          Swal.fire('Approved', 'User approved successfully.', 'success');
        } else {
          Swal.fire('Error', resp.message || 'Approve failed', 'error');
        }
      })
      .fail(xhr => {
        console.error(xhr.responseText);
        Swal.fire('Error', 'Approve request failed', 'error');
      });
  });
}

// SweetAlert confirm + call delete
function confirmDelete(id){
  Swal.fire({
    title: 'Delete this user?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33'
  }).then(res => {
    if(!res.isConfirmed) return;

    $.ajax({
      url: "{{ route('delete.user', ':id') }}".replace(':id', id),
      type: 'DELETE'
    })
    .done(resp => {
      if(resp.success){
        $('#datatable').DataTable().ajax.reload(null, false);
        Swal.fire('Deleted', 'User deleted successfully.', 'success');
      } else {
        Swal.fire('Error', resp.message || 'Delete failed', 'error');
      }
    })
    .fail(xhr => {
      console.error(xhr.responseText);
      Swal.fire('Error', 'Delete request failed', 'error');
    });
  });
}
</script>



<script>
$(document).ready(function() {
  $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('process.mobile.users.ajax') }}",
      type: "POST",
      data: function (d) {
        d._token = "{{ csrf_token() }}";
      }
    },
    scrollX: true,
    columns: [
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          if (row.status === 'pending') {
            return '<div class="text-center">' +
                     '<button type="button" class="btn btn-info btn-approve" onclick="confirmApprove(' + row.id + ')">Approve</button>' +
                     ' <button type="button" class="btn btn-danger btn-delete" onclick="confirmDelete(' + row.id + ')">Delete</button>' +
                   '</div>';
          }
          return '<div class="text-center">' +
                   '<button type="button" class="btn btn-danger btn-delete" onclick="confirmDelete(' + row.id + ')">Delete</button>' +
                 '</div>';
        }
      },
      { data: "full_name",   name: "full_name",   defaultContent: "" },
      { data: "email",       name: "email",       defaultContent: "" },
      { data: "phone_number",name: "phone_number",defaultContent: "" },
      { 
        data: "gender", 
        name: "gender",
        defaultContent: "",
        render: function(data) {
          // API stores enum: male|female|other (nullable)
          if (!data) return '';
          return data.charAt(0).toUpperCase() + data.slice(1);
        }
      },
      { 
        data: "dob", 
        name: "dob", 
        defaultContent: "" 
        // Optionally format here if you want
      },
      { 
        data: "status",
        name: "status",
        render: function(data) {
          return data ? data.charAt(0).toUpperCase() + data.slice(1) : '';
        }
      }
    ]
  });
});
</script>







@endsection
