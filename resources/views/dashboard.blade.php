<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
    @endif

    <style>
        .card-hover {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <x-sidebar title="Panel de Control">
        <x-slot name="navigation">
            <a href="/dashboard" class="block px-4 py-3 rounded-lg bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 font-semibold transition">
                <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"/>
                </svg>
                Panel de Control
            </a>
            <a href="/usuarios" class="block px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 font-semibold transition">
                <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0h6M9 20H3m6 0a9 9 0 1118 0m-9 0a4.5 4.5 0 100-9 4.5 4.5 0 000 9z"/>
                </svg>
                Gestión de Usuarios
            </a>
        </x-slot>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Title -->
            <div class="mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-2">Panel de Control</h2>
                <p class="text-gray-600">Selecciona una opción para comenzar</p>
            </div>

            <!-- Dashboard Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Usuarios y Roles Card -->
                <a href="/usuarios" class="card-link">
                    <div class="card-hover bg-white rounded-2xl p-8 shadow-lg h-full">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full mb-6">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0h6M9 20H3m6 0a9 9 0 1118 0m-9 0a4.5 4.5 0 100-9 4.5 4.5 0 000 9z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Gestión de Usuarios y Roles</h3>
                        <p class="text-gray-600 mb-4">Administra usuarios del sistema y asigna roles. Controla permisos y accesos.</p>
                        <div class="flex items-center text-purple-600 font-semibold">
                            Ir a la gestión
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Settings Card -->
                <div class="card-link">
                    <div class="card-hover bg-white rounded-2xl p-8 shadow-lg h-full opacity-60">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full mb-6">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Configuración</h3>
                        <p class="text-gray-600 mb-4">Ajusta las configuraciones del sistema, preferencias y parámetros generales.</p>
                        <div class="flex items-center text-blue-600 font-semibold">
                            Próximamente
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Reports Card -->
                <div class="card-link">
                    <div class="card-hover bg-white rounded-2xl p-8 shadow-lg h-full opacity-60">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full mb-6">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Reportes</h3>
                        <p class="text-gray-600 mb-4">Visualiza estadísticas, análisis y reportes del sistema en tiempo real.</p>
                        <div class="flex items-center text-green-600 font-semibold">
                            Próximamente
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-sidebar>
</body>
</html>
