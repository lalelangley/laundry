{{-- FE-DOC: Template frontend untuk resources/views/layouts/sidebar.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Ambil user dan role
if (Auth::guard('kasir')->check()) {
    $user = Auth::guard('kasir')->user();
    $nama = $user->nama_kasir;
    $role = 'Kasir';
} elseif (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    $nama = $user->nama;  // ✅ ganti dari nama_admin ke nama
    $role = $user->role->nama_role ?? 'Admin';
} else {
    $user = null;
    $nama = '';
    $role = '';
}

$currentRouteName = Route::currentRouteName() ?? '';
@endphp

@foreach($menus as $menu)
    <!-- Menu items here -->
@endforeach

<!-- OVERLAY -->
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/50 hidden z-40 lg:hidden"
     onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<div id="sidebar"
     class="fixed top-0 left-0 flex h-full w-[280px] -translate-x-full flex-col bg-yellow-400 shadow-2xl transition-all duration-300 z-50 lg:translate-x-0">
    
    <!-- HEADER -->
    <div class="p-6 border-b-2 border-yellow-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900 sidebar-label">Menu</h2>
            <button onclick="toggleDesktopSidebar()" 
                    class="hidden lg:flex w-9 h-9 items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 transition-colors text-gray-900"
                    id="desktopSidebarToggle"
                    type="button">
                <i class="bi bi-layout-sidebar-inset text-lg"></i>
            </button>
            <button onclick="toggleSidebar()" 
                    class="w-8 h-8 flex items-center justify-center rounded-lg lg:hidden
                           bg-yellow-500 hover:bg-yellow-600 transition-colors">
                <i class="bi bi-x-lg text-xl text-gray-900"></i>
            </button>
        </div>
        
        <div class="flex items-center gap-3 sidebar-profile">
        @if(Auth::guard('kasir')->check())
            <img src="{{ $user->gambar ? asset('storage/' . $user->gambar) : asset('images/default-pfp.png') }}"
                class="w-14 h-14 rounded-full border-3 border-gray-900 object-cover sidebar-avatar">
        @elseif(Auth::guard('admin')->check())
            @if($user->gambar)
                <img src="{{ asset('storage/' . $user->gambar) }}"
                    alt="Profile" 
                    class="w-14 h-14 rounded-full border-3 border-gray-900 object-cover sidebar-avatar">
            @else
                <div class="w-14 h-14 rounded-full border-3 border-gray-900 bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center sidebar-avatar">
                    <i class="bi bi-person-circle text-white text-2xl"></i>
                </div>
            @endif
        @endif
        <div class="sidebar-label">
            <p class="font-bold text-gray-900 text-lg">{{ $nama }}</p>
            <p class="text-sm text-gray-800">{{ $role }}</p>
        </div>
    </div>
    </div>

  <!-- MENU -->
<div class="flex-1 overflow-y-auto p-4">
    @forelse($menus as $menu)
        @if($menu->route)
            @php
                $menuRoute = $menu->route;
                $menuBaseRoute = Str::endsWith($menuRoute, '.index')
                    ? Str::beforeLast($menuRoute, '.index')
                    : $menuRoute;

                $isNamedRouteActive = Route::has($menuRoute)
                    && (
                        request()->routeIs($menuRoute)
                        || ($menuBaseRoute !== $menuRoute && Str::startsWith($currentRouteName, $menuBaseRoute . '.'))
                    );

                $menuUrlPath = trim($menuRoute, '/');
                $isUrlRouteActive = !Route::has($menuRoute)
                    && (
                        request()->is($menuUrlPath)
                        || request()->is($menuUrlPath . '/*')
                    );

                $isMenuActive = $isNamedRouteActive || $isUrlRouteActive;
            @endphp

            {{-- Cek apakah route ada di Laravel --}}
            @if(Route::has($menu->route))
                <a href="{{ route($menu->route) }}"
                   class="sidebar-menu-item flex items-center gap-3 p-3 rounded-xl mb-2 transition-all
                          {{ $isMenuActive
                              ? 'bg-gray-900 text-yellow-400 font-bold shadow-lg scale-105' 
                              : 'text-gray-900 hover:bg-yellow-500 hover:pl-5' }}">
                    <i class="{{ $menu->icon ?? 'bi bi-circle' }} text-xl"></i>
                    <span class="font-semibold sidebar-label">{{ $menu->nama_menu }}</span>
                </a>
            @else
                {{-- Jika route tidak terdaftar, tampilkan sebagai link biasa dengan URL --}}
                <a href="{{ url($menu->route) }}"
                   class="sidebar-menu-item flex items-center gap-3 p-3 rounded-xl mb-2 transition-all
                          {{ $isMenuActive
                              ? 'bg-gray-900 text-yellow-400 font-bold shadow-lg scale-105' 
                              : 'text-gray-900 hover:bg-yellow-500 hover:pl-5' }}">
                    <i class="{{ $menu->icon ?? 'bi bi-circle' }} text-xl"></i>
                    <span class="font-semibold sidebar-label">{{ $menu->nama_menu }}</span>
                </a>
            @endif
        @else
            {{-- Jika route null, tampilkan menu disabled atau skip --}}
            <div class="sidebar-menu-item flex items-center gap-3 p-3 rounded-xl mb-2 text-gray-500 cursor-not-allowed opacity-50">
                <i class="{{ $menu->icon ?? 'bi bi-circle' }} text-xl"></i>
                <span class="font-semibold sidebar-label">{{ $menu->nama_menu }}</span>
                <span class="text-xs ml-auto sidebar-label">(No Route)</span>
            </div>
        @endif
    @empty
        <div class="text-center py-8 text-gray-700">
            <i class="bi bi-inbox text-4xl mb-2"></i>
            <p class="text-sm">Tidak ada menu tersedia</p>
        </div>
    @endforelse
</div>

    <!-- LOGOUT -->
    <div class="p-4 border-t-2 border-yellow-500 bg-yellow-400">
        @if(Auth::guard('kasir')->check())
            <form method="POST" action="{{ route('kasir.logout') }}">
                @csrf
                <button type="submit"
                        class="sidebar-logout-btn w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 
                            text-yellow-400 p-3 rounded-xl font-bold transition-all hover:scale-105">
                    <i class="bi bi-box-arrow-right text-xl"></i>
                    <span class="sidebar-label">Logout</span>
                </button>
            </form>
        @elseif(Auth::guard('admin')->check())
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="sidebar-logout-btn w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 
                            text-yellow-400 p-3 rounded-xl font-bold transition-all hover:scale-105">
                    <i class="bi bi-box-arrow-right text-xl"></i>
                    <span class="sidebar-label">Logout</span>
                </button>
            </form>
        @endif
    </div>
</div>

{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}

<script>
function toggleSidebar() {
    if (window.innerWidth >= 1024) {
        return;
    }

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isOpen = !sidebar.classList.contains('-translate-x-full');

    sidebar.classList.toggle('-translate-x-full', isOpen);
    overlay.classList.toggle('hidden', isOpen);
    document.body.classList.toggle('overflow-hidden', !isOpen);
}

function toggleDesktopSidebar() {
    if (window.innerWidth < 1024) {
        toggleSidebar();
        return;
    }

    const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', isCollapsed ? '1' : '0');
}

function syncSidebarState() {
    const isDesktop = window.innerWidth >= 1024;
    const savedCollapsed = localStorage.getItem('sidebar-collapsed') === '1';

    if (isDesktop) {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('sidebarOverlay').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        document.body.classList.toggle('sidebar-collapsed', savedCollapsed);
    } else {
        document.body.classList.remove('sidebar-collapsed');
        document.getElementById('sidebar').classList.add('-translate-x-full');
    }
}

syncSidebarState();

window.addEventListener('resize', () => {
    syncSidebarState();
});
</script>

{{-- FE-DOC: Blok CSS khusus halaman ini. --}}

<style>
#sidebar::-webkit-scrollbar {
    width: 6px;
}
#sidebar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
    border-radius: 10px;
}
#sidebar::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.3);
    border-radius: 10px;
}

@media (min-width: 1024px) {
    body.sidebar-collapsed #sidebar {
        width: 96px;
    }

    body.sidebar-collapsed #sidebar .sidebar-label {
        opacity: 0;
        width: 0;
        overflow: hidden;
        white-space: nowrap;
        pointer-events: none;
    }

    body.sidebar-collapsed #sidebar .sidebar-profile,
    body.sidebar-collapsed #sidebar .sidebar-menu-item,
    body.sidebar-collapsed #sidebar .sidebar-logout-btn {
        justify-content: center;
    }

    body.sidebar-collapsed #sidebar .sidebar-menu-item,
    body.sidebar-collapsed #sidebar .sidebar-logout-btn {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    body.sidebar-collapsed #sidebar .sidebar-avatar {
        width: 3rem;
        height: 3rem;
    }
}
</style>
