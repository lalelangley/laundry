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
                    <ul class="space-y-1 list-disc list-inside ml-2">
                        <li><strong>Toggle "Aktif":</strong> Menentukan apakah menu muncul di sidebar atau tidak</li>
                        <li><strong>Centang Permission:</strong> Bisa pilih kombinasi apapun (View saja, View+Add, dll)</li>
                        <li><strong>Flexible:</strong> Menu bisa aktif tapi cuma View, atau View+Edit tanpa Delete</li>
                        <li><strong>Tombol Bulk:</strong> "Aktifkan Semua" = ON semua menu + full permission</li>
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

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                                <i class="bi bi-list-check text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Pengaturan Hak Akses Menu</h2>
                                <p class="text-sm text-gray-500">Toggle menu & pilih permission yang diinginkan</p>
                            </div>
                        </div>
                        
                        {{-- BULK ACTIONS --}}
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="bulkAction('enable')" 
                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-2">
                                <i class="bi bi-check-all"></i> Aktifkan Semua
                            </button>
                            <button type="button" onclick="bulkAction('disable')" 
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-2">
                                <i class="bi bi-x-lg"></i> Nonaktifkan Semua
                            </button>
                            <button type="button" onclick="bulkAction('view-only')" 
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-semibold transition inline-flex items-center gap-2">
                                <i class="bi bi-eye"></i> View Only Semua
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200">
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 w-1/3">
                                    Menu
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 w-24">
                                    <div class="flex flex-col items-center gap-1">
                                        <i class="bi bi-toggle-on text-lg"></i>
                                        <span>Aktif</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 w-24">
                                    <div class="flex flex-col items-center gap-1">
                                        <i class="bi bi-eye text-lg"></i>
                                        <span>View</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 w-24">
                                    <div class="flex flex-col items-center gap-1">
                                        <i class="bi bi-plus-circle text-lg"></i>
                                        <span>Add</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 w-24">
                                    <div class="flex flex-col items-center gap-1">
                                        <i class="bi bi-pencil text-lg"></i>
                                        <span>Edit</span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 w-24">
                                    <div class="flex flex-col items-center gap-1">
                                        <i class="bi bi-trash text-lg"></i>
                                        <span>Delete</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($menus as $menu)
                                @php
                                    $permData = $permissions[$menu->id] ?? null;

                                    // Menu aktif kalau is_active = true
                                    $menuAktif = $permData ? $permData->is_active : false;
                                @endphp
                                <tr class="hover:bg-gray-50 transition" data-menu-row="{{ $menu->id }}">
                                    {{-- NAMA MENU --}}
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="bi bi-{{ $menu->icon ?? 'circle' }} text-yellow-600"></i>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-900 block">{{ $menu->nama_menu }}</span>
                                                @if($menu->route)
                                                    <span class="text-xs text-gray-500">{{ $menu->route }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- TOGGLE MENU AKTIF (INDEPENDENT) --}}
                                    <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                   name="menus[{{ $menu->id }}][active]"
                                                   value="1"
                                                   class="menu-toggle sr-only peer"
                                                   data-menu="{{ $menu->id }}"
                                                   @checked($menuAktif)>
                                            <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                                        </label>
                                    </td>

                                    {{-- PERMISSION CHECKBOXES (INDEPENDENT) --}}
                                    @foreach(['view','add','edit','delete'] as $perm)
                                        <td class="px-6 py-4 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox"
                                                       name="menus[{{ $menu->id }}][permissions][]"
                                                       value="{{ $perm }}"
                                                       class="perm-{{ $menu->id }} w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                                       @checked($permData && $permData->{'can_'.$perm})>
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('manager.index') }}"
                   class="px-6 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                    <i class="bi bi-x-circle"></i>
                    <span>Batal</span>
                </a>
                <button type="submit"
                        class="px-8 py-3 rounded-xl bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 font-bold transition inline-flex items-center gap-2 shadow-lg shadow-yellow-200">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Simpan Hak Akses</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// =============================
// TOGGLE INDIVIDUAL MENU (INDEPENDENT - NO AUTO UNCHECK)
// =============================
document.querySelectorAll('.menu-toggle').forEach(cb => {
    cb.addEventListener('change', function () {
        let menuId = this.dataset.menu;
        let isChecked = this.checked;
        
        // Visual feedback saja, TIDAK auto uncheck permission
        let row = document.querySelector(`[data-menu-row="${menuId}"]`);
        if (isChecked) {
            row.classList.remove('opacity-50');
            row.classList.add('bg-green-50');
        } else {
            row.classList.add('opacity-50');
            row.classList.remove('bg-green-50');
        }
    });
});

// =============================
// BULK ACTIONS
// =============================
function bulkAction(action) {
    if (action === 'enable') {
        if (!confirm('Aktifkan semua menu dengan full permission (View, Add, Edit, Delete)?')) return;
        
        // Aktifkan semua toggle
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.checked = true;
            let event = new Event('change');
            toggle.dispatchEvent(event);
        });
        
        // Centang semua permission
        document.querySelectorAll('input[type="checkbox"][name*="permissions"]').forEach(cb => {
            cb.checked = true;
        });
    } 
    else if (action === 'disable') {
        if (!confirm('Nonaktifkan semua menu? Ini akan uncheck semua toggle & permission!')) return;
        
        // Nonaktifkan semua toggle
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.checked = false;
            let event = new Event('change');
            toggle.dispatchEvent(event);
        });
        
        // Uncheck semua permission
        document.querySelectorAll('input[type="checkbox"][name*="permissions"]').forEach(cb => {
            cb.checked = false;
        });
    }
    else if (action === 'view-only') {
        if (!confirm('Set semua menu jadi View Only (aktif tapi cuma bisa lihat)?')) return;
        
        // Aktifkan semua toggle
        document.querySelectorAll('.menu-toggle').forEach(toggle => {
            toggle.checked = true;
            let event = new Event('change');
            toggle.dispatchEvent(event);
        });
        
        // Uncheck semua permission dulu
        document.querySelectorAll('input[type="checkbox"][name*="permissions"]').forEach(cb => {
            cb.checked = false;
        });
        
        // Centang hanya View
        document.querySelectorAll('input[type="checkbox"][value="view"]').forEach(cb => {
            cb.checked = true;
        });
    }
}

// =============================
// INITIAL STATE
// =============================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.menu-toggle').forEach(toggle => {
        if (!toggle.checked) {
            let row = document.querySelector(`[data-menu-row="${toggle.dataset.menu}"]`);
            row.classList.add('opacity-50');
        } else {
            let row = document.querySelector(`[data-menu-row="${toggle.dataset.menu}"]`);
            row.classList.add('bg-green-50');
        }
    });
});

// =============================
// KEYBOARD SHORTCUTS
// =============================
document.addEventListener('keydown', function(e) {
    // Ctrl+S = Save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.querySelector('form').submit();
    }
});

// =============================
// AUTO-SAVE WARNING
// =============================
let formChanged = false;
document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', function() {
        formChanged = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin keluar?';
        return e.returnValue;
    }
});

document.querySelector('form').addEventListener('submit', function() {
    formChanged = false;
});
</script>

<style>
/* Row styling */
[data-menu-row]:hover {
    background-color: #f9fafb;
}

/* Smooth transitions */
tr {
    transition: all 0.2s ease;
}

input[type="checkbox"] {
    transition: all 0.15s ease;
}

/* Checked state animation */
input[type="checkbox"]:checked {
    transform: scale(1.05);
}
</style>
@endsection