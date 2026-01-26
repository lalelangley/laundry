<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'id_biaya_tambahan',  // ✅ Added missing field
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
        'keterangan_bayar',
        'tgl_lunas',
        'tgl_estimasi',
        'tgl_transaksi',
        'foto_bukti',
        'foto_bukti_bayar',
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

    // Relationships
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    public function detail_transaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function kasir()
    {
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

    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'id_transaksi', 'id_transaksi');
    }

    // Helper untuk total biaya tambahan
    public function getTotalBiayaTambahanAttribute()
    {
        return $this->biayaTambahan()->sum('nominal');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'id_transaksi', 'id_transaksi');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_transaksi', 'id_transaksi');
    }

    // ✅ Fixed attribute accessor
    public function getEstimasiHariAttribute()
    {
        if (!$this->tgl_estimasi) {
            return null;
        }

        $estimasi = Carbon::parse($this->tgl_estimasi)->startOfDay();
        $today = now()->startOfDay();

        return $today->diffInDays($estimasi, false);
    }
}