<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@php
    if (Auth::guard('kasir')->check()) {
        $user = Auth::guard('kasir')->user();
        $nama = $user->nama_kasir;
        $role = 'Kasir';
    } elseif (Auth::guard('admin')->check()) {
        $user = Auth::guard('admin')->user();
        $nama = $user->nama_admin ?? 'Admin';
        $role = $user->role->nama_role ?? 'Admin';
    } else {
        return; // ⛔ JANGAN TAMPILKAN SIDEBAR JIKA BELUM LOGIN
    }
@endphp

<!-- OVERLAY -->
<div id="sidebarOverlay"
     class="fixed inset-0 bg-black/40 hidden z-40"
     onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<div id="sidebar"
     class="fixed top-0 left-0 w-[80%] sm:w-[300px] h-full bg-[#ffcc00] z-50
            -translate-x-full transition-transform duration-300">

    <!-- PROFILE -->
    <div class="p-4 text-center">
        <img src="{{ $user->profile_photo_url ?? asset('images/default-pfp.png') }}"
             class="w-20 h-20 mx-auto rounded-full border-4 border-yellow-300">
        <p class="mt-3 font-bold">{{ $nama }}</p>
        <p class="text-sm opacity-70">{{ $role }}</p>
    </div>

    <!-- MENU -->
    <div class="p-4 space-y-2">
       @foreach($menus as $menu)
            @if($menu->route && Route::has($menu->route))
                <a href="{{ route($menu->route) }}"
                class="flex items-center gap-3 p-3 rounded-lg hover:bg-yellow-300">
                    <i class="{{ $menu->icon }}"></i>
                    <span>{{ $menu->nama_menu }}</span>
                </a>
            @endif
        @endforeach


       {{-- LOGOUT --}}
    @if(Auth::guard('kasir')->check())
        <form method="POST" action="{{ route('kasir.logout') }}">
            @csrf
            <button class="w-full bg-red-500 text-white p-3 rounded-lg mt-4">
                Logout
            </button>
        </form>
    @elseif(Auth::guard('admin')->check())
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full bg-red-500 text-white p-3 rounded-lg mt-4">
                Logout
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
