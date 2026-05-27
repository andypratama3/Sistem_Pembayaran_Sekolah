<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="app-skin-light">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />
    
    @vite('resources/css/app.css')
    
    @stack('css')

    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        #editor-app { 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }
        [x-cloak] { display: none !important; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        
        #modal-container {
            display: none;
        }
        #modal-container.active {
            display: block;
        }
        #modal-container .modal {
            display: none;
        }
        #modal-container .modal.show {
            display: flex !important;
        }
        #editor-app > footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body class="antialiased">
    
    <div id="editor-app">
        @yield('content')
    </div>

    <div id="modal-container">
        @yield('modal')
    </div>

    <!-- Core Scripts -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>
    <script src="{{ asset('assets/js/swal-init.js') }}"></script>
    
    @vite('resources/js/app.js')
    
<!-- SweetAlert2 (vendors.min.js already bundles SweetAlert2, local fallback) -->
    <script src="{{ asset('assets/vendors/js/sweetalert2.min.js') }}"></script>

    <!-- Alpine.js fallback (Vite should load it from app.js, but adding CDN as backup) -->
    <script defer src="https://unpkg.com/alpinejs@3.4.2/dist/cdn.min.js"></script>

    <script>
        // Ensure Bootstrap Modal is properly initialized
        document.addEventListener('DOMContentLoaded', function() {
            window.bootstrap = window.bootstrap || window.bootstrap5 || window.bootstrap4 || null;
        });
    </script>
    
    @stack('scripts')
</body>
</html>
