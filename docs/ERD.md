# ERD Kasmini Laundry

Dokumen ini menjadi bukti rancangan basis data untuk aplikasi Kasmini Laundry.

```mermaid
erDiagram
    PELANGGAN ||--o{ TRANSAKSI : memiliki
    KASIR ||--o{ TRANSAKSI : memproses
    TRANSAKSI ||--o{ DETAIL_TRANSAKSI : terdiri_dari
    LAYANAN ||--o{ DETAIL_TRANSAKSI : dipilih
    JENIS_LAYANAN ||--o{ DETAIL_TRANSAKSI : bertipe
    PARFUM ||--o{ DETAIL_TRANSAKSI : opsional
    METODE_BAYAR ||--o{ TRANSAKSI : digunakan
    DRIVER ||--o{ TRANSAKSI : mengantar

    PELANGGAN {
        int id_pelanggan PK
        string nama_pelanggan
        string no_hp
        string email
        string alamat
    }

    KASIR {
        int id_kasir PK
        string nama_kasir
        string no_hp
        string password
    }

    TRANSAKSI {
        int id_transaksi PK
        int id_pelanggan FK
        int id_kasir FK
        int id_metode_bayar FK
        double total_harga
        double total_bayar
        double diskon
        string status_bayar
        string status_transaksi
        datetime tgl_transaksi
        datetime tgl_estimasi
    }

    DETAIL_TRANSAKSI {
        int id_detail_transaksi PK
        int id_transaksi FK
        int id_layanan FK
        int id_jenis_layanan FK
        int id_parfum FK
        double harga
        double qty
    }

    LAYANAN {
        int id_layanan PK
        string nama_layanan
    }

    JENIS_LAYANAN {
        int id_jenis_layanan PK
        int id_layanan FK
        string nama_jenis
        double harga
    }

    PARFUM {
        int id_parfum PK
        string nama_parfum
    }

    METODE_BAYAR {
        int id_metode_bayar PK
        string nama_metode_bayar
    }

    DRIVER {
        int id_driver PK
        string nama_driver
        string no_hp
    }
```

Catatan:
- Diagram ini disusun mengikuti model dan relasi yang ada di codebase Laravel.
- Fokus utama ERD adalah alur transaksi, pelanggan, kasir, layanan, dan pembayaran.
