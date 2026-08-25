<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Downloadable "fill this in" template for bulk product import — headers
 * for only the required fields, plus real Excel dropdowns for Category
 * and Unit sourced from the current business's own categories/units so
 * users can't type a name that won't match anything on import.
 */
class ProductImportTemplateExport implements FromArray, WithHeadings, WithEvents
{
    protected const MAX_ROWS = 500;

    protected array $categoryNames;
    protected array $unitNames;

    public function __construct(protected int $businessId)
    {
        $this->categoryNames = Category::where('business_id', $businessId)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $this->unitNames = Unit::where('business_id', $businessId)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function headings(): array
    {
        return [
            'Product Name*',
            'Category*',
            'Unit*',
            'Buying Price*',
            'Selling Price*',
            'Opening Stock*',
        ];
    }

    public function array(): array
    {
        if (empty($this->categoryNames) || empty($this->unitNames)) {
            return [];
        }

        // One example row to show the expected format — uses the business's
        // own first category/unit so it's a valid row, not just a placeholder.
        return [
            ['Example Product', $this->categoryNames[0], $this->unitNames[0], 100, 150, 20],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->styleHeader($sheet);
                $this->styleExampleRow($sheet);
                $this->addDropdowns($sheet);
            },
        ];
    }

    protected function styleHeader(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('229DE7');

        foreach (['A' => 22, 'B' => 20, 'C' => 16, 'D' => 16, 'E' => 16, 'F' => 16] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    protected function styleExampleRow(Worksheet $sheet): void
    {
        if (empty($this->array())) {
            return;
        }

        $sheet->getStyle('A2:F2')->getFont()->setItalic(true)->getColor()->setRGB('94A3B8');
    }

    protected function addDropdowns(Worksheet $sheet): void
    {
        $spreadsheet = $sheet->getParent();
        $lookup = $spreadsheet->createSheet();
        $lookup->setTitle('Lists');

        foreach ($this->categoryNames as $i => $name) {
            $lookup->setCellValue('A' . ($i + 1), $name);
        }
        foreach ($this->unitNames as $i => $name) {
            $lookup->setCellValue('B' . ($i + 1), $name);
        }

        $lookup->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $categoryValidation = $this->buildValidation(
            'Invalid category',
            'Please pick a category from the dropdown list.',
            'Lists!$A$1:$A$' . max(count($this->categoryNames), 1)
        );

        $unitValidation = $this->buildValidation(
            'Invalid unit',
            'Please pick a unit from the dropdown list.',
            'Lists!$B$1:$B$' . max(count($this->unitNames), 1)
        );

        for ($row = 2; $row <= self::MAX_ROWS; $row++) {
            $sheet->getCell("B{$row}")->setDataValidation(clone $categoryValidation);
            $sheet->getCell("C{$row}")->setDataValidation(clone $unitValidation);
        }
    }

    protected function buildValidation(string $errorTitle, string $error, string $formula1): DataValidation
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle($errorTitle);
        $validation->setError($error);
        $validation->setFormula1($formula1);

        return $validation;
    }
}
