<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = "detail_transaksi";
    protected $primaryKey = "id_detail_transaksi";
    public $incrementing = true;

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
        'keterangan',
    ];

    // Detail belongs to Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
