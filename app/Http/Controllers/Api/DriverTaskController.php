<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\FcmToken;
use App\Services\FcmService;
use Illuminate\Support\Facades\Log;

class DriverTaskController extends Controller
{
    public function getPendingTasks($driverId)
    {
        $myTasks = Delivery::with([
            'transaksi:id_transaksi,id_pelanggan,total_harga,status_transaksi,tgl_transaksi',
            'transaksi.pelanggan:id_pelanggan,nama_pelanggan,no_hp',
            'transaksi.detail:id_detail_transaksi,id_transaksi,id_layanan,id_jenis_layanan,id_parfum,qty,harga,id_satuan,tipe_diskon',
            'transaksi.detail.layanan:id_layanan,nama_layanan',
            'transaksi.detail.jenis:id_jenis_layanan,id_layanan,nama_jenis,harga',
            'transaksi.detail.parfum:id_parfum,nama_parfum',
        ])
        ->where('id_driver', $driverId)
        ->where('jenis', 'pickup')
        ->whereIn('status', [
            'pending',
            'accepted',
            'on_the_way_to_pickup',
            'picked_up',
            'on_the_way_to_laundry',
        ])
        ->get();

        return response()->json([
            'success' => true,
            'tasks' => $myTasks,
        ]);
    }

    private function sendDriverStatusNotification($delivery, $status)
    {
        try {
            if (!$delivery->relationLoaded('transaksi')) {
                $delivery->load('transaksi.pelanggan', 'driver');
            }

            $transaksi = $delivery->transaksi;
            if (!$transaksi || !$transaksi->pelanggan) {
                Log::warning("⚠️ Transaksi/pelanggan not found for delivery {$delivery->id_delivery}");
                return;
            }

            $pelanggan = $transaksi->pelanggan;
            $idPelanggan = $pelanggan->id_pelanggan;

            $tokens = FcmToken::where('pelanggan_id', $idPelanggan)
                ->whereNotNull('token')
                ->pluck('token')
                ->filter()
                ->toArray();

            if (empty($tokens)) {
                Log::info("ℹ️ No FCM token for pelanggan {$idPelanggan}");
                return;
            }

            $driverName = $delivery->driver->nama_driver ?? 'Driver';

            $title = '';
            $body = '';
            $type = '';

            if ($delivery->jenis === 'pickup') {
                switch ($status) {
                    case 'accepted':
                        $title = '✅ Driver Menerima Tugas Pickup!';
                        $body = "{$driverName} telah menerima tugas untuk pickup cucian Anda (ORDER/{$transaksi->id_transaksi}).";
                        $type = 'driver_accepted_pickup';
                        break;

                    case 'on_the_way_to_pickup':
                        $title = '🚗 Driver Menuju Lokasi Anda!';
                        $body = "{$driverName} sedang dalam perjalanan untuk pickup cucian Anda (ORDER/{$transaksi->id_transaksi}).";
                        $type = 'driver_on_way_pickup';
                        break;

                    case 'picked_up':
                        $title = '📦 Cucian Berhasil Di-Pickup!';
                        $body = "{$driverName} telah mengambil cucian Anda (ORDER/{$transaksi->id_transaksi}).";
                        $type = 'driver_picked_up';
                        break;

                    case 'on_the_way_to_laundry':
                        $title = '🚚 Menuju Laundry!';
                        $body = "{$driverName} sedang membawa cucian Anda ke laundry (ORDER/{$transaksi->id_transaksi}).";
                        $type = 'driver_to_laundry';
                        break;

                    case 'arrived_at_laundry':
                        $title = '🏪 Cucian Sampai di Laundry!';
                        $body = "Cucian Anda (ORDER/{$transaksi->id_transaksi}) sudah sampai di laundry dan sedang diperiksa.";
                        $type = 'driver_arrived_laundry';
                        break;

                    default:
                        return;
                }
            } elseif ($delivery->jenis === 'antar') {
                switch ($status) {
                    case 'accepted':
                        $title = '✅ Driver Menerima Tugas Antar!';
                        $body = "{$driverName} akan mengantar cucian Anda (ORDER/{$transaksi->id_transaksi}).";
                        $type = 'driver_accepted_delivery';
                        break;

                    case 'on_the_way_to_customer':
                        $title = '🚗 Driver Menuju Lokasi Anda!';
                        $body = "{$driverName} sedang dalam perjalanan mengantar cucian Anda (ORDER/{$transaksi->id_transaksi}).";
                        $type = 'driver_on_way_delivery';
                        break;

                    case 'delivered':
                        $title = '🎉 Cucian Telah Sampai!';
                        $body = "Cucian Anda (ORDER/{$transaksi->id_transaksi}) telah diantar oleh {$driverName}. Terima kasih!";
                        $type = 'driver_delivered';
                        break;

                    default:
                        return;
                }
            }

            foreach ($tokens as $token) {
                try {
                    FcmService::send(
                        $token,
                        $title,
                        $body,
                        [
                            'transaksi_id' => (string) $transaksi->id_transaksi,
                            'type' => $type,
                            'action' => 'open_detail',
                            'delivery_status' => $status,
                            'delivery_type' => $delivery->jenis,
                        ]
                    );

                    Log::info("✅ FCM sent: {$type} - Order {$transaksi->id_transaksi} to " . substr($token, 0, 20) . "...");
                } catch (\Exception $e) {
                    Log::error("❌ FCM Error for token: {$token} - " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error("❌ Error sending driver status notification: " . $e->getMessage());
        }
    }

    private function updateDeliveryStatus(Request $request, string $status)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->where('id_delivery', $request->id_delivery)
            ->where(function($q) use ($request, $status){
                if ($status === 'accepted') {
                    $q->whereNull('id_driver')->orWhere('id_driver', $request->id_driver);
                } else {
                    $q->where('id_driver', $request->id_driver);
                }
            })
            ->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $delivery->status = $status;
        if($status === 'accepted') {
            $delivery->id_driver = $request->id_driver;
        }
        $delivery->save();

        $this->sendDriverStatusNotification($delivery, $status);

        return response()->json([
            'success' => true,
            'message' => "Status updated to {$status}"
        ]);
    }

    public function acceptTask(Request $request)          { return $this->updateDeliveryStatus($request, 'accepted'); }
    public function onTheWayToPickup(Request $request)   { return $this->updateDeliveryStatus($request, 'on_the_way_to_pickup'); }
    public function completePickup(Request $request)     { return $this->updateDeliveryStatus($request, 'picked_up'); }
    public function onTheWayToLaundry(Request $request)  { return $this->updateDeliveryStatus($request, 'on_the_way_to_laundry'); }
    public function arrivedAtLaundry(Request $request)   { return $this->updateDeliveryStatus($request, 'arrived_at_laundry'); }
    public function onTheWayToCustomer(Request $request) { return $this->updateDeliveryStatus($request, 'on_the_way_to_customer'); }
    public function completeDelivery(Request $request)   { return $this->updateDeliveryStatus($request, 'delivered'); }

    public function getDriverHistory($driverId)
    {
        $history = Delivery::with([
            'transaksi:id_transaksi,id_pelanggan,total_harga,status_transaksi,tgl_transaksi',
            'transaksi.pelanggan:id_pelanggan,nama_pelanggan,no_hp',
            'transaksi.detail:id_detail_transaksi,id_transaksi,id_layanan,id_jenis_layanan,id_parfum,qty,harga,id_satuan,tipe_diskon',
            'transaksi.detail.layanan:id_layanan,nama_layanan',
            'transaksi.detail.jenis:id_jenis_layanan,id_layanan,nama_jenis,harga',
            'transaksi.detail.parfum:id_parfum,nama_parfum',
        ])
        ->where('id_driver', $driverId)
        ->whereIn('status', ['arrived_at_laundry','delivered'])
        ->orderBy('updated_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    public function getAntarTasks($driverId)
    {
        $tasks = Delivery::with([
            'transaksi:id_transaksi,id_pelanggan,total_harga,status_transaksi,tgl_transaksi',
            'transaksi.pelanggan:id_pelanggan,nama_pelanggan,no_hp',
            'transaksi.detail:id_detail_transaksi,id_transaksi,id_layanan,id_jenis_layanan,id_parfum,qty,harga,id_satuan,tipe_diskon',
            'transaksi.detail.layanan:id_layanan,nama_layanan',
            'transaksi.detail.jenis:id_jenis_layanan,id_layanan,nama_jenis,harga',
            'transaksi.detail.parfum:id_parfum,nama_parfum',
        ])
        ->where('id_driver', $driverId)
        ->where('jenis', 'antar')
        ->whereIn('status', ['accepted', 'on_the_way_to_customer'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function startAntar(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver'   => 'required',
        ]);

        $delivery = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->where(function($q) {
                $q->whereNull('jenis')
                  ->orWhere('jenis', 'antar');
            })
            ->where('status', 'accepted')
            ->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or invalid status'
            ], 404);
        }

        $delivery->status = 'on_the_way_to_customer';
        $delivery->save();

        $this->sendDriverStatusNotification($delivery, 'on_the_way_to_customer');

        return response()->json([
            'success' => true,
            'message' => 'Driver on the way to customer'
        ]);
    }

    public function completeAntar(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver'   => 'required',
        ]);

        $delivery = Delivery::with(['transaksi.pelanggan', 'driver'])
            ->where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->where('status', 'on_the_way_to_customer')
            ->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or invalid status'
            ], 404);
        }

        $delivery->status = 'delivered';
        $delivery->save();

        $this->sendDriverStatusNotification($delivery, 'delivered');

        return response()->json([
            'success' => true,
            'message' => 'Pengantaran selesai!'
        ]);
    }
}