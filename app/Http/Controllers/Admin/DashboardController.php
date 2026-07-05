<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QueryService;
use App\Models\RevenueDetail;
use App\Models\ExpenseDetail;
use App\Models\User;
use App\Models\CustomerPaid;

class DashboardController extends Controller
{
    public function index()
    {
        $data['totalRevenueUsd'] = 23;
        $data['totalRevenueKhr'] =  23;
        $data['totalRevenueThb'] = 23;

        $data['totalExpenseUsd'] =  23;
        $data['totalExpenseKhr'] =  23;
        $data['totalExpenseThb'] =  23;

        $data['totalCustomerPaidUsd'] = CustomerPaid::sum('amount_usd');
        $data['totalCustomerPaidKhr'] = CustomerPaid::sum('amount_kh');
        
        return view('admin::pages.dashboard')->with($data);
    }
}
