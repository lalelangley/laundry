<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use App\Models\Pelanggan;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FcmTokenController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('📥 FCM Registration:', $request->all());

            // Validasi dasar
            $validated = $request->validate([
                'pelanggan_id' => 'nullable|integer',
                'driver_id'    => 'nullable|integer',
                'token'        => 'required|string',
                'device_name'  => 'required|string',
            ]);

            // Minimal ada salah satu
            if (!$request->pelanggan_id && !$request->driver_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'pelanggan_id atau driver_id harus ada'
                ], 400);
            }

            // Validasi pelanggan exists
            if ($request->pelanggan_id) {
                $pelanggan = Pelanggan::find($request->pelanggan_id);
                if (!$pelanggan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pelanggan tidak ditemukan'
                    ], 404);
                }
            }

            // Validasi driver exists
            if ($request->driver_id) {
                $driver = Driver::find($request->driver_id);
                if (!$driver) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Driver tidak ditemukan'
                    ], 404);
                }
            }

            // Unique keys untuk updateOrCreate
            $uniqueKeys = ['device_name' => $validated['device_name']];
            
            if ($request->pelanggan_id) {
                $uniqueKeys['pelanggan_id'] = $validated['pelanggan_id'];
            }
            
            if ($request->driver_id) {
                $uniqueKeys['driver_id'] = $validated['driver_id'];
            }

            // Simpan atau update
            $fcmToken = FcmToken::updateOrCreate(
                $uniqueKeys,
                [
                    'token' => $validated['token'],
                    'last_used_at' => now(),
                ]
            );

            Log::info('✅ FCM Token saved', [
                'id' => $fcmToken->id,
                'user_type' => $request->pelanggan_id ? 'pelanggan' : 'driver',
                'user_id' => $request->pelanggan_id ?? $request->driver_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token berhasil disimpan!',
                'data' => $fcmToken
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation Error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $deleted = FcmToken::where('token', $validated['token'])->delete();

            Log::info('🗑️ FCM Token deletion', [
                'token' => substr($validated['token'], 0, 30) . '...',
                'deleted' => $deleted,
            ]);

            return response()->json([
                'success' => $deleted > 0,
                'message' => $deleted > 0 ? 'Token dihapus' : 'Token tidak ditemukan'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error delete:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus token'
            ], 500);
        }
    }
}