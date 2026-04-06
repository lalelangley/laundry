# UML Kasmini Laundry

## Use Case Diagram

```mermaid
flowchart LR
    Pelanggan[Pelanggan]
    Kasir[Kasir]
    Admin[Admin]
    Bot[Telegram Bot]
    Email[Email Service]

    Pelanggan --> UC1[Lihat invoice]
    Pelanggan --> UC2[Login aplikasi mobile]
    Kasir --> UC3[Input transaksi]
    Kasir --> UC4[Checkout pembayaran]
    Kasir --> UC5[Bagikan hasil pembayaran]
    Admin --> UC6[Lihat laporan]
    Admin --> UC7[Kelola data master]
    UC5 --> Bot
    UC5 --> Email
```

## Class Diagram

```mermaid
classDiagram
    class Pelanggan {
        +id_pelanggan
        +nama_pelanggan
        +no_hp
        +email
    }

    class Kasir {
        +id_kasir
        +nama_kasir
        +no_hp
    }

    class Transaksi {
        +id_transaksi
        +total_harga
        +total_bayar
        +diskon
        +status_bayar
        +tgl_transaksi
    }

    class DetailTransaksi {
        +id_detail_transaksi
        +qty
        +harga
    }

    class Layanan {
        +id_layanan
        +nama_layanan
    }

    class JenisLayanan {
        +id_jenis_layanan
        +nama_jenis
        +harga
    }

    class MetodeBayar {
        +id_metode_bayar
        +nama_metode_bayar
    }

    Pelanggan "1" --> "many" Transaksi
    Kasir "1" --> "many" Transaksi
    Transaksi "1" --> "many" DetailTransaksi
    Layanan "1" --> "many" DetailTransaksi
    JenisLayanan "1" --> "many" DetailTransaksi
    MetodeBayar "1" --> "many" Transaksi
```

Catatan:
- Use case menekankan pembagian jobdesc pelanggan, kasir, dan admin.
- Class diagram mengikuti model utama yang dipakai oleh sistem transaksi laundry.
