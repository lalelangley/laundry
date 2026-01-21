@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('pesanan.online.detail', $pesanan->id_transaksi) }}"
           class="text-black text-2xl font-bold hover:opacity-70">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-black">Bukti Pembayaran</h1>
            <p class="text-sm text-gray-800">ORDER/{{ $pesanan->id_transaksi }}</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-6">

        {{-- ALERT SISA PEMBAYARAN --}}
        @php
            // ✅ AMBIL DATA PEMBAYARAN TERBARU
            $pembayaranTerbaru = $pesanan->pembayaran()->latest()->first();
            
            // ✅ HITUNG TOTAL DARI DATABASE PEMBAYARAN
            $totalDariPembayaran = $pesanan->pembayaran()->sum('nominal');
            $sisaPembayaran = $pesanan->total_harga - $totalDariPembayaran;
            $isLunas = $pesanan->status_bayar == 'lunas';
            
            // ✅ TENTUKAN METODE PEMBAYARAN UNTUK DITAMPILKAN
            $displayMetodeBayar = null;
            $sourceInfo = '';
            
            if ($pembayaranTerbaru && $pembayaranTerbaru->metodeBayar) {
                $displayMetodeBayar = $pembayaranTerbaru->metodeBayar;
                $sourceInfo = ' (dari pembayaran)';
            } elseif ($pesanan->metodeBayar) {
                $displayMetodeBayar = $pesanan->metodeBayar;
                $sourceInfo = ' (dari transaksi)';
            }
        @endphp

        @if($sisaPembayaran > 0 && !$isLunas)
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-6 shadow">
            <div class="flex items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-red-500 text-2xl mt-1"></i>
                <div class="flex-1">
                    <h3 class="font-bold text-red-800 text-lg">Sisa Pembayaran</h3>
                    <p class="text-red-700 text-sm mt-1">
                        Pelanggan masih memiliki sisa pembayaran yang harus dilunasi
                    </p>
                    <div class="mt-3 bg-white p-3 rounded-lg border border-red-200">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Harga:</span>
                            <span class="font-semibold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-gray-600">Sudah Dibayar:</span>
                            <span class="font-semibold text-green-600">Rp {{ number_format($totalDariPembayaran, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between items-center">
                            <span class="text-red-700 font-bold">Sisa:</span>
                            <span class="font-bold text-red-700 text-xl">Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($isLunas)
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mb-6 shadow">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-green-500 text-2xl"></i>
                <div>
                    <h3 class="font-bold text-green-800 text-lg">Pembayaran Lunas</h3>
                    <p class="text-green-700 text-sm">Pesanan ini sudah lunas dibayar.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- INFO PESANAN --}}
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-yellow-600"></i> Informasi Pesanan
            </h2>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Pelanggan</p>
                    <p class="font-semibold">{{ $pesanan->nama_pelanggan }}</p>
                    <p class="text-sm text-gray-600">{{ $pesanan->no_hp }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Metode Pembayaran</p>
                    
                    @if($displayMetodeBayar)
                        <p class="font-semibold">{{ $displayMetodeBayar->nama_metode_bayar }}</p>
                        <p class="text-xs text-gray-400">{{ $sourceInfo }}</p>
                    @elseif($pembayaranTerbaru && $pembayaranTerbaru->id_metode_bayar)
                        <p class="font-semibold text-orange-600">ID: {{ $pembayaranTerbaru->id_metode_bayar }} (Data tidak ditemukan)</p>
                    @elseif($pesanan->id_metode_bayar)
                        <p class="font-semibold text-orange-600">ID: {{ $pesanan->id_metode_bayar }} (Data tidak ditemukan)</p>
                    @else
                        <p class="font-semibold text-red-600">Belum ditentukan</p>
                    @endif
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Total Tagihan</p>
                    <p class="font-bold text-yellow-600 text-lg">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Status Pembayaran</p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                        @if($pesanan->status_bayar == 'lunas') bg-green-500 text-white
                        @elseif($pesanan->status_bayar == 'DP') bg-blue-500 text-white
                        @else bg-red-500 text-white
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $pesanan->status_bayar)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- FORM UPLOAD BUKTI PEMBAYARAN - DISABLED IF LUNAS --}}
        @if(!$isLunas)
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                <i class="bi bi-receipt text-yellow-600"></i> 
                Upload Bukti Pembayaran
            </h2>

            <form action="{{ route('pesanan.online.simpan-bukti-pembayaran', $pesanan->id_transaksi) }}" 
                method="POST" 
                enctype="multipart/form-data"
                id="formBuktiPembayaran">
                @csrf
                @method('PUT')

                @php
                    // ✅ GUNAKAN $pembayaranTerbaru yang sudah didefinisikan di atas
                    // Jika ada pembayaran, gunakan metode dari pembayaran
                    // Jika tidak ada, gunakan dari transaksi
                    if ($pembayaranTerbaru && $pembayaranTerbaru->id_metode_bayar) {
                        $metodeBayar = $pembayaranTerbaru->metodeBayar;
                    } else {
                        $metodeBayar = $pesanan->metodeBayar;
                    }
                    
                    // Debug info
                    $debugMetodeBayar = [
                        'transaksi_id_metode' => $pesanan->id_metode_bayar ?? 'NULL',
                        'pembayaran_terbaru_id' => $pembayaranTerbaru ? $pembayaranTerbaru->id_metode_bayar : 'NULL',
                        'metode_bayar_found' => $metodeBayar ? 'YES' : 'NO',
                        'nama_metode' => $metodeBayar ? $metodeBayar->nama_metode_bayar : 'NULL'
                    ];
                    
                    // Deteksi cash dengan pengecekan null yang lebih baik
                    $isCash = false;
                    if ($metodeBayar && isset($metodeBayar->nama_metode_bayar)) {
                        $namaMetode = strtolower($metodeBayar->nama_metode_bayar);
                        $isCash = (stripos($namaMetode, 'cash') !== false || 
                                   stripos($namaMetode, 'tunai') !== false);
                    }
                    
                    // CEK APAKAH CUSTOMER SUDAH UPLOAD BUKTI PEMBAYARAN
                    $buktiBayarCustomer = $pesanan->pembayaran()->whereNotNull('foto_bukti')->latest()->first();
                @endphp

                {{-- DEBUG INFO (hapus setelah selesai debugging) --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 text-xs">
                    <p class="font-bold text-yellow-800 mb-2">Debug Info:</p>
                    <pre class="text-yellow-700">{{ json_encode($debugMetodeBayar, JSON_PRETTY_PRINT) }}</pre>
                </div>

                {{-- TIPE PEMBAYARAN --}}
                @if($isCash)
                    <input type="hidden" name="tipe_pembayaran" value="lunas">
                    <input type="hidden" name="nominal_bayar" value="{{ $sisaPembayaran }}">
                    
                    <div class="bg-green-50 border border-green-200 p-4 rounded-lg mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="bi bi-cash-stack text-2xl text-white"></i>
                            </div>
                            <div>
                                <p class="font-bold text-green-800">Pembayaran Cash (Tunai)</p>
                                <p class="text-sm text-green-700">Pelanggan membayar secara tunai kepada kurir saat cucian diterima</p>
                                <p class="text-sm text-green-700 font-semibold mt-2">
                                    Nominal: Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Tipe Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="tipe_pembayaran" value="dp" id="dpPayment" 
                                       class="w-4 h-4 text-blue-600" 
                                       onclick="toggleTipePembayaran('dp')" required>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-800">DP (Down Payment)</p>
                                    <p class="text-sm text-gray-600">Pembayaran sebagian, sisanya dibayar kemudian</p>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition">
                                <input type="radio" name="tipe_pembayaran" value="lunas" id="lunasPayment" 
                                       class="w-4 h-4 text-green-600" 
                                       onclick="toggleTipePembayaran('lunas')" required>
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-800">Lunas</p>
                                    <p class="text-sm text-gray-600">Pembayaran penuh (Rp {{ number_format($sisaPembayaran, 0, ',', '.') }})</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="nominalSection" class="hidden mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nominal Pembayaran DP <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                            <input type="number" 
                                   name="nominal_bayar" 
                                   id="nominalBayar" 
                                   class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0"
                                   min="1"
                                   max="{{ $sisaPembayaran }}">
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            <i class="bi bi-info-circle"></i> Maksimal: Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}
                        </p>
                    </div>
                @endif

                {{-- UPLOAD FOTO BUKTI TRANSFER --}}
                @if(!$isCash)
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Foto Bukti Transfer <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-yellow-500 transition">
                        <input type="file" 
                            name="foto_bukti_bayar" 
                            id="fotoBuktiBayar"
                            accept="image/jpeg,image/jpg,image/png"
                            class="hidden"
                            onchange="previewImage(this)"
                            required>
                        
                        <label for="fotoBuktiBayar" class="cursor-pointer">
                            <div id="uploadPlaceholder">
                                <i class="bi bi-cloud-upload text-5xl text-gray-400 mb-3"></i>
                                <p class="font-semibold text-gray-700">Klik untuk upload foto</p>
                                <p class="text-sm text-gray-500 mt-1">JPG, JPEG, PNG - Maksimal 2MB</p>
                            </div>
                            
                            <div id="imagePreviewContainer" class="hidden">
                                <img id="imagePreview" src="" alt="Preview" class="max-w-xs mx-auto rounded-lg border-2 border-yellow-500">
                                <p class="text-sm text-yellow-600 mt-2 font-semibold">
                                    <i class="bi bi-check-circle-fill"></i> Foto siap diupload
                                </p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="mt-2 flex items-start gap-2 text-sm">
                        <i class="bi bi-info-circle text-blue-500 mt-0.5"></i>
                        <p class="text-gray-600">
                            Upload bukti transfer dari bank atau e-wallet untuk verifikasi pembayaran
                        </p>
                    </div>
                </div>
                @else
                {{-- CASH: Tidak perlu upload foto --}}
                <div class="mb-6 bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <div class="flex items-start gap-2">
                        <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-1">Pembayaran Cash</p>
                            <p>Untuk pembayaran cash, upload bukti pembayaran tidak diperlukan. Cukup konfirmasi bahwa pembayaran telah diterima dari pelanggan.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- KETERANGAN --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Keterangan (Opsional)
                    </label>
                    <textarea name="keterangan_bayar" 
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent resize-none"
                            placeholder="Tambahkan catatan pembayaran jika ada...">{{ old('keterangan_bayar') }}</textarea>
                </div>

                {{-- BUTTONS --}}
                <div class="flex gap-3">
                    <a href="{{ route('pesanan.online.detail', $pesanan->id_transaksi) }}"
                    class="flex-1 py-3 border-2 border-gray-300 rounded-lg hover:bg-gray-50 font-semibold text-center transition">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit"
                            class="flex-1 py-3 bg-yellow-400 text-black rounded-lg hover:bg-yellow-500 font-semibold shadow-md transition">
                        <i class="bi bi-save"></i> 
                        @if($isCash)
                            Konfirmasi Pembayaran Cash
                        @else
                            Simpan Bukti Pembayaran
                        @endif
                    </button>
                </div>
            </form>
        </div>
        @else
        {{-- PESAN JIKA SUDAH LUNAS --}}
        <div class="bg-white rounded-xl shadow p-6">
            <div class="text-center py-8">
                <i class="bi bi-check-circle-fill text-green-500 text-6xl mb-4"></i>
                <h3 class="font-bold text-xl text-gray-800 mb-2">Pembayaran Sudah Lunas</h3>
                <p class="text-gray-600 mb-6">Pesanan ini sudah dibayar penuh. Anda tidak perlu mengunggah bukti pembayaran lagi.</p>
                <a href="{{ route('pesanan.online.detail', $pesanan->id_transaksi) }}"
                   class="inline-block px-6 py-3 bg-yellow-400 text-black rounded-lg hover:bg-yellow-500 font-semibold shadow-md transition">
                    <i class="bi bi-arrow-left"></i> Kembali ke Detail Pesanan
                </a>
            </div>
        </div>
        @endif

        {{-- RIWAYAT PEMBAYARAN (Jika Ada) --}}
        @if($pesanan->pembayaran && $pesanan->pembayaran->count() > 0)
        <div class="bg-white rounded-xl shadow p-6 mt-6">
            <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                <i class="bi bi-clock-history text-yellow-600"></i> Riwayat Pembayaran
            </h2>

            <div class="space-y-3">
                @foreach($pesanan->pembayaran()->orderBy('tanggal_bayar', 'desc')->get() as $bayar)
                <div class="bg-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-50 border border-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-200 p-4 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <p class="font-semibold text-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-800">
                                    {{ $bayar->tipe_pembayaran == 'lunas' ? 'Pelunasan' : 'DP (Down Payment)' }}
                                </p>
                                <span class="inline-block px-2 py-1 bg-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-500 text-white text-xs rounded-full">
                                    {{ strtoupper($bayar->tipe_pembayaran) }}
                                </span>
                            </div>
                            <p class="text-sm text-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-600 mb-2">
                                <i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->format('d M Y') }} 
                                <span class="text-gray-500">• {{ $bayar->created_at->format('H:i') }}</span>
                            </p>
                            
                            @if($bayar->keterangan)
                            <p class="text-sm text-gray-600 mt-2 bg-white p-2 rounded border">
                                <i class="bi bi-chat-left-text"></i> {{ $bayar->keterangan }}
                            </p>
                            @endif

                            @if($bayar->foto_bukti)
                            <div class="mt-3">
                                <button type="button" 
                                        onclick="showFotoBukti('{{ asset('storage/' . $bayar->foto_bukti) }}')"
                                        class="text-sm text-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-600 hover:text-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-800 font-semibold flex items-center gap-1">
                                    <i class="bi bi-image"></i> Lihat Bukti Transfer
                                </button>
                            </div>
                            @endif
                        </div>
                        
                        <div class="text-right ml-4">
                            <p class="font-bold text-{{ $bayar->tipe_pembayaran == 'lunas' ? 'green' : 'blue' }}-800 text-xl">
                                Rp {{ number_format($bayar->nominal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- TOTAL SUMMARY --}}
                <div class="bg-gray-50 border-2 border-gray-300 p-4 rounded-lg mt-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-gray-800 text-lg">Total Sudah Dibayar</p>
                            @if($sisaPembayaran > 0)
                            <p class="text-sm text-red-600 mt-1">
                                <i class="bi bi-exclamation-circle"></i> Sisa: Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}
                            </p>
                            @else
                            <p class="text-sm text-green-600 mt-1">
                                <i class="bi bi-check-circle-fill"></i> Lunas
                            </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800 text-2xl">Rp {{ number_format($totalDariPembayaran, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-500">dari Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL LIHAT FOTO BUKTI --}}
