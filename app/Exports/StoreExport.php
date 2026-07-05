<?php

namespace App\Exports;

use Maatwebsite\Excel\Sheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class StoreExport implements FromView, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    private $data;

    public function __construct($stores)
    {
        $this->data = $stores;
    }

    public function view(): View
    {
        return view('admin::pages.reports.store.export_order', [
            'from_date' => $this->data[($this->data->count() - 1)]->created_at,
            'to_date' => $this->data[0]->created_at,
            'stores' => $this->data
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $startRows = 5;
                $countRows = $this->data->count() + $startRows;
                $cellRange = 'A' . $startRows . ':H' . $startRows; // All headers
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

                //Text style
                $alignmentAllCenter = [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ];

                $event->sheet->styleCells('A1:H1', [
                    'font' => array(
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16
                    ),
                    'alignment' => $alignmentAllCenter
                ]);

                $event->sheet->styleCells('A3:H3', [
                    'font' => array(
                        'name' => 'Arial',
                        'bold' => true
                    ),
                ]);

                $event->sheet->styleCells(
                    $cellRange,
                    [
                        'font' => array(
                            'name' => 'Arial',
                            'bold' => true
                        ),
                        'alignment' => $alignmentAllCenter
                    ]
                );

                $event->sheet->styleCells("A$startRows:A$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("B$startRows:B$countRows", ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]]);
                $event->sheet->styleCells("C$startRows:C$countRows", ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]]);
                $event->sheet->styleCells("D$startRows:D$countRows", ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]]);
                $event->sheet->styleCells("E$startRows:E$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("F$startRows:F$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("G$startRows:G$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("H$startRows:H$countRows", ['alignment' => $alignmentAllCenter]);

                //Make text wrap column
                $event->sheet->getDelegate()->getColumnDimension('D')->setAutoSize(false);
                $worksheet = $event->sheet->getDelegate();
                $worksheet->getColumnDimension('D')->setWidth(37);
                $worksheet->getStyle("D$startRows:D$countRows")->getAlignment()->setWrapText(true);

                //border
                $a1 = [
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            // 'color' => ['argb' => 'FFFF0000'],
                        ]
                    ]
                ];

                foreach ($columns as $column) {
                    $event->sheet->getDelegate()->getStyle($column . $startRows . ':' . $column . $countRows)->applyFromArray($a1); //border column
                    for ($i = $startRows; $i <= $countRows; $i++) {
                        $event->sheet->getDelegate()->getStyle($column . $i)->applyFromArray($a1); //border row
                        $event->sheet->getDelegate()->getStyle($column . $i)->applyFromArray($a1); //border row
                    }
                }
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
