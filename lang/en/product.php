<?php

return [
    'name' => 'Product',
    'name_title' => 'Name',
    'title' => 'Product Management',
    'cost' => 'Cost',
    'price' => 'Price',
    'uom' => 'UOM',
    'category' => 'Category',
    'real_estate_title' => 'Real Estate Management',
    'hotel_title' => 'Hotel Management',
    'product_report' => 'Product Report',
    'gallery' => 'Gallery',
    'dropdown' => [
        'child' => 'Child',
    ],
    'table' => [
        'category' => 'Category',
        'uom' => 'UOM',
        'cost' => 'Cost',
        'price' => 'Price',
    ],
    'button' => [
        'create' => 'Create Product',
        'import' => 'Import Products',
    ],
    'empty_gallery' => [
        'title' => 'Gallery is empty',
        'description' => 'You can add image to gallery by click the button below.'
    ],
    'export' => [
        'id' => 'ID',
        'name' => 'Name',
        'cost' => 'Cost',
        'price' => 'Price',
        'commission' => 'Commission',
        'created_at' => 'Created At',
    ],
    'validation' => [
        'category_required' => 'Category is required',
        'uom_required' => 'UOM is required',
        'name_required' => 'Name is required',
        'name_max' => 'Name must not exceed 50 characters.',
        'name_unique' => 'Name already exists.',
        'cost_required' => 'Cost is required',
        'price_required' => 'Price is required',
        'cost_numeric' => 'Cost format invalid',
        'price_numeric' => 'Price format invalid',
        'status_required' => 'Status is required',
        'status_max' => 'Status must not exceed 1 characters.',
    ],
    'form' => [
        'title' => [
            'create' => 'Create Product',
            'update' => 'Update Product',
            'copy' => 'Copy Product'
        ],
        'real_estate_title' => [
            'create' => 'Create Real Estate',
            'update' => 'Update Real Estate',
            'copy' => 'Copy Real Estate'
        ],
        'hotel_title' => [
            'create' => 'Create Hotel',
            'update' => 'Update Hotel',
            'copy' => 'Copy Hotel'
        ],
        'status' => [
            'label' => 'Status',
            'active' => 'Active',
            'disable' => 'Disable',
        ],
        'button' => [
            'update' => 'Update',
            'submit' => 'Submit',
            'save_new' => 'Save & New',
            'cancel' => 'Cancel',
            'copy' => 'Save Copy'
        ],
        'photo' => [
            'label' => 'Image',
            'thumbnail' => 'Thumbnail',
            'placeholder' => 'Enter thumbnail',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name ...',
            'en' => [
                'label' => 'Name English',
                'placeholder' => 'Enter name English'
            ],
            'km' => [
                'label' => 'Name Khmer',
                'placeholder' => 'Enter name Khmer'
            ],
            'zh' => [
                'label' => 'Name Chinese',
                'placeholder' => 'Enter name Chinese'
            ]
        ],
        'cost' => [
            'label' => 'Cost',
            'placeholder' => 'Enter cost ...',
        ],
        'price' => [
            'label' => 'Price',
            'placeholder' => 'Enter price ...',
        ],
        'category' => [
            'label' => 'Category',
            'placeholder' => 'Select Category',
        ],
        'uom' => [
            'label' => 'UOM',
            'placeholder' => 'Select UOM',
        ],
        'ordering' => [
            'label' => 'Ordering',
            'placeholder' => 'Enter ordering',
        ],
        'description' => [
            'label' => [
                'en' => 'Description English',
                'km' => 'Description Khmer',
                'zh' => 'Description Chinese',
            ],
            'placeholder' => [
                'en' => 'Enter English\'s description',
                'km' => 'Enter Khmer\'s description',
                'zh' => 'Enter Chinese\'s description',
            ],
        ],
        'discount' => [
            'label' => 'Discount',
            'placeholder' => 'Enter discount',
        ],
        'qty' => [
            'label' => 'Quantity',
            'placeholder' => 'Enter quantity',
        ],
        'point_value' => [
            'label' => 'Point Value',
            'placeholder' => 'Enter point value',
        ],
        'choose_store' => [
            'label' => 'Choose Store',
            'placeholder' => 'Select store here',
        ],
        'type' => [
            'label' => 'Choose Type',
            'placeholder' => 'Enter type',
            'data' => [
                [
                    'slug' => 'other-product',
                    'value' => 'Other Product'
                ],
                [
                    'slug' => 'new-product',
                    'value' => 'New Product'
                ],
                [
                    'slug' => 'best-product',
                    'value' => 'Best Product'
                ],
                [
                    'slug' => 'recommend-product',
                    'value' => 'Recommend Product'
                ],
            ]
        ],
        'allow_call' => [
            'label' => 'Allow Call'
        ],
        'allow_add_cart' => [
            'label' => 'Add To Cart'
        ],
        'brand' => [
            'label' => 'Choose Brand'
        ],
        'bedroom_number' => [
            'en' => [
                'label' => 'Bedroom Number',
                'placeholder' => 'Enter bedroom number'
            ],
            'km' => [
                'label' => 'Bedroom Number',
                'placeholder' => 'Enter bedroom number'
            ],
            'zh' => [
                'label' => 'Bedroom Number',
                'placeholder' => 'Enter bedroom number'
            ]
        ],
        'bathroom' => [
            'en' => [
                'label' => 'Bathroom',
                'placeholder' => 'Enter bathroom'
            ],
            'km' => [
                'label' => 'Bathroom',
                'placeholder' => 'Enter bathroom'
            ],
            'zh' => [
                'label' => 'Bathroom',
                'placeholder' => 'Enter bathroom'
            ]
        ],
        'face_position' => [
            'en' => [
                'label' => 'Face Position',
                'placeholder' => 'Enter face position'
            ],
            'km' => [
                'label' => 'Face Position',
                'placeholder' => 'Enter face position'
            ],
            'zh' => [
                'label' => 'Face Position',
                'placeholder' => 'Enter face position'
            ]
        ],
        'size' => [
            'en' => [
                'label' => 'Size',
                'placeholder' => 'Enter size'
            ],
            'km' => [
                'label' => 'Size',
                'placeholder' => 'Enter size'
            ],
            'zh' => [
                'label' => 'Size',
                'placeholder' => 'Enter size'
            ]
        ],
        'facing' => [
            'east' => "East",
            'north' => "North",
            'northeast' => "Northeast",
            'northwest' => "Northwest",
            'southeast' => "Southeast",
            'southwest' => "Southwest",
            'west' => "West",
        ]
    ],
    'description' => [
        'label' =>
        [
            'en' => 'Description English',
            'km' => 'Description Khmer',
            'zh' => 'Description Chinese',
        ],
    ],
];
