<?php

return [
    'title'    => 'ការគ្រប់គ្រងរបាយការណ៍ការបញ្ជាទិញ',
    'subtitle' => 'របាយការណ៍សង្ខេបការបញ្ជាទិញទំនិញពីអ្នកផ្គត់ផ្គង់',

    'tab' => [
        'daily'   => 'របាយការណ៍បញ្ជាទិញប្រចាំថ្ងៃ',
        'monthly' => 'របាយការណ៍បញ្ជាទិញប្រចាំខែ',
    ],

    'presets' => [
        'title'        => 'ជម្រើសលឿន:',
        'today'        => 'ថ្ងៃនេះ',
        'yesterday'    => 'ម្សិលមិញ',
        'last_7_days'  => '៧ ថ្ងៃចុងក្រោយ',
        'last_30_days' => '៣០ ថ្ងៃចុងក្រោយ',
        'this_month'   => 'ខែនេះ',
        'last_month'   => 'ខែមុន',
    ],

    'filter' => [
        'from_date'          => 'ចាប់ពីថ្ងៃ',
        'to_date'            => 'ដល់ថ្ងៃ',
        'year'               => 'ឆ្នាំ',
        'from_month'         => 'ចាប់ពីខែ',
        'to_month'           => 'ដល់ខែ',
        'shop'               => 'សាខា / ឃ្លាំង',
        'all_shops'          => 'គ្រប់សាខា',
        'supplier'           => 'អ្នកផ្គត់ផ្គង់',
        'all_suppliers'      => 'គ្រប់អ្នកផ្គត់ផ្គង់',
        'status'             => 'ស្ថានភាពទំនិញ',
        'status_all'         => 'គ្រប់ស្ថានភាព',
        'payment_status'     => 'ស្ថានភាពទូទាត់',
        'all_payment_statuses'=> 'គ្រប់ការទូទាត់',
        'keyword_search'     => 'ស្វែងរក',
        'placeholder_search' => 'លេខកូដបញ្ជាទិញ, វិក្កយបត្រ, អ្នកផ្គត់ផ្គង់...',
        'placeholder_date'   => 'YYYY-MM-DD',
    ],

    'kpi' => [
        'total_purchases'    => 'ចំនួនបញ្ជាទិញសរុប',
        'total_volume'       => 'ទឹកប្រាក់បញ្ជាទិញសរុប ($)',
        'total_paid'         => 'ទឹកប្រាក់បានទូទាត់ ($)',
        'total_outstanding'  => 'បំណុលនៅសល់ត្រូវសង ($)',
    ],

    'table' => [
        'no'                  => 'ល.រ',
        'date'                => 'កាលបរិច្ឆេទ',
        'purchase_number'     => 'លេខកូដបញ្ជាទិញ',
        'supplier_invoice_no' => 'លេខវិក្កយបត្រអ្នកផ្គត់ផ្គង់',
        'supplier'            => 'អ្នកផ្គត់ផ្គង់',
        'shop'                => 'សាខា / ឃ្លាំង',
        'items_count'         => 'ចំនួនមុខទំនិញ',
        'total_amount'        => 'ទឹកប្រាក់សរុប ($)',
        'paid_amount'         => 'បានទូទាត់ ($)',
        'remaining_amount'    => 'នៅសល់ ($)',
        'status'              => 'ស្ថានភាពទំនិញ',
        'payment_status'      => 'ស្ថានភាពទូទាត់',
        'purchases_count'     => 'ចំនួនវិក្កយបត្រ',
    ],

    'excel' => [
        'exporting' => 'កំពុងទាញយកទិន្នន័យ...',
        'filename'  => 'Purchase_Report',
        'sheet'     => 'Purchases',
    ],
];
