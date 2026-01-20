<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $messaging;

    public function __construct()
    {
        // Get Firebase Messaging instance
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Kirim notifikasi ke single FCM token
     */
    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(
                    Notification::create($title, $body)
                )
                ->withData($data);

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
            Log::error('❌ Token not found (might be expired):', [
                'token' => substr($fcmToken, 0, 30) . '...',
                'error' => $e->getMessage()
            ]);
            
            // Hapus token yang invalid
            \DB::table('fcm_tokens')->where('token', $fcmToken)->delete();
            
            return [
                'success' => false,
                'error' => 'Token not found or expired'
            ];

        } catch (\Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
            Log::error('❌ Invalid message:', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Invalid message'];

        } catch (\Exception $e) {
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
        $tokens = \DB::table('fcm_tokens')
            ->where('pelanggan_id', $pelangganId)
            ->pluck('token');

        if ($tokens->isEmpty()) {
            Log::warning('⚠️ No FCM tokens found for pelanggan', ['pelanggan_id' => $pelangganId]);
            return ['success' => false, 'error' => 'No tokens found'];
        }

        $results = [];
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
        $tokens = \DB::table('fcm_tokens')
            ->where('driver_id', $driverId)
            ->pluck('token');

        if ($tokens->isEmpty()) {
            Log::warning('⚠️ No FCM tokens found for driver', ['driver_id' => $driverId]);
            return ['success' => false, 'error' => 'No tokens found'];
        }

        $results = [];
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