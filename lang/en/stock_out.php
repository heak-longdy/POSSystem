<?php

return [
    'title' => 'Stock Out Management',
    'select_shop' => 'Select Shop',

    'table' => [
        'no' => 'Nº',
        'product' => 'Product',
        'shop' => 'Shop',
        'category' => 'Category',
        'uom' => 'UOM',
        'qty' => 'Qty',
        'date' => 'Date',
        'remark' => 'Remark',
        'to' => 'To',
        'stock_type' => 'Stock Type',
        'requested_by' => 'Requested By',
        'status' => 'Status',
    ],

    'button' => [
        'create' => 'Create Stock Out',
        'save' => 'Save',
        'save_new' => 'Save & New',
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
    ],

    'form' => [
        'title' => [
            'create' => 'Create Stock Out',
            'update' => 'Update Stock Out',
            'view' => 'View Stock Out',
        ],
        'shop' => 'Shop',
        'select_shop' => 'Select Shop',
        'product' => 'Product',
        'select_product' => 'Select Product',
        'stock_type' => 'Stock Type',
        'select_stock_type' => 'Select Stock Type',
        'to' => 'To',
        'current_stock' => 'Current Stock',
        'qty' => 'Qty',
        'placeholder_qty' => 'Enter qty ...',
        'status' => 'Status',
        'remark' => 'Remark',
        'placeholder_remark' => 'Enter remark ...',
    ],

    'stock_types' => [
        '100' => 'Sell To Customer',
        '200' => 'Shop Use',
        '300' => 'Sell To Gift',
    ],

    'status' => [
        'confirmed' => 'Confirmed',
        'disabled' => 'Disabled',
    ],

    'filter' => [
        'search_product' => 'Search product...',
        'select_shop' => 'Select Shop',
        'date' => 'Date',
    ],

    'empty' => [
        'title' => 'Stock out is empty',
        'description' => 'You can create a new stock out by clicking the button below.',
    ],

    'validation' => [
        'shop_required' => 'Shop is required',
        'shop_invalid' => 'Shop is invalid',
        'product_required' => 'Product is required',
        'product_invalid' => 'Product is invalid',
        'to_required' => 'Stock type is required',
        'to_invalid' => 'Stock type is invalid',
        'qty_required' => 'Qty is required',
        'qty_integer' => 'Qty format invalid',
        'qty_min' => 'Qty must be at least 1',
        'qty_limited' => 'Qty is limited or out of stock',
        'remark_max' => 'Remark must not exceed 1000 characters.',
    ],

    'message' => [
        'create_success' => 'Create success.',
        'create_failed' => 'Create unsuccess!',
        'update_success' => 'Update success.',
        'update_failed' => 'Update unsuccess!',
        'enable_success' => 'Enable successful!',
        'disable_success' => 'Disable successful!',
        'status_failed' => 'Status unsuccess!',
        'delete_success' => 'Delete success!',
        'delete_failed' => 'Delete unsuccess!',
        'restore_success' => 'Restore success!',
        'restore_failed' => 'Restore unsuccess!',
        'destroy_success' => 'Delete success!',
        'destroy_failed' => 'Delete unsuccess!',
    ],
];
