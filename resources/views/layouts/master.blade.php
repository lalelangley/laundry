<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<<<<<<< HEAD
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Google Font: Poppins --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>

=======
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
>>>>>>> c842378ffa117087b054c4c9d4728216779bf064
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/master-layout-improved.css') }}">
    <style>
        body { 
            font-family: 'Poppins', sans-serif;
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
<<<<<<< HEAD
    <div class="min-h-screen relative z-[1]">
=======
    <div class="min-h-screen relative z-[1] page-content">
>>>>>>> c842378ffa117087b054c4c9d4728216779bf064
        @yield('content')
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- GLOBAL LOADING SCRIPT --}}
    <script>
        (function () {
            const loading = document.getElementById('loading');
            const pageContent = document.querySelector('.page-content');
            
            // === SHOW LOADING SAAT MAU PINDAH HALAMAN ===
            function showLoading() {
                loading?.classList.add('show');
                pageContent?.classList.remove('show');
            }

            // Semua submit form
            document.addEventListener('submit', function (e) {
                if (e.target.tagName === 'FORM') {
                    showLoading();
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
                    const href = link.getAttribute('href');
                    // Skip hash links and javascript: links
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        e.preventDefault();
                        showLoading();
                        setTimeout(() => {
                            window.location.href = href;
                        }, 100);
                    }
                }
            }, true);

            // === FADE IN SAAT HALAMAN BARU SIAP ===
            window.addEventListener('pageshow', () => {
                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    loading?.classList.remove('show');
                    pageContent?.classList.add('show');
                }, 50);
            });

            // Initial page load
            if (document.readyState === 'complete') {
                pageContent?.classList.add('show');
            } else {
                window.addEventListener('load', () => {
                    pageContent?.classList.add('show');
                });
            }
        })();
    </script>
    
        {{-- Script tambahan dari halaman --}}
    @yield('scripts')
<<<<<<< HEAD

    @stack('scripts')


=======
    @stack('scripts')
>>>>>>> c842378ffa117087b054c4c9d4728216779bf064
</body>
</html>