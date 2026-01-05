<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_pelanggan',
        'id_kasir',
        'id_driver',
        'id_metode_bayar',
        'nama_pelanggan',
        'no_hp',
        'total_harga',
        'total_bayar',
        'dp',
        'diskon',
        'tipe_diskon',
        'status_bayar',
        'status_transaksi',
        'jenis_transaksi',
        'keterangan',
        'tgl_lunas',
        'tgl_estimasi',
        'tgl_transaksi',
    ];

    protected $casts = [
        'total_harga' => 'double',
        'total_bayar' => 'double',
        'dp' => 'double',
        'diskon' => 'double',
        'tgl_lunas' => 'date',
        'tgl_estimasi' => 'date',
        'tgl_transaksi' => 'date',
    ];

    // ✅ Relasi - PASTIKAN NAMA TABEL & FOREIGN KEY BENAR
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function kasir()
    {
        // ⚠️ GANTI 'users' dengan nama tabel kasir yang benar
        // Misal: 'kasir', 'pegawai', atau 'karyawan'
        return $this->belongsTo(Kasir::class, 'id_kasir', 'id_kasir');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }

    public function metodeBayar()
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar', 'id_metode_bayar');
    }

    // Relasi ke detail transaksi jika ada
    public function detail_transaksi()
    {
        // ✅ Sesuaikan dengan nama tabel yang benar
        // Cek di database: detail_transaksi atau transaksi_detail?
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    // ✅ TAMBAHKAN: Accessor untuk detail (opsional)
    // Jika view menggunakan $transaksi->detail
    public function getDetailAttribute()
    {
        return $this->detailTransaksi;
    }
}
