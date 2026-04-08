<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * ========================================================
     * SERVICE NOTIFIKASI FIREBASE
     * Fungsi:
     * - Mengirim push notification ke token FCM
     * - Mengirim notifikasi ke banyak device pelanggan/driver
     * - Menangani error token invalid atau pesan gagal kirim
     *
     * Konsep:
     * - Class-object
     * - Method/procedure
     * - Percabangan dan perulangan
     * - Penanganan error dengan try-catch
     * - Array hasil response
     * ========================================================
     */

    protected $messaging;

    public function __construct()
    {
        // [CLASS-OBJECT] Ambil instance Firebase Messaging dari container Laravel.
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Kirim notifikasi ke single FCM token
     */
    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        try {
            // [OBJECT + METHOD] Susun object pesan FCM untuk satu token tujuan.
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(
                    Notification::create($title, $body)
                )
                ->withData($data);

            // [METHOD] Kirim message menggunakan object Firebase Messaging.
            $result = $this->messaging->send($message);

            Log::info('✅ Notification sent', [
                'token' => substr($fcmToken, 0, 30) . '...',
                'title' => $title,
                'body' => $body,
            ]);

            return [
                'success' => true,
                'message' => 'Notification sent',
                'result' => $result,
            ];

        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            // [PENANGANAN ERROR] Token tidak valid/expired ditangani khusus.
            Log::error('❌ Token not found (might be expired):', [
                'token' => substr($fcmToken, 0, 30) . '...',
                'error' => $e->getMessage()
            ]);
            
            // [METHOD] Hapus token invalid dari database agar tidak dipakai lagi.
            \DB::table('fcm_tokens')->where('token', $fcmToken)->delete();
            
            return [
                'success' => false,
                'error' => 'Token not found or expired'
            ];

        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            // [PENANGANAN ERROR] Format pesan tidak valid.
            Log::error('❌ Invalid message:', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Invalid message'];

        } catch (\Exception $e) {
            // [PENANGANAN ERROR] Tangkap error umum agar alur aplikasi tetap aman.
            Log::error('❌ Notification error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim notifikasi ke pelanggan (bisa multiple devices)
     */
    public function sendToPelanggan($pelangganId, $title, $body, $data = [])
    {
        // [METHOD] Ambil seluruh token device pelanggan sebagai collection/array data.
        $tokens = \DB::table('fcm_tokens')
            ->where('pelanggan_id', $pelangganId)
            ->pluck('token');

        // [PERCABANGAN] Jika pelanggan belum punya token, proses dihentikan.
        if ($tokens->isEmpty()) {
            Log::warning('⚠️ No FCM tokens found for pelanggan', ['pelanggan_id' => $pelangganId]);
            return ['success' => false, 'error' => 'No tokens found'];
        }

        // [ARRAY] Penampung hasil kirim ke tiap device.
        $results = [];

        // [PERULANGAN] Kirim notif ke semua token milik pelanggan.
        foreach ($tokens as $token) {
            $results[] = $this->sendNotification($token, $title, $body, $data);
        }

        Log::info('📤 Sent notifications to pelanggan', [
            'pelanggan_id' => $pelangganId,
            'device_count' => count($tokens),
        ]);

        return $results;
    }

    /**
     * Kirim notifikasi ke driver (bisa multiple devices)
     */
    public function sendToDriver($driverId, $title, $body, $data = [])
    {
        // [METHOD] Ambil seluruh token device driver.
        $tokens = \DB::table('fcm_tokens')
            ->where('driver_id', $driverId)
            ->pluck('token');

        // [PERCABANGAN] Jika token kosong, kembalikan response gagal.
        if ($tokens->isEmpty()) {
            Log::warning('⚠️ No FCM tokens found for driver', ['driver_id' => $driverId]);
            return ['success' => false, 'error' => 'No tokens found'];
        }

        // [ARRAY] Penampung hasil kirim ke banyak device driver.
        $results = [];

        // [PERULANGAN] Kirim satu per satu ke semua token driver.
        foreach ($tokens as $token) {
            $results[] = $this->sendNotification($token, $title, $body, $data);
        }

        Log::info('📤 Sent notifications to driver', [
            'driver_id' => $driverId,
            'device_count' => count($tokens),
        ]);

        return $results;
    }
}
