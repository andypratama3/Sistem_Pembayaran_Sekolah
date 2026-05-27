<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="flash-type" content="{{ Session::get('flash_type') }}">
    <meta name="flash-message" content="{{ Session::get('flash_message') }}">
    <!--! END: Theme Initialization Script !-->

    <!--! The above meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>@yield('title')</title>
    <!--! END:  Apps Title-->
    <!--! BEGIN: Favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />
    <!--! END: Favicon-->
    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <!--! END: Bootstrap CSS-->
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/select2-theme.min.css') }}" />

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-calendar.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-theme.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-time-picker.min.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/vendors/css/tui-date-picker.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/dataTables.bs5.min.css') }}" />


    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/datepicker.min.css') }}">
    <!--! END: Vendors CSS-->
    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    @stack('css')
    <style>
        body {
            font-family: 'Inter', sans-serif !important;
            letter-spacing: -0.01em;
        }

        /* Premium UI Enhancements */
        :root {
            --primary-gradient: linear-gradient(135deg, #3454d1 0%, #6366f1 100%);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --premium-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            --soft-blue: rgba(52, 84, 209, 0.05);
        }

        html.app-skin-dark {
            --glass-bg: rgba(15, 23, 42, 0.7);
            --glass-border: rgba(148, 163, 184, 0.1);
        }

        .premium-card {
            /* transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); */
            border: 1px solid var(--glass-border) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
        }

        .blur-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.4) !important;
        }


        html.app-skin-dark .premium-card:hover {
            border-color: rgba(99, 102, 241, 0.3) !important;
            background: rgba(30, 41, 59, 0.8) !important;
        }

        .glass-header {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid var(--glass-border) !important;
        }

        .glass-sidebar {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-right: 1px solid var(--glass-border) !important;
        }

        .bg-premium-gradient {
            background: var(--primary-gradient) !important;
            position: relative;
            overflow: hidden;
        }

        .bg-premium-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 80%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .avatar-text.bg-soft-primary {
            background: rgba(52, 84, 209, 0.1) !important;
            color: #3454d1 !important;
            border: 1px solid rgba(52, 84, 209, 0.2) !important;
        }

        .btn-premium {
            background: var(--primary-gradient);
            color: white;
            border: none;
            box-shadow: 0 4px 14px 0 rgba(52, 84, 209, 0.39);
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(52, 84, 209, 0.23);
            color: white;
        }

        .btn-premium-light {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white !important;
            transition: all 0.3s ease;
        }

        .btn-premium-light:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .btn-quick-action {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(52, 84, 209, 0.1) !important;
            background: transparent;
        }

        .btn-quick-action:hover {
            background: rgba(52, 84, 209, 0.05) !important;
            border-color: #3454d1 !important;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -10px rgba(52, 84, 209, 0.3);
        }

        .btn-quick-action i {
            transition: transform 0.3s ease;
        }

        .btn-quick-action:hover i {
            transform: scale(1.2);
        }

        /* Sticky Footer Implementation */
        .nxl-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .nxl-content {
            flex: 1 0 auto;
        }

        .footer {
            flex-shrink: 0;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            padding: 1rem 2rem;
        }

        html.app-skin-dark .footer {
            background: #0f172a;
            border-top-color: rgba(148, 163, 184, 0.25);
        }

        /* Select2 inside Input Group Fix */
        .input-group>.select2-container--bootstrap-5 {
            flex: 1 1 auto;
            width: 1% !important;
        }

        .input-group>.select2-container--bootstrap-5 .select2-selection {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }

        /* If Select2 is NOT the last element (e.g. there is a button after it) */
        .input-group>.select2-container--bootstrap-5:not(:last-child) .select2-selection {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        /* The button after select2 should have no left radius */
        .input-group>.select2-container--bootstrap-5+.btn {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            z-index: 3;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
        }

        html.app-skin-dark .input-group-text {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(148, 163, 184, 0.25);
            color: #94a3b8;
        }

        .bg-soft-light {
            background-color: #f1f5f9 !important; /* Slightly darker for better contrast in light mode */
        }

        html.app-skin-dark .bg-soft-light {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f8fafc !important;
        }

        .form-control.bg-soft-light::placeholder,
        .form-select.bg-soft-light {
            color: #64748b !important;
        }

        html.app-skin-dark .form-control.bg-soft-light,
        html.app-skin-dark .form-select.bg-soft-light {
            color: #f8fafc !important;
        }

        /* Prioritized Light Mode Visibility & Premium Search */
        .table-search-box .input-group {
            border: 2px solid #cbd5e1 !important; /* Clearly visible border in light mode */
            background-color: #ffffff !important;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 320px;
        }

        .table-search-box .input-group:focus-within {
            border-color: #3454d1 !important;
            box-shadow: 0 0 0 4px rgba(52, 84, 209, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-1px);
        }

        .table-search-box .input-group-text {
            color: #64748b;
            background-color: transparent !important;
            padding-left: 12px;
            border: none !important;
        }

        .table-search-box .form-control {
            border: none !important;
            padding-left: 8px;
            background-color: transparent !important;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b !important;
            height: 40px !important;
        }

        .table-search-box .form-control::placeholder {
            color: #94a3b8 !important;
            font-weight: 400;
        }

        html.app-skin-dark .table-search-box .input-group {
            border-color: rgba(148, 163, 184, 0.2) !important;
            background-color: rgba(30, 41, 59, 0.8) !important;
        }

        html.app-skin-dark .table-search-box .form-control {
            color: #f8fafc !important;
        }

        /* Hover visibility fix - ensure text remains dark/visible in light mode */
        .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: rgba(52, 84, 209, 0.04) !important;
            color: #0f172a !important;
            box-shadow: inset 0 0 0 9999px rgba(52, 84, 209, 0.04) !important;
        }

        html.app-skin-dark .table-hover > tbody > tr:hover > * {
            --bs-table-accent-bg: rgba(255, 255, 255, 0.05) !important;
            color: #f8fafc !important;
            box-shadow: inset 0 0 0 9999px rgba(255, 255, 255, 0.05) !important;
        }

        .filter-form-area {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        .form-select.border-0.bg-soft-light {
            background-color: #ffffff !important;
            border: 2px solid #e2e8f0 !important;
            font-size: 13px;
            font-weight: 500;
            border-radius: 10px;
        }

        .btn-soft-danger {
            background-color: #fff1f2 !important;
            color: #e11d48 !important;
            border: 1px solid #fecdd3 !important;
        }

        .btn-soft-danger:hover {
            background-color: #ffe4e6 !important;
            color: #be123c !important;
        }

        .fs-11 { font-size: 11px !important; }
        .ls-1 { letter-spacing: 0.5px !important; }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Datepicker Z-Index Fix */
        .daterangepicker,
        .flatpickr-calendar,
        .datepicker-dropdown {
            z-index: 9999 !important;
        }

        /* Chat App Layout Fix */
        html.apps-chat-active,
        html.apps-chat-active body {
            height: 100% !important;
            overflow: hidden !important;
        }

        html.apps-chat-active .nxl-header,
        html.apps-chat-active .nxl-navigation {
            flex-shrink: 0;
        }
    </style>
    <!--! END: Custom CSS-->
    @stack('css')
</head>

<body>
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Manu !-->
    <!--! ================================================================ !-->
    <nav class="nxl-navigation glass-sidebar">
        @include('layouts.partial.nav')
    </nav>
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Manu !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header glass-header">
        @include('layouts.partial.header')
    </header>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    <main class="nxl-container">
        <div class="nxl-content">
            <!-- [ page-header ] start -->
            @yield('page-header')
            <!-- [ page-header ] end -->
            <!-- [ Main Content ] start -->
            <div class="main-content">
                @yield('chat-app')
                <div class="row">
                    @yield('content')
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
        <!-- [ Footer ] start -->
        @include('layouts.partial.footer')
        <!-- [ Footer ] end -->
    </main>

    @yield('modal')
    @stack('modals')
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Theme Customizer !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! [End] Theme Customizer !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    @include('layouts.partial.script')
    @include('components.search-modal')

    <!-- Laravel Echo - Bundled locally via Vite (resources/js/echo.js) -->
    <!-- No CDN - improved performance & reliability -->


</body>

</html>
