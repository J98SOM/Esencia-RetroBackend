import '../css/app.css';
import '../css/sidebar.css';
import '../css/dashboard.css';
import '../css/login.css';
import '../css/usuarios-index.css';
import Swal from 'sweetalert2';
import './sidebar.js';
import './sidebar-init.js';
import './dashboard.js';
import './login.js';
import './usuarios-index.js';

// Expose Swal globally
window.Swal = Swal;

// Protect authenticated routes - run only once per page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', protectRoutes);
} else {
    protectRoutes();
}

function protectRoutes() {
    const token = localStorage.getItem('auth_token');
    const currentPath = window.location.pathname;
    const isLoginPage = currentPath === '/login';
    const isProtectedRoute = currentPath === '/dashboard' || currentPath === '/usuarios';

    // Only redirect if needed - prevent redirect loops
    if (isProtectedRoute && !token) {
        // On protected route without token, go to login
        window.location.href = '/login';
    } else if (isLoginPage && token) {
        // On login page with token, go to dashboard
        window.location.href = '/dashboard';
    }
    // Otherwise, page loads normally
}

