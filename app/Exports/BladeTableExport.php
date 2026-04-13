<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BladeTableExport implements FromView, ShouldAutoSize, WithTitle
{
    /**
     * Export class generik untuk laporan berbentuk tabel.
     *
     * Kaitan dengan unit kompetensi:
     * - Unit 4: menunjukkan pemisahan logic export dari controller
     * - Unit 5: memakai array heading, row, meta, dan footer
     * - Unit 6: menjadi bagian dokumentasi implementasi export Excel
     */
    public function __construct(
        private string $title,
        private array $headings,
        private array $rows,
        private array $meta = [],
        private ?array $footerRow = null
    ) {
    }

    public function view(): View
    {
        // View Blade dipakai agar format Excel antar laporan lebih seragam dan mudah dirawat.
        return view('exports.report-table', [
            'title' => $this->title,
            'headings' => $this->headings,
            'rows' => $this->rows,
            'meta' => $this->meta,
            'footerRow' => $this->footerRow,
        ]);
    }

    public function title(): string
    {
        // Excel membatasi nama sheet maksimal 31 karakter.
        return substr($this->title, 0, 31);
    }
}
