<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';

    protected $fillable = [
        'id_transaksi',
        'id_layanan',
        'id_jenis_layanan',
        'id_parfum',
        'harga',
        'qty',
        'diskon',
        'tipe_diskon',
        'total_harga',
        'status_transaksi',
        'tgl_transaksi',
    ];

    protected $casts = [
        'harga' => 'double',
        'qty' => 'double',
        'diskon' => 'double',
        'total_harga' => 'double',
        'lama_hari' => 'integer',
        'lama_jam' => 'integer',
        'status_transaksi' => 'integer',
        'tgl_transaksi' => 'date',
    ];

    // =========================
    // RELASI
    // =========================

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function jenis()
{
    return $this->belongsTo(
        JenisLayanan::class,
        'id_jenis_layanan',     
        'id_jenis_layanan'      
    );
}



    public function parfum()
    {
        return $this->belongsTo(Parfum::class, 'id_parfum');
    }
}

