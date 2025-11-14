@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')

  <title>Organs</title>

  <!-- DataTables -->
  <link href="{{asset('assets')}}/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="{{asset('assets')}}/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="{{asset('assets')}}/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

  <link href="{{asset('assets')}}/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="{{asset('assets')}}/css/metismenu.min.css" rel="stylesheet" type="text/css">
  <link href="{{asset('assets')}}/css/icons.css" rel="stylesheet" type="text/css">
  <link href="{{asset('assets')}}/css/style.css" rel="stylesheet" type="text/css">

  <style>
        .popup {
            display: none;
            position: fixed;
            z-index: 999;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: #12192b; /* A darker shade that complements #293446 */
            box-shadow: 0 5px 15px rgba(21, 33, 54, 0.7); /* Darker shadow to match the background color */
            padding: 20px;
            border-radius: 10px;
            animation: bounceIn 0.5s both ease-out; /* Bounce in animation with ease-out timing function */
        }

        @keyframes bounceIn {
            0% {
                transform: translate(-50%, -50%) scale(0.1);
                opacity: 0;
            }
            60% {
                transform: translate(-50%, -50%) scale(1.2);
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
            }
        }

  </style>

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
                <h4 class="page-title">Add Organs</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a>App</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#">Organs</a>
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
                    <form id="organForm" method="POST" action="{{ route('add.organ') }}" enctype="multipart/form-data">
                        @csrf
            
                        <h4 style="text-align: center;">Add Organ Details</h4>
                        <hr>
            
                        <div style="margin-top: 20px; padding-bottom:50px;">
                            
                            <!-- Organ Name (Dropdown with full list) -->
                            <div class="form-group row">
                                <label for="organ_name" class="col-sm-2 col-form-label">Organ Name:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="organ_name" name="organ_name" required>
                                        <option value="" disabled selected>Select Organ</option>
                                        <option value="Kidney">Kidney</option>
                                        <option value="Liver">Liver</option>
                                        <option value="Heart">Heart</option>
                                        <option value="Lung">Lung</option>
                                        <option value="Pancreas">Pancreas</option>
                                        <option value="Small Intestine">Small Intestine</option>
                                        <option value="Corneas">Corneas</option>
                                        <option value="Heart Valves">Heart Valves</option>
                                        <option value="Bone Marrow">Bone Marrow</option>
                                        <option value="Skin">Skin</option>
                                    </select>
                                </div>
            
                                <!-- Blood Type (Dropdown) -->
                                <label for="blood_type" class="col-sm-2 col-form-label">Blood Type:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="blood_type" name="blood_type" required>
                                        <option value="" disabled selected>Select Blood Type</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
            
                            <!-- Donor Name -->
                            <div class="form-group row">
                                <label for="donor_name" class="col-sm-2 col-form-label">Donor Name:</label>
                                <div class="col-sm-4">
                                    <input 
                                        class="form-control" 
                                        type="text" 
                                        placeholder="Enter Donor Name" 
                                        id="donor_name" 
                                        name="donor_name" 
                                        pattern="[A-Za-z\s]+" 
                                        title="Only letters and spaces are allowed"
                                        required
                                    >
                                </div>

                                <!-- Donor Age -->
                                <label for="donor_age" class="col-sm-2 col-form-label">Donor Age:</label>
                                <div class="col-sm-4">
                                    <input 
                                        class="form-control" 
                                        type="number" 
                                        placeholder="Enter Donor Age" 
                                        id="donor_age" 
                                        name="donor_age" 
                                        min="18" 
                                        max="100" 
                                        required
                                    >
                                </div>
                            </div>


                            <!-- Donor Gender (Dropdown) -->
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Donor Gender:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="donor_gender" name="donor_gender" required>
                                        <option value="" disabled selected>Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
            
                                <!-- Organ Condition (Dropdown) -->
                                <label for="organ_condition" class="col-sm-2 col-form-label">Organ Condition:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="organ_condition" name="organ_condition" required>
                                        <option value="" disabled selected>Select Condition</option>
                                        <option value="Fresh">Fresh</option>
                                        <option value="Stored">Stored</option>
                                        <option value="Preserved">Preserved</option>
                                    </select>
                                </div>
                            </div>
            
                            <!-- Organ Type (Dropdown) -->
                            <div class="form-group row">
                                <label for="organ_type" class="col-sm-2 col-form-label">Organ Type:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="organ_type" name="organ_type" required>
                                        <option value="" disabled selected>Select Organ Type</option>
                                        <option value="Vital">Vital</option>
                                        <option value="Non-vital">Non-vital</option>
                                        <option value="Tissue">Tissue</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Submit Button with float:right -->
                            <div style="clear:both;">
                                <div style="float:right;">
                                    <input type="submit" value="Add Organ" class="btn btn-success">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table id="datatable" class="table table-bordered display nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Organ Name</th>
                                <th>Blood Type</th>
                                <th>Donor Name</th>
                                <th>Donor Age</th> <!-- Added Donor Age -->
                                <th>Donor Gender</th> <!-- Added Donor Gender -->
                                <th>Organ Type</th>
                                <th>Condition</th>
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
        document.getElementById("donor_age").addEventListener("change", function() {
            const ageInput = document.getElementById("donor_age");
            const ageValue = parseInt(ageInput.value, 10);
    
            if (ageValue < 18) {
                alert("Donor age must be above 18.");
                ageInput.value = ""; // Clear the input
            }
        });
    </script>
    

    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 6000); // 6 seconds
        });
    </script>


    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('process.organs.ajax') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = "{{ csrf_token() }}"; // Include CSRF token
                    }
                },
                "scrollX": true,
                "columns": [
                    {
                        "data": null,
                        "render": function (data, type, row) {
                            var deleteUrl = '{{ route("delete.organ", ":id") }}';
                            deleteUrl = deleteUrl.replace(':id', row.id);

                            return '<div class="text-center">' +
                                '<button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(' + row.id + ')">Delete</button>' +
                                '</div>';
                        }
                    },
                    { "data": "organ_name", "name": "organ_name" },
                    { "data": "blood_type", "name": "blood_type" },
                    { "data": "donor_name", "name": "donor_name" },
                    { "data": "donor_age", "name": "donor_age" },
                    { "data": "donor_gender", "name": "donor_gender" },
                    { "data": "organ_type", "name": "organ_type" },
                    { "data": "organ_condition", "name": "organ_condition" }
                ]
            });
        });

        // Confirm delete function using SweetAlert
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteOrgan(id);
                }
            })
        }

        // Delete function
        function deleteOrgan(id) {
            $.ajax({
                url: '{{ route("delete.organ", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Deleted!',
                            'The organ has been deleted.',
                            'success'
                        );
                        $('#datatable').DataTable().ajax.reload(); // Reload DataTable
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to delete the organ.',
                            'error'
                        );
                    }
                }
            });
        }
    </script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const donorNameInput = document.getElementById("donor_name");
        const donorAgeInput = document.getElementById("donor_age");

        donorNameInput.addEventListener("input", function () {
            // Remove any characters that are not letters or spaces
            donorNameInput.value = donorNameInput.value.replace(/[^a-zA-Z\s]/g, "");
        });

        donorAgeInput.addEventListener("input", function () {
            const age = parseInt(donorAgeInput.value);
            if (age < 18) {
                donorAgeInput.setCustomValidity("Donor age must be 18 or above.");
            } else {
                donorAgeInput.setCustomValidity("");
            }
        });
    });
</script>



@endsection
