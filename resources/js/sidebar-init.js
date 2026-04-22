/**
 * Sidebar Management - Initialize on DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    // Only set user name, don't redirect
    const userNameElement = document.getElementById('sidebar-user-name');
    if (userNameElement && user.name) {
        userNameElement.textContent = user.name;
    }
    
    // Restore sidebar state on page load
    restoreSidebarState();
});

// Handle window resize
window.addEventListener('resize', function() {
    restoreSidebarState();
});

/**
 * Restore sidebar state on page load
 */
function restoreSidebarState() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    
    // En pantallas pequeñas (mobile), siempre expandido
    const isMobile = window.innerWidth < 1024; // lg breakpoint en Tailwind
    
    if (isMobile) {
        sidebar.classList.remove('sidebar-minimized');
        sidebar.classList.add('sidebar-expanded');
        localStorage.setItem('sidebar_minimized', 'false');
        return;
    }
    
    // En desktop, restaurar estado guardado
    const isMinimized = localStorage.getItem('sidebar_minimized') === 'true';
    
    if (isMinimized) {
        sidebar.classList.remove('sidebar-expanded');
        sidebar.classList.add('sidebar-minimized');
    } else {
        sidebar.classList.add('sidebar-expanded');
        sidebar.classList.remove('sidebar-minimized');
    }
}

/**
 * Minimize/Expand sidebar on desktop
 */
function minimizeSidebar() {
    // No permitir minimizar en mobile
    const isMobile = window.innerWidth < 1024;
    if (isMobile) return;
    
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    
    const isMinimized = sidebar.classList.contains('sidebar-minimized');
    
    if (isMinimized) {
        sidebar.classList.remove('sidebar-minimized');
        sidebar.classList.add('sidebar-expanded');
        localStorage.setItem('sidebar_minimized', 'false');
    } else {
        sidebar.classList.add('sidebar-minimized');
        sidebar.classList.remove('sidebar-expanded');
        localStorage.setItem('sidebar_minimized', 'true');
    }
}

/**
 * Toggle sidebar on mobile
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
}

/**
 * Logout
 */
async function logout() {
    const Swal = window.Swal;
    if (!Swal) {
        if (confirm('¿Deseas cerrar sesión?')) {
            await performLogout();
        }
        return;
    }

    const result = await Swal.fire({
        title: '¿Cerrar Sesión?',
        text: '¿Deseas cerrar tu sesión actual?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#b45309',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    await performLogout();
}

/**
 * Perform logout action
 */
async function performLogout() {
    const token = localStorage.getItem('auth_token');
    try {
        await fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
        });
    } catch (error) {
        console.error('Error:', error);
    } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
    }
}

// Expose functions globally for inline onclick handlers
window.toggleSidebar = toggleSidebar;
window.minimizeSidebar = minimizeSidebar;
window.logout = logout;
