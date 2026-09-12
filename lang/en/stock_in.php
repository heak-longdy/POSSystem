<?php

return [
    'title' => 'Stock In Management',
    'select_shop' => 'Select Shop',

    'table' => [
        'no' => 'Nº',
        'product' => 'Product',
        'shop' => 'Shop',
        'supplier' => 'Supplier',
        'category' => 'Category',
        'uom' => 'UOM',
        'qty' => 'Qty',
        'date' => 'Date',
        'remark' => 'Remark',
        'requested_by' => 'Requested By',
        'status' => 'Status',
    ],

    'button' => [
        'create' => 'Create Stock In',
        'save' => 'Save',
        'save_new' => 'Save & New',
        'submit' => 'Submit',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
    ],

    'form' => [
        'title' => [
            'create' => 'Create Stock In',
            'update' => 'Update Stock In',
            'view' => 'View Stock In',
        ],
        'supplier' => 'Supplier',
        'select_supplier' => 'Select Supplier',
        'shop' => 'Shop',
        'select_shop' => 'Select Shop',
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
        'title' => 'Stock in is empty',
        'description' => 'You can create a new stock in by clicking the button below.',
    ],

    'validation' => [
        'supplier_required' => 'Supplier is required',
        'supplier_invalid' => 'Supplier is invalid',
        'shop_required' => 'Shop is required',
        'shop_invalid' => 'Shop is invalid',
        'product_required' => 'Product is required',
        'product_invalid' => 'Product is invalid',
        'qty_required' => 'Qty is required',
        'qty_integer' => 'Qty format invalid',
        'qty_min' => 'Qty must be at least 1',
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
