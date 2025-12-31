@extends('layouts.master')

@section('content')

{{-- DEBUG INFO (Remove after testing) --}}
@if(config('app.debug'))
<div class="bg-blue-100 border-2 border-blue-300 p-4 rounded-xl mb-5 mx-5">
    <h4 class="font-bold text-blue-900 mb-2">🔍 DEBUG INFO:</h4>
    <div class="text-sm text-blue-800 space-y-1">
        <p><strong>Jenis ID:</strong> {{ isset($jenis) ? $jenis->id_jenis_layanan : '❌ Variable $jenis NOT SET' }}</p>
        <p><strong>Nama Jenis:</strong> {{ isset($jenis) ? $jenis->nama_jenis : '❌ NOT SET' }}</p>
        <p><strong>Harga:</strong> {{ isset($jenis) ? 'Rp ' . number_format($jenis->harga) : '❌ NOT SET' }}</p>
        <p><strong>Satuan ID:</strong> {{ isset($jenis) ? $jenis->id_satuan : '❌ NOT SET' }}</p>
        <p><strong>From:</strong> {{ request('from') ?? '❌ NOT SET' }}</p>
        <p><strong>Satuan Count:</strong> {{ isset($satuan) ? count($satuan) : '❌ Variable $satuan NOT SET' }}</p>
    </div>
</div>
@endif

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('admin2.layanan.edit', request('from', 0)) }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-bold">Ubah Jenis Layanan</span>
</div>

<div class="px-5 mt-6 mb-10">

    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-xl mb-5">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Check if $jenis exists --}}
    @if(!isset($jenis))
        <div class="bg-red-500 text-white p-4 rounded-xl mb-5">
            <h4 class="font-bold mb-2">❌ ERROR: Variable $jenis tidak ditemukan!</h4>
            <p>Pastikan method controller mengirim variable 'jenis' ke view.</p>
        </div>
    @else

    <form action="{{ route('admin2.layanan.jenis.update', $jenis->id_jenis_layanan ?? 0) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-6 rounded-2xl shadow-lg space-y-5">
        @csrf
        @method('PUT')

        <input type="hidden" name="from" value="{{ request('from', $jenis->id_layanan ?? 0) }}">

        {{-- Upload Gambar --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-2">
                <i class="bi bi-image me-1"></i>Gambar Jenis Layanan
            </label>

            {{-- Preview Container --}}
            <div class="mb-3">
                @if(!empty($jenis->gambar))
                    <img id="preview" 
                         src="{{ asset('images/jenis/' . $jenis->gambar) }}"
                         alt="Preview" 
                         class="w-full max-w-xs h-48 object-cover rounded-xl border-2 border-gray-200"
                         onerror="this.src='{{ asset('images/default.png') }}'">
                @else
                    <img id="preview" 
                         src="{{ asset('images/default.png') }}"
                         alt="Preview" 
                         class="w-full max-w-xs h-48 object-cover rounded-xl border-2 border-gray-200">
                @endif
            </div>

            {{-- Upload Button --}}
            <div class="relative">
                <input type="file" 
                       name="gambar" 
                       id="gambar"
                       accept="image/*"
                       class="hidden"
                       onchange="previewImage(event)">
                
                <label for="gambar" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 rounded-xl cursor-pointer transition">
                    <i class="bi bi-camera-fill text-xl"></i>
                    <span class="font-semibold">Ganti Gambar</span>
                </label>
                <span id="fileName" class="ml-3 text-sm text-gray-600"></span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, JPEG, GIF, WEBP (Max: 2MB)</p>
        </div>

        {{-- Nama Jenis --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-2">
                <i class="bi bi-tag me-1"></i>Nama Jenis Layanan *
            </label>
            <input type="text" 
                   name="nama_jenis" 
                   value="{{ old('nama_jenis', $jenis->nama_jenis ?? '') }}"
                   class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                   placeholder="Contoh: Cuci Kering"
                   required>
        </div>

        {{-- Satuan --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-2">
                <i class="bi bi-rulers me-1"></i>Satuan *
            </label>
            <select name="id_satuan"
                    class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                    required>
                <option value="">-- Pilih Satuan --</option>
                @if(isset($satuan))
                    @foreach($satuan as $s)
                        <option value="{{ $s->id_satuan }}" 
                                {{ old('id_satuan', $jenis->id_satuan ?? '') == $s->id_satuan ? 'selected' : '' }}>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                @else
                    <option value="" disabled>❌ Data satuan tidak tersedia</option>
                @endif
            </select>
        </div>

        {{-- Harga --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-2">
                <i class="bi bi-currency-dollar me-1"></i>Harga *
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="number" 
                       name="harga"
                       value="{{ old('harga', $jenis->harga ?? 0) }}"
                       class="w-full p-3 pl-12 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                       placeholder="0"
                       min="0"
                       required>
            </div>
        </div>

        {{-- Lama Pengerjaan --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="font-semibold text-gray-700 block mb-2">
                    <i class="bi bi-clock me-1"></i>Lama Pengerjaan *
                </label>
                <input type="number" 
                       name="lama"
                       value="{{ old('lama', $jenis->lama ?? 0) }}"
                       class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                       placeholder="0"
                       min="0"
                       required>
            </div>
            <div>
                <label class="font-semibold text-gray-700 block mb-2">Satuan Waktu *</label>
                <select name="lama_satuan"
                        class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                        required>
                    <option value="">-- Pilih --</option>
                    <option value="Jam" {{ old('lama_satuan', $jenis->lama_satuan ?? '') == 'Jam' ? 'selected' : '' }}>Jam</option>
                    <option value="Hari" {{ old('lama_satuan', $jenis->lama_satuan ?? '') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Minggu" {{ old('lama_satuan', $jenis->lama_satuan ?? '') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                </select>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-2">
                <i class="bi bi-chat-left-text me-1"></i>Keterangan (Opsional)
            </label>
            <textarea name="keterangan"
                      rows="3"
                      class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                      placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan', $jenis->keterangan ?? '') }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="flex justify-between items-center mt-6">
            <a href="{{ route('admin2.layanan.edit', request('from', $jenis->id_layanan ?? 0)) }}"
               class="px-6 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                <i class="bi bi-x-circle"></i>
                <span>Batal</span>
            </a>
            <button type="submit"
                    class="px-8 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold transition inline-flex items-center gap-2 shadow-lg">
                <i class="bi bi-check-circle-fill"></i>
                <span>Update Jenis</span>
            </button>
        </div>
    </form>

    @endif

</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const fileName = document.getElementById('fileName');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(file);
        fileName.textContent = file.name;
    } else {
        fileName.textContent = '';
    }
}
</script>

@endsection