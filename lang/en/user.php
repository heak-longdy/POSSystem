<?php

return [
    'name' => 'User',
    'title' => 'User Management',
    'customer_report' => 'Customer Report',
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
        'role' => 'Select Role',
    ],
    'button' => [
        'create' => 'Create New User',
        'import' => 'Import Users',
        'reload' => 'Refresh',
        'search' => 'Search',
    ],
    'empty' => [
        'title' => 'User is empty',
        'description' => 'You can create a new User by clicking the button below.',
    ],
    'form' => [
        'title' => [
            'change_password' => 'Change Password',
            'create' => 'Create New User',
            'update' => 'Update User',
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
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter name',
        ],
        'first_name' => [
            'label' => 'First Name',
            'placeholder' => 'Enter first name',
        ],
        'last_name' => [
            'label' => 'Last Name',
            'placeholder' => 'Enter last name',
        ],
        'email' => [
            'label' => 'Email',
            'placeholder' => 'Enter email',
        ],
        'phone' => [
            'label' => 'Phone',
            'placeholder' => 'Enter phone',
        ],
        'status' => [
            'label' => 'Status',
            'active' => 'Active',
            'disable' => 'Disable',
        ],
        'language_preference' => [
            'label' => 'Language Preference',
        ],
        'profile' => [
            'label' => 'Profile',
            'placeholder' => 'Enter profile',
        ],
        'role' => [
            'label' => 'Role',
            'placeholder' => 'Enter role',
        ],
        'gender' => [
            'label' => 'Gender',
            'placeholder' => 'Enter gender',
            'data' => [
                ['value' => 'male', 'name' => 'Male'],
                ['value' => 'female', 'name' => 'Female'],
                ['value' => 'other', 'name' => 'Other']
            ]
        ],
        'button' => [
            'update' => 'Update',
            'submit' => 'Submit',
            'cancel' => 'Cancel',
        ],
    ],
    'table' => [
        'no' => 'Nº',
        'profile' => 'Profile',
        'name' => 'Name',
        'email' => 'Email',
        'language' => 'Language',
        'post_date' => 'Post Date',
        'action' => 'Action',
        'role' => 'Role',
        'status' => 'Status',
    ],
    'roles' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'manager' => 'Manager',
        'cashier' => 'Cashier',
        'staff' => 'Staff',
    ],
    'validation' => [
        'name_required' => 'Name is required',
        'status_required' => 'Status is required',
        'status_numeric' => 'Status is invalid format',
        'language_preference_required' => 'Language Preference is required',
        'language_preference_in' => 'Language Preference is invalid',
        'email_required' => 'Email is required',
        'email_unique' => 'Email already exists',
        'email_format' => 'Please provide a valid email address.',
        'phone_unique' => 'Phone number already exists',
        'phone_required' => 'Phone is required',
        'phone_numeric' => 'Phone is invalid format',
        'identity_unique' => 'Identity already exists',
        'identity_required' => 'Identity is required',
        'identity_numeric' => 'Identity is invalid format',
        'password_required' => 'Password is required',
        'password_min' => 'Password must be at least 6 characters',
        'password_same' => 'The password does not match confirm password',
        'confirm_password_required' => 'Confirm Password is required',
        'confirm_password_min' => 'Confirm password must be at least 6 characters',
    ],
    'message' => [
        'change_password_success' => 'Change password successful!',
        'change_password_error' => 'Change password failed!',
        'cannot_modify_super_admin' => 'Cannot modify super admin permissions.',
        'permission_success' => 'Set permission successful!',
        'permission_error' => 'Failed to update permissions',
    ],
];
