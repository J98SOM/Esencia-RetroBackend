// Import SweetAlert2
import Swal from 'sweetalert2';
import '../css/sweetalert.css';

// Hacer que las funciones sean globales
(function() {
    let rolesMap = {};
    const token = localStorage.getItem('auth_token');

    // Tab switching
    window.switchTab = function(tabName) {
        const tabs = document.querySelectorAll('.tab-content');
        const buttons = document.querySelectorAll('.tab-button');
        
        tabs.forEach(tab => tab.classList.remove('active'));
        buttons.forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(`${tabName}-tab`).classList.add('active');
        event.target.classList.add('active');
        
        if (tabName === 'usuarios') {
            loadUsers();
        } else {
            loadRoles();
        }
    };

    // Users Management
    window.loadUsers = async function() {
        try {
            // Load roles first to populate rolesMap
            const rolesResponse = await fetch('/api/roles', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!rolesResponse.ok) throw new Error('Failed to fetch roles');
            const rolesData = await rolesResponse.json();
            rolesMap = {};
            const roles = rolesData.roles || rolesData.data || [];
            roles.forEach(role => {
                rolesMap[role.id] = role.name;
            });

            // Load users
            const usersResponse = await fetch('/api/users', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!usersResponse.ok) throw new Error('Failed to fetch users');
            const usersData = await usersResponse.json();
            const users = usersData.users || usersData.data || [];
            displayUsers(users);
        } catch (error) {
            console.error('Error loading users:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar usuarios',
                confirmButtonText: 'Aceptar'
            });
        }
    };

    function displayUsers(users) {
        const tbody = document.getElementById('users-tbody');
        tbody.innerHTML = '';

        users.forEach(user => {
            const roleId = user.role_id;
            const roleName = roleId && rolesMap[roleId] ? rolesMap[roleId] : 'Usuario';
            const roleBadgeClass = roleName.toLowerCase() === 'admin' ? 'badge-admin' : 
                                  roleName.toLowerCase() === 'moderator' ? 'badge-moderator' : 'badge-user';
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 text-white">${user.id}</td>
                <td class="px-6 py-4 text-white font-medium">${user.name}</td>
                <td class="px-6 py-4 text-white">${user.email}</td>
                <td class="px-6 py-4">
                    <span class="inline-block px-3 py-1 rounded-full text-white text-xs font-semibold ${roleBadgeClass}">
                        ${roleName}
                    </span>
                </td>
                <td class="px-6 py-4 text-white text-sm">${new Date(user.created_at).toLocaleDateString('es-ES')}</td>
                <td class="px-6 py-4 text-center space-x-2">
                    <button onclick="editUser(${user.id})" class="px-3 py-1 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded hover:from-amber-700 hover:to-amber-800 text-xs font-semibold">
                        Editar
                    </button>
                    <button onclick="deleteUser(${user.id})" class="px-3 py-1 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded hover:from-orange-700 hover:to-orange-800 text-xs font-semibold">
                        Eliminar
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    window.openUserModal = function(mode = 'create') {
        // Only reset if creating new user
        if (mode === 'create') {
            document.getElementById('user-id').value = '';
            document.getElementById('user-form').reset();
            document.getElementById('user-modal-title').textContent = 'Crear Usuario';
        }
        document.getElementById('user-form-error').classList.add('hidden');
        document.getElementById('user-modal').classList.remove('hidden');
        
        // Load roles if creating
        if (mode === 'create') {
            loadRolesForSelect();
        }
    };

    window.closeUserModal = function() {
        document.getElementById('user-modal').classList.add('hidden');
    };

    async function loadRolesForSelect() {
        try {
            const response = await fetch('/api/roles', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch roles');
            const data = await response.json();
            const select = document.getElementById('user-role-input');
            select.innerHTML = '<option value="">-- Selecciona un rol --</option>';
            const roles = data.roles || data.data || [];
            roles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id;
                option.textContent = role.name;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading roles for select:', error);
        }
    }

    window.editUser = async function(userId) {
        try {
            // Load roles first for the select
            await loadRolesForSelect();

            const response = await fetch(`/api/users/${userId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch user');
            const data = await response.json();
            const user = data.user || data.data;

            // Populate fields
            document.getElementById('user-id').value = user.id;
            document.getElementById('user-name-input').value = user.name;
            document.getElementById('user-email-input').value = user.email;
            document.getElementById('user-password-input').value = '';
            document.getElementById('user-password-confirm-input').value = '';
            
            // Set role after roles are loaded
            setTimeout(() => {
                document.getElementById('user-role-input').value = user.role_id || '';
            }, 100);
            
            document.getElementById('user-modal-title').textContent = 'Editar Usuario';
            document.getElementById('user-form-error').classList.add('hidden');

            openUserModal('edit');
        } catch (error) {
            console.error('Error loading user:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar usuario: ' + error.message,
                confirmButtonText: 'Aceptar'
            });
        }
    };

    window.deleteUser = async function(userId) {
        Swal.fire({
            title: '¿Eliminar Usuario?',
            text: '¿Estás seguro de que deseas eliminar este usuario?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: false
        }).then(async (result) => {
            if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/api/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to delete user');
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: 'Usuario eliminado correctamente',
                showConfirmButton: false,
                timer: 1500
            });
            loadUsers();
        } catch (error) {
            console.error('Error deleting user:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al eliminar usuario',
                confirmButtonText: 'Aceptar'
            });
        }
        });
    };

    // Roles Management
    window.loadRoles = async function() {
        try {
            const response = await fetch('/api/roles', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch roles');
            const data = await response.json();
            const roles = data.roles || data.data || [];
            displayRoles(roles);
        } catch (error) {
            console.error('Error loading roles:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar roles',
                confirmButtonText: 'Aceptar'
            });
        }
    };

    function displayRoles(roles) {
        const tbody = document.getElementById('roles-tbody');
        tbody.innerHTML = '';

        roles.forEach(role => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 text-white">${role.id}</td>
                <td class="px-6 py-4 text-white font-medium">${role.name}</td>
                <td class="px-6 py-4 text-white">${role.description || '-'}</td>
                <td class="px-6 py-4 text-white text-sm">${new Date(role.created_at).toLocaleDateString('es-ES')}</td>
                <td class="px-6 py-4 text-center space-x-2">
                    <button onclick="editRole(${role.id})" class="px-3 py-1 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded hover:from-amber-700 hover:to-amber-800 text-xs font-semibold">
                        Editar
                    </button>
                    <button onclick="deleteRole(${role.id})" class="px-3 py-1 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded hover:from-orange-700 hover:to-orange-800 text-xs font-semibold">
                        Eliminar
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    window.openRoleModal = function(mode = 'create') {
        // Only reset if creating new role
        if (mode === 'create') {
            document.getElementById('role-id').value = '';
            document.getElementById('role-form').reset();
            document.getElementById('role-modal-title').textContent = 'Crear Rol';
        }
        document.getElementById('role-form-error').classList.add('hidden');
        document.getElementById('role-modal').classList.remove('hidden');
    };

    window.closeRoleModal = function() {
        document.getElementById('role-modal').classList.add('hidden');
    };

    window.editRole = async function(roleId) {
        try {
            const response = await fetch(`/api/roles/${roleId}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch role');
            const data = await response.json();
            const role = data.role || data.data;

            // Populate fields
            document.getElementById('role-id').value = role.id;
            document.getElementById('role-name-input').value = role.name;
            document.getElementById('role-description-input').value = role.description || '';
            document.getElementById('role-modal-title').textContent = 'Editar Rol';
            document.getElementById('role-form-error').classList.add('hidden');

            openRoleModal('edit');
        } catch (error) {
            console.error('Error loading role:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar rol: ' + error.message,
                confirmButtonText: 'Aceptar'
            });
        }
    };

    window.deleteRole = async function(roleId) {
        Swal.fire({
            title: '¿Eliminar Rol?',
            text: '¿Estás seguro de que deseas eliminar este rol?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: false
        }).then(async (result) => {
            if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/api/roles/${roleId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to delete role');
            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: 'Rol eliminado correctamente',
                showConfirmButton: false,
                timer: 1500
            });
            loadRoles();
        } catch (error) {
            console.error('Error deleting role:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al eliminar rol',
                confirmButtonText: 'Aceptar'
            });
        }
        });
    };

    // Scroll utilities
    window.scrollToUsersTable = function() {
        document.getElementById('users-section').scrollIntoView({ behavior: 'smooth' });
    };

    window.scrollToRolesTable = function() {
        document.getElementById('roles-section').scrollIntoView({ behavior: 'smooth' });
    };

    // Form submission handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Check authentication
        if (!token) {
            window.location.href = '/login';
            return;
        }

        // User form submission
        const userForm = document.getElementById('user-form');
        if (userForm) {
            userForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const userId = document.getElementById('user-id').value;
                const name = document.getElementById('user-name-input').value;
                const email = document.getElementById('user-email-input').value;
                const password = document.getElementById('user-password-input').value;
                const passwordConfirm = document.getElementById('user-password-confirm-input').value;
                const roleId = document.getElementById('user-role-input').value;

                // Validation
                if (!name || !email || !roleId) {
                    showUserError('Por favor completa todos los campos requeridos');
                    return;
                }

                if (password && password !== passwordConfirm) {
                    showUserError('Las contraseñas no coinciden');
                    return;
                }

                if (password && password.length < 8) {
                    showUserError('La contraseña debe tener al menos 8 caracteres');
                    return;
                }

                try {
                    const method = userId ? 'PUT' : 'POST';
                    const url = userId ? `/api/users/${userId}` : '/api/users';
                    const body = {
                        name,
                        email,
                        role_id: roleId
                    };

                    if (password) {
                        body.password = password;
                    }

                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(body)
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error al guardar usuario');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: userId ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    closeUserModal();
                    loadUsers();
                } catch (error) {
                    console.error('Error saving user:', error);
                    showUserError(error.message);
                }
            });
        }

        // Role form submission
        const roleForm = document.getElementById('role-form');
        if (roleForm) {
            roleForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const roleId = document.getElementById('role-id').value;
                const name = document.getElementById('role-name-input').value;
                const description = document.getElementById('role-description-input').value;

                // Validation
                if (!name) {
                    showRoleError('Por favor ingresa el nombre del rol');
                    return;
                }

                try {
                    const method = roleId ? 'PUT' : 'POST';
                    const url = roleId ? `/api/roles/${roleId}` : '/api/roles';

                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name,
                            description
                        })
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Error al guardar rol');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: roleId ? 'Rol actualizado correctamente' : 'Rol creado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    closeRoleModal();
                    loadRoles();
                } catch (error) {
                    console.error('Error saving role:', error);
                    showRoleError(error.message);
                }
            });
        }

        // Load initial data only if we're on the usuarios page
        const usuariosTab = document.getElementById('usuarios-tab');
        if (usuariosTab) {
            loadUsers();
        }
    });

    function showUserError(message) {
        const errorDiv = document.getElementById('user-form-error');
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function showRoleError(message) {
        const errorDiv = document.getElementById('role-form-error');
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }
})();
