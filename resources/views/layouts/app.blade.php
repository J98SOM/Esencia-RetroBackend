<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    <script>
        window.__AUTHENTICATED__ = @json(auth()->check());
        window.__AUTH_USER__ = @json(auth()->user()?->only(['id', 'name', 'email']));
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
