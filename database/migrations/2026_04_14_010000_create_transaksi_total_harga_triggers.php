<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_detail_transaksi_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_detail_transaksi_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_detail_transaksi_after_delete');

        DB::unprepared('
            CREATE TRIGGER trg_detail_transaksi_after_insert
            AFTER INSERT ON detail_transaksi
            FOR EACH ROW
            BEGIN
                UPDATE transaksi
                SET total_harga = (
                    SELECT COALESCE(SUM(COALESCE(subtotal, qty * harga, 0)), 0)
                    FROM detail_transaksi
                    WHERE id_transaksi = NEW.id_transaksi
                )
                WHERE id_transaksi = NEW.id_transaksi;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER trg_detail_transaksi_after_update
            AFTER UPDATE ON detail_transaksi
            FOR EACH ROW
            BEGIN
                UPDATE transaksi
                SET total_harga = (
                    SELECT COALESCE(SUM(COALESCE(subtotal, qty * harga, 0)), 0)
                    FROM detail_transaksi
                    WHERE id_transaksi = NEW.id_transaksi
                )
                WHERE id_transaksi = NEW.id_transaksi;

                IF OLD.id_transaksi IS NOT NULL AND OLD.id_transaksi <> NEW.id_transaksi THEN
                    UPDATE transaksi
                    SET total_harga = (
                        SELECT COALESCE(SUM(COALESCE(subtotal, qty * harga, 0)), 0)
                        FROM detail_transaksi
                        WHERE id_transaksi = OLD.id_transaksi
                    )
                    WHERE id_transaksi = OLD.id_transaksi;
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER trg_detail_transaksi_after_delete
            AFTER DELETE ON detail_transaksi
            FOR EACH ROW
            BEGIN
                UPDATE transaksi
                SET total_harga = (
                    SELECT COALESCE(SUM(COALESCE(subtotal, qty * harga, 0)), 0)
                    FROM detail_transaksi
                    WHERE id_transaksi = OLD.id_transaksi
                )
                WHERE id_transaksi = OLD.id_transaksi;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_detail_transaksi_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_detail_transaksi_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_detail_transaksi_after_delete');
    }
};
