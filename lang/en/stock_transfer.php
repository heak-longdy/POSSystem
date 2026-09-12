<?php

return [
    'title' => 'Stock Transfer Management',
    'select_shop' => 'Select Shop',

    'table' => [
        'no' => 'Nº',
        'product' => 'Product',
        'category' => 'Category',
        'uom' => 'UOM',
        'qty' => 'Qty',
        'date' => 'Date',
        'remark' => 'Remark',
        'from_shop' => 'From Shop',
        'to_shop' => 'To Shop',
        'requested_by' => 'Requested By',
        'status' => 'Status',
    ],

    'button' => [
        'create' => 'Create Stock Transfer',
        'save' => 'Save',
        'save_new' => 'Save & New',
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
    ],

    'form' => [
        'title' => [
            'create' => 'Create Stock Transfer',
            'update' => 'Update Stock Transfer',
            'view' => 'View Stock Transfer',
        ],
        'from_shop' => 'From Shop',
        'select_from_shop' => 'Select Shop',
        'to_shop' => 'To Shop',
        'select_to_shop' => 'Select Shop',
        'product' => 'Product',
        'select_product' => 'Select Product',
        'current_stock' => 'Current Stock',
        'qty' => 'Qty',
        'placeholder_qty' => 'Enter qty ...',
        'status' => 'Status',
        'remark' => 'Remark',
        'placeholder_remark' => 'Enter remark ...',
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
        'title' => 'Stock transfer is empty',
        'description' => 'You can create a new stock transfer by clicking the button below.',
    ],

    'validation' => [
        'from_shop_required' => 'From shop is required',
        'from_shop_invalid' => 'From shop is invalid',
        'to_shop_required' => 'To shop is required',
        'to_shop_invalid' => 'To shop is invalid',
        'to_shop_different' => 'To shop must be different from from shop',
        'product_required' => 'Product is required',
        'product_invalid' => 'Product is invalid',
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
