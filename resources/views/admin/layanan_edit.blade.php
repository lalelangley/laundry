@extends('layouts.master')

@section('content')
@php
    $from = request()->route('layanan');
@endphp

{{-- HEADER --}}
<div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-3 shadow w-full sticky top-0 z-10">
    <a href="{{ route('layanan.index') }}"
    class="text-black text-2xl font-bold leading-none hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-semibold">Edit Layanan</span>
</div>

<div class="px-6 mt-8 w-full max-w-full pb-8">

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5 text-base shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                <span class="font-bold">Terdapat kesalahan:</span>
            </div>
            <ul class="ml-5 list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-xl mb-5 text-base shadow-md flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-xl"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('layanan.update', $layanan->id_layanan) }}" method="POST" class="bg-white rounded-2xl shadow-lg p-6">
    @csrf
    @method('PUT')

        {{-- NAMA LAYANAN --}}
        <div class="mb-6">
            <label class="block font-bold text-gray-700 text-lg mb-2">
                <i class="bi bi-tag-fill text-yellow-500 mr-1"></i>
                Nama Layanan
            </label>
            <input type="text" 
                   name="nama_layanan"
                   value="{{ old('nama_layanan', $layanan->nama_layanan) }}"
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base 
                          focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition-all"
                   placeholder="Contoh: Cuci Kering Setrika"
                   required>
        </div>

        {{-- PROSES (FIXED) --}}
        @php
            $prosesList = ['Cuci', 'Kering', 'Setrika'];
            
            // ✅ FIX: Parse proses dengan benar
            $rawProses = $layanan->proses ?? '';
            
            // Jika dalam format JSON array
            if (is_string($rawProses) && Str::startsWith(trim($rawProses), '[')) {
                $selectedProses = json_decode($rawProses, true) ?: [];
            } 
            // Jika dalam format comma-separated
            else {
                $parts = explode(',', trim($rawProses, "[]\"' "));
                $selectedProses = array_map('trim', $parts);
                $selectedProses = array_filter($selectedProses, fn($s) => !empty($s));
            }
            
            // Override dengan old input jika ada
            if (old('proses')) {
                $selectedProses = old('proses');
            }
        @endphp

        <div class="mb-8">
            <label class="block font-bold text-gray-700 text-lg mb-3">
                <i class="bi bi-gear-fill text-yellow-500 mr-1"></i>
                Proses Layanan
            </label>

            <div class="grid grid-cols-3 gap-4 w-full">
                @foreach ($prosesList as $p)
                    @php
                        $isChecked = in_array($p, $selectedProses);
                    @endphp
                    <label class="flex items-center gap-2 px-4 py-3 border-2 rounded-xl cursor-pointer text-base font-medium transition-all
                                   {{ $isChecked ? 'bg-yellow-50 border-yellow-400 text-yellow-700' : 'bg-white border-gray-200 hover:border-yellow-300' }}">
                        <input type="checkbox" 
                               name="proses[]" 
                               value="{{ $p }}"
                               class="w-5 h-5 text-yellow-500 rounded focus:ring-2 focus:ring-yellow-400"
                               {{ $isChecked ? 'checked' : '' }}>
                        
                        @php
                            $icons = ['Cuci' => 'bi-droplet-fill', 'Kering' => 'bi-wind', 'Setrika' => 'bi-iron'];
                        @endphp
                        <i class="bi {{ $icons[$p] ?? 'bi-gear' }} text-lg"></i>
                        <span>{{ $p }}</span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <i class="bi bi-info-circle"></i>
                Pilih minimal 1 proses layanan
            </p>
        </div>


        {{-- JENIS LAYANAN --}}
        <div class="border-t-2 border-gray-100 pt-6">
            <h3 class="mb-4 font-bold text-gray-800 text-xl flex items-center gap-2">
                <i class="bi bi-list-ul text-yellow-500"></i>
                Jenis Layanan
            </h3>

            @php 
                $jenisBaru = session()->get("jenis_baru_{$layanan->id_layanan}", []); 
            @endphp

            <div class="space-y-4">

                {{-- JENIS LAMA (FIXED IMAGE) --}}
                @foreach ($layanan->jenis as $jenis)
                    <a href="{{ route('layanan.jenis.edit', $jenis->id_jenis_layanan) }}?from={{ $layanan->id_layanan }}"
                       class="flex gap-4 p-4 bg-white border-2 border-gray-200 rounded-2xl shadow hover:bg-yellow-50 hover:border-yellow-400 hover:shadow-md transition-all w-full group">

                        {{-- ✅ FIXED IMAGE SECTION --}}
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl overflow-hidden flex-shrink-0 border-2 border-gray-200 group-hover:border-yellow-300 transition-all">
                            @if(!empty($jenis->gambar))
                                <img src="{{ asset('storage/' . $jenis->gambar) }}"
                                     alt="{{ $jenis->nama_jenis }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-100 to-yellow-200\'><i class=\'bi bi-image text-3xl text-yellow-400\'></i></div>';">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="bi bi-image text-3xl text-gray-300"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-lg text-gray-800 truncate">{{ $jenis->nama_jenis }}</p>

                            <p class="text-green-600 font-semibold text-base">
                                Rp {{ number_format($jenis->harga, 0, ',', '.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}
                            </p>

                            <p class="text-gray-500 text-sm flex items-center gap-1">
                                <i class="bi bi-clock text-base"></i>
                                {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                            </p>
                        </div>

                        <div class="flex items-center">
                            <div class="bg-yellow-100 p-2 rounded-lg group-hover:bg-yellow-400 transition-all">
                                <i class="bi bi-pencil-square text-xl text-yellow-600 group-hover:text-white"></i>
                            </div>
                        </div>
                    </a>
                @endforeach


                {{-- JENIS BARU (SESSION) - FIXED IMAGE --}}
                @if(count($jenisBaru) > 0)
                    @foreach ($jenisBaru as $index => $jb)
                        <div class="flex gap-4 p-4 bg-yellow-50 border-2 border-yellow-300 rounded-2xl shadow-md">

                            {{-- ✅ FIXED IMAGE SECTION --}}
                            <div class="w-20 h-20 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl overflow-hidden flex-shrink-0 border-2 border-yellow-300">
                                @if(!empty($jb['gambar']))
                                    <img src="{{ asset('storage/' . $jb['gambar']) }}"
                                         alt="{{ $jb['nama_jenis'] ?? 'Jenis' }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><i class=\'bi bi-image text-3xl text-yellow-400\'></i></div>';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="bi bi-image text-3xl text-yellow-400"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-lg text-gray-800">
                                    {{ $jb['nama_jenis'] ?? '-' }}
                                    <span class="text-xs text-yellow-700 font-normal bg-yellow-200 px-2 py-1 rounded ml-2">belum disimpan</span>
                                </p>

                                <p class="text-green-600 font-semibold text-base">
                                    Rp {{ number_format($jb['harga'] ?? 0, 0, ',', '.') }} /
                                    @php
                                        $satuanName = '-';
                                        if(!empty($jb['id_satuan'])){
                                            $s = \App\Models\Satuan::find($jb['id_satuan']);
                                            if($s) $satuanName = $s->nama_satuan;
                                        }
                                    @endphp
                                    {{ $satuanName }}
                                </p>

                                <p class="text-gray-500 text-sm flex items-center gap-1">
                                    <i class="bi bi-clock text-base"></i>
                                    {{ $jb['lama'] ?? '-' }} {{ $jb['lama_satuan'] ?? '' }}
                                </p>
                            </div>

                            <div class="flex items-center">
                                <span class="text-xs bg-yellow-500 text-white px-3 py-1.5 rounded-lg font-bold shadow">
                                    <i class="bi bi-star-fill mr-1"></i>BARU
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- EMPTY STATE --}}
                @if($layanan->jenis->count() === 0 && count($jenisBaru) === 0)
                    <div class="text-center py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300">
                        <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 text-lg font-semibold">Belum ada jenis layanan</p>
                        <p class="text-gray-400 text-sm mt-1">Tambahkan jenis layanan untuk melanjutkan</p>
                    </div>
                @endif

            </div>

            {{-- BUTTON TAMBAH --}}
            <a href="{{ route('layanan.jenis.create', $layanan->id_layanan) }}"
               class="block mt-6 bg-yellow-400 hover:bg-yellow-500 active:scale-95 transition-all text-black 
                      text-center py-4 rounded-xl font-bold text-lg shadow-md hover:shadow-lg 
                      inline-flex items-center justify-center gap-2">
                <i class="bi bi-plus-circle-fill text-xl"></i> 
                <span>Tambah Jenis Layanan</span>
            </a>
        </div>

        {{-- SUBMIT --}}
        <div class="flex gap-3 justify-end mt-8 pt-6 border-t-2 border-gray-100">
            <a href="{{ route('layanan.index') }}"
               class="bg-gray-200 hover:bg-gray-300 active:scale-95 transition-all px-8 py-3 rounded-xl 
                      font-bold text-gray-700 text-lg shadow-md inline-flex items-center gap-2">
                <i class="bi bi-x-circle"></i>
                <span>Batal</span>
            </a>
            
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 active:scale-95 transition-all px-8 py-3 rounded-xl 
                           font-bold text-white text-lg shadow-lg hover:shadow-xl inline-flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span>Update Layanan</span>
            </button>
        </div>

    </form>

</div>

@endsection


@section('scripts')
<script>
// Auto-hide success message
window.addEventListener('DOMContentLoaded', function() {
    const successMsg = document.querySelector('.bg-green-500');
    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = 'opacity 0.5s ease-out';
            successMsg.style.opacity = '0';
            setTimeout(() => successMsg.remove(), 500);
        }, 5000);
    }
});

// Checkbox visual feedback
document.querySelectorAll('input[type="checkbox"][name="proses[]"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const label = this.closest('label');
        if (this.checked) {
            label.classList.add('bg-yellow-50', 'border-yellow-400', 'text-yellow-700');
            label.classList.remove('bg-white', 'border-gray-200');
        } else {
            label.classList.remove('bg-yellow-50', 'border-yellow-400', 'text-yellow-700');
            label.classList.add('bg-white', 'border-gray-200');
        }
    });
});
</script>
@endsection