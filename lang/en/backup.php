<?php

return [
    'title'          => 'Backup & Restore',
    'subtitle'       => 'Securely backup, download, restore, and synchronize POS system data with local and cloud storage.',
    'breadcrumb'     => 'Backup & Restore',

    // Metrics
    'metrics' => [
        'total_backups'      => 'Total Backups',
        'backup_storage'    => 'Backup Storage Used',
        'database_size'     => 'Active Database Size',
        'uploads_size'      => 'Uploaded Media Size',
        'uploads_count'     => 'Media Files',
        'cloud_status'      => 'Cloud Storage',
        'cloud_connected'   => 'Connected & Ready',
        'cloud_unconfigured'=> 'Not Configured',
        'last_backup'       => 'Last Backup',
        'never'             => 'Never',
    ],

    // Disk Usage Monitoring
    'disk_usage' => [
        'title'               => 'Server Disk Usage & Storage Health',
        'local_title'         => 'Local Machine Storage Health',
        'server_title'        => 'Server Disk Usage & Storage Health',
        'subtitle'            => 'Real-time overview of filesystem storage capacity, used space, and free space availability.',
        'local_subtitle'      => 'Real-time dynamic overview of your local computer disk capacity, used space, and free space availability.',
        'server_subtitle'     => 'Real-time dynamic overview of host server filesystem capacity, used space, and free space availability.',
        'total_capacity'      => 'Total Capacity',
        'used_space'          => 'Used Space',
        'available_space'     => 'Available Space',
        'local_capacity_sub'  => 'Local Machine Disk',
        'server_capacity_sub' => 'Server Storage Capacity',
        'local_used_sub'      => 'Local Used Disk Space',
        'server_used_sub'     => 'Active Data & Backups',
        'local_free_sub'      => 'Local Free Space Available',
        'server_free_sub'     => 'Free Space on Server',
        'status_healthy'      => 'Healthy',
        'status_warning'      => 'Moderate Usage',
        'status_critical'     => 'Critical - Low Space',
        'database_share'      => 'Active Database',
        'uploads_share'       => 'Uploaded Media',
        'backups_share'       => 'Local Backups',
        'other_share'         => 'System & Application Files',
        'free_share'          => 'Free Available Space',
        'clean_hint'          => 'To optimize disk space, you can reduce maximum local backups retention or download older backups to your local computer.',
    ],

    // Tabs
    'tabs' => [
        'local_backups' => 'Server Backups',
        'cloud_backups' => 'Cloud Storage Backups',
        'restore'       => 'Restore Data',
        'settings'      => 'Cloud & Auto-Backup Settings',
    ],

    // Local Backups Table
    'table' => [
        'name'          => 'Backup Name',
        'filename'      => 'Filename',
        'type'          => 'Type',
        'size'          => 'Size',
        'tables_files'  => 'Tables / Files',
        'created_at'    => 'Created Date',
        'status'        => 'Status',
        'cloud_status'  => 'Cloud Sync',
        'actions'       => 'Actions',
        'no_backups'    => 'No server backups found. Click "Create Backup Now" to create your first backup.',
    ],

    // Types
    'types' => [
        'full'     => 'All System Data (DB + Files)',
        'database' => 'Database Only',
    ],

    // Cloud Backups Table
    'cloud_table' => [
        'filename'      => 'Cloud File',
        'size'          => 'Size',
        'last_modified' => 'Last Modified (Cloud)',
        'actions'       => 'Actions',
        'no_cloud'      => 'No backups found in cloud storage.',
        'not_configured'=> 'Cloud storage is not configured yet. Configure your S3/R2 credentials in the Settings tab to enable cloud backup & restore.',
    ],

    // Action Buttons
    'actions' => [
        'create_backup'     => 'Create Backup Now',
        'download'          => 'Download to PC',
        'upload_to_cloud'   => 'Upload to Cloud',
        'restore'           => 'Restore System',
        'restore_from_file' => 'Restore from Local File',
        'delete'            => 'Delete',
        'refresh'           => 'Refresh List',
        'test_connection'   => 'Test Cloud Connection',
        'save_settings'     => 'Save Configuration',
        'cancel'            => 'Cancel',
        'confirm_restore'   => 'Yes, Restore Now',
        'browse_file'       => 'Browse Computer',
    ],

    // Create Modal
    'create_modal' => [
        'title'             => 'Create System Backup',
        'type_label'        => 'Backup Scope',
        'type_full'         => 'All System Data (Complete Database + Uploaded Media & Files)',
        'type_db'           => 'Database Only (All Tables & Application Records)',
        'name_label'        => 'Custom Backup Name (Optional)',
        'name_placeholder'  => 'e.g. Monthly Maintenance Backup',
        'notes_label'       => 'Notes / Remarks (Optional)',
        'notes_placeholder' => 'Add any details or notes for this backup...',
        'upload_cloud_label'=> 'Upload to Cloud Storage after creation',
        'upload_cloud_hint' => 'Automatically syncs this backup to your configured cloud storage bucket.',
        'submit'            => 'Start Backup Process',
    ],

    // Restore Section
    'restore_section' => [
        'title'             => 'Restore System Data',
        'subtitle'          => 'Restore your entire POS system or database from a backup file stored on your computer or cloud storage.',
        'warning_title'     => 'Important Safety Notice',
        'warning_text'      => 'Restoring system data will overwrite the current database and uploaded files with the contents of the chosen backup package. An automated safety snapshot can be created prior to restoring.',
        'upload_box_title'  => 'Upload Backup from Local Computer',
        'upload_box_desc'   => 'Drag & drop your backup package (.zip) or database dump (.sql) here, or browse from your computer.',
        'snapshot_option'   => 'Create an automatic safety backup of current system before restoring (Recommended)',
        'selected_file'     => 'Selected File',
        'start_restore'     => 'Proceed with Restore',
    ],

    // Confirm Restore Modal
    'confirm_modal' => [
        'title'         => 'Confirm System Restore',
        'message'       => 'Are you sure you want to restore system data from',
        'consequence'   => 'Existing records will be updated or replaced according to the backup package. Do not refresh or close this browser window during the restoration process.',
    ],

    // Cloud Settings
    'settings' => [
        'title'                 => 'Cloud Storage & Automation Configuration',
        'subtitle'              => 'Configure S3-compatible cloud storage (AWS S3, Cloudflare R2, MinIO, DigitalOcean Spaces, Hostinger) for off-site backup redundancy.',
        'provider'              => 'Storage Provider',
        'endpoint'              => 'Custom Endpoint URL (Optional)',
        'endpoint_hint'         => 'Leave blank for standard AWS S3. For Cloudflare R2, MinIO, or DigitalOcean, enter the custom endpoint URL.',
        'region'                => 'Region',
        'bucket'                => 'Bucket Name',
        'access_key'            => 'Access Key ID',
        'secret_key'            => 'Secret Access Key',
        'path_prefix'           => 'Folder / Prefix',
        'path_prefix_hint'      => 'Folder inside bucket where backups will be stored (e.g. backups/).',
        'use_path_style'        => 'Use Path-Style Endpoint (Required for MinIO / local S3)',
        'auto_upload'           => 'Auto-Upload to Cloud',
        'auto_upload_hint'      => 'Automatically upload all future newly created backups to cloud storage.',
        'retention'             => 'Maximum Local Backups to Keep',
        'retention_hint'        => 'Older local backups exceeding this limit will be automatically rotated out.',
    ],

    // Messages & Alerts
    'messages' => [
        'create_success'        => 'System backup created successfully!',
        'restore_success'       => 'System data restored successfully! Application caches have been cleared.',
        'upload_cloud_success'  => 'Backup file uploaded to cloud storage successfully!',
        'delete_success'        => 'Local backup deleted successfully.',
        'delete_cloud_success'  => 'Cloud backup deleted successfully.',
        'settings_saved'        => 'Backup settings and cloud credentials saved successfully!',
        'test_success'          => 'Cloud storage connection test passed successfully!',
        'test_failed'           => 'Cloud storage connection test failed.',
        'file_required'         => 'Please select a valid backup file (.zip or .sql).',
        'operation_in_progress' => 'Processing... Please wait. Do not close this window.',
    ],
];

