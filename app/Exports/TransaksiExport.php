<?php
namespace App\Exports;

use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransaksiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $filterType, $tanggalAwal, $tanggalAkhir, $statusBayar;

    public function __construct($filterType, $tanggalAwal, $tanggalAkhir, $statusBayar)
    {
        $this->filterType = $filterType;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->statusBayar = $statusBayar;
    }

    /**
     * ✅ Ganti FromQuery ke FromCollection untuk lebih stabil
     */
    public function collection()
    {
        $query = Transaksi::query()
            ->select('transaksi.*')
            ->where('status_transaksi', 'selesai');

        // Filter berdasarkan kolom tanggal
        $column = match($this->filterType) {
            'tanggal_masuk' => 'tgl_transaksi',
            'tanggal_selesai' => 'tgl_estimasi',
            'tanggal_bayar' => 'tgl_lunas',
            default => 'tgl_transaksi'
        };

        $query->whereBetween($column, [$this->tanggalAwal, $this->tanggalAkhir]);

        // Filter status bayar
        if ($this->statusBayar !== 'semua') {
            $query->where('status_bayar', $this->statusBayar);
        }

        return $query->orderBy('tgl_transaksi', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Transaksi',
            'Nama Pelanggan',
            'No HP',
            'Tanggal Transaksi',
            'Tgl Estimasi',
            'Tgl Lunas',
            'Status Bayar',
            'Status Transaksi',
            'Jenis Transaksi',
            'Total Harga',
            'Total Bayar',
            'DP',
            'Diskon',
            'Tipe Diskon',
            'Keterangan'
        ];
    }

    public function map($transaksi): array
    {
        static $no = 1;

        return [
            $no++,
            $transaksi->id_transaksi ?? '-',
            $transaksi->nama_pelanggan ?? '-',
            $transaksi->no_hp ?? '-',
            $transaksi->tgl_transaksi ? date('d/m/Y', strtotime($transaksi->tgl_transaksi)) : '-',
            $transaksi->tgl_estimasi ? date('d/m/Y', strtotime($transaksi->tgl_estimasi)) : '-',
            $transaksi->tgl_lunas ? date('d/m/Y', strtotime($transaksi->tgl_lunas)) : '-',
            $this->formatStatusBayar($transaksi->status_bayar),
            $this->formatStatusTransaksi($transaksi->status_transaksi),
            ucfirst($transaksi->jenis_transaksi ?? 'offline'),
            $transaksi->total_harga ?? 0,
            $transaksi->total_bayar ?? 0,
            $transaksi->dp ?? 0,
            $transaksi->diskon ?? 0,
            $transaksi->tipe_diskon === 'percent' ? 'Persen' : 'Nominal',
            $transaksi->keterangan ?? '-'
        ];
    }

    private function formatStatusBayar($status)
    {
        return match($status) {
            'belum_lunas' => 'Belum Lunas',
            'DP' => 'DP',
            'lunas' => 'Lunas',
            default => ucfirst($status ?? '-')
        };
    }

    private function formatStatusTransaksi($status)
    {
        return match($status) {
            'antrian' => 'Antrian',
            'proses' => 'Proses',
            'siap_di_ambil' => 'Siap Di Ambil',
            'pick_up' => 'Pick Up',
            'selesai' => 'Selesai',
            default => ucfirst($status ?? '-')
        };
    }

    public function styles(Worksheet $sheet)
    {
        // Auto size columns
        foreach(range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Style header
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FACC15']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Set row height untuk header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Format number columns dengan border
        $lastRow = $sheet->getHighestRow();
        
        if ($lastRow > 1) {
            // Border untuk semua data
            $sheet->getStyle('A1:P' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);

            // Format currency columns (K, L, M, N)
            foreach(['K', 'L', 'M', 'N'] as $col) {
                $sheet->getStyle($col . '2:' . $col . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }

            // Center alignment untuk kolom tertentu
            foreach(['A', 'H', 'I', 'J', 'O'] as $col) {
                $sheet->getStyle($col . '2:' . $col . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Data Transaksi';
    }
}