<?php

namespace App\Exports;

use Maatwebsite\Excel\Sheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Color;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class OrderExport implements FromView, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    private $data;

    public function __construct($orders)
    {
        $this->data = $orders;
    }

    public function view(): View
    {
        return view('admin::pages.reports.order.export_order', [
            'from_date' => $this->data[($this->data->count() - 1)]->created_at,
            'to_date' => $this->data[0]->created_at,
            'orders' => $this->data
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $startRows = 6;
                $countRows = $this->data->count() + $startRows;
                $cellRange = 'A' . $startRows . ':I' . $startRows; // All headers
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

                //Text style
                $alignmentAllCenter = [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ];

                $event->sheet->styleCells('A1:I1', [
                    'font' => array(
                        'name' => 'Arial',
                        'bold' => true,
                        'size' => 16
                    ),
                    'alignment' => $alignmentAllCenter
                ]);

                $event->sheet->styleCells('A3:I3', [
                    'font' => array(
                        'name' => 'Arial',
                        'bold' => true
                    ),
                ]);

                //Heading
                $event->sheet->styleCells('A4:D4', [
                    'font' => array(
                        'name' => 'Arial',
                        'color' => ['argb' => Color::COLOR_WHITE],
                        'bold' => true
                    ),
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => Color::COLOR_BLACK],
                    ],
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
                $event->sheet->styleCells("B$startRows:B$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("C$startRows:C$countRows", ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]]);
                $event->sheet->styleCells("D$startRows:D$countRows", ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]]);
                $event->sheet->styleCells("E$startRows:E$countRows", ['alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]]);
                $event->sheet->styleCells("F$startRows:F$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("G$startRows:G$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("H$startRows:H$countRows", ['alignment' => $alignmentAllCenter]);
                $event->sheet->styleCells("I$startRows:I$countRows", ['alignment' => $alignmentAllCenter]);

                $event->sheet->getDelegate()->getColumnDimension('E')->setAutoSize(false);

                $worksheet = $event->sheet->getDelegate();
                $worksheet->getColumnDimension('E')->setWidth(37);
                $worksheet->getStyle("E$startRows:E$countRows")->getAlignment()->setWrapText(true);
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
            'D' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_ACCOUNTING_USD,
        ];
    }
}
