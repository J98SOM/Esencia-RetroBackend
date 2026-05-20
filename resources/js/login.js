// Import styles
import '../css/app.css';
import '../css/login.css';
import '../css/sweetalert.css';

// Import SweetAlert2
import Swal from 'sweetalert2';

// Expose Swal globally
window.Swal = Swal;

// Login specific functionality - only runs if form exists
function initializeLogin() {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) return; // Exit if not on login page

    if (window.__AUTHENTICATED__) {
        window.location.href = '/dashboard';
        return;
    }
    
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    const togglePasswordBtn = document.getElementById('toggle-password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');

    // Toggle password visibility
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isPassword = passwordInput.type === 'password';
            
            if (isPassword) {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error de Autenticación',
            text: message,
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false,
        });
    }

    function showSuccess() {
        Swal.fire({
            icon: 'success',
            title: '¡Bienvenido!',
            text: 'Redirigiendo al panel de control...',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: async () => {
                await Swal.showLoading();
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1500);
            }
        });
    }

    function setButtonLoading(isLoading) {
        submitBtn.disabled = isLoading;
        if (isLoading) {
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        } else {
            btnText.classList.remove('hidden');
            btnSpinner.classList.add('hidden');
        }
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            showError('Por favor completa todos los campos');
            return;
        }

        setButtonLoading(true);

        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    email: email,
                    password: password,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error en la autenticación');
            }

            showSuccess();

        } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'Error al iniciar sesión');
        } finally {
            setButtonLoading(false);
        }
    });
}

// Initialize on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLogin);
} else {
    initializeLogin();
}

// Expose globally for debugging
window.initializeLogin = initializeLogin;

