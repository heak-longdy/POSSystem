<?php

return [
    'name' => 'Customer',
    'title' => 'Customer Management',
    'customer_report' => 'Customer Report',

    'button' => [
        'create' => 'Create Customer',
        'import' => 'Import Customers',
    ],

    'empty' => [
        'title' => 'Customer is empty',
        'description' => 'You can create a new Customer by clicking the button below.',
    ],

    'form' => [
        'title' => [
            'create' => 'Create Customer',
            'update' => 'Update Customer',
            'change_password' => 'Change Password',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name ...',
        ],
        'phone' => [
            'label' => 'Phone',
            'placeholder' => 'Enter phone ...',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'Enter email ...',
        ],
        'address' => [
            'label' => 'Address',
            'placeholder' => 'Enter address ...',
        ],
        'password' => [
            'label' => 'Password',
            'placeholder' => 'Enter password',
        ],
        'new_password' => [
            'label' => 'New Password',
            'placeholder' => 'Enter new password',
        ],
        'password_confirmation' => [
            'label' => 'Confirm Password',
            'placeholder' => 'Confirm password',
        ],
        'profile' => [
            'label' => 'Profile',
            'placeholder' => 'Enter profile',
        ],
        'id_card' => [
            'label' => 'ID Card',
            'placeholder' => 'Enter id card',
        ],
    ],

    'validation' => [
        'name_required' => 'Please enter the customer name.',
        'name_max' => 'The name may not be greater than 255 characters.',
        'password_required' => 'Password is required.',
        'password_min' => 'Password must be at least 6 characters.',
        'password_confirmed' => 'Password confirmation does not match.',
        'status_required' => 'Status is required.',
        'status_boolean' => 'Status must be true or false.',
    ],
];
