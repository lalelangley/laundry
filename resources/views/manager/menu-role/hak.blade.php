{{-- FE-DOC: Template frontend untuk resources/views/manager/menu-role/hak.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('manager.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Setting Hak Akses</span>
    </div>

    {{-- ROLE SELECTOR --}}
    <div class="px-8 py-6">
        <div class="bg-white rounded-xl shadow-sm border-2 border-gray-200 p-6 inline-block">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="bi bi-shield-check me-2"></i>Pilih Role
            </label>
            <select name="role_id"
                    class="border-2 border-gray-300 rounded-xl px-5 py-3 w-80 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 transition outline-none cursor-pointer font-medium"
                    onchange="location.href='?role_id='+this.value">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected($selectedRoleId == $role->id)>
                        {{ strtoupper($role->nama_role) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- INFO BOX --}}
    <div class="px-8 pb-6">
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-5">
            <div class="flex gap-3">
                <i class="bi bi-info-circle-fill text-blue-600 text-xl flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-blue-900">
                    <p class="font-semibold mb-1">📋 Cara Penggunaan:</p>
                    <ul class="space-y-1 ml-2">
                        <li>• <strong>Permission "Lihat"</strong> = Menentukan menu muncul/tidak di sidebar</li>
                        <li>• <strong>Lihat ON</strong> = Menu muncul di sidebar</li>
                        <li>• <strong>Lihat OFF</strong> = Menu tidak muncul di sidebar</li>
                        <li>• <strong>Permission lain</strong> = Fitur yang bisa dilakukan (Tambah, Edit, Hapus, dll)</li>
                        <li>• Perubahan akan tersimpan otomatis setiap kali toggle diubah</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <div class="px-8 pb-10">
        <form method="POST" action="{{ route('manager.role.hak.save') }}">
            @csrf
            <input type="hidden" name="role_id" value="{{ $selectedRoleId }}">

            {{-- BULK ACTIONS --}}
            <div class="mb-6 flex items-center gap-3 justify-end">
                <button type="button" onclick="bulkAction('enable')" 
                    class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-105 inline-flex items-center gap-2">
                    <i class="bi bi-check-all text-lg"></i> Aktifkan Semua
                </button>
                <button type="button" onclick="bulkAction('disable')" 
                    class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-bold transition-all shadow-lg hover:shadow-xl hover:scale-105 inline-flex items-center gap-2">
                    <i class="bi bi-x-lg text-lg"></i> Nonaktifkan Semua
                </button>
            </div>

            {{-- MENU CARDS GRID --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($menus as $menu)
                    @php
                        $permData = $permissions[$menu->id] ?? null;
                        $menuAktif = $permData ? $permData->is_active : false;
                        
                        // Tentukan permission yang tersedia berdasarkan nama menu
                        $menuSlug = strtolower(str_replace([' ', '-'], '_', $menu->nama_menu));
                        
                        $availablePerms = [];
                        switch($menuSlug) {
                            case 'layanan':
                            case 'satuan':
                            case 'parfum':
                            case 'pelanggan':
                            case 'pengeluaran':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                    ['value' => 'add', 'label' => 'Tambah', 'icon' => 'plus-circle-fill'],
                                    ['value' => 'edit', 'label' => 'Edit', 'icon' => 'pencil-fill'],
                                    ['value' => 'delete', 'label' => 'Hapus', 'icon' => 'trash-fill'],
                                ];
                                break;
                            case 'metode_bayar':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                    ['value' => 'add', 'label' => 'Tambah', 'icon' => 'plus-circle-fill'],
                                    ['value' => 'delete', 'label' => 'Hapus', 'icon' => 'trash-fill'],
                                ];
                                break;
                            case 'transaksi':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                    ['value' => 'add', 'label' => 'Tambah', 'icon' => 'plus-circle-fill'],
                                    ['value' => 'edit', 'label' => 'Edit', 'icon' => 'pencil-fill'],
                                    ['value' => 'delete', 'label' => 'Hapus', 'icon' => 'trash-fill'],
                                    ['value' => 'cancel', 'label' => 'Batal', 'icon' => 'x-circle-fill'],
                                ];
                                break;
                            case 'pesanan_online':
                            case 'riwayat':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                    ['value' => 'edit', 'label' => 'Edit', 'icon' => 'pencil-fill'],
                                    ['value' => 'delete', 'label' => 'Hapus', 'icon' => 'trash-fill'],
                                ];
                                break;
                            case 'laporan':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                ];
                                break;
                            case 'pengaturan':
                            case 'data':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                    ['value' => 'edit', 'label' => 'Edit Pengaturan', 'icon' => 'pencil-fill'],
                                    ['value' => 'add', 'label' => 'Metode Bayar', 'icon' => 'credit-card-fill'],
                                    ['value' => 'restore', 'label' => 'Restore Data', 'icon' => 'arrow-repeat'],
                                    ['value' => 'hapus_backup', 'label' => 'Hapus Backup', 'icon' => 'trash-fill'],
                                ];
                                break;
                            case 'user_manager':
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                    ['value' => 'add', 'label' => 'Tambah', 'icon' => 'plus-circle-fill'],
                                    ['value' => 'edit', 'label' => 'Edit', 'icon' => 'pencil-fill'],
                                    ['value' => 'delete', 'label' => 'Hapus', 'icon' => 'trash-fill'],
                                ];
                                break;
                            default:
                                $availablePerms = [
                                    ['value' => 'view', 'label' => 'Lihat', 'icon' => 'eye-fill'],
                                ];
                        }
                    @endphp

                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-gray-100 hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 menu-card" data-menu-id="{{ $menu->id }}">
                        {{-- CARD HEADER --}}
                        <div class="bg-gradient-to-r from-yellow-50 to-white px-6 py-4 border-b-2 border-yellow-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                        <i class="bi bi-{{ $menu->icon ?? 'circle' }} text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $menu->nama_menu }}</h3>
                                        @if($menu->route)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $menu->route }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- TOGGLE MENU AKTIF --}}
                                <label class="relative inline-flex items-center cursor-pointer group">
                                    <input type="checkbox"
                                           name="menus[{{ $menu->id }}][active]"
                                           value="1"
                                           class="menu-toggle sr-only peer"
                                           data-menu="{{ $menu->id }}"
                                           @checked($menuAktif)>
                                    <div class="toggle-main w-16 h-8 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-7 after:w-7 after:transition-all after:shadow-md peer-checked:bg-green-500 shadow-inner"></div>
                                </label>
                            </div>
                        </div>

                        {{-- CARD BODY - PERMISSIONS --}}
                        <div class="px-6 py-5 space-y-2.5 bg-gray-50/50">
                            @foreach($availablePerms as $perm)
                                @php
                                    // ✅ FIXED: Mapping permission value ke field database yang benar
                                    $fieldName = match($perm['value']) {
                                        'view' => 'can_view',
                                        'add' => match($menuSlug) {
                                            'pengaturan', 'data' => 'can_access_settings',
                                            default => 'can_add'
                                        },
                                        'edit' => 'can_edit',
                                        'delete' => 'can_delete',
                                        'cancel' => 'can_cancel',
                                        'password' => 'can_change_password',
                                        'restore' => 'can_restore_data',
                                        'hapus_backup' => 'show_delete_backup',
                                        'logout' => 'show_logout',
                                        default => 'can_view'
                                    };
                                    
                                    // Cek apakah permission ini aktif
                                    $isChecked = $permData && isset($permData->{$fieldName}) && $permData->{$fieldName};
                                @endphp

                                <div class="flex items-center justify-between py-3 px-4 bg-white hover:bg-yellow-50/50 rounded-xl transition-all border border-gray-100 hover:border-yellow-200 hover:shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-yellow-400/10 rounded-lg flex items-center justify-center">
                                            <i class="bi bi-{{ $perm['icon'] }} text-yellow-600 text-base"></i>
                                        </div>
                                        <span class="text-gray-800 font-semibold text-sm">{{ $perm['label'] }}</span>
                                    </div>
                                    
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox"
                                               name="menus[{{ $menu->id }}][permissions][]"
                                               value="{{ $perm['value'] }}"
                                               class="perm-toggle-{{ $menu->id }} sr-only peer"
                                               @checked($isChecked)>
                                        <div class="toggle-perm w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-3 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-gradient-to-r peer-checked:from-green-400 peer-checked:to-green-500 shadow-inner"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </form>
    </div>
