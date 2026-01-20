<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FcmService
{
    /**
     * Get Access Token dari Service Account
     */
    private static function getAccessToken()
    {
        try {
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
            
            if (!file_exists($credentialsPath)) {
                Log::error('Firebase credentials file not found: ' . $credentialsPath);
                return null;
            }

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                json_decode(file_get_contents($credentialsPath), true)
            );

            $token = $credentials->fetchAuthToken();
            
            return $token['access_token'] ?? null;

        } catch (\Exception $e) {
            Log::error('Error getting FCM access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kirim FCM Notification (HTTP v1 API)
     *
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @return array
     */
    public static function send($token, $title, $body, $data = [])
    {
        try {
            $accessToken = self::getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token'
                ];
            }

            $projectId = env('FIREBASE_PROJECT_ID');
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Payload FCM v1
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ], array_map('strval', $data)), // Convert all to string
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'default_channel',
                        ],
                    ],
                ],
            ];

            Log::info('📤 Sending FCM v1', [
                'token' => substr($token, 0, 30) . '...',
                'title' => $title,
                'body' => $body,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $result = $response->json();

            if ($response->successful()) {
                Log::info('✅ FCM sent successfully', [
                    'message_name' => $result['name'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Notifikasi berhasil dikirim',
                    'data' => $result
                ];
            } else {
                Log::error('❌ FCM failed', [
                    'status' => $response->status(),
                    'response' => $result,
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal mengirim notifikasi',
                    'error' => $result['error'] ?? 'Unknown error'
                ];
            }

        } catch (\Exception $e) {
            Log::error('❌ FCM Exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kirim ke Multiple Tokens
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public static function sendMultiple($tokens, $title, $body, $data = [])
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($tokens as $token) {
            $result = self::send($token, $title, $body, $data);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
            
            $results[] = $result;
        }

        Log::info("📊 FCM Batch: {$successCount} success, {$failCount} failed");

        return [
            'success' => $successCount > 0,
            'total' => count($tokens),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results
        ];
    }

    /**
     * Send Topic Notification
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public static function sendToTopic($topic, $title, $body, $data = [])
    {
        try {
            $accessToken = self::getAccessToken();
            
            if (!$accessToken) {
                return ['success' => false, 'message' => 'No access token'];
            }

            $projectId = env('FIREBASE_PROJECT_ID');
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $payload = [
                'message' => [
                    'topic' => $topic,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info("✅ Topic notification sent: {$topic}");
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json()];

        } catch (\Exception $e) {
            Log::error('Topic notification error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate FCM Token
     */
    public static function validateToken($token)
    {
        $result = self::send($token, 'Test', 'Token validation', []);
        return $result['success'];
    }
}