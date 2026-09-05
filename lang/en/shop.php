<?php

return [
    'name' => 'Shop',
    'title' => 'Shop Management',
    'subTitle' => 'SubShop Management',
    'shop_report' => 'Shop Report',

    'tab' => [
        'shop' => 'Shop',
        'product' => 'Product',
    ],

    'table' => [
        'product' => 'Product',
        'category' => 'Category',
        'uom' => 'UOM',
        'price' => 'Price',
        'point' => 'Point',
        'max_qty' => 'Max Qty',
        'commission' => 'Commission',
        'commission_type' => 'Commission Type',
    ],

    'action' => [
        'product' => 'Product',
    ],

    'button' => [
        'create' => 'Create Shop',
        'add_product' => 'Add Product to Shop',
        'add_product_row' => 'Add Product',
    ],

    'form' => [
        'title' => [
            'create' => 'Create Shop',
            'update' => 'Update Shop',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name ...',
        ],
        'phone' => [
            'label' => 'Phone Number',
            'placeholder' => 'Enter phone ...',
        ],
        'address' => [
            'label' => 'Address',
            'placeholder' => 'Enter address ...',
        ],
        'image' => [
            'label' => 'Image',
        ],
    ],

    'product' => [
        'title' => 'Shop Product',
        'title_create' => 'Add Product to Shop',
        'title_edit' => 'Update Shop Product',
        'toolbar_title' => 'Product assignment',
        'toolbar_desc' => 'Choose products and configure shop-specific price, points, limit, commission, and status.',
        'all_assigned_alert' => 'All active products are already assigned to this shop.',
        'assigned_product' => 'Assigned product',
        'product_label' => 'Product',
        'select_product' => 'Select Product',
        'product' => 'Product',
    ],

    'placeholder' => [
        'enter_price' => 'Enter price ...',
        'enter_point' => 'Enter point ...',
        'enter_max_qty' => 'Enter max qty ...',
        'enter_commission' => 'Enter commission ...',
        'search' => 'Search...',
    ],

    'select_type' => 'Select Type',
    'commission_usd' => 'USD ($)',
    'commission_percent' => '%',

    'empty' => [
        'title' => 'No product',
        'description' => 'You can add a product to this shop by clicking the button below',
        'trash_title' => 'No trash product',
        'trash_description' => 'Trash product data not found.',
    ],

    'message' => [
        'products_saved' => 'Shop products saved successfully.',
        'products_save_failed' => 'Shop products save unsuccess!',
        'delete_success' => 'Delete success!',
        'delete_failed' => 'Delete unsuccess!',
        'restore_success' => 'Restore success!',
        'restore_failed' => 'Move to restore unsuccess!',
        'destroy_success' => 'Delete success!',
        'destroy_failed' => 'Delete unsuccess!',
    ],

    'validation' => [
        'name_required' => 'Name is required',
        'name_max' => 'Name must not exceed 50 characters.',
        'name_unique' => 'Name already exists.',
        'phone_required' => 'Phone is required',
        'phone_max' => 'Phone must not exceed 20 characters.',
        'address_required' => 'Address is required',
        'status_required' => 'Status is required',
        'status_max' => 'Status must not exceed 1 characters.',

        'products_required' => 'Please add at least one product.',
        'products_array' => 'Product data format is invalid.',
        'product_id_required' => 'Product is required.',
        'product_id_exists' => 'Selected product does not exist.',
        'product_id_distinct' => 'Product must not be duplicated.',
        'price_required' => 'Price is required.',
        'price_numeric' => 'Price format invalid.',
        'price_min' => 'Price must be greater than or equal to 0.',
        'point_numeric' => 'Point format invalid.',
        'point_min' => 'Point must be greater than or equal to 0.',
        'max_qty_integer' => 'Max quantity format invalid.',
        'max_qty_min' => 'Max quantity must be greater than or equal to 0.',
        'commission_numeric' => 'Commission format invalid.',
        'commission_min' => 'Commission must be greater than or equal to 0.',
        'commission_type_required' => 'Commission type is required.',
        'commission_type_invalid' => 'Commission type is invalid.',
        'status_invalid' => 'Status is invalid.',
        'product_already_exists' => 'This product already exists in this shop.',
        'shop_product_not_belong' => 'This shop product does not belong to the selected shop.',
        'cannot_change_product' => 'Existing shop product cannot be changed to another product.',
    ],
];
