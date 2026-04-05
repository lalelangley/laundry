<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_transaksi',
        'id_layanan',
        'id_jenis_layanan',
        'id_parfum',
        'subtotal',
        'qty',
        'keterangan',
        'id_satuan',
        'tipe_diskon'
    ];

    // ================= RELATIONS =================

    public function transaksi()
    {
        return $this->belongsTo(
            Transaksi::class,
            'id_transaksi',
            'id_transaksi'
        );
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisLayanan::class, 'id_jenis_layanan');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan');
    }

    public function parfum()
    {
        return $this->belongsTo(Parfum::class, 'id_parfum');
    }
}

