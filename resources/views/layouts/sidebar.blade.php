<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Sidebar Overlay -->
<div id="sidebarOverlay"
    class="fixed inset-0 bg-black/40 hidden z-40"
    onclick="toggleSidebar()">
</div>

<!-- SIDEBAR -->
<div id="sidebar"
    class="fixed top-0 left-0 w-[80%] sm:w-[300px] h-full bg-[#ffcc00] shadow-xl z-50 -translate-x-full transition-transform duration-300">

    <!-- HEADER -->
    <div class="p-4 pb-2 border-b border-black/20">
        <div class="flex items-center justify-between">
            <div onclick="toggleSidebar()" class="text-3xl cursor-pointer font-bold">×</div>
        </div>
    </div>

    <!-- PROFILE -->
    <div class="flex flex-col items-center mt-6 mb-8">
        <img 
            src="{{ Auth::user()->profile_photo_url ?? asset('images/default-pfp.png') }}" 
            class="w-20 h-20 rounded-full object-cover border-4 border-yellow-400 shadow"
            alt="Profile Picture"
        >
        
        <p class="mt-3 font-bold text-lg text-black">
            {{ auth()->user()->nama ?? 'Admin' }}
        </p>

        <p class="text-sm text-black/70 -mt-1">
            Admin Utama
        </p>
    </div>

    <!-- MENU -->
    <div class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-250px)]">

        <!-- ITEM -->
        <a href="{{ route('layanan.index') }}" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-basket text-2xl"></i> 
            <span class="font-bold">LAYANAN</span>
         </a>


        <a href="{{ route('parfum.index') }}" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-wind text-2xl"></i> 
            <span class="font-bold">PARFUM</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-people text-2xl"></i> 
            <span class="font-bold">PELANGGAN</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-clock-history text-2xl"></i> 
            <span class="font-bold">RIWAYAT</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-cash-coin text-2xl"></i> 
            <span class="font-bold">PENGELUARAN</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-clipboard-data text-2xl"></i> 
            <span class="font-bold">LAPORAN</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-gear text-2xl"></i> 
            <span class="font-bold">PENGATURAN</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-shield-lock text-2xl"></i> 
            <span class="font-bold">SEKURITI</span>
        </a>

        <a href="#" 
            class="flex items-center gap-3 bg-[#ffcc00] p-4 rounded-lg shadow
                   transition-all duration-150 hover:bg-yellow-300 hover:shadow-lg hover:scale-[1.02]">
            <i class="bi bi-key text-2xl"></i> 
            <span class="font-bold">GANTI PASSWORD</span>
        </a>

        <!-- LOGOUT -->
        <form action="{{ route('logout') }}" method="POST" class="w-full">
            @csrf
            <button type="submit"
                class="flex items-center gap-3 bg-red-500 text-white p-4 rounded-lg shadow w-full text-left
                       transition-all duration-150 hover:bg-red-600 hover:shadow-lg hover:scale-[1.02]">
                <i class="bi bi-box-arrow-right text-2xl"></i>
                <span class="font-bold">LOG OUT</span>
            </button>
        </form>

    </div>
</div>

<script>
    function toggleSidebar() {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebarOverlay');
        sb.classList.toggle('-translate-x-full');
        ov.classList.toggle('hidden');
    }
</script>
