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
        'id_jenis',
        'id_parfum',
        'gambar',
        'nama_jenis',
        'nama_parfum',
        'nama_layanan',
        'lama_hari',
        'lama_jam',
        'proses',
        'harga',
        'satuan',
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

    // Relasi ke Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    // Relasi ke Layanan
    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    // Relasi ke Jenis
    public function jenis()
    {
        return $this->belongsTo(JenisLayanan::class, 'id_jenis');
    }

    // Relasi ke Parfum
    public function parfum()
    {
        return $this->belongsTo(Parfum::class, 'id_parfum');
    }
}
