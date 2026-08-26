<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * One reusable Excel export for every report — driven entirely by the
 * headings + already-flattened rows the export Job builds from
 * ReportExportRegistry, rather than one export class per report.
 *
 * Lays out a small branded banner (business name + report title) above
 * the data so exported sheets are identifiable without the caller ever
 * knowing about a "template" — the banner is just the first few rows.
 */
class GenericReportExport implements FromArray, WithTitle, WithEvents
{
    protected const HEADING_ROW = 4;

    public function __construct(
        protected array $headings,
        protected array $rows,
        protected string $title,
        protected array $branding = []
    ) {}

    public function array(): array
    {
        return [
            [$this->branding['name'] ?? 'Bizaa'],
            [$this->title],
            [],
            $this->headings,
            ...$this->rows,
        ];
    }

    public function title(): string
    {
        // Excel sheet names can't exceed 31 chars or contain : \ / ? * [ ]
        return substr(preg_replace('/[:\\\\\/\?\*\[\]]/', '', $this->title), 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(max(count($this->headings), 1));
                $color = ltrim($this->branding['color'] ?? '#229DE7', '#');

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB($color);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('334155');

                $headingRange = 'A' . self::HEADING_ROW . ':' . $lastCol . self::HEADING_ROW;
                $sheet->getStyle($headingRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle($headingRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);

                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
