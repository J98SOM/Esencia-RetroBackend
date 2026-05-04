<!-- Sidebar Component -->
<div class="flex h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-screen w-64 bg-surface-container-low border-r border-surface-container shadow-2xl transform transition-all duration-300 ease-in-out z-40 -translate-x-full lg:translate-x-0 lg:relative lg:w-64 sidebar-expanded">
        <div class="flex flex-col h-full bg-gradient-to-b from-surface-container-low to-background">
            <!-- Sidebar Header -->
            <div class="border-b border-surface-container flex items-center justify-center h-20 bg-surface-container/50 shadow-[0_5px_15px_rgba(234,188,78,0.05)]">
    
    <!-- Logo grande -->
    <img src="{{ asset('img/logo_max.png') }}" class="sidebar-title h-28 w-40 object-contain transition-all duration-300 pointer-events-none">

    <!-- Logo pequeño -->
    <img src="{{ asset('img/logo_mi.png') }}" class="sidebar-logo h-28 w-28 object-contain rounded-lg transition-all duration-300">

    <!-- Botón mobile -->
    <button onclick="toggleSidebar()"
        class="lg:hidden text-on-surface-variant hover:text-on-surface flex-shrink-0 w-6 h-6 flex items-center justify-center absolute right-2 top-2">
        
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

            <!-- Expand/Minimize button (same place for both states) -->
            <div class="px-4 py-2 border-b border-surface-container">
                <button onclick="minimizeSidebar()" class="hidden lg:flex text-white/60 hover:text-white flex-shrink-0 w-full items-center justify-center sidebar-toggle-btn transition-all duration-300 rounded-lg hover:bg-surface-container-highest hover:shadow-[inset_0_0_10px_rgba(234,188,78,0.1)] py-2" title="Minimizar/Expandir">
                    <svg class="w-6 h-6 minimize-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>

                    <svg class="w-6 h-6 expand-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>



            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                {{ $navigation ?? '' }}

                @php $mesasActive = request()->routeIs('mesas.*'); @endphp
                <a href="{{ route('mesas.index') }}" class="block px-4 py-3 rounded-lg {{ $mesasActive ? 'bg-gradient-to-r from-primary to-primary-dark text-white shadow-[0_0_15px_rgba(234,188,78,0.4)] font-bold transition' : 'text-white/70 hover:text-white hover:bg-surface-container font-semibold transition' }}">
                    <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V8zm5 4h4"/>
                    </svg>
                    <span>Gestión de Mesas</span>
                </a>
                @php $productosActive = request()->routeIs('productos.*'); @endphp
                <a href="{{ route('productos.index') }}" class="block mt-2 px-4 py-3 rounded-lg {{ $productosActive ? 'bg-gradient-to-r from-primary to-primary-dark text-white shadow-[0_0_15px_rgba(234,188,78,0.4)] font-bold transition' : 'text-white/70 hover:text-white hover:bg-surface-container font-semibold transition' }}">
                    <svg class="w-5 h-5 inline-block mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                    <span>Gestión de Productos</span>
                </a>
            </nav>

            <!-- User Profile Section -->
            <div class="p-4 border-t border-surface-container space-y-4 bg-surface-container/30">
                <!-- User Info -->
                <div class="bg-gradient-to-r from-primary-container/40 to-surface-container rounded-lg p-4 user-info transition-all duration-300 border border-primary-container/50 shadow-[inset_0_0_15px_rgba(234,188,78,0.05)]">
                    <p class="text-xs text-white/70 mb-2 user-label uppercase tracking-widest">Autenticado</p>
                    <p id="sidebar-user-name" class="text-sm font-semibold text-white truncate user-name tracking-wide">Usuario</p>
                </div>

                <!-- Logout Button -->
                <button
                    onclick="logout()"
                    class="w-full bg-gradient-to-r from-surface-container to-surface-container-low border border-surface-container hover:border-primary-container/80 hover:text-primary text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 logout-btn hover:shadow-[0_0_15px_rgba(234,188,78,0.15)] group"
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
        <header class="bg-surface-container shadow-[0_5px_15px_rgba(0,0,0,0.5)] sticky top-0 z-20 border-b border-surface-container-high">
            <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <!-- Mobile Menu Button -->
                <button 
                    onclick="toggleSidebar()" 
                    class="lg:hidden text-white/60 hover:text-white transition"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Page Title -->
                <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-primary to-primary-dark tracking-wide">{{ $title ?? 'Dashboard' }}</h2>

                <div></div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-auto bg-surface-variant">
            {{ $slot }}
        </main>
    </div>
</div>