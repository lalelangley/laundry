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
        $myTasks = Delivery::with('transaksi')
            ->where('id_driver', $driverId)
            ->whereIn('status', [
                'pending',
                'accepted',
                'on_the_way_to_pickup',
                'picked_up',
                'on_the_way_to_laundry'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $myTasks,
        ]);
    }

    // ======================================================
    // ACCEPT TASK
    // ======================================================
    public function acceptTask(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver'   => 'required',
        ]);

        $delivery = Delivery::find($request->id_delivery);

        if (!$delivery) {
            return response()->json(['success' => false, 'message' => 'Task not found']);
        }

        if ($delivery->id_driver !== null) {
            return response()->json(['success' => false, 'message' => 'Task already taken']);
        }

        $delivery->id_driver = $request->id_driver;
        $delivery->status = 'accepted';
        $delivery->save();

        return response()->json(['success' => true, 'message' => 'Task accepted']);
    }

    // ======================================================
    // ON THE WAY TO PICKUP
    // ======================================================
    public function onTheWayToPickup(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = 'on_the_way_to_pickup';
        $delivery->save();

        return response()->json(['success'=>true,'message'=>'Driver on the way to pickup']);
    }

    // ======================================================
    // COMPLETE PICKUP
    // ======================================================
    public function completePickup(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = 'picked_up';
        $delivery->save();

        return response()->json(['success'=>true,'message'=>'Pickup completed']);
    }

    // ======================================================
    // ON THE WAY TO LAUNDRY
    // ======================================================
    public function onTheWayToLaundry(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = 'on_the_way_to_laundry';
        $delivery->save();

        return response()->json(['success'=>true,'message'=>'Driver heading to laundry']);
    }

    // ======================================================
    // ARRIVED AT LAUNDRY
    // ======================================================
    public function arrivedAtLaundry(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = 'arrived_at_laundry';
        $delivery->save();

        return response()->json(['success'=>true,'message'=>'Arrived at laundry']);
    }

    // ======================================================
    // ON THE WAY TO CUSTOMER
    // ======================================================
    public function onTheWayToCustomer(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = 'on_the_way_to_customer';
        $delivery->save();

        return response()->json(['success'=>true,'message'=>'Heading to customer']);
    }

    // ======================================================
    // COMPLETE DELIVERY
    // ======================================================
    public function completeDelivery(Request $request)
    {
        $request->validate([
            'id_delivery' => 'required',
            'id_driver' => 'required',
        ]);

        $delivery = Delivery::where('id_delivery', $request->id_delivery)
            ->where('id_driver', $request->id_driver)
            ->first();

        if (!$delivery) return response()->json(['success'=>false,'message'=>'Task not found']);

        $delivery->status = 'delivered';
        $delivery->save();

        return response()->json(['success'=>true,'message'=>'Delivery completed']);
    }
}
