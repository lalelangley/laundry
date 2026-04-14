<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KasminiLaundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); min-height: 100vh; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4 py-8">
    <div class="w-full max-w-md">
        <a href="{{ route('login') }}" class="inline-flex items-center mb-4 px-4 py-2 bg-white/90 hover:bg-white rounded-full shadow transition">
            <i class="fas fa-arrow-left text-yellow-600 mr-2"></i>
            <span class="font-semibold text-gray-800">Kembali ke Login</span>
        </a>

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-8 py-6 text-center">
                <div class="flex justify-center mb-3">
                    <div class="bg-white w-20 h-20 rounded-full shadow-lg flex items-center justify-center">
                        <i class="fas fa-unlock-keyhole text-4xl text-yellow-500"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white">Atur Password Baru</h1>
                <p class="text-yellow-50 text-sm mt-1">Masukkan password baru untuk akun Anda</p>
            </div>

            <div class="px-8 py-6">
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
                        <ul class="text-red-700 text-sm font-medium space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset.process') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="role" value="{{ $role }}">

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">Email</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email', $email) }}"
                               readonly
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-100 text-gray-600 outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">Password Baru</label>
                        <input type="password"
                               name="password"
                               autocomplete="new-password"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                               placeholder="Minimal 6 karakter">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2 text-sm">Konfirmasi Password Baru</label>
                        <input type="password"
                               name="password_confirmation"
                               autocomplete="new-password"
                               class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                               placeholder="Ketik ulang password baru">
                    </div>

                    <button type="submit"
                            class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 py-3 rounded-lg font-bold shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
