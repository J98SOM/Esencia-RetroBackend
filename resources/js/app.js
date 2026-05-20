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

function protectRoutes() {
    if (window.__AUTHENTICATED__) {
        return;
    }

    if (window.location.pathname === '/login') {
        return;
    }

    if (window.location.pathname === '/dashboard' || window.location.pathname === '/usuarios') {
        window.location.href = '/login';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', protectRoutes);
} else {
    protectRoutes();
}

