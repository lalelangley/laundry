<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Laundry</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
        }
    </style>
</head>

<body class="flex justify-center items-center min-h-screen px-4">

    <div class="w-full max-w-md">

        <!-- Card Kuning -->
        <div class="relative bg-yellow-400 rounded-3xl p-6 shadow-xl">

            <!-- Logo bulat -->
            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2">
                <div class="bg-white w-20 h-20 rounded-full shadow-md flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" class="w-12" alt="logo">
                </div>
            </div>

            <div class="mt-12">

                <h2 class="text-center text-xl font-semibold mb-4">Pilih Pengguna</h2>

                @if(session('error'))
                    <p class="text-red-600 text-center mb-3">{{ session('error') }}</p>
                @endif

                <form method="POST" action="{{ route('login.process') }}">
                    @csrf

                    <!-- DROPDOWN USER (Admin + Kasir) -->
                    <label class="font-medium">Pilih User</label>
                    <select name="user_id"
                        class="w-full mt-1 mb-4 p-3 rounded-xl focus:ring-2 focus:ring-black">
                        
                        <option value="">-- Pilih --</option>

                        <optgroup label="Admin">
                            @foreach($admins as $admin)
                                <option value="admin-{{ $admin->id_admin }}">
                                    {{ $admin->nama }}
                                </option>
                            @endforeach
                        </optgroup>

                        <optgroup label="Kasir">
                            @foreach($kasirs as $kasir)
                                <option value="kasir-{{ $kasir->id_kasir }}">
                                    {{ $kasir->nama_kasir }}
                                </option>
                            @endforeach
                        </optgroup>

                    </select>

                    <!-- PIN -->
                    <label class="font-medium">Masukkan PIN</label>
                    <input type="password" name="pin"
                        class="w-full mt-1 p-3 rounded-xl focus:ring-2 focus:ring-black tracking-widest text-center text-lg"
                        placeholder="••••••">

                    <button
                        class="w-full bg-black text-white mt-5 py-3 rounded-xl text-lg font-semibold shadow active:scale-95">
                        Login
                    </button>

                </form>

            </div>
        </div>

        <!-- Gambar dekor bawah -->
        <div class="mt-6 flex justify-center">
            <img src="{{ asset('images/decor.png') }}" class="w-64 opacity-90">
        </div>

    </div>

</body>
</html>
