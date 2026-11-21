<?php

return [
    'name' => 'Customer',
    'title' => 'Customer Management',
    'customer_report' => 'Customer Report',

    'button' => [
        'create' => 'Create Customer',
        'import' => 'Import Customers',
    ],

    'table' => [
        'code' => 'Customer Code',
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
        'code' => [
            'label' => 'Customer Code',
            'placeholder' => 'Enter customer code ...',
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
        'image_1' => [
            'label' => 'Image 1',
            'placeholder' => 'Choose image 1',
        ],
        'image_2' => [
            'label' => 'Image 2',
            'placeholder' => 'Choose image 2',
        ],
        'id_card' => [
            'label' => 'ID Card',
            'placeholder' => 'Enter id card',
        ],
        'payment_restriction' => [
            'label' => 'Payment Restriction',
            'yes'   => 'Yes',
            'no'    => 'No',
        ],
        'location' => [
            'title' => 'Customer Location',
            'hint' => 'Click anywhere on the map or drag the pin to set the customer location.',
            'latitude' => 'Latitude',
            'latitude_placeholder' => 'e.g. 11.5564',
            'longitude' => 'Longitude',
            'longitude_placeholder' => 'e.g. 104.9282',
            'locate_me' => 'Use Current Location',
            'locating' => 'Detecting location...',
            'clear' => 'Clear Pin',
            'open_google_maps' => 'Open in Google Maps',
            'search_placeholder' => 'Search place name or coordinates (e.g. 11.5564, 104.9282)...',
            'search_btn' => 'Search',
            'searching' => 'Searching...',
            'search_no_results' => 'No location found for this search.',
            'geo_not_supported' => 'Geolocation is not supported by your browser.',
            'geo_permission_denied' => 'Location access was denied or unavailable.',
            'pin_placed' => 'Location pinned',
            'map_link' => 'Google Maps Link',
            'fullscreen' => 'Fullscreen',
            'exit_fullscreen' => 'Exit Fullscreen',
        ],
    ],

    'validation' => [
        'code_max' => 'The customer code may not be greater than 50 characters.',
        'code_unique' => 'The customer code has already been taken.',
        'name_required' => 'Please enter the customer name.',
        'name_max' => 'The name may not be greater than 255 characters.',
        'phone_required' => 'Please enter the phone number.',
        'phone_max' => 'The phone may not be greater than 50 characters.',
        'latitude_numeric' => 'The latitude must be a valid number.',
        'latitude_between' => 'The latitude must be between -90 and 90 degrees.',
        'longitude_numeric' => 'The longitude must be a valid number.',
        'longitude_between' => 'The longitude must be between -180 and 180 degrees.',
        'password_required' => 'Password is required.',
        'password_min' => 'Password must be at least 6 characters.',
        'password_confirmed' => 'Password confirmation does not match.',
        'status_required' => 'Status is required.',
        'status_boolean' => 'Status must be true or false.',
        'status_invalid' => 'The selected status is invalid.',
        'payment_restriction_required' => 'Payment restriction selection is required.',
        'payment_restriction_invalid' => 'The selected payment restriction is invalid.',
    ],

    'detail' => [
        'title' => 'Customer Details',
        'info_title' => 'Customer Information',
        'system_info' => 'System & Audit Records',
        'images_title' => 'Customer Documents & Photos',
        'location' => 'Location Coordinates',
        'view_on_map' => 'View on Google Maps',
        'no_location' => 'No location pinned',
        'slots_count' => ':count Slots',
        'no_image' => 'No image uploaded',
        'no_photo' => 'No Photo',
        'no_document' => 'No Document',
        'uploaded' => 'Uploaded',
        'profile_desc' => 'Account avatar photo',
        'image_1_desc' => 'National ID / Document 1',
        'image_2_desc' => 'Additional document / Document 2',
        'created_by' => 'Created By',
        'created_at' => 'Created Date',
        'updated_at' => 'Last Updated',
        'database_id' => 'Database ID',
        'copied' => 'Copied!',
        'copy' => 'Copy',
        'click_to_zoom' => 'Click to view full photo',
        'gallery_help' => 'Click on any image to inspect high-resolution documents in gallery view.',
        'action' => [
            'edit' => 'Edit Customer',
            'back_to_list' => 'Back to List',
        ],
    ],
];
