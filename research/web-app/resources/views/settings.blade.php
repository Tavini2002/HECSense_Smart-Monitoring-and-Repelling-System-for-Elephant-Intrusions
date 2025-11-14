@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')

<title>IOV - Settings</title>

<link href="{{asset('assets')}}/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

<link href="{{asset('assets')}}/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="{{asset('assets')}}/css/metismenu.min.css" rel="stylesheet" type="text/css">
<link href="{{asset('assets')}}/css/icons.css" rel="stylesheet" type="text/css">
<link href="{{asset('assets')}}/css/style.css" rel="stylesheet" type="text/css">

@endsection


@section('PageContent')


<div class="container-fluid">
    <div class="page-title-box">

        <div class="row align-items-center ">
            <div class="col-md-8">
                <div class="page-title-box">
                    <h4 class="page-title">Credentials Settings</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">IOV</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" style="text-transform: capitalize;">Credentials</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

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

        </div>

        <!-- Change Username Form -->
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Change Username</h5>
                    <hr>
                    <form id="updateUsernameForm" action="{{ route('update.username') }}" method="POST" onsubmit="confirmUpdate(event)">
                        @csrf
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ $username[0] }}" placeholder="Enter the username" oninput="checkUsernameChange()">
                        </div>
                        
                        <div class="text-right">
                            <button class="btn btn-secondary" type="submit" id="updateButton" disabled>Change</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        
        <!-- Change Password Form -->
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Change Password</h5>
                    <hr>
                    <form id="changePasswordForm" action="{{ route('update.password') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="current_password">Current Password:</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password:</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm Password:</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="Confirm new password" required>
                        </div>
                        
                        <div class="text-right">
                            <button class="btn btn-info" type="submit">Change</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        
    </div>
    


</div>


@endsection


@section('FooterAssets')

    <!-- jQuery  -->
    <script src="{{asset('assets')}}/js/jquery.min.js"></script>
    <script src="{{asset('assets')}}/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('assets')}}/js/metismenu.min.js"></script>
    <script src="{{asset('assets')}}/js/jquery.slimscroll.js"></script>
    <script src="{{asset('assets')}}/js/waves.min.js"></script>

    <script src="{{asset('assets')}}/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <!-- App js -->
    <script src="{{asset('assets')}}/js/app.js"></script>

    <script src="{{asset('assets')}}/plugins/sweet-alert2/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 6000); // 6 seconds
        });
    </script>

    <script>
        function confirmUpdate(event) {
            event.preventDefault(); // Prevent the default form submission
        
            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to update the username?",
                icon: "warning",
                showCancelButton: true,
                background: "#12192b",
                color: "#fff",
                confirmButtonColor: "#fb4365",
                cancelButtonColor: "#20d4b6",
                confirmButtonText: "Yes, update it!",
                cancelButtonText: "No, cancel!",
                customClass: {
                    popup: 'custom-swal-popup'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, submit the form for updating username
                    document.getElementById('updateUsernameForm').submit();
                }
            });
        }
    </script>

    <script>
        // Store the original username value
        const originalUsername = "{{ $username[0] }}";
        
        function checkUsernameChange() {
            const usernameField = document.getElementById('username');
            const updateButton = document.getElementById('updateButton');
            
            // Check if the current value differs from the original username
            if (usernameField.value.trim() !== originalUsername) {
                updateButton.disabled = false;
                updateButton.classList.remove('btn-secondary');
                updateButton.classList.add('btn-info');
            } else {
                updateButton.disabled = true;
                updateButton.classList.remove('btn-info');
                updateButton.classList.add('btn-secondary');
            }
        }

    </script>


@endsection
