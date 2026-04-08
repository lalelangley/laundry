<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * ============================================================
 * MODEL TRANSAKSI
 * Fungsi:
 * - Merepresentasikan tabel `transaksi`
 * - Menyimpan data transaksi laundry online/offline
 * - Menyediakan relasi ke detail, pelanggan, kasir, driver,
 *   metode bayar, biaya tambahan, delivery, dan pembayaran
 *
 * Konsep:
 * - Class-object Eloquent Model
 * - Array fillable dan casts
 * - Method relasi belongsTo / hasMany
 * - Accessor attribute tambahan
 * ============================================================
 */
class Transaksi extends Model
{
    // Trait factory memudahkan pembuatan data dummy/testing.
    use HasFactory;

    // Nama tabel dan primary key model transaksi.
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    // Array field yang diizinkan untuk mass assignment.
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
        'keterangan_bayar',
        'tgl_lunas',
        'tgl_estimasi',
        'tgl_transaksi',
        'foto_bukti',
        'foto_bukti_bayar',
    ];

    // Casting tipe data agar angka dan tanggal dibaca konsisten oleh aplikasi.
    protected $casts = [
        'total_harga' => 'double',
        'total_bayar' => 'double',
        'dp' => 'double',
        'diskon' => 'double',
        'tgl_lunas' => 'date',
        'tgl_estimasi' => 'date',
        'tgl_transaksi' => 'date',
    ];

    // ========================================================
    // RELATIONSHIPS
    // Kumpulan relasi antar tabel sesuai rancangan basis data.
    // ========================================================

    // Satu transaksi memiliki banyak detail transaksi.
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    // Alias relasi detail untuk kebutuhan penamaan lain di view/controller.
    public function detail_transaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    // Satu transaksi milik satu pelanggan.
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    // Satu transaksi dicatat oleh satu kasir/admin.
    public function kasir()
    {
        return $this->belongsTo(Kasir::class, 'id_kasir', 'id_kasir');
    }

    // Satu transaksi dapat dikaitkan ke satu driver.
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }

    // Satu transaksi memakai satu metode pembayaran.
    public function metodeBayar()
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar', 'id_metode_bayar');
    }

    // Satu transaksi dapat memiliki banyak biaya tambahan.
    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'id_transaksi', 'id_transaksi');
    }

    // Accessor/helper untuk menghitung total biaya tambahan dari seluruh item terkait.
    public function getTotalBiayaTambahanAttribute()
    {
        return $this->biayaTambahan()->sum('nominal');
    }

    // Relasi transaksi ke data delivery/pengantaran.
    public function delivery()
    {
        return $this->hasMany(Delivery::class, 'id_transaksi', 'id_transaksi');
    }

    // Relasi transaksi ke riwayat pembayaran.
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_transaksi', 'id_transaksi');
    }

    // ========================================================
    // ACCESSOR TAMBAHAN
    // Dipakai untuk menghitung sisa/selisih hari estimasi.
    // ========================================================
    public function getEstimasiHariAttribute()
    {
        // [PERCABANGAN] Jika belum ada tanggal estimasi, kembalikan null.
        if (!$this->tgl_estimasi) {
            return null;
        }

        // [OBJECT + METHOD] Carbon dipakai untuk menghitung selisih hari.
        $estimasi = Carbon::parse($this->tgl_estimasi)->startOfDay();
        $today = now()->startOfDay();

        // Nilai negatif berarti estimasi sudah lewat.
        return $today->diffInDays($estimasi, false);
    }
}
