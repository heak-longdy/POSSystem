<?php

return [
    'title' => 'ការគ្រប់គ្រងការបញ្ចូលស្តុក',
    'select_shop' => 'ជ្រើសរើសហាង',

    'table' => [
        'no' => 'ល.រ',
        'product' => 'ផលិតផល',
        'shop' => 'ហាង',
        'supplier' => 'អ្នកផ្គត់ផ្គង់',
        'category' => 'ប្រភេទ',
        'uom' => 'ខ្នាត',
        'qty' => 'បរិមាណ',
        'date' => 'កាលបរិច្ឆេទ',
        'remark' => 'ចំណាំ',
        'requested_by' => 'ស្នើសុំដោយ',
        'status' => 'ស្ថានភាព',
    ],

    'button' => [
        'create' => 'បង្កើតការបញ្ចូលស្តុក',
        'save' => 'រក្សាទុក',
        'save_new' => 'រក្សាទុក & បង្កើតថ្មី',
        'submit' => 'រក្សាទុក',
        'cancel' => 'បោះបង់',
        'edit' => 'កែប្រែ',
    ],

    'form' => [
        'title' => [
            'create' => 'បង្កើតការបញ្ចូលស្តុក',
            'update' => 'កែប្រែការបញ្ចូលស្តុក',
            'view' => 'មើលការបញ្ចូលស្តុក',
        ],
        'supplier' => 'អ្នកផ្គត់ផ្គង់',
        'select_supplier' => 'ជ្រើសរើសអ្នកផ្គត់ផ្គង់',
        'shop' => 'ហាង',
        'select_shop' => 'ជ្រើសរើសហាង',
        'product' => 'ផលិតផល',
        'select_product' => 'ជ្រើសរើសផលិតផល',
        'current_stock' => 'ស្តុកបច្ចុប្បន្ន',
        'qty' => 'បរិមាណ',
        'placeholder_qty' => 'បញ្ចូលបរិមាណ...',
        'status' => 'ស្ថានភាព',
        'remark' => 'ចំណាំ',
        'placeholder_remark' => 'បញ្ចូលចំណាំ...',
    ],

    'status' => [
        'confirmed' => 'បានបញ្ជាក់',
        'disabled' => 'អសកម្ម',
    ],

    'filter' => [
        'search_product' => 'ស្វែងរកផលិតផល...',
        'select_shop' => 'ជ្រើសរើសហាង',
        'date' => 'កាលបរិច្ឆេទ',
    ],

    'empty' => [
        'title' => 'មិនមានការបញ្ចូលស្តុកទេ',
        'description' => 'អ្នកអាចបង្កើតការបញ្ចូលស្តុកថ្មីដោយចុចប៊ូតុងខាងក្រោម។',
    ],

    'validation' => [
        'supplier_required' => 'សូមជ្រើសរើសអ្នកផ្គត់ផ្គង់',
        'supplier_invalid' => 'អ្នកផ្គត់ផ្គង់មិនត្រឹមត្រូវ',
        'shop_required' => 'សូមជ្រើសរើសហាង',
        'shop_invalid' => 'ហាងមិនត្រឹមត្រូវ',
        'product_required' => 'សូមជ្រើសរើសផលិតផល',
        'product_invalid' => 'ផលិតផលមិនត្រឹមត្រូវ',
        'qty_required' => 'សូមបញ្ចូលបរិមាណ',
        'qty_integer' => 'ទម្រង់បរិមាណមិនត្រឹមត្រូវ',
        'qty_min' => 'បរិមាណត្រូវមានយ៉ាងតិច ១',
        'remark_max' => 'ចំណាំមិនត្រូវលើសពី ១០០០ តួអក្សរឡើយ',
    ],

    'message' => [
        'create_success' => 'បង្កើតបានជោគជ័យ!',
        'create_failed' => 'បង្កើតមិនជោគជ័យ!',
        'update_success' => 'កែប្រែបានជោគជ័យ!',
        'update_failed' => 'កែប្រែមិនជោគជ័យ!',
        'enable_success' => 'បើកដំណើរការបានជោគជ័យ!',
        'disable_success' => 'បិទដំណើរការបានជោគជ័យ!',
        'status_failed' => 'ប្តូរស្ថានភាពមិនជោគជ័យ!',
        'delete_success' => 'លុបបានជោគជ័យ!',
        'delete_failed' => 'លុបមិនជោគជ័យ!',
        'restore_success' => 'ស្តារឡើងវិញបានជោគជ័យ!',
        'restore_failed' => 'ស្តារឡើងវិញមិនជោគជ័យ!',
        'destroy_success' => 'លុបជាអចិន្ត្រៃយ៍បានជោគជ័យ!',
        'destroy_failed' => 'លុបជាអចិន្ត្រៃយ៍មិនជោគជ័យ!',
    ],
];
