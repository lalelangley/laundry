<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@php
use Illuminate\Support\Facades\Auth;

// Ambil user dan role
if (Auth::guard('kasir')->check()) {
    $user = Auth::guard('kasir')->user();
    $nama = $user->nama_kasir;
    $role = 'Kasir';
} elseif (Auth::guard('admin')->check()) {
    $user = Auth::guard('admin')->user();
    $nama = $user->nama_admin ?? 'Admin';
    $role = $user->role->nama_role ?? 'Admin';
} elseif (Auth::guard('admin2')->check()) {
    $user = Auth::guard('admin2')->user();
    $nama = $user->nama_admin ?? 'Admin2';
    $role = $user->role->nama_role ?? 'Admin';
} else {
    $user = null;
    $nama = '';
    $role = '';
}

// ✅ Gunakan $menus dari AppServiceProvider
// Variabel $menus sudah tersedia dari View Composer
@endphp

<!-- OVERLAY -->
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/50 hidden z-40"
     onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<div id="sidebar"
     class="fixed top-0 left-0 w-[280px] h-full bg-yellow-400 z-50 
            -translate-x-full transition-transform duration-300 shadow-2xl">
    
    <!-- HEADER -->
    <div class="p-6 border-b-2 border-yellow-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Menu</h2>
            <button onclick="toggleSidebar()" 
                    class="w-8 h-8 flex items-center justify-center rounded-lg 
                           bg-yellow-500 hover:bg-yellow-600 transition-colors">
                <i class="bi bi-x-lg text-xl text-gray-900"></i>
            </button>
        </div>
        
        <div class="flex items-center gap-3">
            <img src="{{ $user->profile_photo_url ?? asset('images/default-pfp.png') }}"
                 class="w-14 h-14 rounded-full border-3 border-gray-900 object-cover">
            <div>
                <p class="font-bold text-gray-900 text-lg">{{ $nama }}</p>
                <p class="text-sm text-gray-800">{{ $role }}</p>
            </div>
        </div>
    </div>


    <!-- MENU -->
    <div class="p-4 overflow-y-auto h-[calc(100vh-250px)]">
        @forelse($menus as $menu)
            @if($menu->route && Route::has($menu->route))
                @php
                    // Ambil permission dari pivot
                    $pivot = $menu->roles->first()?->pivot;
                @endphp
                
                <a href="{{ route($menu->route) }}"
                   class="flex items-center gap-3 p-3 rounded-xl mb-2 transition-all
                          {{ request()->routeIs($menu->route) 
                              ? 'bg-gray-900 text-yellow-400 font-bold shadow-lg scale-105' 
                              : 'text-gray-900 hover:bg-yellow-500 hover:pl-5' }}">
                    <i class="{{ $menu->icon }} text-xl"></i>
                    <span class="font-semibold">{{ $menu->nama_menu }}</span>
                </a>
            @endif
        @empty
            <div class="text-center py-8 text-gray-700">
                <i class="bi bi-inbox text-4xl mb-2"></i>
                <p class="text-sm">Tidak ada menu tersedia</p>
            </div>
        @endforelse
    </div>

    <!-- LOGOUT -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t-2 border-yellow-500 bg-yellow-400">
        @if(Auth::guard('kasir')->check())
            <form method="POST" action="{{ route('kasir.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 
                               text-yellow-400 p-3 rounded-xl font-bold transition-all hover:scale-105">
                    <i class="bi bi-box-arrow-right text-xl"></i>
                    <span>Logout</span>
                </button>
            </form>
        @elseif(Auth::guard('admin')->check() || Auth::guard('admin2')->check())
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 
                               text-yellow-400 p-3 rounded-xl font-bold transition-all hover:scale-105">
                    <i class="bi bi-box-arrow-right text-xl"></i>
                    <span>Logout</span>
                </button>
            </form>
        @endif
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
}
</script>

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
</style>