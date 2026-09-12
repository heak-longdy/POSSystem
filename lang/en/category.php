<?php

return [
    'name' => 'Category',
    'title' => 'Category Management',
    'subTitle' => 'Subcategory Management',
    'dropdown' => [
        'subcategory' => 'Subcategory',
    ],
    'tab' => [
        'active' => 'Active',
        'disable' => 'Disable',
        'trash' => 'Trash',
    ],
    'breadcrumb' => [
        'all' => 'All',
    ],
    'filter' => [
        'search' => 'Search...',
        'all' => 'All',
    ],
    'button' => [
        'create' => 'Create Category',
        'create_new' => 'Create New',
        'import' => 'Import Categories',
        'reload' => 'Refresh',
        'search' => 'Search',
    ],
    'table' => [
        'no' => 'Nº',
        'name' => 'Name',
        'status' => 'Status',
        'action' => 'Action',
    ],
    'empty' => [
        'title' => ':name is empty',
        'description' => 'You can create a new :name by clicking the button below.',
    ],
    'form' => [
        'title' => [
            'create' => 'Create Category',
            'update' => 'Update Category',
        ],
        'status' => [
            'label' => 'Status',
            'active' => 'Active',
            'disable' => 'Disable',
        ],
        'button' => [
            'update' => 'Update',
            'submit' => 'Submit',
            'cancel' => 'Cancel',
        ],
        'photo' => [
            'label' => 'Image',
            'placeholder' => 'Enter picture',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name ...',
            'en' => [
                'label' => 'Title English',
                'placeholder' => 'Enter title English',
            ],
            'km' => [
                'label' => 'Title Khmer',
                'placeholder' => 'Enter title Khmer',
            ],
            'zh' => [
                'label' => 'Title Chinese',
                'placeholder' => 'Enter title Chinese',
            ],
        ],
        'ordering' => [
            'label' => 'Ordering',
            'placeholder' => 'Enter ordering',
        ],
        'popular_ordering' => [
            'label' => 'Ordering Popular',
            'placeholder' => 'Enter ordering popular',
        ],
        'type' => [
            'label' => 'Type',
            'placeholder' => 'Please select type',
        ],
    ],
    'validation' => [
        'name_required' => 'Name is required',
        'name_max' => 'Name must not exceed 50 characters.',
        'status_required' => 'Status is required',
        'status_max' => 'Status must not exceed 1 characters.',
        'status_numeric' => 'Status format invalid',
    ],
    'dialog' => [
        'confirm_delete' => 'Are you sure you want to delete this category?',
        'confirm_action' => 'Are you sure want to :action ?',
    ],
];
