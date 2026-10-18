<?php

return [
    'name' => 'Position',
    'title' => 'Position Management',
    'position_report' => 'Position Report',
    'button' => [
        'create' => 'Create Position',
    ],
    'form' => [
        'title' => [
            'create' => 'Create Position',
            'update' => 'Update Position',
        ],
        'name' => [
            'label' => 'Title',
            'placeholder' => 'Enter title ...',
        ],
    ],
    'empty' => [
        'title' => 'No positions found',
        'description' => 'You can create a new position by clicking the button below.',
    ],
    'validation' => [
        'title_required' => 'Title is required',
        'title_max' => 'Title must not exceed 50 characters.',
        'status_required' => 'Status is required',
        'status_max' => 'Status must not exceed 1 character.',
        'status_numeric' => 'Status format invalid',
        'image_required' => 'Image is required',
    ],
    'message' => [
        'create_success' => 'Position created successfully.',
        'update_success' => 'Position updated successfully.',
        'save_failed' => 'Unable to save the position.',
        'disable_success' => 'Position disabled successfully.',
        'enable_success' => 'Position enabled successfully.',
        'status_failed' => 'Unable to update the position status.',
        'restore_success' => 'Position restored successfully.',
        'restore_failed' => 'Unable to restore the position.',
        'delete_success' => 'Position deleted successfully.',
        'delete_failed' => 'Unable to delete the position.',
        'request_failed' => 'Request failed.',
    ],
];
