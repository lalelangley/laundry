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

    {{-- FORM --}}
    <div class="px-8 pb-10">
        <form method="POST" action="{{ route('manager.role.hak.save') }}">
            @csrf
            <input type="hidden" name="role_id" value="{{ $selectedRoleId }}">

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="px-8 py-5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-yellow-400 rounded-xl flex items-center justify-center">
                            <i class="bi bi-list-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Pengaturan Hak Akses Menu</h2>
                            <p class="text-sm text-gray-500">Centang menu yang aktif dan atur permission untuk role ini</p>
                        </div>
                    </div>
                </div>

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
                                    $permData = $permissions[$menu->id] ?? null;

                                    // menuAktif dicentang kalau setidaknya 1 permission aktif
                                    $menuAktif = $permData ? (
                                        $permData->can_view ||
                                        $permData->can_add ||
                                        $permData->can_edit ||
                                        $permData->can_delete
                                    ) : false;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-8 py-4">
                                        <span class="font-medium text-gray-900">{{ $menu->nama_menu }}</span>
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
                                            <input type="checkbox"
                                                   name="permissions[{{ $menu->id }}][]"
                                                   value="{{ $perm }}"
                                                   class="perm-{{ $menu->id }} w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                                   @checked($permData && $permData->{'can_'.$perm})
                                                   @disabled(!$menuAktif)>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('manager.index') }}"
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