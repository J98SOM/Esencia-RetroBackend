// Login specific functionality
function initializeLogin() {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) return; // Exit if not on login page
    
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');
    const errorAlert = document.getElementById('error-alert');
    const errorMessage = document.getElementById('error-message');
    const successAlert = document.getElementById('success-alert');

    function showError(message) {
        if (!errorAlert) return;
        errorMessage.textContent = message;
        errorAlert.classList.remove('hidden');
        if (successAlert) successAlert.classList.add('hidden');
    }

    function showSuccess() {
        if (!successAlert) return;
        successAlert.classList.remove('hidden');
        if (errorAlert) errorAlert.classList.add('hidden');
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
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    password: password,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error en la autenticación');
            }

            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));

            showSuccess();

            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1200);

        } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'Error al iniciar sesión');
        } finally {
            setButtonLoading(false);
        }
    });
}

// Initialize immediately and on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLogin);
} else {
    initializeLogin();
}

// Expose globally for debugging
window.initializeLogin = initializeLogin;

