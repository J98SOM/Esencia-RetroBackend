<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - {{ config('app.name', 'Laravel') }}</title>
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #2d1b4e 0%, #1a0f3d 25%, #0f3a5e 75%, #1a1a3e 100%);
            min-height: 100vh;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: linear-gradient(135deg, rgba(139, 69, 200, 0.2) 0%, rgba(72, 180, 240, 0.15) 100%);
        }

        .input-underline {
            border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            background: transparent;
            color: white;
            transition: border-color 0.3s ease;
        }

        .input-underline:focus {
            outline: none;
            border-bottom-color: rgba(255, 255, 255, 0.8);
        }

        .input-underline::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-gradient {
            background: linear-gradient(90deg, #8b45c8 0%, #48b4f0 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover:not(:disabled) {
            box-shadow: 0 8px 32px rgba(139, 69, 200, 0.4);
            transform: translateY(-2px);
        }

        .btn-gradient:disabled {
            opacity: 0.7;
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #8b45c8 0%, #48b4f0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            margin: 0 auto 24px;
        }

        .checkbox-custom {
            accent-color: #48b4f0;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Alert Messages -->
        <div id="error-alert" class="hidden mb-6 p-4 bg-red-500/20 border border-red-400/50 text-red-200 rounded-lg text-sm backdrop-blur">
            <p id="error-message"></p>
        </div>

        <div id="success-alert" class="hidden mb-6 p-4 bg-green-500/20 border border-green-400/50 text-green-200 rounded-lg text-sm flex items-center gap-2 backdrop-blur">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>¡Bienvenido! Redirigiendo...</span>
        </div>

        <!-- Login Card -->
        <div class="login-card rounded-3xl p-12 shadow-2xl">
            <!-- Avatar -->
            <div class="avatar-circle">
                👤
            </div>

            <!-- Login Form -->
            <form id="login-form" class="space-y-8">
                <!-- Email Input -->
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-white/70" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            placeholder="Email ID"
                            class="input-underline flex-1 text-white placeholder-white/60 pb-3 text-base"
                        />
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-white/70" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Password"
                            class="input-underline flex-1 text-white placeholder-white/60 pb-3 text-base"
                        />
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            class="checkbox-custom w-4 h-4 rounded"
                        />
                        <span class="text-white/80">Remember me</span>
                    </label>
                    <a href="#" class="text-blue-300 hover:text-blue-200 transition">
                        Forgot Password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submit-btn"
                    class="btn-gradient w-full text-white font-bold py-3 rounded-xl transition duration-200 flex items-center justify-center gap-2 mt-8"
                >
                    <span id="btn-text">LOGIN</span>
                    <span id="btn-spinner" class="hidden">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>

            <!-- Test Credentials (Hidden but available) -->
            <div class="mt-8 pt-6 border-t border-white/10">
                <p class="text-xs text-white/50 text-center mb-4">Usuarios de prueba</p>
                <div class="text-xs text-white/40 text-center space-y-2">
                    <p><strong>Admin:</strong> admin@example.com</p>
                    <p><strong>Pass:</strong> password123</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('login-form');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');
        const errorAlert = document.getElementById('error-alert');
        const errorMessage = document.getElementById('error-message');
        const successAlert = document.getElementById('success-alert');

        function showError(message) {
            errorMessage.textContent = message;
            errorAlert.classList.remove('hidden');
            successAlert.classList.add('hidden');
        }

        function showSuccess() {
            successAlert.classList.remove('hidden');
            errorAlert.classList.add('hidden');
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

        document.addEventListener('DOMContentLoaded', () => {
            emailInput.value = 'admin@example.com';
            passwordInput.value = 'password123';
        });
    </script>
</body>
</html>

