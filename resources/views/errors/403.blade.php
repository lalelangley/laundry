<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak!',
            text: '{{ $exception->getMessage() }}',
            confirmButtonText: 'Kembali',
            confirmButtonColor: '#FACC15',
        }).then(() => {
            window.history.back();
        });
    </script>
</body>
</html>