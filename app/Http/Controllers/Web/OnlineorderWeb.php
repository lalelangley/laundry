<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Driver;

class OnlineorderWeb extends Controller
{
    /**
     * Tampilkan delivery yang BELUM ada driver
     */
    public function index()
{
    $deliveries = Delivery::with('transaksi')
        ->whereNull('id_driver')
        ->get();

    return view('onlineorder.listonlinedriver', compact('deliveries'));
}

    
    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'id_driver' => 'required|exists:driver,id_driver'
        ]);

        Delivery::where('id_delivery', $id)->update([
            'id_driver' => $request->id_driver,
            'status'    => 'accepted'
        ]);

        return redirect()
            ->route('onlineorder.index')
            ->with('success', 'Driver berhasil ditugaskan');
    }
}
