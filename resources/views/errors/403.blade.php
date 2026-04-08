<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/errors/403.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gray-50">
    <!-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. -->
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak!',
            html: `
                <div class="text-center">
                    <p class="text-gray-700 text-lg mb-4">{{ $exception->getMessage() }}</p>
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg p-4 mx-auto max-w-md">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-info-circle-fill text-red-500 text-lg flex-shrink-0 mt-0.5"></i>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-red-800 mb-1">Butuh Akses?</p>
                                <p class="text-xs text-red-700">Hubungi administrator untuk mendapatkan izin akses.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            confirmButtonText: '<i class="bi bi-arrow-left me-2"></i>Kembali',
            confirmButtonColor: '#ef4444',
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'rounded-3xl shadow-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200'
            },
            showClass: {
                popup: 'animate__animated animate__fadeIn animate__faster'
            }
        }).then(() => {
            window.history.back();
        });
    </script>
</body>
</html>