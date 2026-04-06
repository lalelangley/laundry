<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * PengaturanController
 * 
 * Controller untuk mengelola pengaturan aplikasi, termasuk:
 * - Pengaturan umum outlet (nama, alamat, foto)
 * - Pengaturan omzet
 * - Backup & restore database
 * - Manajemen metode pembayaran
 * 
 * Mendukung tiga role panel: Admin, Kasir, dan Admin2
 */
class PengaturanController extends Controller
{
    // =============================
    // HELPER: Cek apakah user adalah Super Admin (role_id = 1)
    // =============================
    private function isSuperAdmin()
    {
        // Ambil user dari guard admin atau kasir
        $user = auth('admin')->user() ?? auth('kasir')->user();

        // Kembalikan true jika user ada dan role_id-nya adalah 1 (Super Admin)
        return $user && (int)$user->role_id === 1;
    }

    // =============================
    // HELPER: Ambil daftar izin (permissions) berdasarkan role user
    // =============================
    private function getCustomPermissions()
    {
        // Ambil user yang sedang login dari guard admin atau kasir
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        // Jika tidak ada user yang login, kembalikan semua izin false
        if (!$user) {
            return [
                'can_change_password' => false,
                'can_backup'          => false,
                'can_restore'         => false,
                'can_delete_backup'   => false,
                'can_see_logout'      => false,
                'can_manage_menu'     => false,
                'can_add'             => false,
                'can_delete'          => false,
            ];
        }
        
        // Super Admin (role_id = 1) mendapatkan akses penuh ke semua fitur
        if ((int)$user->role_id === 1) {
            return [
                'can_change_password' => true,
                'can_backup'          => true,
                'can_restore'         => true,
                'can_delete_backup'   => true,
                'can_see_logout'      => true,
                'can_manage_menu'     => true,
                'can_add'             => true,
                'can_delete'          => true,
            ];
        }
        
        // Untuk role selain Super Admin, ambil izin dari tabel menu_role
        // berdasarkan role_id user dan nama menu 'Pengaturan'
        $permission = \App\Models\MenuRole::whereHas('menu', function($query) use ($user) {
                $query->where('role_id', $user->role_id)
                      ->where('nama_menu', 'Pengaturan');
            })
            ->where('role_id', $user->role_id)
            ->first();
        
        // Jika tidak ditemukan record permission, berikan izin default minimal
        if (!$permission) {
            return [
                'can_change_password' => true,  // semua user boleh ganti password
                'can_backup'          => false,
                'can_restore'         => false,
                'can_delete_backup'   => false,
                'can_see_logout'      => true,  // semua user bisa logout
                'can_manage_menu'     => false,
                'can_add'             => false,
                'can_delete'          => false,
            ];
        }
        
        // Petakan kolom can_add dan can_delete dari menu_role ke izin backup/restore
        return [
            'can_change_password' => true,
            'can_backup'          => $permission->can_add    ?? false, // backup = hak tambah
            'can_restore'         => $permission->can_delete ?? false, // restore = hak hapus
            'can_delete_backup'   => $permission->can_delete ?? false,
            'can_see_logout'      => true,
            'can_manage_menu'     => false,
            'can_add'             => $permission->can_add    ?? false,
            'can_delete'          => $permission->can_delete ?? false,
        ];
    }

    // =============================
    // ADMIN - INDEX
    // Menampilkan halaman utama pengaturan untuk panel Admin
    // =============================
    public function index()
    {
        // Validasi akses: non-Super Admin harus punya izin 'view' pada menu pengaturan
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        // Ambil semua data pengaturan dari tabel, diubah menjadi array key => value
        $pengaturan = DB::table('pengaturan')
            ->pluck('value', 'key')
            ->toArray();
        
        // Ambil daftar izin untuk ditampilkan/disembunyikan di view
        $permissions = $this->getCustomPermissions();
            
        return view('pengaturan.index', compact('pengaturan', 'permissions'));
    }

