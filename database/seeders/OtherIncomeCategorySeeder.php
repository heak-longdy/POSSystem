<?php

namespace Database\Seeders;

use App\Models\OtherIncomeCategory;
use Illuminate\Database\Seeder;

class OtherIncomeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'              => 'Supplier Volume Bonus',
                'code'              => 'supplier_bonus',
                'requires_supplier' => true,
                'description'       => 'Quarterly or periodic purchase volume bonus received from suppliers',
                'ordering'          => 1,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Supplier Cashback / Rebate',
                'code'              => 'supplier_cashback',
                'requires_supplier' => true,
                'description'       => 'Direct purchase cashback or promotional rebates from suppliers',
                'ordering'          => 2,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Marketing & Display Allowance',
                'code'              => 'supplier_marketing',
                'requires_supplier' => true,
                'description'       => 'Promotional compensation, product shelf space, and display allowances',
                'ordering'          => 3,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Damaged / Return Compensation',
                'code'              => 'damaged_compensation',
                'requires_supplier' => true,
                'description'       => 'Settlement or cash reimbursements for damaged or returned inventory',
                'ordering'          => 4,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Scrap & Packaging Material Sale',
                'code'              => 'scrap_sales',
                'requires_supplier' => false,
                'description'       => 'Revenue from selling empty cartons, pallets, and recyclable scrap',
                'ordering'          => 5,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Rental & Subletting Income',
                'code'              => 'rental_sublet',
                'requires_supplier' => false,
                'description'       => 'Rent received from ATM placement, food/drink kiosks, or sublet space',
                'ordering'          => 6,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Interest & Forex Gain',
                'code'              => 'financial_gain',
                'requires_supplier' => false,
                'description'       => 'Bank interest and favorable currency exchange gains',
                'ordering'          => 7,
                'status'            => 1,
                'is_system'         => true,
            ],
            [
                'name'              => 'Miscellaneous Income',
                'code'              => 'miscellaneous',
                'requires_supplier' => false,
                'description'       => 'Other non-operating business receipts',
                'ordering'          => 8,
                'status'            => 1,
                'is_system'         => true,
            ],
        ];

        foreach ($categories as $cat) {
            OtherIncomeCategory::updateOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
