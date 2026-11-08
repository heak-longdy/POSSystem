<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupRecord extends Model
{
    use HasFactory;

    protected $table = 'backup_records';

    protected $fillable = [
        'name',
        'filename',
        'file_path',
        'disk',
        'type',
        'size_bytes',
        'tables_count',
        'files_count',
        'status',
        'cloud_status',
        'cloud_uploaded_at',
        'last_restored_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'size_bytes'        => 'integer',
        'tables_count'      => 'integer',
        'files_count'       => 'integer',
        'cloud_uploaded_at' => 'datetime',
        'last_restored_at'  => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}

