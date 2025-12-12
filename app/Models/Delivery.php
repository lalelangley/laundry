<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'delivery';
    protected $primaryKey = 'id_delivery';

    protected $fillable = [
        'id_transaksi',
        'id_driver',
        'jenis',            // pickup / antar
        'alamat_tujuan',
        'status',           // pending / accepted / on_the_way_to_pickup / pickup_done / on_the_way_to_delivery / delivery_done
        'waktu',
    ];

    // ======================
    // ENUM STATUS DELIVERY
    // ======================
    public const STATUS_PENDING               = 'pending';
    public const STATUS_ACCEPTED              = 'accepted';
    public const STATUS_OTW_PICKUP            = 'on_the_way_to_pickup';
    public const STATUS_PICKUP_DONE           = 'pickup_done';
    public const STATUS_OTW_DELIVERY          = 'on_the_way_to_delivery';
    public const STATUS_DELIVERY_DONE         = 'delivery_done';

    // ======================
    // RELASI
    // ======================

    // Delivery → Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    // Delivery → Driver
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }
}
     
