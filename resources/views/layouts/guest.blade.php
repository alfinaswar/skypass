<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts (Dengan Fallback Offline) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>
            body {
                font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 px-4">
            
            <!-- Logo & Header Text Wrapper -->
            <div class="text-center">
                <a href="/" class="inline-block">
                    <img src="{{ asset('images/Stockx.in logo.gif') }}" alt="Stockx.in Logo" class="w-72 h-auto mx-auto" />
                </a>
                
                <!-- Teks Petunjuk Login -->
                <p class="mt-3 text-sm font-bold text-gray-600 uppercase tracking-widest">
                    Enter your login credentials
                </p>
                <p class="mt-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    (Admin or Supervisor)
                </p>
            </div>

            <!-- Login Form Card Box -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-5 bg-white shadow-md overflow-hidden sm:rounded-lg border border-gray-100">
                {{ $slot }}
            </div>

            <!-- Catatan Bawah Box (Diberi margin-top agar tidak menempel) -->
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Forgot your password? <span class="font-semibold text-gray-700">Contact supervisor</span></span>
                </p>
            </div>

        </div>
    </body>
</html>