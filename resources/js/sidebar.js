// Sidebar Management

const user = window.__AUTH_USER__ || {};

// Set user name in sidebar
document.addEventListener('DOMContentLoaded', function() {
    const userNameElement = document.getElementById('sidebar-user-name');
    if (userNameElement) {
        userNameElement.textContent = user.name || 'Usuario';
    }
    
    // Restore sidebar state on page load
    restoreSidebarState();
});

/**
 * Restore sidebar state on page load
 */
function restoreSidebarState() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;
    
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
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
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
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'same-origin',
        });
    } catch (error) {
        console.error('Error:', error);
    } finally {
        window.location.href = '/login';
    }
}
