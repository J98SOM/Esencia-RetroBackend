<!-- Sidebar Component -->
<div class="flex h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-screen w-64 bg-white shadow-xl transform transition-all duration-300 ease-in-out z-40 -translate-x-full lg:translate-x-0 lg:relative lg:w-64 sidebar-expanded">
        <div class="flex flex-col h-full">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent sidebar-title transition-all duration-300">
                    Esencia
                </h1>
                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>



            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                {{ $navigation ?? '' }}
            </nav>

            <!-- User Profile Section -->
            <div class="p-4 border-t border-gray-200 space-y-4">
                <!-- User Info -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 user-info transition-all duration-300">
                    <p class="text-xs text-gray-500 mb-2 user-label">Usuario autenticado</p>
                    <p id="sidebar-user-name" class="text-sm font-semibold text-gray-900 truncate user-name">Usuario</p>
                </div>

                <!-- Logout Button -->
                <button
                    onclick="logout()"
                    class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 logout-btn"
                    title="Cerrar Sesión"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="logout-text">Cerrar Sesión</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/50 lg:hidden z-30" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white shadow-md sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <!-- Mobile Menu Button -->
                <button 
                    onclick="toggleSidebar()" 
                    class="lg:hidden text-gray-600 hover:text-gray-900 transition"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title -->
                <h2 class="text-2xl font-bold text-gray-900">{{ $title ?? 'Dashboard' }}</h2>

                <div></div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-auto bg-gradient-to-br from-blue-50 to-indigo-50">
            {{ $slot }}
        </main>
    </div>
</div>

<style>
    #sidebar {
        transition: all 0.3s ease-in-out;
        overflow: hidden;
    }

    /* Navigation items styling */
    #sidebar nav {
        overflow-x: hidden;
        overflow-y: auto;
    }

    #sidebar nav a {
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 0.75rem;
    }
</style>

<script>
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    if (!token) {
        window.location.href = '/login';
    }

    // Set user name in sidebar
    document.getElementById('sidebar-user-name').textContent = user.name || 'Usuario';

    /**
     * Toggle sidebar on mobile
     */
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    /**
     * Logout
     */
    async function logout() {
        if (!confirm('¿Deseas cerrar sesión?')) return;

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
</script>
