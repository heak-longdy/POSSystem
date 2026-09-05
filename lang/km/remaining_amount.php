<?php

return [
    'title' => 'ការគ្រប់គ្រងទឹកប្រាក់នៅសល់',
    'excel_report' => 'របាយការណ៍ទឹកប្រាក់នៅសល់',
    'export_loading' => 'កំពុងនាំចេញរបាយការណ៍ទឹកប្រាក់នៅសល់...',

    'tab' => [
        'all_outstanding' => 'នៅសល់ទាំងអស់',
        'partial_paid' => 'បង់ប្រាក់ខ្លះ',
        'pending_payment' => 'រង់ចាំការទូទាត់',
        'fully_paid' => 'បានបង់ពេញ',
    ],

    'action' => [
        'manage_payment' => 'គ្រប់គ្រងការទូទាត់',
        'payment_history' => 'ប្រវត្តិនៃការទូទាត់',
        'send_reminder' => 'ផ្ញើសាររំលឹក',
        'edit_payment' => 'កែប្រែការទូទាត់',
        'delete_payment' => 'លុបការទូទាត់',
    ],

    'ledger' => [
        'total_amount' => 'ចំនួនទឹកប្រាក់សរុប',
        'amount_paid' => 'ចំនួនទឹកប្រាក់បានបង់',
        'remaining_balance' => 'សមតុល្យនៅសល់',
    ],

    'modal' => [
        'make_payment' => 'ធ្វើការទូទាត់',
        'payment_history' => 'ប្រវត្តិនៃការទូទាត់',
    ],

    'form' => [
        'payment_amount' => 'ចំនួនទឹកប្រាក់ទូទាត់ ($)',
        'full_balance' => 'សមតុល្យពេញ',
        'payment_method' => 'វិធីសាស្ត្រទូទាត់',
        'payment_date_time' => 'កាលបរិច្ឆេទ & ពេលវេលាទូទាត់',
        'note_reference' => 'ចំណាំ / លេខយោង (ស្រេចចិត្ត)',
    ],

    'placeholder' => [
        'note' => 'ឧ. លេខសម្គាល់ប្រតិបត្តិការ, ចំណាំបង្កាន់ដៃ...',
    ],

    'button' => [
        'send_reminder' => 'ផ្ញើសាររំលឹក',
        'sending' => 'កំពុងផ្ញើ...',
        'record_payment' => 'កត់ត្រាការទូទាត់',
        'processing_payment' => 'កំពុងដំណើរការការទូទាត់...',
    ],

    'history' => [
        'empty' => 'មិនទាន់មានប្រតិបត្តិការទូទាត់នៅឡើយទេ។',
        'date_time' => 'កាលបរិច្ឆេទ & ពេលវេលា',
        'method' => 'វិធីសាស្ត្រ',
        'amount' => 'ចំនួនទឹកប្រាក់',
        'staff' => 'បុគ្គលិក',
        'note' => 'ចំណាំ',
        'actions' => 'សកម្មភាព',
        'date' => 'កាលបរិច្ឆេទ',
    ],

    'confirm' => [
        'delete_payment_record' => 'តើអ្នកប្រាកដជាចង់លុបការទូទាត់ចំនួន <b>:amount</b> (:method) ទេ?',
    ],

    'message' => [
        'reminder_sent_success' => 'បានផ្ញើសាររំលឹកការទូទាត់ដោយជោគជ័យ។',
        'reminder_sent_booking' => 'បានផ្ញើសាររំលឹកការទូទាត់ដោយជោគជ័យសម្រាប់ការកក់ :invoice។',
        'error_record_payment' => 'មិនអាចកត់ត្រាការទូទាត់បានទេ។',
        'error_update_payment' => 'មិនអាចកែប្រែការទូទាត់បានទេ។',
        'error_delete_payment' => 'មិនអាចលុបការទូទាត់បានទេ។',
        'error_send_reminder' => 'មិនអាចផ្ញើសាររំលឹកបានទេ។',
        'cannot_add_payment_rejected' => 'មិនអាចបន្ថែមការទូទាត់ទៅលើការកក់ដែលបានបដិសេធឡើយ។',
        'booking_already_paid' => 'ការកក់នេះបានបង់ប្រាក់ពេញរួចរាល់ហើយ។',
        'payment_exceeds_available' => 'ចំនួនទឹកប្រាក់ទូទាត់លើសពីសមតុល្យដែលអាចប្រើបាន។',
        'cannot_edit_payment_rejected' => 'មិនអាចកែប្រែការទូទាត់លើការកក់ដែលបានបដិសេធឡើយ។',
        'cannot_delete_payment_rejected' => 'មិនអាចលុបការទូទាត់លើការកក់ដែលបានបដិសេធឡើយ។',
        'cannot_send_reminder_rejected' => 'មិនអាចផ្ញើសាររំលឹកសម្រាប់ការកក់ដែលបានបដិសេធឡើយ។',
        'no_outstanding_balance' => 'ការកក់នេះមិនមានសមតុល្យនៅសល់ត្រូវទូទាត់ឡើយ។',
    ],

    'notification' => [
        'reminder_title' => 'សាររំលឹកការទូទាត់៖ ការកក់ :invoice',
        'reminder_desc' => 'សូមជម្រាបសួរ :customer នេះជាសាររំលឹកថាអ្នកមានសមតុល្យនៅសល់ចំនួន :remaining (សរុប៖ :total, បានបង់៖ :paid) សម្រាប់ការកក់របស់អ្នកនៅថ្ងៃ :date។',
    ],

    'excel' => [
        'sheet_name' => 'របាយការណ៍ទឹកប្រាក់នៅសល់',
        'file_prefix' => 'របាយការណ៍_ទឹកប្រាក់នៅសល់_',
        'booking_id' => 'លេខកូដកក់',
        'booking_date' => 'កាលបរិច្ឆេទកក់',
        'shop' => 'ហាង',
        'barber' => 'ជាងកាត់សក់',
        'customer_phone' => 'លេខទូរស័ព្ទអតិថិជន',
        'pay_status' => 'ស្ថានភាពទូទាត់',
        'total_price' => 'តម្លៃសរុប',
        'paid_amount' => 'ចំនួនទឹកប្រាក់បានបង់',
        'remaining_balance' => 'សមតុល្យនៅសល់',
        'last_pay_date' => 'កាលបរិច្ឆេទបង់ប្រាក់ចុងក្រោយ',
    ],
];
