<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/js/app.js'])

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
