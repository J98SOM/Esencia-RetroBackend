<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - {{ config('app.name', 'Laravel') }}</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js', 'resources/js/login.js'])
    @endif
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-md">
            <div class="px-6 lg:px-8 py-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Esencia
                </h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 md:py-16">
            <div class="w-full max-w-md">
                <!-- Alert Messages -->
                <div id="error-alert" class="hidden mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <p id="error-message"></p>
                </div>

                <div id="success-alert" class="hidden mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>¡Bienvenido! Redirigiendo...</span>
                </div>

                <!-- Login Card -->
                <div class="bg-white rounded-2xl p-8 shadow-lg">
                    <!-- Card Header -->
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Bienvenido de vuelta</h2>
                        <p class="text-gray-600">Inicia sesión para acceder a tu cuenta</p>
                    </div>

                    <!-- Login Form -->
                    <form id="login-form" class="space-y-6">
                        <!-- Email Input -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <div class="relative">
                                <div class="absolute left-0 top-0 h-full flex items-center pl-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    required
                                    placeholder="tu@email.com"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                />
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña</label>
                            <div class="relative">
                                <div class="absolute left-0 top-0 h-full flex items-center pl-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    placeholder="Mínimo 8 caracteres"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                />
                            </div>
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                />
                                <span class="text-gray-700">Recuerda mi cuenta</span>
                            </label>
                            <a href="#" class="text-purple-600 hover:text-purple-700 font-semibold transition">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            id="submit-btn"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 rounded-lg transition duration-200 flex items-center justify-center gap-2 mt-8 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                        >
                            <span id="btn-text">Iniciar Sesión</span>
                            <span id="btn-spinner" class="hidden">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>


</body>
</html>

