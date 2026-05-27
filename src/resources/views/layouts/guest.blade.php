<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #020617;
                color: #e2e8f0;
            }

            .bg-white {
                background-color: #0f172a !important;
            }

            .bg-slate-100,
            .bg-slate-50,
            .bg-slate-50\/80 {
                background-color: #020617 !important;
            }

            .text-slate-900,
            .text-slate-700 {
                color: #e2e8f0 !important;
            }

            .border-slate-200,
            .border-slate-200\/80 {
                border-color: rgba(148, 163, 184, 0.35) !important;
            }
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-900 bg-slate-100">
    <div class="min-h-screen px-4 py-6 lg:px-8 lg:py-10">
        <div class="mx-auto overflow-hidden border shadow-xl max-w-7xl rounded-3xl border-slate-200/80 bg-white">
            <div class="grid min-h-[calc(100vh-5rem)] grid-cols-1 lg:grid-cols-12">
                <aside
                    class="relative hidden overflow-hidden lg:col-span-7 lg:flex lg:items-center lg:justify-center bg-gradient-to-br from-cyan-600 to-blue-700">
                    <img src="{{ asset('assets/images/auth/auth-cover-login-bg.svg') }}" alt="Auth cover"
                        class="w-[78%] max-w-2xl drop-shadow-2xl">
                    <div class="absolute left-8 top-8 right-8 text-white">
                        <h2 class="text-3xl font-bold tracking-tight">{{ config('app.name', 'ProductSchool') }}</h2>
                        <p class="mt-2 text-sm text-cyan-100">{{ __('Kelola sekolah dengan mudah') }}</p>
                    </div>
                </aside>

                <main class="lg:col-span-5 flex items-center justify-center bg-slate-50/80">
                    <div class="w-full max-w-md px-6 py-10 sm:px-8">
                        <a href="/"
                            class="mb-8 inline-flex items-center gap-3 text-slate-700 hover:text-slate-900">
                            <x-application-logo class="w-10 h-10 fill-current text-cyan-600" />
                            <span
                                class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const requiredFields = document.querySelectorAll(
                'input[required], select[required], textarea[required], input[aria-required="true"], select[aria-required="true"], textarea[aria-required="true"]'
            );

            requiredFields.forEach((field) => {
                const type = (field.getAttribute('type') || '').toLowerCase();
                if (['hidden', 'submit', 'button', 'reset'].includes(type)) {
                    return;
                }

                const fieldId = field.getAttribute('id');
                if (!fieldId) {
                    return;
                }

                const label = document.querySelector(`label[for="${fieldId}"]`);
                if (!label) {
                    return;
                }

                const hasMarker = label.querySelector('.required-marker') || /\*/.test(label.textContent ||
                    '');
                if (hasMarker) {
                    return;
                }

                const marker = document.createElement('span');
                marker.className = 'text-red-500 required-marker ml-1';
                marker.textContent = '*';
                label.appendChild(marker);
            });
        });
    </script>
</body>

</html>
