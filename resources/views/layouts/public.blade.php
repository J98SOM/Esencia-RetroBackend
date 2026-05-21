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
    <title>@yield('title', config('app.name')) - {{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/js/login.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased">
    @yield('content')

    @stack('scripts')
</body>
</html>
