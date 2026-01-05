<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
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
    <link rel="stylesheet" href="{{ asset('css/master-layout-improved.css') }}">
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
    
    {{-- Script tambahan dari halaman --}}
    @yield('scripts')

    @stack('scripts')

</body>
</html>