<?php

return [
    'name' => 'Staff',
    'title' => 'Staff Management',
    'subtitle' => 'Manage staff members and information',

    'button' => [
        'create' => 'Create Staff',
        'update' => 'Update Staff',
        'save' => 'Save',
        'save_new' => 'Save & New',
        'cancel' => 'Cancel',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'restore' => 'Restore',
        'destroy' => 'Destroy',
        'back' => 'Back',
    ],

    'table' => [
        'no' => 'Nº',
        'image' => 'Image',
        'name' => 'Name',
        'position' => 'Position',
        'phone_number' => 'Phone Number',
        'email' => 'Email',
        'address' => 'Address',
        'status' => 'Status',
        'action' => 'Action',
    ],

    'form' => [
        'title' => [
            'create' => 'Create Staff',
            'update' => 'Update Staff',
        ],
        'image' => [
            'label' => 'Image',
            'click_to_select' => 'Click to select image',
            'change_image' => 'Change Image',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter staff name ...',
        ],
        'position' => [
            'label' => 'Position',
            'placeholder' => '-- Select Position --',
        ],
        'phone_number' => [
            'label' => 'Phone Number',
            'placeholder' => 'Enter phone number ...',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'Enter email address ...',
        ],
        'address' => [
            'label' => 'Address',
            'placeholder' => 'Enter staff address ...',
        ],
        'status' => [
            'label' => 'Status',
            'active' => 'Active',
            'disable' => 'Disable',
        ],
    ],

    'validation' => [
        'name_required' => 'Staff name is required',
        'name_max' => 'Staff name must not exceed 255 characters',
        'position_exists' => 'Selected position is invalid',
        'email_format' => 'Email address must be a valid email format',
        'status_required' => 'Status is required',
    ],

    'empty' => [
        'title' => 'No staff found',
        'description' => 'You can create a new staff member by clicking the button above.',
    ],

    'dialog' => [
        'confirm_delete' => 'Are you sure you want to delete this staff member?',
    ],
];
