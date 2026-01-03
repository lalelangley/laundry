@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('admin2.manager.index') }}" class="text-black text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-gray-900">Hak Akses Kasir</span>
    </div>

    {{-- KASIR INFO --}}
    <div class="px-8 py-6">
        <div class="bg-white rounded-xl shadow-sm border-2 border-gray-200 p-5 inline-flex items-center gap-4">
            <div class="w-14 h-14 bg-yellow-400 rounded-full flex items-center justify-center">
                <i class="bi bi-person-fill text-white text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $kasir->nama_kasir }}</h3>
                <p class="text-sm text-gray-500">
                    No HP: <span class="font-semibold text-gray-700">{{ $kasir->no_hp ?? '-' }}</span>
                </p>
                <p class="text-sm text-gray-500">
                    Role: <span class="font-semibold text-gray-700">KASIR</span>
                </p>
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <div class="px-8 pb-10">
        <form method="POST" action="{{ route('admin2.manager.menu-role.akses.save', $kasir->id_kasir) }}">
            @csrf
            {{-- ROLE KASIR --}}
            <input type="hidden" name="role_id" value="3">

            {{-- TABLE CARD --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                {{-- Card Header --}}
                <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                            <i class="bi bi-shield-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Pengaturan Hak Akses Menu</h2>
                            <p class="text-sm text-gray-500">Centang menu yang aktif dan atur permission untuk kasir ini</p>
                        </div>
                    </div>
                </div>

                {{-- TABLE MENU --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200">
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Menu</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    <i class="bi bi-toggle-on me-1"></i>Aktif
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    <i class="bi bi-eye me-1"></i>View
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    <i class="bi bi-plus-circle me-1"></i>Add
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($menus as $menu)
                                @php
                                    $permData  = $permissions[$menu->id] ?? null;
                                    $menuAktif = (bool) $permData;
                                    $allowedActions = $menuActions[$menu->slug] ?? ['view','add','edit','delete'];
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-{{ $menu->icon ?? 'circle' }} text-gray-400"></i>
                                            <span class="font-medium text-gray-900">{{ $menu->nama_menu }}</span>
                                        </div>
                                    </td>
                                    {{-- MENU AKTIF --}}
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox"
                                               name="menus[]"
                                               value="{{ $menu->id }}"
                                               class="menu-toggle w-5 h-5 text-yellow-600 rounded focus:ring-2 focus:ring-yellow-500 cursor-pointer"
                                               data-menu="{{ $menu->id }}"
                                               @checked($menuAktif)>
                                    </td>
                                    {{-- PERMISSION --}}
                                    @foreach(['view','add','edit','delete'] as $perm)
                                        <td class="px-6 py-4 text-center">
                                            @if(in_array($perm, $allowedActions))
                                                <input type="checkbox"
                                                       name="permissions[{{ $menu->id }}][]"
                                                       value="{{ $perm }}"
                                                       class="perm-{{ $menu->id }} w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                                       @checked($permData && $permData->{'can_'.$perm})
                                                       @disabled(!$menuAktif)>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BUTTON --}}
            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin2.manager.index') }}"
                   class="px-6 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                    <i class="bi bi-x-circle"></i>
                    <span>Batal</span>
                </a>
                <button type="submit"
                        class="px-8 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold transition inline-flex items-center gap-2 shadow-lg shadow-yellow-200">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Simpan Hak Akses</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif

{{-- JS CONTROL --}}
<script>
document.querySelectorAll('.menu-toggle').forEach(cb => {
    cb.addEventListener('change', function () {
        let menuId = this.dataset.menu;
        document.querySelectorAll('.perm-' + menuId).forEach(p => {
            p.disabled = !this.checked;
            if (!this.checked) p.checked = false;
        });
    });
});
</script>
@endsection