    // =============================
    // ADMIN - UPDATE PENGATURAN UMUM
    // Menyimpan perubahan nama outlet, alamat, dan foto outlet
    // =============================
    public function update(Request $request)
    {
        \Log::info('📥 UPDATE REQUEST:', $request->all());
    
        // Validasi akses edit
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        
        // Validasi input: nama dan alamat wajib diisi, foto opsional (maks 2MB)
        $request->validate([
            'nama_outlet'   => 'required|string|max:255',
            'alamat_outlet' => 'required|string',
            'foto_outlet'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Simpan atau perbarui nama outlet di tabel pengaturan
        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'nama_outlet'],
            ['value' => $request->nama_outlet]
        );

        // Simpan atau perbarui alamat outlet
        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'alamat_outlet'],
            ['value' => $request->alamat_outlet]
        );

        // Jika ada file foto yang diupload, simpan ke disk 'public' dan catat path-nya
        if ($request->hasFile('foto_outlet')) {
            $path = $request->file('foto_outlet')
                ->store('outlet', 'public');
                
            DB::table('pengaturan')->updateOrInsert(
                ['key' => 'foto_outlet'],
                ['value' => $path]
            );
        }

        return response()->json([
            'status'  => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    }

    // =============================
    // ADMIN - UPDATE OMZET
    // Menyimpan pilihan cara perhitungan omzet (dari status 'selesai' atau 'lunas')
    // =============================
    public function updateOmzet(Request $request)
    {
        // Validasi akses edit
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        
        // Hanya menerima nilai 'selesai' atau 'lunas'
        $request->validate([
            'hitung_omzet_dari' => 'required|in:selesai,lunas'
        ]);

        // Simpan atau perbarui pengaturan omzet
        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'hitung_omzet_dari'],
            ['value' => $request->hitung_omzet_dari]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Pengaturan omzet disimpan'
        ]);
    }

    // =============================
    // ADMIN - BACKUP DATABASE
    // Memicu proses backup database melalui helper processBackup()
    // =============================
    public function backup()
    {
        // Hanya user dengan izin 'add' yang boleh membuat backup
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        return $this->processBackup();
    }

    // =============================
    // ADMIN - RESTORE DATABASE
    // Memicu proses restore database dari backup terbaru
    // =============================
    public function restore()
    {
        // Hanya user dengan izin 'delete' yang boleh melakukan restore
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        return $this->processRestore();
    }

    // =============================
    // ADMIN - DELETE BACKUP
    // Menghapus file backup berdasarkan nama file
    // =============================
    public function deleteBackup($filename)
    {
        // Validasi akses hapus
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        // Bangun path lengkap ke file backup
        $backupPath = storage_path('app/private/Laravel/Laravel');
        $filePath   = $backupPath . '/' . $filename;

        // Cek apakah file backup benar-benar ada
        if (!file_exists($filePath)) {
            return response()->json([
                'status'  => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        // Hapus file backup dari filesystem
        unlink($filePath);

        return response()->json([
            'status'  => true,
            'message' => 'Backup berhasil dihapus'
        ]);
    }

    // =============================
    // ADMIN - LIST BACKUPS
    // Mengembalikan daftar file backup yang tersedia (JSON)
    // =============================
    public function listBackups()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        return $this->getBackupList();
    }

    // =============================
    // ADMIN - DOWNLOAD BACKUP
    // Mengunduh file backup berdasarkan nama file
    // =============================
    public function downloadBackup($filename)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        return $this->processDownloadBackup($filename);
    }

    // =============================
    // ADMIN - HALAMAN METODE PEMBAYARAN
    // Menampilkan daftar metode pembayaran yang tersedia
    // =============================
    public function metodeBayar()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        // Ambil semua metode bayar, diurutkan berdasarkan nama
        $metode = DB::table('metode_bayar')
            ->orderBy('nama_metode_bayar')
            ->get();
        
        $permissions = $this->getCustomPermissions();
            
        return view('pengaturan.metode_bayar', compact('metode', 'permissions'));
    }

    // =============================
    // ADMIN - SIMPAN METODE PEMBAYARAN
    // Menambahkan metode pembayaran baru ke database
    // =============================
    public function storeMetodeBayar(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        // Validasi nama metode bayar wajib diisi, maks 100 karakter
        $request->validate([
            'nama_metode_bayar' => 'required|string|max:100'
        ]);

        // Insert data baru ke tabel metode_bayar beserta timestamp
        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['status' => true]);
    }

    // =============================
    // ADMIN - HAPUS METODE PEMBAYARAN
    // Menghapus metode pembayaran berdasarkan ID
    // =============================
    public function deleteMetodeBayar($id)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        try {
            // Hapus record metode bayar berdasarkan primary key id_metode_bayar
            DB::table('metode_bayar')
                ->where('id_metode_bayar', $id)
                ->delete();

            return redirect()->route('pengaturan.metode')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
                
        } catch (\Exception $e) {
            // Tangani error jika penghapusan gagal (misal: constraint FK)
            return redirect()->route('pengaturan.metode')
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }

    // ==================== KASIR METHODS ====================
    // Semua method Kasir memanggil method inti yang sama dengan panel Admin
    // Perbedaan hanya pada nama route dan view yang digunakan
    
    public function indexKasir()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $pengaturan  = DB::table('pengaturan')->pluck('value', 'key')->toArray();
        $permissions = $this->getCustomPermissions();
            
        return view('kasir.pengaturan.index', compact('pengaturan', 'permissions'));
    }

    // Mendelegasikan update ke method utama update()
    public function updateKasir(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        return $this->update($request);
    }

    // Mendelegasikan update omzet ke method utama updateOmzet()
    public function updateOmzetKasir(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        return $this->updateOmzet($request);
    }

    public function backupKasir()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        return $this->processBackup();
    }

    public function restoreKasir()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        return $this->processRestore();
    }

    public function deleteBackupKasir($filename)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        $backupPath = storage_path('app/private/Laravel/Laravel');
        $filePath   = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return response()->json([
                'status'  => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        unlink($filePath);

        return response()->json([
            'status'  => true,
            'message' => 'Backup berhasil dihapus'
        ]);
    }

    public function listBackupsKasir()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        return $this->getBackupList();
    }

    public function downloadBackupKasir($filename)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        return $this->processDownloadBackup($filename);
    }

    public function metodeBayarKasir()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $metode      = DB::table('metode_bayar')->orderBy('nama_metode_bayar')->get();
        $permissions = $this->getCustomPermissions();
            
        return view('kasir.pengaturan.metode_bayar', compact('metode', 'permissions'));
    }

    public function storeMetodeBayarKasir(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        $request->validate(['nama_metode_bayar' => 'required|string|max:100']);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['status' => true]);
    }

    public function deleteMetodeBayarKasir($id)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        try {
            DB::table('metode_bayar')->where('id_metode_bayar', $id)->delete();
            return redirect()->route('kasir.pengaturan.metode')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('kasir.pengaturan.metode.index')
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }

    // ==================== ADMIN2 METHODS ====================
    // Sama seperti Kasir, semua method Admin2 mendelegasikan ke method inti
    
    public function indexAdmin2()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $pengaturan  = DB::table('pengaturan')->pluck('value', 'key')->toArray();
        $permissions = $this->getCustomPermissions();
            
        return view('admin2.pengaturan.index', compact('pengaturan', 'permissions'));
    }

    public function updateAdmin2(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        return $this->update($request);
    }

    public function updateOmzetAdmin2(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        return $this->updateOmzet($request);
    }

    public function backupAdmin2()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        return $this->processBackup();
    }

    public function restoreAdmin2()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        return $this->processRestore();
    }

    public function deleteBackupAdmin2($filename)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        $backupPath = storage_path('app/private/Laravel/Laravel');
        $filePath   = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return response()->json([
                'status'  => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        unlink($filePath);
        return response()->json(['status' => true, 'message' => 'Backup berhasil dihapus']);
    }

    public function listBackupsAdmin2()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        return $this->getBackupList();
    }

    public function downloadBackupAdmin2($filename)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        return $this->processDownloadBackup($filename);
    }

    public function metodeBayarAdmin2()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $metode      = DB::table('metode_bayar')->orderBy('nama_metode_bayar')->get();
        $permissions = $this->getCustomPermissions();
            
        return view('admin2.pengaturan.metode_bayar', compact('metode', 'permissions'));
    }

    public function storeMetodeBayarAdmin2(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        $request->validate(['nama_metode_bayar' => 'required|string|max:100']);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['status' => true]);
    }

    public function deleteMetodeBayarAdmin2($id)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        try {
            DB::table('metode_bayar')->where('id_metode_bayar', $id)->delete();
            return redirect()->route('admin2.pengaturan.metode')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('admin2.pengaturan.metode')
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * processBackup()
     * 
     * Menjalankan backup database menggunakan package spatie/laravel-backup
     * via Symfony Process agar tidak terkena timeout PHP default.
     * 
     * Perintah: php artisan backup:run --only-db
     * Output: file .zip disimpan di storage/app/private/Laravel/Laravel/
     */
    private function processBackup()
    {
        try {
            \Log::info('🔥 Backup dimulai...');
            
            // Naikkan batas waktu eksekusi dan memori untuk proses backup besar
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            // Jalankan artisan backup:run via Symfony Process
            // Menggunakan Process (bukan Artisan::call) agar berjalan di proses terpisah
            // dan tidak terkena timeout HTTP request
            $process = new \Symfony\Component\Process\Process([
                PHP_BINARY,          // path ke executable PHP
                base_path('artisan'), // path ke file artisan
                'backup:run',
                '--only-db'          // hanya backup database, tanpa file
            ], base_path());

            $process->setTimeout(300); // timeout proses 5 menit
            \Log::info('📤 Running backup process...');
            
            $process->run();

            // Ambil output standar dan output error dari proses
            $output      = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            \Log::info('📥 Process output: ' . $output);
            
            if ($errorOutput) {
                \Log::warning('⚠️ Error output: ' . $errorOutput);
            }

            // Jika proses tidak berhasil, kembalikan respons error
            if (!$process->isSuccessful()) {
                \Log::error('❌ Backup gagal');
                return response()->json([
                    'status'  => false,
                    'message' => 'Backup gagal: ' . ($errorOutput ?: $output)
                ], 500);
            }

            \Log::info('✅ Backup selesai!');
            
            // Bersihkan cache stat file agar glob() membaca kondisi terkini
            clearstatcache();

            // Hitung total file backup yang ada setelah proses selesai
            $backupPath = storage_path('app/private/Laravel/Laravel');
            clearstatcache(true, $backupPath);
            $files = glob($backupPath . '/*.zip');
            
            \Log::info('📦 Total backup setelah proses: ' . count($files));

            return response()->json([
                'status'        => true,
                'message'       => 'Backup database berhasil dibuat!',
                'total_backups' => count($files)
            ]);

        } catch (\Exception $e) {
            // Tangkap exception tak terduga dan log stack trace-nya
            \Log::error('❌ Exception: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status'  => false,
                'message' => 'Backup gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * processRestore()
     * 
     * Melakukan restore database dari file backup .zip terbaru.
     * Alur:
     * 1. Cari file .zip terbaru di folder backup
     * 2. Ekstrak menggunakan ZipArchive (ekstensi bawaan PHP)
     * 3. Temukan file .sql hasil ekstrak
     * 4. Import ke database via perintah CLI `mysql` menggunakan exec()
     * 5. Bersihkan folder temp setelah selesai
     */
    private function processRestore()
    {
        try {
            $backupPath = storage_path('app/private/Laravel/Laravel');
            
            // Pastikan folder backup ada
            if (!file_exists($backupPath)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Folder backup tidak ditemukan'
                ], 404);
            }

            // Cari semua file backup .zip di folder backup
            $files = glob($backupPath . '/*.zip');
            
            if (empty($files)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak ada file backup!'
                ], 404);
            }

            // Urutkan berdasarkan waktu modifikasi terbaru (descending)
            // agar file backup paling baru digunakan
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            $latestBackup = $files[0];

            // Siapkan direktori sementara untuk mengekstrak isi zip
            $tempDir = storage_path('app/private/temp_restore');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Buka dan ekstrak file zip menggunakan ZipArchive (bawaan PHP)
            $zip = new \ZipArchive;
            if ($zip->open($latestBackup) !== true) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Gagal membuka file backup'
                ], 500);
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Cari file SQL hasil ekstrak di subfolder db-dumps/
            $sqlFiles = glob($tempDir . '/db-dumps/*.sql');
            
            if (empty($sqlFiles)) {
                // Bersihkan folder temp jika file SQL tidak ditemukan
                $this->deleteDirectory($tempDir);
                return response()->json([
                    'status'  => false,
                    'message' => 'File SQL tidak ditemukan'
                ], 404);
            }

            $sqlFile = $sqlFiles[0]; // Ambil file SQL pertama

            // Ambil konfigurasi koneksi database dari config/database.php
            $db = config('database.connections.mysql');

            // Bangun perintah CLI mysql untuk import file SQL
            // Catatan: server harus memiliki mysql client terinstall
            $command = sprintf(
                'mysql -h%s -P%s -u%s -p%s %s < %s 2>&1',
                escapeshellarg($db['host']),
                escapeshellarg($db['port']),
                escapeshellarg($db['username']),
                escapeshellarg($db['password']),
                escapeshellarg($db['database']),
                escapeshellarg($sqlFile)
            );

            // Jalankan perintah import via exec(), tangkap output dan status kode
            exec($command, $output, $status);

            // Hapus folder temp setelah proses restore selesai
            $this->deleteDirectory($tempDir);

            // Status 0 = sukses, selain 0 = gagal
            if ($status !== 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Restore gagal',
                    'error'   => implode("\n", $output)
                ], 500);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Database BERHASIL di-restore'
            ]);

        } catch (\Throwable $e) {
            // Tangkap semua jenis error termasuk Error dan Exception
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * getBackupList()
     * 
     * Mengambil daftar file backup .zip dan mengembalikannya sebagai JSON.
     * Setiap item berisi: nama file, ukuran (format human-readable), tanggal, dan izin hapus.
     */
    private function getBackupList()
    {
        try {
            $backupPath = storage_path('app/private/Laravel/Laravel');
            \Log::info('📂 Backup path: ' . $backupPath);

            // Jika folder backup belum ada, kembalikan array kosong
            if (!file_exists($backupPath)) {
                \Log::warning('⚠️ Folder backup tidak ditemukan');
                return response()->json(['status' => true, 'backups' => []]);
            }

            // Refresh stat cache agar data file selalu terkini
            clearstatcache();
            $files = glob($backupPath . '/*.zip');
            
            \Log::info('📦 Total file ditemukan: ' . count($files));

            // Ambil izin user untuk menentukan apakah tombol hapus ditampilkan
            $permissions = $this->getCustomPermissions();

            // Buat array data backup dari setiap file yang ditemukan
            $backups = [];
            foreach ($files as $file) {
                clearstatcache(true, $file); // refresh stat per file
                
                $backups[] = [
                    'filename'   => basename($file),
                    'size'       => $this->formatBytes(filesize($file)), // ukuran dalam KB/MB
                    'date'       => date('d M Y H:i:s', filemtime($file)),
                    'can_delete' => $permissions['can_delete_backup']
                ];
            }

            // Urutkan backup dari yang terbaru ke yang terlama
            usort($backups, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            \Log::info('✅ Backup list:', $backups);

            return response()->json([
                'status'  => true,
                'backups' => $backups
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error list backups: ' . $e->getMessage());
            
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * processDownloadBackup()
     * 
     * Mengunduh file backup berdasarkan nama file.
     * Menggunakan response()->download() bawaan Laravel.
     * 
     * @param string $filename Nama file backup yang akan diunduh
     */
    private function processDownloadBackup($filename)
    {
        $backupPath = storage_path('app/private/Laravel/Laravel');
        $filePath   = $backupPath . '/' . $filename;

        // Kembalikan 404 jika file tidak ditemukan
        if (!file_exists($filePath)) {
            abort(404, 'File backup tidak ditemukan');
        }

        // Kirim file sebagai response download ke browser
        return response()->download($filePath);
    }

    /**
     * deleteDirectory()
     * 
     * Menghapus direktori beserta seluruh isinya secara rekursif.
     * Digunakan untuk membersihkan folder temp setelah restore selesai.
     * 
     * @param string $dir Path direktori yang akan dihapus
     * @return bool True jika berhasil
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        // Jika bukan direktori, hapus sebagai file biasa
        if (!is_dir($dir)) {
            return unlink($dir);
        }

        // Iterasi semua isi direktori dan hapus secara rekursif
        foreach (scandir($dir) as $item) {
            // Lewati referensi direktori saat ini dan induk
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        // Hapus direktori yang sudah kosong
        return rmdir($dir);
    }

    /**
     * formatBytes()
     * 
     * Mengubah ukuran file dari bytes menjadi format yang mudah dibaca manusia
     * (B, KB, MB, GB, TB).
     * 
     * @param int $bytes     Ukuran dalam bytes
     * @param int $precision Jumlah angka desimal (default: 2)
     * @return string Ukuran dalam format human-readable (misal: "2.45 MB")
     */
    private function formatBytes($bytes, $precision = 2)
    {
        // Daftar satuan ukuran dari terkecil ke terbesar
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        // Bagi terus dengan 1024 sampai nilai < 1024 atau satuan habis
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}