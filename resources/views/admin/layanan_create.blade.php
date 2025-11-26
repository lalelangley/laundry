@extends('layouts.master')

@section('content')
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.index') }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Tambah Layanan</span>
</div>

<div class="px-5 pb-32 pt-6">

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-200 text-green-800 rounded-xl shadow">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-200 text-red-800 p-4 rounded-xl mb-4 shadow">
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM PUNYA ID --}}
    <form id="form-layanan"
          action="{{ route('layanan.store') }}" 
          method="POST" 
          enctype="multipart/form-data">

        @csrf

        <label class="font-bold text-lg">Nama Layanan</label>
        <input type="text" name="nama_layanan" class="w-full bg-gray-200 p-4 rounded-xl mt-1 mb-6">

        <label class="font-bold text-lg">Proses</label>
        <div class="flex gap-3 mt-3 mb-8">
            @foreach(['Cuci','Kering','Setrika'] as $p)
                <label class="flex items-center gap-2 border-2 border-yellow-400 rounded-full px-6 py-2">
                    <input type="checkbox" name="proses[]" value="{{ $p }}" class="w-5 h-5 accent-yellow-500">
                    <span class="text-lg font-medium">{{ $p }}</span>
                </label>
            @endforeach
        </div>

        <a href="{{ route('jenis_layanan.create') }}" 
           class="bg-yellow-400 px-5 py-2 rounded-full text-white font-semibold shadow inline-block mb-4">
            + Tambah Jenis Layanan
        </a>

        {{-- LIST JENIS --}}
        <div id="list-jenis" class="mb-6 space-y-3">
            @php $items = session()->get('jenis_baru', []); @endphp

            @if(count($items) > 0)

                @foreach($items as $i => $it)
                    <div class="bg-white p-4 rounded-xl shadow flex items-start gap-3">

                        <div class="w-20 h-20 bg-gray-100 rounded overflow-hidden">
                            @if(!empty($it['gambar']))
                                <img src="{{ asset($it['gambar']) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    No Image
                                </div>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="font-semibold">{{ $it['nama'] }}</div>
                            <div class="text-sm text-gray-600">
                                Rp {{ number_format($it['harga'],0,',','.') }} • {{ $it['satuan'] }}
                            </div>
                        </div>

                        <div>
                            <form action="{{ route('jenis_layanan.remove', $i) }}" method="POST">
                                @csrf
                                <button class="text-red-500">Hapus</button>
                            </form>
                        </div>

                    </div>
                @endforeach

            @else
                <div class="text-gray-500">Belum ada jenis layanan.</div>
            @endif
        </div>

    </form>

    {{-- FIXED BUTTON (DI LUAR FORM) --}}
    <div class="fixed bottom-0 left-0 w-full px-5 pb-5 bg-white">
        <a href="#"
           onclick="document.getElementById('form-layanan').submit();"
           class="w-full bg-green-600 py-4 rounded-2xl text-white text-xl font-bold shadow block text-center">
           Simpan Layanan & Jenis
        </a>
    </div>

</div>
@endsection
