@extends('layouts.dashboard_master_layout')

@section('HeaderAssets')
    <title>IOV | Account Settings</title>

    {{-- Datepicker --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">

    {{-- Core styles --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/metismenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endsection


@section('PageContent')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="page-title">Credential Management</h4>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">IOV</a></li>
                    <li class="breadcrumb-item"><a href="#">Settings</a></li>
                    <li class="breadcrumb-item active text-capitalize">Credentials</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
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

    {{-- Forms --}}
    <div class="row">

        {{-- Username Update --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-center mb-3">Update Username</h5>
                    <hr>

                    <form id="usernameUpdateForm"
                          action="{{ route('update.username') }}"
                          method="POST"
                          onsubmit="handleUsernameConfirm(event)">
                        @csrf

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text"
                                   class="form-control"
                                   name="username"
                                   id="usernameInput"
                                   value="{{ $username[0] }}"
                                   placeholder="Enter username"
                                   oninput="toggleUsernameButton()">
                        </div>

                        <div class="text-right">
                            <button type="submit"
                                    id="usernameSubmitBtn"
                                    class="btn btn-secondary"
                                    disabled>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Password Update --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-center mb-3">Update Password</h5>
                    <hr>

                    <form action="{{ route('update.password') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-info">Update</button>
                        </div>
                    </form>

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

    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweet-alert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    {{-- Auto-hide alerts --}}
    <script>
        $(function () {
            setTimeout(() => $('.alert').fadeOut('slow'), 6000);
        });
    </script>

    {{-- Username confirmation --}}
    <script>
        function handleUsernameConfirm(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Confirm Action',
                text: 'Proceed with username update?',
                icon: 'warning',
                showCancelButton: true,
                background: '#12192b',
                color: '#ffffff',
                confirmButtonColor: '#fb4365',
                cancelButtonColor: '#20d4b6',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel'
            }).then((res) => {
                if (res.isConfirmed) {
                    document.getElementById('usernameUpdateForm').submit();
                }
            });
        }
    </script>

    {{-- Username change detection --}}
    <script>
        const initialUsername = "{{ $username[0] }}";

        function toggleUsernameButton() {
            const input = document.getElementById('usernameInput');
            const btn = document.getElementById('usernameSubmitBtn');

            if (input.value.trim() !== initialUsername) {
                btn.disabled = false;
                btn.classList.replace('btn-secondary', 'btn-info');
            } else {
                btn.disabled = true;
                btn.classList.replace('btn-info', 'btn-secondary');
            }
        }
    </script>

@endsection
