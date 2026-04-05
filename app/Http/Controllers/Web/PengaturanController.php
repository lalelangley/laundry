<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PengaturanController extends Controller
{
    // =============================
    // HELPER: Check if Super Admin
    // =============================
    private function isSuperAdmin()
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        return $user && (int)$user->role_id === 1;
    }

    // =============================
    // HELPER: Get Custom Permissions
    // =============================
    private function getCustomPermissions()
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return [
                'can_change_password' => false,
                'can_backup' => false,
                'can_restore' => false,
                'can_delete_backup' => false,
                'can_see_logout' => false,
                'can_manage_menu' => false,
                'can_add' => false,
                'can_delete' => false,
            ];
        }
        
        // ✅ Super Admin bypass - Full access INCLUDING can_add & can_delete
        if ((int)$user->role_id === 1) {
            return [
                'can_change_password' => true,
                'can_backup' => true,
                'can_restore' => true,
                'can_delete_backup' => true,
                'can_see_logout' => true,
                'can_manage_menu' => true,
                'can_add' => true,
                'can_delete' => true,
            ];
        }
        
        // Get permission from menu_role for other users
        $permission = \App\Models\MenuRole::whereHas('menu', function($query) use ($user) {
                $query->where('role_id', $user->role_id)
                      ->where('nama_menu', 'Pengaturan');
            })
            ->where('role_id', $user->role_id)
            ->first();
        
        if (!$permission) {
            return [
                'can_change_password' => true,
                'can_backup' => false,
                'can_restore' => false,
                'can_delete_backup' => false,
                'can_see_logout' => true,
                'can_manage_menu' => false,
                'can_add' => false,
                'can_delete' => false,
            ];
        }
        
        return [
            'can_change_password' => true,
            'can_backup' => $permission->can_add ?? false,
            'can_restore' => $permission->can_delete ?? false,
            'can_delete_backup' => $permission->can_delete ?? false,
            'can_see_logout' => true,
            'can_manage_menu' => false,
            'can_add' => $permission->can_add ?? false,
            'can_delete' => $permission->can_delete ?? false,
        ];
    }

    // =============================
    // ADMIN - INDEX
    // =============================
    public function index()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $pengaturan = DB::table('pengaturan')
            ->pluck('value', 'key')
            ->toArray();
        
        $permissions = $this->getCustomPermissions();
            
        return view('pengaturan.index', compact('pengaturan', 'permissions'));
    }

    // =============================
    // ADMIN - UPDATE PENGATURAN UMUM
    // =============================
    public function update(Request $request)
    {
        \Log::info('📥 UPDATE REQUEST:', $request->all());
    
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        
        $request->validate([
            'nama_outlet'   => 'required|string|max:255',
            'alamat_outlet' => 'required|string',
            'foto_outlet'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'nama_outlet'],
            ['value' => $request->nama_outlet]
        );

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'alamat_outlet'],
            ['value' => $request->alamat_outlet]
        );

        if ($request->hasFile('foto_outlet')) {
            $path = $request->file('foto_outlet')
                ->store('outlet', 'public');
                
            DB::table('pengaturan')->updateOrInsert(
                ['key' => 'foto_outlet'],
                ['value' => $path]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    }

    // =============================
    // ADMIN - UPDATE OMZET
    // =============================
    public function updateOmzet(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        
        $request->validate([
            'hitung_omzet_dari' => 'required|in:selesai,lunas'
        ]);

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'hitung_omzet_dari'],
            ['value' => $request->hitung_omzet_dari]
        );

        return response()->json([
            'status' => true,
            'message' => 'Pengaturan omzet disimpan'
        ]);
    }

    // =============================
    // ADMIN - BACKUP DATABASE
    // =============================
    public function backup()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        return $this->processBackup();
    }

    // =============================
    // ADMIN - RESTORE DATABASE
    // =============================
    public function restore()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        return $this->processRestore();
    }

    // =============================
    // ADMIN - DELETE BACKUP
    // =============================
    public function deleteBackup($filename)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        $backupPath = storage_path('app/private/Laravel/Laravel');
        $filePath = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        unlink($filePath);

        return response()->json([
            'status' => true,
            'message' => 'Backup berhasil dihapus'
        ]);
    }

    // =============================
    // ADMIN - LIST BACKUPS
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
    // =============================
    public function metodeBayar()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $metode = DB::table('metode_bayar')
            ->orderBy('nama_metode_bayar')
            ->get();
        
        $permissions = $this->getCustomPermissions();
            
        return view('pengaturan.metode_bayar', compact('metode', 'permissions'));
    }

    // =============================
    // ADMIN - SIMPAN METODE PEMBAYARAN
    // =============================
    public function storeMetodeBayar(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        $request->validate([
            'nama_metode_bayar' => 'required|string|max:100'
        ]);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true]);
    }

    // =============================
    // ADMIN - HAPUS METODE PEMBAYARAN
    // =============================
    public function deleteMetodeBayar($id)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        try {
            DB::table('metode_bayar')
                ->where('id_metode_bayar', $id)
                ->delete();

            return redirect()->route('pengaturan.metode')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
                
        } catch (\Exception $e) {
            return redirect()->route('pengaturan.metode')
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }

    // ==================== KASIR METHODS ====================
    
    public function indexKasir()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $pengaturan = DB::table('pengaturan')
            ->pluck('value', 'key')
            ->toArray();
        
        $permissions = $this->getCustomPermissions();
            
        return view('kasir.pengaturan.index', compact('pengaturan', 'permissions'));
    }

    public function updateKasir(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'edit');
        }
        
        return $this->update($request);
    }

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
        $filePath = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        unlink($filePath);

        return response()->json([
            'status' => true,
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
        
        $metode = DB::table('metode_bayar')
            ->orderBy('nama_metode_bayar')
            ->get();
        
        $permissions = $this->getCustomPermissions();
            
        return view('kasir.pengaturan.metode_bayar', compact('metode', 'permissions'));
    }

    public function storeMetodeBayarKasir(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        $request->validate([
            'nama_metode_bayar' => 'required|string|max:100'
        ]);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true]);
    }

    public function deleteMetodeBayarKasir($id)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        try {
            DB::table('metode_bayar')
                ->where('id_metode_bayar', $id)
                ->delete();

            return redirect()->route('kasir.pengaturan.metode')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
                
        } catch (\Exception $e) {
            return redirect()->route('kasir.pengaturan.metode.index')
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }

    // ==================== ADMIN2 METHODS ====================
    
    public function indexAdmin2()
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'view');
        }
        
        $pengaturan = DB::table('pengaturan')
            ->pluck('value', 'key')
            ->toArray();
        
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
        $filePath = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'File backup tidak ditemukan'
            ], 404);
        }

        unlink($filePath);

        return response()->json([
            'status' => true,
            'message' => 'Backup berhasil dihapus'
        ]);
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
        
        $metode = DB::table('metode_bayar')
            ->orderBy('nama_metode_bayar')
            ->get();
        
        $permissions = $this->getCustomPermissions();
            
        return view('admin2.pengaturan.metode_bayar', compact('metode', 'permissions'));
    }

    public function storeMetodeBayarAdmin2(Request $request)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'add');
        }
        
        $request->validate([
            'nama_metode_bayar' => 'required|string|max:100'
        ]);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true]);
    }

    public function deleteMetodeBayarAdmin2($id)
    {
        if (!$this->isSuperAdmin()) {
            requirePermission('pengaturan', 'delete');
        }
        
        try {
            DB::table('metode_bayar')
                ->where('id_metode_bayar', $id)
                ->delete();

            return redirect()->route('admin2.pengaturan.metode')
                ->with('success', 'Metode pembayaran berhasil dihapus!');
                
        } catch (\Exception $e) {
            return redirect()->route('admin2.pengaturan.metode')
                ->with('error', 'Gagal menghapus metode pembayaran: ' . $e->getMessage());
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================
    
    private function processBackup()
    {
        try {
            \Log::info('🔥 Backup dimulai...');
            
            set_time_limit(300);
            ini_set('memory_limit', '512M');

            $process = new \Symfony\Component\Process\Process([
                PHP_BINARY,
                base_path('artisan'),
                'backup:run',
                '--only-db'
            ], base_path());

            $process->setTimeout(300);
            \Log::info('📤 Running backup process...');
            
            $process->run();
            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            \Log::info('📥 Process output: ' . $output);
            
            if ($errorOutput) {
                \Log::warning('⚠️ Error output: ' . $errorOutput);
            }

            if (!$process->isSuccessful()) {
                \Log::error('❌ Backup gagal');
                return response()->json([
                    'status' => false,
                    'message' => 'Backup gagal: ' . ($errorOutput ?: $output)
                ], 500);
            }

            \Log::info('✅ Backup selesai!');
            
            clearstatcache();

            $backupPath = storage_path('app/private/Laravel/Laravel');
            clearstatcache(true, $backupPath);
            $files = glob($backupPath . '/*.zip');
            
            \Log::info('📦 Total backup setelah proses: ' . count($files));

            return response()->json([
                'status' => true,
                'message' => 'Backup database berhasil dibuat!',
                'total_backups' => count($files)
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Exception: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => false,
                'message' => 'Backup gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processRestore()
    {
        try {
            $backupPath = storage_path('app/private/Laravel/Laravel');
            
            if (!file_exists($backupPath)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Folder backup tidak ditemukan'
                ], 404);
            }

            $files = glob($backupPath . '/*.zip');
            
            if (empty($files)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada file backup!'
                ], 404);
            }

            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            $latestBackup = $files[0];

            $tempDir = storage_path('app/private/temp_restore');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zip = new \ZipArchive;
            if ($zip->open($latestBackup) !== true) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal membuka file backup'
                ], 500);
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $sqlFiles = glob($tempDir . '/db-dumps/*.sql');
            
            if (empty($sqlFiles)) {
                $this->deleteDirectory($tempDir);
                return response()->json([
                    'status' => false,
                    'message' => 'File SQL tidak ditemukan'
                ], 404);
            }

            $sqlFile = $sqlFiles[0];

            $db = config('database.connections.mysql');
            $command = sprintf(
                'mysql -h%s -P%s -u%s -p%s %s < %s 2>&1',
                escapeshellarg($db['host']),
                escapeshellarg($db['port']),
                escapeshellarg($db['username']),
                escapeshellarg($db['password']),
                escapeshellarg($db['database']),
                escapeshellarg($sqlFile)
            );

            exec($command, $output, $status);

            $this->deleteDirectory($tempDir);

            if ($status !== 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Restore gagal',
                    'error' => implode("\n", $output)
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'Database BERHASIL di-restore'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getBackupList()
    {
        try {
            $backupPath = storage_path('app/private/Laravel/Laravel');
            \Log::info('📂 Backup path: ' . $backupPath);

            if (!file_exists($backupPath)) {
                \Log::warning('⚠️ Folder backup tidak ditemukan');
                return response()->json(['status' => true, 'backups' => []]);
            }

            clearstatcache();
            $files = glob($backupPath . '/*.zip');
            
            \Log::info('📦 Total file ditemukan: ' . count($files));

            $permissions = $this->getCustomPermissions();

            $backups = [];
            foreach ($files as $file) {
                clearstatcache(true, $file);
                
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'date' => date('d M Y H:i:s', filemtime($file)),
                    'can_delete' => $permissions['can_delete_backup']
                ];
            }

            usort($backups, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            \Log::info('✅ Backup list:', $backups);

            return response()->json([
                'status' => true,
                'backups' => $backups
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error list backups: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function processDownloadBackup($filename)
    {
        $backupPath = storage_path('app/private/Laravel/Laravel');
        $filePath = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            abort(404, 'File backup tidak ditemukan');
        }

        return response()->download($filePath);
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}