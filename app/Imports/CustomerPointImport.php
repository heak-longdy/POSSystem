<?php

namespace App\Imports;

use App\Models\CustomerPoint;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithGroupedHeadingRow;

class CustomerPointImport implements  ToCollection, WithGroupedHeadingRow
{

    public function collection(Collection $row){
        foreach($row as $val){
            CustomerPoint::create([
                "customer_id" => isset($val['customer_id']) && $val['customer_id'] ? $val['customer_id'] : 1,
                "shop_id" => $val['shop_id'],
                "brand_id" => $val['brand_id'],
                "total_point" => $val['remaing_point'],
                "total_receving_point" =>$val['total_receving_points'],
                "used_point" => $val['used_point'],
                "count_of_using_service" => $val['count_of_using_service'],
            ]);
        }
    }
    // public function model(array $row)
    // {
    //     foreach($row as $val){
    //         CustomerPoint::create([
    //             "customer_id" => isset($val['customer_id']) && $val['customer_id'] ? $val['customer_id'] : 1,
    //             "shop_id" => $val['shop_id'],
    //             "brand_id" => $val['brand_id'],
    //             "total_point" => $val['remaing_point'],
    //             "total_receving_point" =>$val['total_receving_points'],
    //             "used_point" => $val['used_point'],
    //             "count_of_using_service" => $val['count_of_using_service'],
    //         ]);
    //     }
    // }
}
