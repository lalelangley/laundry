<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Delivery;

class DriverTaskController extends Controller
{
    // ======================================================
    // GET TASKS FOR THIS DRIVER
    // ======================================================
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
        ->whereIn('status', [
            'pending',
            'accepted',
            'on_the_way_to_pickup',
            'picked_up',
            'on_the_way_to_laundry',
            'on_the_way_to_customer',
        ])
        ->get();

        return response()->json([
            'success' => true,
            'tasks' => $myTasks,
        ]);
    }

    // ======================================================
    // DRIVER ACTIONS (ACCEPT, PICKUP, DELIVERY, ETC)
    // ======================================================
    private function updateDeliveryStatus(Request $request, string $status)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where(function($q) use ($request, $status){
                // jika status "accept" boleh id_driver null
                if ($status === 'accepted') {
                    $q->whereNull('id_driver')->orWhere('id_driver', $request->id_driver);
                } else {
                    $q->where('id_driver', $request->id_driver);
                }
            })
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = $status;
        if($status === 'accepted') {
            $delivery->id_driver = $request->id_driver;
        }
        $delivery->save();

        return response()->json(['success'=>true,'message'=>"Status updated to {$status}"]);
    }

    public function acceptTask(Request $request)          { return $this->updateDeliveryStatus($request, 'accepted'); }
    public function onTheWayToPickup(Request $request)   { return $this->updateDeliveryStatus($request, 'on_the_way_to_pickup'); }
    public function completePickup(Request $request)     { return $this->updateDeliveryStatus($request, 'picked_up'); }
    public function onTheWayToLaundry(Request $request)  { return $this->updateDeliveryStatus($request, 'on_the_way_to_laundry'); }
    public function arrivedAtLaundry(Request $request)   { return $this->updateDeliveryStatus($request, 'arrived_at_laundry'); }
    public function onTheWayToCustomer(Request $request) { return $this->updateDeliveryStatus($request, 'on_the_way_to_customer'); }
    public function completeDelivery(Request $request)   { return $this->updateDeliveryStatus($request, 'delivered'); }

    // ======================================================
    // GET DRIVER HISTORY
    // ======================================================
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
}
