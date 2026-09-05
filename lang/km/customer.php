<?php

return [
    'name' => 'អតិថិជន',
    'title' => 'ការគ្រប់គ្រងអតិថិជន',
    'customer_report' => 'របាយការណ៍អតិថិជន',

    'button' => [
        'create' => 'បង្កើតអតិថិជន',
        'import' => 'នាំចូលអតិថិជន',
    ],

    'empty' => [
        'title' => 'មិនមានអតិថិជនទេ',
        'description' => 'អ្នកអាចបង្កើតអតិថិជនថ្មីដោយចុចប៊ូតុងខាងក្រោម។',
    ],

    'form' => [
        'title' => [
            'create' => 'បង្កើតអតិថិជន',
            'update' => 'កែប្រែអតិថិជន',
            'change_password' => 'ប្តូរពាក្យសម្ងាត់',
        ],
        'name' => [
            'label' => 'ឈ្មោះ',
            'placeholder' => 'បញ្ចូលឈ្មោះ...',
        ],
        'phone' => [
            'label' => 'លេខទូរស័ព្ទ',
            'placeholder' => 'បញ្ចូលលេខទូរស័ព្ទ...',
        ],
        'email' => [
            'label' => 'អ៊ីមែល',
            'placeholder' => 'បញ្ចូលអ៊ីមែល...',
        ],
        'address' => [
            'label' => 'អាសយដ្ឋាន',
            'placeholder' => 'បញ្ចូលអាសយដ្ឋាន...',
        ],
        'password' => [
            'label' => 'ពាក្យសម្ងាត់',
            'placeholder' => 'បញ្ចូលពាក្យសម្ងាត់',
        ],
        'new_password' => [
            'label' => 'ពាក្យសម្ងាត់ថ្មី',
            'placeholder' => 'បញ្ចូលពាក្យសម្ងាត់ថ្មី',
        ],
        'password_confirmation' => [
            'label' => 'បញ្ជាក់ពាក្យសម្ងាត់',
            'placeholder' => 'បញ្ជាក់ពាក្យសម្ងាត់',
        ],
        'profile' => [
            'label' => 'រូបភាពគណនី',
            'placeholder' => 'ជ្រើសរើសរូបភាព',
        ],
        'id_card' => [
            'label' => 'អត្តសញ្ញាណប័ណ្ណ',
            'placeholder' => 'បញ្ចូលអត្តសញ្ញាណប័ណ្ណ',
        ],
    ],

    'validation' => [
        'name_required' => 'សូមបញ្ចូលឈ្មោះអតិថិជន',
        'name_max' => 'ឈ្មោះមិនត្រូវលើសពី ២៥៥ តួអក្សរទេ',
        'password_required' => 'សូមបញ្ចូលពាក្យសម្ងាត់',
        'password_min' => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៦ តួអក្សរ',
        'password_confirmed' => 'ការបញ្ជាក់ពាក្យសម្ងាត់មិនត្រូវគ្នាទេ',
        'status_required' => 'សូមជ្រើសរើសស្ថានភាព',
        'status_boolean' => 'ស្ថានភាពមិនត្រឹមត្រូវ',
    ],
];