</div>

{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}

<script>
// =============================
// AUTO-SAVE FUNCTION
// =============================
function autoSave(menuId, field, value) {
    let formData = {
        _token: '{{ csrf_token() }}',
        role_id: {{ $selectedRoleId }},
        menus: {}
    };
    
    let menuData = {
        active: document.querySelector(`.menu-toggle[data-menu="${menuId}"]`)?.checked ? 1 : 0,
        permissions: []
    };
    
    // Collect all checked permissions
    document.querySelectorAll(`.perm-toggle-${menuId}:checked`).forEach(cb => {
        menuData.permissions.push(cb.value);
    });
    
    // Update specific permission if needed
    if (field.startsWith('can_')) {
        let permValue = field.replace('can_', '');
        if (value && !menuData.permissions.includes(permValue)) {
            menuData.permissions.push(permValue);
        } else if (!value) {
            menuData.permissions = menuData.permissions.filter(p => p !== permValue);
        }
    }
    
    if (field === 'is_active') {
        menuData.active = value ? 1 : 0;
    }
    
    formData.menus[menuId] = menuData;
    
    // ✅ DEBUG: Log data yang dikirim
    console.log('Auto-save data:', formData);
    
    fetch('{{ route("manager.role.hak.quick") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Save response:', data);
        showToast('success', '✓ Tersimpan');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Gagal menyimpan');
    });
}

