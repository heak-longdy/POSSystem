<?php

return [
    'name' => 'Supplier',
    'title' => 'Supplier Management',

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
        'create' => 'Create Supplier',
        'create_new' => 'Create New',
        'import' => 'Import Suppliers',
        'reload' => 'Refresh',
        'search' => 'Search',
    ],
    'table' => [
        'no' => 'Nº',
        'name' => 'Name',
        'ordering' => 'Ordering',
        'status' => 'Status',
        'action' => 'Action',
    ],
    'empty' => [
        'title' => ':name is empty',
        'description' => 'You can create a new :name by clicking the button below.',
    ],
    'form' => [
        'title' => [
            'create' => 'Create Supplier',
            'update' => 'Update Supplier',
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
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name ...',
        ],
        'ordering' => [
            'label' => 'Ordering',
            'placeholder' => 'Enter ordering ...',
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
        'confirm_delete' => 'Are you sure you want to delete this supplier?',
        'confirm_action' => 'Are you sure want to :action ?',
    ],
];
