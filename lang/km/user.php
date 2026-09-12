<?php

return [
    'name' => 'អ្នកប្រើប្រាស់',
    'title' => 'ការគ្រប់គ្រងអ្នកប្រើប្រាស់',
    'customer_report' => 'របាយការណ៍អតិថិជន',
    'tab' => [
        'active' => 'សកម្ម',
        'disable' => 'អសកម្ម',
        'trash' => 'ធុងសំរាម',
    ],
    'breadcrumb' => [
        'all' => 'ទាំងអស់',
    ],
    'filter' => [
        'search' => 'ស្វែងរក...',
        'all' => 'ទាំងអស់',
        'role' => 'ជ្រើសរើសតួនាទី',
    ],
    'button' => [
        'create' => 'បង្កើតអ្នកប្រើប្រាស់ថ្មី',
        'import' => 'នាំចូលអ្នកប្រើប្រាស់',
        'reload' => 'ផ្ទុកឡើងវិញ',
        'search' => 'ស្វែងរក',
    ],
    'empty' => [
        'title' => 'មិនមានអ្នកប្រើប្រាស់ទេ',
        'description' => 'អ្នកអាចបង្កើតអ្នកប្រើប្រាស់ថ្មីដោយចុចប៊ូតុងខាងក្រោម។',
    ],
    'form' => [
        'title' => [
            'change_password' => 'ប្តូរពាក្យសម្ងាត់',
            'create' => 'បង្កើតអ្នកប្រើប្រាស់ថ្មី',
            'update' => 'កែប្រែអ្នកប្រើប្រាស់',
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
        'name' => [
            'label' => 'ឈ្មោះ',
            'placeholder' => 'បញ្ចូលឈ្មោះ...',
        ],
        'first_name' => [
            'label' => 'នាមខ្លួន',
            'placeholder' => 'បញ្ចូលនាមខ្លួន',
        ],
        'last_name' => [
            'label' => 'នាមត្រកូល',
            'placeholder' => 'បញ្ចូលនាមត្រកូល',
        ],
        'email' => [
            'label' => 'អ៊ីមែល',
            'placeholder' => 'បញ្ចូលអ៊ីមែល...',
        ],
        'phone' => [
            'label' => 'លេខទូរស័ព្ទ',
            'placeholder' => 'បញ្ចូលលេខទូរស័ព្ទ...',
        ],
        'status' => [
            'label' => 'ស្ថានភាព',
            'active' => 'សកម្ម',
            'disable' => 'អសកម្ម',
        ],
        'language_preference' => [
            'label' => 'ភាសាដែលពេញចិត្ត',
        ],
        'profile' => [
            'label' => 'រូបភាពគណនី',
            'placeholder' => 'បញ្ចូលរូបភាពគណនី',
        ],
        'role' => [
            'label' => 'តួនាទី',
            'placeholder' => 'បញ្ចូលតួនាទី',
        ],
        'gender' => [
            'label' => 'ភេទ',
            'placeholder' => 'ជ្រើសរើសភេទ',
            'data' => [
                ['value' => 'male', 'name' => 'ប្រុស'],
                ['value' => 'female', 'name' => 'ស្រី'],
                ['value' => 'other', 'name' => 'ផ្សេងៗ']
            ]
        ],
        'button' => [
            'update' => 'កែប្រែ',
            'submit' => 'រក្សាទុក',
            'cancel' => 'បោះបង់',
        ],
    ],
    'table' => [
        'no' => 'ល.រ',
        'profile' => 'រូបភាពគណនី',
        'name' => 'ឈ្មោះ',
        'email' => 'អ៊ីមែល',
        'language' => 'ភាសា',
        'post_date' => 'កាលបរិច្ឆេទបង្ហោះ',
        'action' => 'សកម្មភាព',
        'role' => 'តួនាទី',
        'status' => 'ស្ថានភាព',
    ],
    'roles' => [
        'super_admin' => 'អ្នកគ្រប់គ្រងជាន់ខ្ពស់',
        'admin' => 'អ្នកគ្រប់គ្រង',
        'manager' => 'អ្នកចាត់ការ',
        'cashier' => 'បេឡាធិការ',
        'staff' => 'បុគ្គលិក',
    ],
    'validation' => [
        'name_required' => 'សូមបញ្ចូលឈ្មោះ',
        'status_required' => 'សូមជ្រើសរើសស្ថានភាព',
        'status_numeric' => 'ទម្រង់ស្ថានភាពមិនត្រឹមត្រូវ',
        'language_preference_required' => 'សូមជ្រើសរើសភាសាដែលពេញចិត្ត',
        'language_preference_in' => 'ភាសាដែលពេញចិត្តមិនត្រឹមត្រូវ',
        'email_required' => 'សូមបញ្ចូលអ៊ីមែល',
        'email_unique' => 'អ៊ីមែលនេះមានរួចហើយ',
        'email_format' => 'សូមបញ្ចូលទម្រង់អ៊ីមែលឲ្យបានត្រឹមត្រូវ',
        'phone_unique' => 'លេខទូរស័ព្ទនេះមានរួចហើយ',
        'phone_required' => 'សូមបញ្ចូលលេខទូរស័ព្ទ',
        'phone_numeric' => 'ទម្រង់លេខទូរស័ព្ទមិនត្រឹមត្រូវ',
        'identity_unique' => 'អត្តសញ្ញាណនេះមានរួចហើយ',
        'identity_required' => 'សូមបញ្ចូលអត្តសញ្ញាណ',
        'identity_numeric' => 'ទម្រង់អត្តសញ្ញាណមិនត្រឹមត្រូវ',
        'password_required' => 'សូមបញ្ចូលពាក្យសម្ងាត់',
        'password_min' => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៦ តួអក្សរ',
        'password_same' => 'ពាក្យសម្ងាត់មិនត្រូវគ្នាជាមួយការបញ្ជាក់ពាក្យសម្ងាត់ទេ',
        'confirm_password_required' => 'សូមបញ្ជាក់ពាក្យសម្ងាត់',
        'confirm_password_min' => 'ការបញ្ជាក់ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៦ តួអក្សរ',
    ],
    'message' => [
        'change_password_success' => 'ប្តូរពាក្យសម្ងាត់បានជោគជ័យ!',
        'change_password_error' => 'ការប្តូរពាក្យសម្ងាត់បានបរាជ័យ!',
        'cannot_modify_super_admin' => 'មិនអាចកែប្រែសិទ្ធិរបស់ Super Admin បានទេ។',
        'permission_success' => 'កំណត់សិទ្ធិបានជោគជ័យ!',
        'permission_error' => 'ការកំណត់សិទ្ធិបានបរាជ័យ',
    ],
];
