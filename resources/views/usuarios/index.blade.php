@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('navigation')
    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-white/70 hover:text-white hover:bg-surface-container font-semibold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"/>
        </svg>
        <span>Panel de Control</span>
    </a>
    <a href="{{ route('usuarios.index') }}" class="block px-4 py-3 rounded-lg bg-gradient-to-r from-primary to-primary-dark text-white shadow-[0_0_15px_rgba(234,188,78,0.4)] font-bold transition">
        <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0h6M9 20H3m6 0a9 9 0 1118 0m-9 0a4.5 4.5 0 100-9 4.5 4.5 0 000 9z"/>
        </svg>
        <span>Gestión de Usuarios</span>
    </a>
@endsection

@section('content')
    <!-- Tabs Navigation -->
    <div class="bg-surface-container border-b border-surface-container-high sticky top-0 z-20 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-8">
                <button class="tab-button active" onclick="switchTab('usuarios')">
                    Gestión de Usuarios
                </button>
                <button class="tab-button" onclick="switchTab('roles')">
                    Gestión de Roles
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
                    <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="openUserModal()">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-primary-container/50 to-surface-container-high rounded-full mb-4 border border-inverse-primary/50">
                            <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Crear Usuario</h3>
                        <p class="text-sm text-white/70">Agrega un nuevo usuario al sistema</p>
                    </div>

                    <!-- View Users Card -->
                    <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="scrollToUsersTable()">
                        <div class="flex items-center justify-center w-12 h-12 bg-surface-container rounded-full mb-4 border border-surface-container-high">
                            <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Ver Usuarios</h3>
                        <p class="text-sm text-white/70">Lista de todos los usuarios</p>
                    </div>

                    <!-- Reload Users Card -->
                    <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="loadUsers()">
                        <div class="flex items-center justify-center w-12 h-12 bg-primary-container/40 rounded-full mb-4 border border-primary-container/50">
                            <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Actualizar</h3>
                        <p class="text-sm text-white/70">Recargar lista de usuarios</p>
                    </div>
                </div>

                <!-- Users Table -->
                <div id="users-section" class="bg-surface-container-low rounded-2xl shadow-lg overflow-hidden border border-surface-container">
                    <div class="px-6 py-4 bg-gradient-to-r from-surface-container to-surface-container-lowest border-b border-surface-container">
                        <h2 class="text-xl font-bold text-white">Lista de Usuarios</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-striped text-white">
                            <thead class="bg-surface-container border-b border-surface-container-high">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-white">#ID</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Email</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Rol</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Registro</th>
                                    <th class="px-6 py-3 text-center font-semibold text-white">Acciones</th>
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
                    <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="openRoleModal()">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-primary-container/50 to-surface-container-high rounded-full mb-4 border border-inverse-primary/50">
                            <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Crear Rol</h3>
                        <p class="text-sm text-white/70">Agrega un nuevo rol al sistema</p>
                    </div>

                    <!-- View Roles Card -->
                    <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="scrollToRolesTable()">
                        <div class="flex items-center justify-center w-12 h-12 bg-surface-container rounded-full mb-4 border border-surface-container-high">
                            <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Ver Roles</h3>
                        <p class="text-sm text-white/70">Lista de todos los roles</p>
                    </div>

                    <!-- Reload Roles Card -->
                    <div class="card-hover bg-surface-container-low rounded-2xl p-6 shadow-lg cursor-pointer border border-surface-container hover:border-primary-container/50 transition-all duration-300 group" onclick="loadRoles()">
                        <div class="flex items-center justify-center w-12 h-12 bg-primary-container/40 rounded-full mb-4 border border-primary-container/50">
                            <svg class="w-6 h-6 text-primary group-hover:text-primary-dark transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Actualizar</h3>
                        <p class="text-sm text-white/70">Recargar lista de roles</p>
                    </div>
                </div>

                <!-- Roles Table -->
                <div id="roles-section" class="bg-surface-container-low rounded-2xl shadow-lg overflow-hidden border border-surface-container">
                    <div class="px-6 py-4 bg-gradient-to-r from-surface-container to-surface-container-lowest border-b border-surface-container">
                        <h2 class="text-xl font-bold text-white">Lista de Roles</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-striped text-white">
                            <thead class="bg-surface-container border-b border-surface-container-high">
                                <tr>
                                    <th class="px-6 py-3 text-left font-semibold text-white">#ID</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Nombre</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Descripción</th>
                                    <th class="px-6 py-3 text-left font-semibold text-white">Registro</th>
                                    <th class="px-6 py-3 text-center font-semibold text-white">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="roles-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Modal -->
        <div id="user-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/50">
            <div class="bg-surface-container-low rounded-2xl shadow-2xl max-w-md w-full p-8 border border-surface-container">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="user-modal-title" class="text-2xl font-bold text-white">Crear Usuario</h2>
                    <button onclick="closeUserModal()" class="text-white/60 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="user-form" class="space-y-4">
                    <input type="hidden" id="user-id">

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Nombre</label>
                        <input type="text" id="user-name-input" placeholder="Juan Pérez" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Email</label>
                        <input type="email" id="user-email-input" placeholder="juan@example.com" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Contraseña</label>
                        <input type="password" id="user-password-input" placeholder="Mínimo 8 caracteres" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"/>
                        <p class="text-xs text-white/70 mt-1">Déjalo vacío si no deseas cambiar la contraseña</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Confirmar Contraseña</label>
                        <input type="password" id="user-password-confirm-input" placeholder="Confirma la contraseña" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Rol</label>
                        <select id="user-role-input" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">-- Selecciona un rol --</option>
                        </select>
                    </div>

                    <div id="user-form-error" class="hidden p-4 bg-error-container border border-error text-on-error-container rounded-lg text-sm"></div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 text-white font-semibold py-2 rounded-lg bg-amber-600 hover:bg-amber-700 transition">
                            Guardar
                        </button>
                        <button type="button" onclick="closeUserModal()" class="flex-1 px-4 py-2 border border-surface-container-high text-white/70 font-semibold rounded-lg hover:bg-surface-container-highest hover:text-white">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Role Modal -->
        <div id="role-modal" class="modal-overlay hidden fixed inset-0 flex items-center justify-center p-4 z-50 bg-black/50">
            <div class="bg-surface-container-low rounded-2xl shadow-2xl max-w-md w-full p-8 border border-surface-container">
                <div class="flex items-center justify-between mb-6">
                    <h2 id="role-modal-title" class="text-2xl font-bold text-white">Crear Rol</h2>
                    <button onclick="closeRoleModal()" class="text-white/60 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="role-form" class="space-y-4">
                    <input type="hidden" id="role-id">

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Nombre</label>
                        <input type="text" id="role-name-input" placeholder="Moderador" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required/>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">Descripción</label>
                        <textarea id="role-description-input" placeholder="Descripción del rol" class="w-full px-4 py-2 bg-surface-container border border-surface-container-high text-white placeholder-white/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4"></textarea>
                    </div>

                    <div id="role-form-error" class="hidden p-4 bg-error-container border border-error text-on-error-container rounded-lg text-sm"></div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 text-white font-semibold py-2 rounded-lg bg-amber-600 hover:bg-amber-700 transition">
                            Guardar
                        </button>
                        <button type="button" onclick="closeRoleModal()" class="flex-1 px-4 py-2 border border-surface-container-high text-white/70 font-semibold rounded-lg hover:bg-surface-container-highest hover:text-white">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
