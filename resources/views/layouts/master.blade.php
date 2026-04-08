<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/layouts/master.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $title ?? 'Dashboard')</title>
    <!-- Versi terbaru Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
      crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @stack('styles')   {{-- ← TAMBAH INI --}}
    <!-- FE-DOC: Blok CSS khusus halaman ini. -->
    <style>
        body { 
            font-family: 'Poppins', sans-serif;
        }
        
        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #FACC15;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Page content fade in */
        .page-content {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .page-content.show {
            opacity: 1;
        }
    </style>
</head>

<body class="bg-gray-100">
    {{-- 🔄 GLOBAL LOADING --}}
    <div class="loading-overlay" id="loading">
        <div class="spinner"></div>
    </div>

    {{-- Sidebar --}}
    @include('layouts.sidebar')
    

    {{-- Main Content --}}
    <div class="min-h-screen relative z-[1] page-content">
        @yield('content')
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
        {{-- ✅ PASS USER PERMISSIONS TO JAVASCRIPT --}}
    @php
        $permissions = [
            'parfum' => canDelete('parfum'),
            'satuan' => canDelete('satuan'),
            'pelanggan' => canDelete('pelanggan'),
            'transaksi' => canDelete('transaksi'),
            'pengeluaran' => canDelete('pengeluaran'),
            'layanan' => canDelete('layanan'),
            'jenis' => canDelete('jenis'),
        ];
    @endphp

    <!-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. -->

    <script>
        window.userPermissions = @json($permissions);
    </script>
    
    {{-- ============================================
         🗑️ REUSABLE DELETE CONFIRMATION FUNCTION
         ============================================ --}}
    <script>
    /**
     * 🗑️ REUSABLE DELETE CONFIRMATION
     * ✅ AUTO CEK PERMISSION dari layout - Tidak perlu attribute tambahan di blade
     * 
     * CARA PAKAI:
     * 
     * 1. SIMPLE:
     *    <button onclick="confirmDelete(this, 'parfum')" data-nama="Lavender">Hapus</button>
     * 
     * 2. DENGAN DETAIL:
     *    <button onclick="confirmDelete(this, 'parfum')" 
     *            data-nama="Lavender" 
     *            data-harga="Rp 50.000">Hapus</button>
     * 
     * Types: parfum, satuan, pelanggan, transaksi, pengeluaran, layanan, jenis
     */
    function confirmDelete(button, type = 'item') {
        const form = button.closest('form');
        if (!form) {
            console.error('Form not found!');
            return;
        }
        
    // ✅ AUTO-CHECK PERMISSION dari window.userPermissions
    if (window.userPermissions && window.userPermissions[type] === false) {
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak!',
            html: `
                <div class="text-center">
                    <p class="text-gray-700 text-lg mb-4">
                        Anda tidak memiliki izin untuk menghapus data ini.
                    </p>
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-4 mx-auto max-w-md">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-info-circle-fill text-red-500 text-lg flex-shrink-0 mt-0.5"></i>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-red-800 mb-1">Butuh Akses?</p>
                                <p class="text-xs text-red-700">
                                    Hubungi administrator sistem untuk mendapatkan izin penghapusan data.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Mengerti',
            width: '500px',
            backdrop: `rgba(0,0,0,0.4)`,
            customClass: {
                popup: 'rounded-3xl shadow-2xl',
                confirmButton: 'rounded-xl px-8 py-3 font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200'
            },
            showClass: {
                popup: 'animate__animated animate__fadeIn animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut animate__faster'
            }
        });
        return; // Stop execution
    }
        
        // Get data from attributes
        const nama = button.getAttribute('data-nama') || 'Item ini';
        const harga = button.getAttribute('data-harga');
        const tanggal = button.getAttribute('data-tanggal');
        const email = button.getAttribute('data-email');
        const noHp = button.getAttribute('data-nohp');
        const id = button.getAttribute('data-id');
        
        // Define type configurations
        const typeConfig = {
            parfum: {
                title: 'Hapus Parfum?',
                icon: 'bi-flower1',
                iconColor: 'text-pink-600',
                bgColor: 'from-pink-50 to-rose-50',
                borderColor: 'border-pink-200',
                highlightColor: 'text-pink-600'
            },
            satuan: {
                title: 'Hapus Satuan?',
                icon: 'bi-basket3',
                iconColor: 'text-yellow-600',
                bgColor: 'from-yellow-50 to-orange-50',
                borderColor: 'border-yellow-200',
                highlightColor: 'text-yellow-600'
            },
            pelanggan: {
                title: 'Hapus Pelanggan?',
                icon: 'bi-person-fill',
                iconColor: 'text-blue-600',
                bgColor: 'from-blue-50 to-indigo-50',
                borderColor: 'border-blue-200',
                highlightColor: 'text-blue-600'
            },
            transaksi: {
                title: 'Hapus Transaksi?',
                icon: 'bi-receipt-cutoff',
                iconColor: 'text-red-600',
                bgColor: 'from-red-50 to-orange-50',
                borderColor: 'border-red-200',
                highlightColor: 'text-red-600'
            },
            pengeluaran: {
                title: 'Hapus Pengeluaran?',
                icon: 'bi-cash-coin',
                iconColor: 'text-yellow-600',
                bgColor: 'from-yellow-50 to-orange-50',
                borderColor: 'border-yellow-200',
                highlightColor: 'text-yellow-600'
            },
            layanan: {
                title: 'Hapus Layanan?',
                icon: 'bi-gear-fill',
                iconColor: 'text-purple-600',
                bgColor: 'from-purple-50 to-pink-50',
                borderColor: 'border-purple-200',
                highlightColor: 'text-purple-600'
            },
            jenis: {
                title: 'Hapus Jenis Layanan?',
                icon: 'bi-tags-fill',
                iconColor: 'text-indigo-600',
                bgColor: 'from-indigo-50 to-blue-50',
                borderColor: 'border-indigo-200',
                highlightColor: 'text-indigo-600'
            },
            item: {
                title: 'Hapus Data?',
                icon: 'bi-trash-fill',
                iconColor: 'text-red-600',
                bgColor: 'from-red-50 to-orange-50',
                borderColor: 'border-red-200',
                highlightColor: 'text-red-600'
            }
        };
        
        const config = typeConfig[type] || typeConfig.item;
        
        // Build detail HTML
        let detailsHTML = `
            <div class="flex items-center gap-2 mb-2">
                <i class="bi ${config.icon} ${config.iconColor} text-xl"></i>
                <p class="font-bold ${config.highlightColor} text-lg">${nama}</p>
            </div>
        `;
        
        // Add additional details if available
        if (id) {
            detailsHTML += `<p class="text-sm text-gray-600"><i class="bi bi-hash"></i> ID: ${id}</p>`;
        }
        if (email) {
            detailsHTML += `<p class="text-sm text-gray-600"><i class="bi bi-envelope"></i> ${email}</p>`;
        }
        if (noHp) {
            detailsHTML += `<p class="text-sm text-gray-600"><i class="bi bi-telephone"></i> ${noHp}</p>`;
        }
        if (tanggal) {
            detailsHTML += `<p class="text-sm text-gray-600"><i class="bi bi-calendar3"></i> ${tanggal}</p>`;
        }
        if (harga) {
            detailsHTML += `<p class="font-bold ${config.highlightColor} text-lg mt-1"><i class="bi bi-cash-coin"></i> ${harga}</p>`;
        }
        
        // Show SweetAlert
        Swal.fire({
            title: config.title,
            html: `
                <div class="text-left">
                    <p class="text-gray-600 mb-3">Apakah Anda yakin ingin menghapus data berikut?</p>
                    <div class="bg-gradient-to-r ${config.bgColor} border-2 ${config.borderColor} rounded-xl p-4 my-4 shadow-sm">
                        ${detailsHTML}
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3">
                        <p class="text-sm text-blue-700 flex items-start gap-2">
                            <i class="bi bi-exclamation-circle text-blue-500 text-lg mt-0.5"></i>
                            <span>Data yang sudah dihapus tidak dapat dikembalikan.</span>
                        </p>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-trash-fill me-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Batal',
            reverseButtons: true,
            width: '550px',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:shadow-xl',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            },
            backdrop: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus Data...',
                    html: `
                        <div class="flex flex-col items-center gap-3">
                            <i class="bi bi-hourglass-split text-5xl text-yellow-500 animate-pulse"></i>
                            <p class="text-gray-600">Mohon tunggu sebentar</p>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                form.submit();
            }
        });
    }
    </script>
    
    {{-- GLOBAL LOADING SCRIPT - FIXED --}}
    <script>
        (function () {
            const loading = document.getElementById('loading');
            const pageContent = document.querySelector('.page-content');
            
            // === SHOW LOADING SAAT MAU PINDAH HALAMAN ===
            function showLoading() {
                if (loading) {
                    loading.classList.add('show');
                }
                if (pageContent) {
                    pageContent.classList.remove('show');
                }
            }
            
            function hideLoading() {
                if (loading) {
                    loading.classList.remove('show');
                }
                if (pageContent) {
                    pageContent.classList.add('show');
                }
            }

            // ✅ PERBAIKAN: Skip form yang download file (Excel, PDF, dll)
            document.addEventListener('submit', function (e) {
                if (e.target.tagName === 'FORM') {
                    const form = e.target;
                    
                    // ✅ Daftar form yang TIDAK perlu loading overlay
                    const skipLoadingForms = [
                        'data-ajax',           // Form AJAX
                        'data-no-loading',     // Form dengan atribut khusus
                        'data-download',       // Form download
                    ];
                    
                    // Cek apakah form ini adalah form download/export
                    const isDownloadForm = skipLoadingForms.some(attr => form.hasAttribute(attr)) ||
                                         form.id === 'formBayar' ||
                                         form.classList.contains('ajax-form') ||
                                         form.action.includes('/export') ||
                                         form.action.includes('/download') ||
                                         form.action.includes('/print');
                    
                    // ✅ Jangan show loading untuk download/export form
                    if (!isDownloadForm) {
                        showLoading();
                    }
                }
            }, true);

            // Input date auto submit
            document.addEventListener('change', function (e) {
                if (e.target.type === 'date') {
                    showLoading();
                    e.target.form?.submit();
                }
            }, true);

            // Intercept all link clicks for smooth transition
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (link && !link.hasAttribute('target') && link.href.startsWith(window.location.origin)) {
                    let href = link.getAttribute('href');
                    
                    // Skip hash links, javascript: links, and download links
                    if (href && 
                        href !== '#' && 
                        !href.startsWith('javascript:') &&
                        !link.hasAttribute('download') &&
                        !link.classList.contains('no-loading')) {
                        
                        e.preventDefault();
                        
                        if (link.href.includes('/layanan')) {
                            const url = new URL(link.href);
                            const fromSidebar = link.closest('#sidebar') !== null;
                            
                            if (fromSidebar && !href.includes('from=')) {
                                url.search = '';
                                href = url.pathname;
                            }
                        }
                        
                        showLoading();
                        setTimeout(() => {
                            window.location.href = href;
                        }, 100);
                    }
                }
            }, true);

            // ✅ PERBAIKAN: Hide loading jika halaman sudah loaded
            window.addEventListener('pageshow', () => {
                setTimeout(() => {
                    hideLoading();
                }, 50);
            });

            // Initial page load
            if (document.readyState === 'complete') {
                hideLoading();
            } else {
                window.addEventListener('load', () => {
                    hideLoading();
                });
            }
            
            // ✅ Fallback: Hide loading setelah 10 detik (jika stuck)
            setTimeout(() => {
                hideLoading();
            }, 10000);
        })();
    </script>
    
    {{-- ===============================================
         FLASH MESSAGE HANDLER dengan SweetAlert2
         =============================================== --}}

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#10b981',
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:scale-105 transition-transform'
            }
        });
    </script>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK',
            showClass: {
                popup: 'animate__animated animate__shakeX'
            },
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:scale-105 transition-transform'
            }
        });
    </script>
    @endif

    {{-- WARNING MESSAGE --}}
    @if(session('warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: '{{ session('warning') }}',
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:scale-105 transition-transform'
            }
        });
    </script>
    @endif

    {{-- INFO MESSAGE --}}
    @if(session('info'))
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: '{{ session('info') }}',
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:scale-105 transition-transform'
            }
        });
    </script>
    @endif

    {{-- ===============================================
         🌐 GLOBAL FETCH ERROR HANDLER
         Handles common HTTP errors (401, 403, 500, etc.)
         =============================================== --}}
    <script>
    /**
     * ✅ GLOBAL FETCH ERROR HANDLER
     * Automatically handles common HTTP errors with user-friendly messages
     * 
     * CARA PAKAI:
     * 
     * const res = await fetch(url, options);
     * const handled = await handleFetchError(res);
     * if (handled) return; // Error sudah ditangani
     * 
     * // Lanjut proses normal
     * const data = await res.json();
     */
    window.handleFetchError = async function(response, customMessages = {}) {
        const defaultMessages = {
            401: {
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir. Silakan login kembali.',
                icon: 'warning',
                showConfirmButton: true,
                timer: 3000,
                callback: () => {
                    setTimeout(() => {
                        window.location.href = "{{ route('login') ?? '/login' }}";
                    }, 2000);
                }
            },
            403: {
                title: 'Akses Ditolak',
                text: 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            },
            404: {
                title: 'Tidak Ditemukan',
                text: 'Data atau halaman yang Anda cari tidak ditemukan.',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            },
            422: {
                title: 'Validasi Gagal',
                text: 'Data yang Anda masukkan tidak valid. Silakan periksa kembali.',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            },
            429: {
                title: 'Terlalu Banyak Permintaan',
                text: 'Anda melakukan terlalu banyak permintaan. Silakan tunggu sebentar.',
                icon: 'warning',
                confirmButtonColor: '#f59e0b'
            },
            500: {
                title: 'Kesalahan Server',
                text: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            },
            502: {
                title: 'Server Tidak Tersedia',
                text: 'Server sedang tidak tersedia. Silakan coba lagi nanti.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            },
            503: {
                title: 'Layanan Tidak Tersedia',
                text: 'Layanan sedang dalam pemeliharaan. Silakan coba lagi nanti.',
                icon: 'info',
                confirmButtonColor: '#3b82f6'
            }
        };

        // Jika response OK, tidak ada error
        if (response.ok) {
            return false;
        }

        const status = response.status;
        const config = customMessages[status] || defaultMessages[status];

        // Jika tidak ada config untuk status ini, return false
        if (!config) {
            return false;
        }

        // Tampilkan SweetAlert
        const result = await Swal.fire({
            title: config.title,
            text: config.text,
            icon: config.icon,
            confirmButtonColor: config.confirmButtonColor || '#3b82f6',
            confirmButtonText: config.confirmButtonText || 'OK',
            timer: config.timer,
            timerProgressBar: config.timer ? true : false,
            showConfirmButton: config.showConfirmButton !== false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg'
            }
        });

        // Execute callback if exists
        if (config.callback) {
            config.callback(result);
        }

        return true; // Error sudah ditangani
    };

    /**
     * ✅ FETCH WRAPPER WITH AUTO ERROR HANDLING
     * 
     * CARA PAKAI (Recommended):
     * 
     * const data = await fetchWithErrorHandling(url, {
     *     method: 'POST',
     *     body: formData
     * });
     * 
     * if (!data) return; // Error sudah ditangani otomatis
     * 
     * // Lanjut proses data
     * console.log(data);
     */
    window.fetchWithErrorHandling = async function(url, options = {}, customMessages = {}) {
        try {
            const response = await fetch(url, options);
            
            // Handle HTTP errors
            const errorHandled = await handleFetchError(response, customMessages);
            if (errorHandled) {
                return null;
            }

            // Parse response
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
            
        } catch (error) {
            console.error('Fetch error:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Koneksi',
                text: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg'
                }
            });
            
            return null;
        }
    };
    </script>

    {{-- Script tambahan dari halaman --}}
    @yield('scripts')

    @stack('scripts')


</body>
</html>
