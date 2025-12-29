<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta content="A management system for IOV" name="description" />
    <meta content="Mpowerr" name="author" />
    <link rel="shortcut icon" href="{{ asset('assets') }}/images/iov-logo.jpg">

    <style>

        .footer-link {
            color: #23cbe0; /* Initial link color */
            text-decoration: none; /* Remove underline */
        }

        .footer-link:hover {
            color: #0e86e7; /* Link color on hover */
        }

        /* Global CSS to set text color to white for various input fields */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            color: white !important; /* Set text color to white */
        }

        /* Style for focused input fields and select elements */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            color: white !important; /* Set text color to white */
        }

        /* Style for select options */
        select option {
            color: white !important; /* Set option text color to black for better readability */
        }

    </style>

    <style>
        /* Custom CSS for SweetAlert2 */
        .swal2-popup {
            border: 2px solid #6E7681; /* Set border color and width */
        }

        /* Language Switcher Vertical Centering */
        .navbar-custom {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .language-switcher-buttons {
            display: flex !important;
            align-items: center !important;
            margin-bottom: 0 !important;
            height: 70px !important;
        }

        .language-switcher-buttons .list-inline-item {
            display: flex !important;
            align-items: center !important;
        }
    </style>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('wheel', function(e) {
                if (e.target.type === 'number' && e.target === document.activeElement) {
                    e.preventDefault();
                }
            }, { passive: false });
        });
    </script>

    


    @yield('HeaderAssets')

</head>

<body>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Top Bar Start -->
        <div class="topbar">

            <!-- LOGO -->
            <div class="topbar-left">
                <a href="index.html" class="logo" style="margin: 10px;">
                    <img src="{{ asset('assets') }}/images/iov-logo.png" class="logo-lg rounded-circle" alt="" style="width: 50px; height: 50px; object-fit: cover; margin-bottom: 3px !important;">
                </a>
            </div>


            <!-- Search input -->
            <div class="search-wrap" id="search-wrap">
                <div class="search-bar">
                    <input class="search-input" type="search" placeholder="{{ __('messages.search') }}" />
                    <a href="#" class="close-search toggle-search" data-target="#search-wrap">
                        <i class="mdi mdi-close-circle"></i>
                    </a>
                </div>
            </div>

            <nav class="navbar-custom">
               
                <ul class="list-inline menu-left mb-0">
                    <li class="float-left">
                        <button class="button-menu-mobile open-left waves-effect">
                            <i class="mdi mdi-menu"></i>
                        </button>
                    </li>
                </ul>

                <!-- Language Switcher -->
                <ul class="list-inline float-right mb-0 language-switcher-buttons">
                    <li class="list-inline-item">
                        <a href="{{ route('language.switch', 'en') }}" class="btn btn-sm waves-effect waves-light {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-secondary' }}" style="margin-right: 5px;">
                            English
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ route('language.switch', 'si') }}" class="btn btn-sm waves-effect waves-light {{ app()->getLocale() == 'si' ? 'btn-primary' : 'btn-secondary' }}" style="margin-right: 5px;">
                            සිංහල
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ route('language.switch', 'ta') }}" class="btn btn-sm waves-effect waves-light {{ app()->getLocale() == 'ta' ? 'btn-primary' : 'btn-secondary' }}">
                            தமிழ்
                        </a>
                    </li>
                </ul>

            </nav>

        </div>
        <!-- Top Bar End -->

        <!-- ========== Left Sidebar Start ========== -->
        <div class="left side-menu">
            <div class="slimscroll-menu" id="remove-scroll">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu" id="side-menu">
                        <li class="menu-title">{{ __('messages.menu') }}</li>
                    
                        <li>
                            <a href="{{ route('show.dashboard') }}" class="waves-effect">
                                <i class="dripicons-meter"></i> <span> {{ __('messages.dashboard') }} </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('sessions.index') }}" class="waves-effect">
                                <i class="mdi mdi-video"></i> <span> {{ __('messages.detection_sessions') }} </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('detections.index') }}" class="waves-effect">
                                <i class="mdi mdi-radar"></i> <span> {{ __('messages.detections') }} </span>
                            </a>
                        </li>
                    
                        {{-- 
                        <li>
                            <a href="{{ route('show.organs') }}" class="waves-effect">
                                <i class="fa fa-heart"></i> <span> Add Organs </span>
                            </a>
                        </li>
                        
                    
                        <li>
                            <a href="{{ route('show.organ.requests') }}" class="waves-effect">
                                <i class="fa fa-dna"></i> <span>Organ Requests</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('show.messages') }}" class="waves-effect">
                                <i class="fa fa-envelope"></i> <span> Messages </span>
                            </a>
                        </li>    
                        
                        --}}
                    
                        <li>
                            <a href="{{ route('show.mobile.users') }}" class="waves-effect">
                                <i class="fa fa-users"></i> <span> {{ __('messages.user_accounts') }} </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('show.settings') }}" class="waves-effect">
                                <i class="fa fa-cog"></i> <span> {{ __('messages.settings') }} </span>
                            </a>
                        </li>
                    
                    
                        <li>
                            <a href="{{ route('logout') }}" class="waves-effect">
                                <i class="fas fa-power-off"></i> <span> {{ __('messages.logout') }} </span>
                            </a>
                        </li>
                    </ul>
                    

                </div>
                <!-- Sidebar -->
                <div class="clearfix"></div>

            </div>
            <!-- Sidebar -left -->

        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="content-page">
            <!-- Start content -->
            <div class="content">


                @yield('PageContent')

            </div>
            <!-- content -->

            <footer class="footer">
                © {{ date('Y') }} {{ __('messages.all_rights_reserved') }}.
            </footer>                     

        </div>
        <!-- ============================================================== -->
        <!-- End Right content here -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    @yield('FooterAssets')

  <!-- SMS Under Development -->
    <script id="sweetHolder"></script>
    <script>
        document.getElementById('sendSMSLink').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default link behavior

            // Check if the script is already loaded
            if (typeof Swal === 'undefined') {
                // Dynamically load the SweetAlert2 script if not already loaded
                var script = document.getElementById("sweetHolder");
                script.src = "{{ asset('assets/plugins/sweet-alert2/sweetalert2.all.min.js') }}";

                // Wait for the script to load before executing the SweetAlert
                script.onload = function() {
                    // Display SweetAlert with a message
                    showUnderDevelopmentAlert();
                };
            } else {
                // If the script is already loaded, just show the alert
                showUnderDevelopmentAlert();
            }
        });

        // Function to display the SweetAlert
        function showUnderDevelopmentAlert() {
            Swal.fire({
                icon: 'info',
                background: "#3f4f69",
                color: "#ffffff",
                title: 'Under Development',
                text: 'The SMS sending feature is currently under development. Please check back later.',
                confirmButtonText: 'OK'
            });
        }
    </script>



    <!-- SMS Under Development -->

</body>

</html>