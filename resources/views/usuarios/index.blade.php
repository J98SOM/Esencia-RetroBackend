<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - {{ config('app.name', 'Laravel') }}</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/usuarios-index.css', 'resources/js/app.js', 'resources/js/usuarios-index.js'])
    @endif
</head>
<body class="font-sans antialiased">
    <x-sidebar title="Gestión del Sistema">
        <x-slot name="navigation">
        <a href="/dashboard" class="block px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 font-semibold transition">
            <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"/>
            </svg>
            Panel de Control
        </a>
        <a href="/usuarios" class="block px-4 py-3 rounded-lg bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 font-semibold transition">
            <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0h6M9 20H3m6 0a9 9 0 1118 0m-9 0a4.5 4.5 0 100-9 4.5 4.5 0 000 9z"/>
            </svg>
            Gestión de Usuarios
        </a>
    </x-slot>

    <!-- Tabs Navigation -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-8">
                    <button class="tab-button active" onclick="switchTab('usuarios')">
                        👥 Gestión de Usuarios
                    </button>
                    <button class="tab-button" onclick="switchTab('roles')">
                        🔐 Gestión de Roles
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- USUARIOS TAB -->
            <div id="usuarios-tab" class="tab-content active">
                <!-- Action Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    <!-- Create User Card -->
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg cursor-pointer" onclick="openUserModal()">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Crear Usuario</h3>
                        <p class="text-sm text-gray-600">Agrega un nuevo usuario al sistema</p>
                    </div>

                    <!-- View Users Card -->
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg cursor-pointer" onclick="scrollToUsersTable()">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Ver Usuarios</h3>
                        <p class="text-sm text-gray-600">Lista de todos los usuarios</p>
                    </div>

                    <!-- Reload Users Card -->
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg cursor-pointer" onclick="loadUsers()">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Actualizar</h3>
                        <p class="text-sm text-gray-600">Recargar lista de usuarios</p>
                    </div>
                </div>

                <!-- Users Table -->
                <div id="users-section" class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500">
                        <h2 class="text-xl font-bold text-white">Lista de Usuarios</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-striped">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">#ID</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Email</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Rol</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Registro</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-900">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="users-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ROLES TAB -->
            <div id="roles-tab" class="tab-content">
                <!-- Action Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                    <!-- Create Role Card -->
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg cursor-pointer" onclick="openRoleModal()">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Crear Rol</h3>
                        <p class="text-sm text-gray-600">Agrega un nuevo rol al sistema</p>
                    </div>

                    <!-- View Roles Card -->
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg cursor-pointer" onclick="scrollToRolesTable()">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Ver Roles</h3>
                        <p class="text-sm text-gray-600">Lista de todos los roles</p>
                    </div>

                    <!-- Reload Roles Card -->
                    <div class="card-hover bg-white rounded-2xl p-6 shadow-lg cursor-pointer" onclick="loadRoles()">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Actualizar</h3>
                        <p class="text-sm text-gray-600">Recargar lista de roles</p>
                    </div>
                </div>

                <!-- Roles Table -->
                <div id="roles-section" class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-cyan-500">
                        <h2 class="text-xl font-bold text-white">Lista de Roles</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-striped">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">#ID</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Descripción</th>
                                    <th class="px-6 py-3 text-left font-semibold text-gray-900">Creado</th>
                                    <th class="px-6 py-3 text-center font-semibold text-gray-900">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="roles-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Modal -->
        <div id="user-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="user-modal-title" class="text-2xl font-bold text-gray-900">Crear Usuario</h2>
                    <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="user-form" class="space-y-4">
                    <input type="hidden" id="user-id">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
                        <input type="text" id="user-name-input" placeholder="Juan Pérez" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" id="user-email-input" placeholder="juan@example.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Contraseña</label>
                        <input type="password" id="user-password-input" placeholder="Mínimo 8 caracteres" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"/>
                        <p class="text-xs text-gray-500 mt-1">Déjalo vacío si no deseas cambiar la contraseña</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Contraseña</label>
                        <input type="password" id="user-password-confirm-input" placeholder="Confirma la contraseña" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rol</label>
                        <select id="user-role-input" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Selecciona un rol --</option>
                        </select>
                    </div>

                    <div id="user-form-error" class="hidden p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="btn-primary flex-1 text-white font-semibold py-2 rounded-lg">
                            Guardar
                        </button>
                        <button type="button" onclick="closeUserModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Modal -->
        <div id="role-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="role-modal-title" class="text-2xl font-bold text-gray-900">Crear Rol</h2>
                    <button onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="role-form" class="space-y-4">
                    <input type="hidden" id="role-id">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
                        <input type="text" id="role-name-input" placeholder="Moderador" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                        <textarea id="role-description-input" placeholder="Descripción del rol" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"></textarea>
                    </div>

                    <div id="role-form-error" class="hidden p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="btn-primary flex-1 text-white font-semibold py-2 rounded-lg">
                            Guardar
                        </button>
                        <button type="button" onclick="closeRoleModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-sidebar>
</body>
</html>
