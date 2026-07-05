<?php

namespace App\Exports;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Shop;
use App\Models\Barber;
use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpParser\Node\Expr\Cast\Bool_;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use Symfony\Component\HttpFoundation\Request;


class BookingExport implements FromView 
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view():view
    {
        return view("admin::export.excelExportBooking",[
            'bookingData' =>  $this->data
        ]);
    }
 
}
