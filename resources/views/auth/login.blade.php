<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Masuk') }} - {{ config('app.name', 'ProductSchool') }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #3454d1 0%, #6366f1 100%);
        }
        .btn-premium {
            background: var(--primary-gradient);
            color: white;
            border: none;
            box-shadow: 0 4px 14px 0 rgba(52, 84, 209, 0.39);
            transition: all 0.2s ease;
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 84, 209, 0.23);
            color: white;
        }
        .auth-cover-sidebar-inner {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(52, 84, 209, 0.1);
            border-color: #3454d1;
        }
    </style>
</head>

<body>
    <main class="auth-cover-wrapper">
        <div class="auth-cover-content-inner">
            <div class="auth-cover-content-wrapper">
                <div class="auth-img">
                    <img src="{{ asset('assets/images/auth/auth-cover-login-bg.svg') }}" alt=""
                        class="img-fluid">
                </div>
            </div>
        </div>

        <div class="auth-cover-sidebar-inner">
            <div class="auth-cover-card-wrapper">
                <div class="auth-cover-card p-sm-5">
                    <div class="mb-5 wd-50">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="img-fluid">
                        </a>
                    </div>

                    <h2 class="mb-4 fs-20 fw-bolder">{{ __('Masuk') }}</h2>
                    <h4 class="mb-2 fs-13 fw-bold">{{ __('Masuk ke akun Anda') }}</h4>
                    <p class="fs-12 fw-medium text-muted">{{ __('Silakan masuk untuk melanjutkan') }}</p>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="pt-2 mt-4 w-100">
                        @csrf

                        <div class="mb-4">
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="{{ __('Email atau Username') }}" required autofocus
                                autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="position-relative">
                                <input id="password" type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="{{ __('Kata Sandi') }}" required
                                    autocomplete="current-password" style="padding-right: 45px;">
                                <button type="button" id="togglePassword" class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent" style="z-index: 10;">
                                    <i class="feather feather-eye text-muted" id="passwordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text fs-11 text-muted mt-2">
                                {{ __('Hint: Masukkan kata sandi Anda dengan benar.') }}
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember">
                                <label class="custom-control-label c-pointer"
                                    for="rememberMe">{{ __('Ingat saya') }}</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="fs-11 text-primary">{{ __('Lupa kata sandi?') }}</a>
                            @endif
                        </div>

                        <div class="mt-5">
                            <button type="submit"
                                class="btn btn-lg btn-premium w-100">{{ __('Masuk') }}</button>
                        </div>
                    </form>

                    <div class="mt-5">
                        <p class="mb-0 fs-11 text-muted">{{ __('Login aman dengan enkripsi') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const icon = document.querySelector('#passwordIcon');

            if (togglePassword) {
                togglePassword.addEventListener('click', function (e) {
                    // toggle the type attribute
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);

                    // toggle the eye icon
                    if (type === 'text') {
                        icon.classList.remove('feather-eye');
                        icon.classList.add('feather-eye-off');
                    } else {
                        icon.classList.remove('feather-eye-off');
                        icon.classList.add('feather-eye');
                    }
                });
            }
        });
    </script>
</body>

</html>
