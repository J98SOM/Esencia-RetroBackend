<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.__AUTHENTICATED__ = @json(auth()->check());
        window.__AUTH_USER__ = @json(auth()->user()?->only(['id', 'name', 'email']));
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
