<?php

namespace App\Exports;

use App\Models\Customer;
use GuzzleHttp\Psr7\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Customer_Export implements FromCollection, WithHeadings , WithStyles
{ 
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Customer::select('name','phone','address','total_point')->get();
    }
    public function headings(): array
    {
        return [
            'Name',
            'Phone Number',
            'Address',
            'Total Point',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
              // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
           
              
        ];



    }
}
