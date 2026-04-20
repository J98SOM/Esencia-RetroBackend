<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ config('app.name', 'Laravel') }}
                </h1>
                <span class="text-sm text-gray-600 dark:text-gray-400">/ Dashboard</span>
            </div>
            <button
                id="logout-btn"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition"
            >
                Cerrar Sesión
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                ¡Bienvenido, <span id="user-name">Usuario</span>!
            </h2>
            <p class="text-gray-600 dark:text-gray-400">
                Tu sesión se ha iniciado correctamente. Puedes gestionar tu perfil y datos desde aquí.
            </p>
        </div>

        <!-- User Info Card -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Información del Perfil</h3>
                
                <div class="space-y-4">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Nombre</p>
                        <p id="profile-name" class="text-lg font-semibold text-gray-900 dark:text-white">-</p>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Correo Electrónico</p>
                        <p id="profile-email" class="text-lg font-semibold text-gray-900 dark:text-white break-all">-</p>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Rol</p>
                        <div id="profile-role" class="inline-block">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold text-white bg-blue-600">-</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Fecha de Registro</p>
                        <p id="profile-created" class="text-lg font-semibold text-gray-900 dark:text-white">-</p>
                    </div>
                </div>

                <button
                    id="refresh-btn"
                    class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    Actualizar Datos
                </button>
            </div>

            <!-- Token Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Token de Autenticación</h3>
                
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg mb-4">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Tu token (para API requests):</p>
                    <div class="bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-700">
                        <p id="token-display" class="text-xs text-gray-900 dark:text-white break-all font-mono">
                            Cargando...
                        </p>
                    </div>
                </div>

                <button
                    id="copy-token-btn"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition mb-4"
                >
                    Copiar Token
                </button>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="text-sm text-blue-700 dark:text-blue-400">
                        <span class="font-semibold">Consejo:</span> Usa este token en el header <code class="bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded text-xs">Authorization: Bearer {token}</code> para acceder a endpoints protegidos.
                    </p>
                </div>
            </div>
        </div>

        <!-- API Endpoints Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 mt-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Endpoints de API Disponibles</h3>
            
            <div class="space-y-3">
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm font-mono text-gray-900 dark:text-white mb-1">GET /api/me</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Obtener datos del usuario autenticado</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm font-mono text-gray-900 dark:text-white mb-1">GET /api/users</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Listar todos los usuarios</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm font-mono text-gray-900 dark:text-white mb-1">GET /api/users/{id}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Obtener un usuario específico</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm font-mono text-gray-900 dark:text-white mb-1">PUT /api/users/{id}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Actualizar un usuario</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm font-mono text-gray-900 dark:text-white mb-1">DELETE /api/users/{id}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Eliminar un usuario</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <p class="text-sm font-mono text-gray-900 dark:text-white mb-1">POST /api/logout</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Cerrar sesión y revocar token</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Obtener token del localStorage
         */
        function getAuthToken() {
            return localStorage.getItem('auth_token');
        }

        /**
         * Verificar autenticación
         */
        function checkAuth() {
            const token = getAuthToken();
            if (!token) {
                window.location.href = '/login';
                return false;
            }
            return true;
        }

        /**
         * Cargar datos del usuario desde localStorage
         */
        function loadUserFromStorage() {
            const userStr = localStorage.getItem('user');
            if (userStr) {
                const user = JSON.parse(userStr);
                displayUserInfo(user);
            }
        }

        /**
         * Mostrar información del usuario
         */
        function displayUserInfo(user) {
            document.getElementById('user-name').textContent = user.name;
            document.getElementById('profile-name').textContent = user.name;
            document.getElementById('profile-email').textContent = user.email;
            
            const roleSpan = document.getElementById('profile-role');
            const roleBg = user.role === 'admin' ? 'bg-purple-600' : 'bg-blue-600';
            roleSpan.innerHTML = `<span class="px-3 py-1 rounded-full text-sm font-semibold text-white ${roleBg}">${user.role}</span>`;
            
            // Formatear fecha
            const createdDate = new Date(user.created_at);
            const formattedDate = createdDate.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('profile-created').textContent = formattedDate;

            // Mostrar token
            const token = getAuthToken();
            document.getElementById('token-display').textContent = token || 'No disponible';
        }

        /**
         * Copiar token al portapapeles
         */
        document.getElementById('copy-token-btn').addEventListener('click', () => {
            const token = getAuthToken();
            if (token) {
                navigator.clipboard.writeText(token).then(() => {
                    alert('¡Token copiado al portapapeles!');
                }).catch(() => {
                    alert('Error al copiar el token');
                });
            }
        });

        /**
         * Actualizar datos del usuario
         */
        document.getElementById('refresh-btn').addEventListener('click', async () => {
            const token = getAuthToken();
            const btn = document.getElementById('refresh-btn');
            btn.disabled = true;
            btn.textContent = 'Actualizando...';

            try {
                const response = await fetch('/api/me', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Error al obtener datos');
                }

                const data = await response.json();
                localStorage.setItem('user', JSON.stringify(data.user));
                displayUserInfo(data.user);
                alert('Datos actualizados correctamente');
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar los datos');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Actualizar Datos';
            }
        });

        /**
         * Logout
         */
        document.getElementById('logout-btn').addEventListener('click', async () => {
            if (!confirm('¿Deseas cerrar sesión?')) {
                return;
            }

            const token = getAuthToken();
            const btn = document.getElementById('logout-btn');
            btn.disabled = true;

            try {
                const response = await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Error al cerrar sesión');
                }

                // Limpiar localStorage
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');

                // Redirigir a login
                window.location.href = '/login';
            } catch (error) {
                console.error('Error:', error);
                // Aún así limpiamos y redirigimos
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        });

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            if (checkAuth()) {
                loadUserFromStorage();
            }
        });
    </script>
</body>
</html>
