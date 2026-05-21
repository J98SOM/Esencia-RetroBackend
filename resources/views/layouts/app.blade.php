<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $apiToken = session('api_token');

        if (auth()->check() && ! $apiToken) {
            $apiToken = auth()->user()->createToken('web-session-token')->plainTextToken;
            session(['api_token' => $apiToken]);
        }
    @endphp
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    <script>
        window.__AUTHENTICATED__ = @json(auth()->check());
        window.__AUTH_USER__ = @json(auth()->user()?->only(['id', 'name', 'email']));
        window.__AUTH_TOKEN__ = @json($apiToken);
        window.getApiAuthToken = function () {
            return localStorage.getItem('api_token') || window.__AUTH_TOKEN__ || '';
        };
        window.setApiAuthToken = function (token) {
            if (!token) {
                window.clearApiAuthToken();
                return;
            }

            localStorage.setItem('api_token', token);
            window.__AUTH_TOKEN__ = token;
        };
        window.clearApiAuthToken = function () {
            localStorage.removeItem('api_token');
            window.__AUTH_TOKEN__ = '';
        };
        window.getApiHeaders = function (headers = {}) {
            const token = window.getApiAuthToken ? window.getApiAuthToken() : '';

            return {
                Accept: 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...headers,
            };
        };
    </script>
    
    @vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/js/app.js'])
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Material Symbols (Google) -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    @stack('styles')
</head>
<body class="font-sans antialiased">
    <x-sidebar :title="$pageTitle ?? 'Dashboard'">
        <x-slot name="navigation">
            @yield('navigation')
        </x-slot>

        @yield('content')
    </x-sidebar>

    @stack('scripts')
</body>
</html>
