<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    use HasFactory;

    protected $table = 'backup_settings';

    protected $fillable = [
        'cloud_driver',
        's3_endpoint',
        's3_region',
        's3_bucket',
        's3_access_key',
        's3_secret_key',
        's3_path_prefix',
        's3_use_path_style',
        'auto_backup_enabled',
        'auto_backup_frequency',
        'auto_upload_cloud',
        'max_local_backups',
        'disk_quota_gb',
    ];

    protected $casts = [
        's3_use_path_style'   => 'boolean',
        'auto_backup_enabled' => 'boolean',
        'auto_upload_cloud'   => 'boolean',
        'max_local_backups'   => 'integer',
        'disk_quota_gb'       => 'integer',
    ];

    /**
     * Retrieve the active setting or create default.
     */
    public static function getActive(): self
    {
        return static::firstOrCreate([], [
            'cloud_driver'         => 's3',
            's3_region'            => env('AWS_DEFAULT_REGION', 'us-east-1'),
            's3_bucket'            => env('AWS_BUCKET', ''),
            's3_access_key'        => env('AWS_ACCESS_KEY_ID', ''),
            's3_secret_key'        => env('AWS_SECRET_ACCESS_KEY', ''),
            's3_path_prefix'       => 'backups/',
            's3_use_path_style'    => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'auto_backup_enabled'  => false,
            'auto_backup_frequency'=> 'daily',
            'auto_upload_cloud'    => false,
            'max_local_backups'    => 15,
            'disk_quota_gb'        => 50,
        ]);
    }

    /**
     * Determine if cloud storage credentials are configured.
     */
    public function isCloudConfigured(): bool
    {
        return !empty($this->s3_bucket) && !empty($this->s3_access_key) && !empty($this->s3_secret_key);
    }
}