// =============================
// TOGGLE MENU
// =============================
document.querySelectorAll('.menu-toggle').forEach(cb => {
    cb.addEventListener('change', function () {
        let menuId = this.dataset.menu;
        let isChecked = this.checked;
        
        autoSave(menuId, 'is_active', isChecked);
        
        let card = document.querySelector(`[data-menu-id="${menuId}"]`);
        
        if (isChecked) {
            card.classList.remove('opacity-50', 'grayscale');
            card.classList.add('border-green-300');
            card.classList.remove('border-gray-100');
        } else {
            card.classList.add('opacity-50', 'grayscale');
            card.classList.remove('border-green-300');
            card.classList.add('border-gray-100');
        }
    });
});

// =============================
// TOGGLE PERMISSIONS
// =============================
document.querySelectorAll('input[type="checkbox"][name*="permissions"]').forEach(cb => {
    cb.addEventListener('change', function() {
        let menuId = this.name.match(/menus\[(\d+)\]/)[1];
        let permission = this.value;
        let isChecked = this.checked;
        
        console.log(`Permission changed: Menu ${menuId}, ${permission} = ${isChecked}`);
        
        autoSave(menuId, 'can_' + permission, isChecked);
    });
});

// =============================
// TOAST NOTIFICATION
// =============================
function showToast(type, message) {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'check-circle-fill' : 'x-circle-fill';
    
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-xl shadow-2xl z-50 flex items-center gap-3 animate-slideIn`;
    toast.innerHTML = `
        <i class="bi bi-${icon} text-xl"></i>
        <span class="font-semibold">${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// =============================
// BULK ACTIONS
// =============================
function bulkAction(action) {
    if (action === 'enable') {
        if (!confirm('Aktifkan semua menu dengan full permission?')) return;
        
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));
        });
        
        // Small delay between each permission to avoid overwhelming the server
        let delay = 0;
        document.querySelectorAll('input[type="checkbox"][name*="permissions"]').forEach(cb => {
            setTimeout(() => {
                cb.checked = true;
                cb.dispatchEvent(new Event('change'));
            }, delay);
            delay += 50; // 50ms delay between each
        });
    } 
    else if (action === 'disable') {
        if (!confirm('Nonaktifkan semua menu?')) return;
        
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.checked = false;
            toggle.dispatchEvent(new Event('change'));
        });
        
        document.querySelectorAll('input[type="checkbox"][name*="permissions"]').forEach(cb => {
            cb.checked = false;
        });
    }
}

// =============================
// INITIAL STATE
// =============================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded. Setting initial states...');
    
    document.querySelectorAll('.menu-toggle').forEach(toggle => {
        let menuId = toggle.dataset.menu;
        let card = document.querySelector(`[data-menu-id="${menuId}"]`);
        
        if (!toggle.checked) {
            card.classList.add('opacity-50', 'grayscale');
        } else {
            card.classList.add('border-green-300');
            card.classList.remove('border-gray-100');
        }
    });
});
</script>

{{-- FE-DOC: Blok CSS khusus halaman ini. --}}

<style>
/* Card hover effects */
.menu-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.menu-card:hover {
    transform: translateY(-4px);
}

/* Toggle animations - MAIN (Menu On/Off) */
.toggle-main {
    transition: background-color 0.3s ease;
}

.toggle-main:after {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Toggle animations - PERMISSION */
.toggle-perm {
    transition: background-color 0.3s ease;
}

.toggle-perm:after {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Smooth opacity transitions */
.menu-card {
    transition: opacity 0.3s ease, border-color 0.3s ease, transform 0.3s ease, filter 0.3s ease;
}

/* Grayscale effect for inactive cards */
.grayscale {
    filter: grayscale(0.6);
}

/* Toast animations */
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.animate-slideIn {
    animation: slideIn 0.3s ease-out;
}

/* Responsive adjustments */
@media (max-width: 1280px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 1024px) {
    .grid {
        grid-template-columns: 1fr;
    }
}

/* Smooth animations on load */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.menu-card {
    animation: fadeInUp 0.4s ease-out;
    animation-fill-mode: both;
}

.menu-card:nth-child(1) { animation-delay: 0.05s; }
.menu-card:nth-child(2) { animation-delay: 0.1s; }
.menu-card:nth-child(3) { animation-delay: 0.15s; }
.menu-card:nth-child(4) { animation-delay: 0.2s; }
.menu-card:nth-child(5) { animation-delay: 0.25s; }
.menu-card:nth-child(6) { animation-delay: 0.3s; }
.menu-card:nth-child(7) { animation-delay: 0.35s; }
.menu-card:nth-child(8) { animation-delay: 0.4s; }
.menu-card:nth-child(9) { animation-delay: 0.45s; }
</style>

@if(session('success'))
<script>
const successMsg = document.createElement('div');
successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-2xl z-50 flex items-center gap-3 animate-slideIn';
successMsg.innerHTML = `
    <i class="bi bi-check-circle-fill text-2xl"></i>
    <div>
        <p class="font-bold">Berhasil!</p>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
`;
document.body.appendChild(successMsg);

setTimeout(() => {
    successMsg.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => successMsg.remove(), 300);
}, 3000);
</script>
@endif

@endsection