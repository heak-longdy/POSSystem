<?php
return [
    'title' => 'File Manager',
    'menu' => [
        'all_files' => 'All Files',
        'trash_bin' => 'Trash Bin',
        'setting' => 'Setting',
    ],
    'breadcrumb' => [
        'all_files' => 'All Files',
        'trash_bin' => 'Trash Bin',
        'setting' => 'Setting',
    ],
    'input' => [
        'search' => 'Search file...',
    ],
    'button' => [
        'upload_file' => 'Upload File',
        'create_folder' => 'Create Folder',
        'close' => 'Close',
        'delete' => 'Delete',
        'save' => 'Save',
        'choose_files' => 'Choose Files',
        'unselect_files' => 'Unselect Files',
        'rename' => 'Rename',
        'restore' => 'Restore',
        'delete' => 'Delete',
        'save' => 'Save',
        'reload' => 'Reload',
        'delete_all' => 'Delete All',
        'restore_all' => 'Restore All',
        'open' => 'Open',
        'copy-link' => 'Copy Link',
    ],
    'tooltip' => [
        'name' => 'Name',
        'size' => 'Size',
        'type' => 'Type',
        'dimensions' => 'Dimensions',
        'uploaded_at' => 'Date Uploaded',
        'created_at' => 'Date Created',
        'deleted_at' => 'Date Deleted',
    ],
    'create-folder' => [
        'title' => 'Create Folder',
        'form' => [
            'folder_name' => [
                'label' => 'Folder Name',
                'placeholder' => 'Folder Name',
            ],
        ],
        'message' => [
            'name_required' => 'Folder name is required',
            'name_unique' => 'Folder name is already exists',
        ],
        'button' => [
            'cancel' => 'Cancel',
            'create' => 'Create',
        ],
    ],
    'upload-file' => [
        'title' => 'Upload Files',
        'form' => [
            'file' => [
                'label' => 'Upload File',
                'placeholder' => 'Click here or drag and drop files to upload',
            ],
        ],
        'message' => [
            'file' => 'File is empty',
        ],
        'button' => [
            'close' => 'Close',
            'save' => 'Upload',
            'pause' => 'Pause',
            'cancel' => 'Cancel',
        ],
    ],
    'rename-folder' => [
        'title' => 'Rename Folder',
        'form' => [
            'folder_name' => [
                'label' => 'Folder Name',
                'placeholder' => 'Folder Name',
            ],
        ],
        'message' => [
            'name_required' => 'Folder name is required',
            'name_unique' => 'Folder name is already exists',
        ],
        'button' => [
            'cancel' => 'Cancel',
            'save' => 'Save',
        ],
    ],
    'delete-file' => [
        'title' => 'Delete',
        'confirm' => 'Are you sure to delete ',
        'form' => [
            'to_trash' => [
                'label' => 'Move to Trash',
            ],
        ],
        'button' => [
            'cancel' => 'Cancel',
            'delete' => 'Delete',
        ],
    ],
    'restore-file' => [
        'title' => 'Restore',
        'confirm' => 'Are you sure to restore ',
        'button' => [
            'cancel' => 'Cancel',
            'restore' => 'Restore',
        ],
    ],
];