<div id="fotoBuktiModal" class="fixed inset-0 bg-black/90 flex items-center justify-center px-4 z-[9999] hidden" onclick="closeFotoBukti()">
    <div class="relative max-w-4xl w-full">
        <button onclick="closeFotoBukti()" class="absolute -top-12 right-0 text-white text-4xl hover:text-gray-300 transition">
            <i class="bi bi-x-circle"></i>
        </button>
        <img id="fotoBuktiImage" src="" alt="Bukti Transfer" class="w-full h-auto rounded-lg shadow-2xl">
    </div>
</div>

<script>
function toggleTipePembayaran(tipe) {
    const nominalSection = document.getElementById('nominalSection');
    const nominalInput = document.getElementById('nominalBayar');
    
    if (tipe === 'dp') {
        nominalSection.classList.remove('hidden');
        nominalInput.required = true;
        nominalInput.value = '';
    } else {
        nominalSection.classList.add('hidden');
        nominalInput.required = false;
        nominalInput.value = '{{ $sisaPembayaran }}';
    }
}

function previewImage(input) {
    const file = input.files[0];
    
    if (file) {
        // Validasi ukuran (max 2MB)
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            input.value = '';
            return;
        }

        // Validasi tipe
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
            input.value = '';
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('uploadPlaceholder').classList.add('hidden');
            document.getElementById('imagePreviewContainer').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function showFotoBukti(imageUrl) {
    document.getElementById('fotoBuktiImage').src = imageUrl;
    document.getElementById('fotoBuktiModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFotoBukti() {
    document.getElementById('fotoBuktiModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFotoBukti();
    }
});

// Validation before submit
document.getElementById('formBuktiPembayaran')?.addEventListener('submit', function(e) {
    const tipePembayaran = document.querySelector('input[name="tipe_pembayaran"]:checked');
    
    if (!tipePembayaran) {
        e.preventDefault();
        alert('Silakan pilih tipe pembayaran terlebih dahulu!');
        return false;
    }
    
    if (tipePembayaran.value === 'dp') {
        const nominal = document.getElementById('nominalBayar').value;
        const maxNominal = {{ $sisaPembayaran }};
        
        if (!nominal || nominal <= 0) {
            e.preventDefault();
            alert('Silakan masukkan nominal pembayaran DP!');
            return false;
        }
        
        if (parseFloat(nominal) > maxNominal) {
            e.preventDefault();
            alert('Nominal DP tidak boleh lebih dari sisa pembayaran (Rp ' + maxNominal.toLocaleString('id-ID') + ')');
            return false;
        }
    }
    
    return true;
});
</script>

@endsection