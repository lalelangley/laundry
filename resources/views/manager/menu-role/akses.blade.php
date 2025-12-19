@extends('layouts.master')
@section('content')

<div class="min-h-screen bg-gray-50 px-8 py-10">

    <h1 class="text-2xl font-bold mb-4">Hak Akses: {{ $user->nama }}</h1>

    <form action="{{ route('manager.permission.save.user') }}" method="POST">
        @csrf
        <input type="hidden" name="user_type" value="{{ $user_type }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">

        <table class="w-full bg-white rounded-xl shadow overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left">Menu</th>
                    <th class="px-4 py-4 text-center">View</th>
                    <th class="px-4 py-4 text-center">Add</th>
                    <th class="px-4 py-4 text-center">Edit</th>
                    <th class="px-4 py-4 text-center">Delete</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($menus as $menu)
                    <tr>
                        <td class="px-6 py-4">{{ $menu->nama_menu }}</td>
                        @foreach(['view','add','edit','delete'] as $perm)
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox"
                                    name="permissions[{{ $menu->id }}][]"
                                    value="{{ $perm }}"
                                    @if(isset($userPermissions[$menu->id][$perm]) && $userPermissions[$menu->id][$perm])
                                        checked
                                    @endif
                                >
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6">
            <button type="submit"
                class="bg-yellow-400 hover:bg-yellow-500 px-6 py-2 rounded-lg font-semibold text-gray-900">
                Simpan Hak Akses
            </button>
        </div>
    </form>

</div>

@endsection
