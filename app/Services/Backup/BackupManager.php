<?php

namespace App\Services\Backup;

use App\Models\BackupRecord;
use App\Models\BackupSetting;
use App\Services\Backup\Cloud\CloudStorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class BackupManager
{
    protected DatabaseBackupService $dbService;
    protected FileBackupService $fileService;
    protected CloudStorageManager $cloudManager;
    protected string $backupDir;

    public function __construct(
        DatabaseBackupService $dbService,
        FileBackupService $fileService,
        CloudStorageManager $cloudManager
    ) {
        $this->dbService = $dbService;
        $this->fileService = $fileService;
        $this->cloudManager = $cloudManager;

        $this->backupDir = storage_path('app/backups');
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
    }

    public function getCloudManager(): CloudStorageManager
    {
        return $this->cloudManager;
    }

    /**
     * Create a new backup (Full or Database only).
     */
    public function createBackup(array $options = [], ?int $userId = null): BackupRecord
    {
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        $type = $options['type'] ?? 'full'; // 'full' or 'database'
        $isFull = ($type === 'full');
        $customName = $options['name'] ?? null;
        $notes = $options['notes'] ?? null;
        $uploadToCloud = !empty($options['upload_cloud']);

        $timestamp = date('Y-m-d_His');
        $prefix = $isFull ? 'backup_full_' : 'backup_db_';
        $filename = $prefix . $timestamp . '.zip';
        $zipPath = $this->backupDir . '/' . $filename;

        $tempSqlPath = storage_path('app/temp_dump_' . uniqid() . '.sql');

        try {
            // 1. Dump Database to temporary SQL file
            $dbStats = $this->dbService->dump($tempSqlPath);

            // 2. Package into ZIP archive (with uploads if full backup)
            $archiveStats = $this->fileService->archive($zipPath, $tempSqlPath, $isFull, $dbStats);

            // 3. Record in database
            $record = BackupRecord::create([
                'name'         => $customName ?: ($isFull ? 'Full System Backup' : 'Database Backup') . ' (' . date('Y-m-d H:i') . ')',
                'filename'     => $filename,
                'file_path'    => $zipPath,
                'disk'         => 'local',
                'type'         => $type,
                'size_bytes'   => file_exists($zipPath) ? filesize($zipPath) : 0,
                'tables_count' => $dbStats['tables_count'] ?? 0,
                'files_count'  => $archiveStats['files_count'] ?? 0,
                'status'       => 'completed',
                'cloud_status' => 'none',
                'created_by'   => $userId,
                'notes'        => $notes,
            ]);

            // 4. Upload to Cloud if requested or auto-upload is configured
            $setting = BackupSetting::getActive();
            if (($uploadToCloud || $setting->auto_upload_cloud) && $this->cloudManager->isConfigured()) {
                try {
                    $this->cloudManager->upload($zipPath, $filename);
                    $record->update([
                        'disk'              => 'both',
                        'cloud_status'      => 'uploaded',
                        'cloud_uploaded_at' => now(),
                    ]);
                } catch (\Throwable $cloudEx) {
                    $record->update([
                        'cloud_status' => 'failed',
                        'notes'        => ($record->notes ? $record->notes . " | " : "") . "Cloud upload failed: " . $cloudEx->getMessage(),
                    ]);
                }
            }

            // 5. Prune old local backups according to setting
            $this->pruneOldBackups($setting->max_local_backups);

            return $record;
        } finally {
            if (file_exists($tempSqlPath)) {
                @unlink($tempSqlPath);
            }
        }
    }

    /**
     * Restore system from an uploaded file (from user's computer).
     */
    public function restoreFromUploadedFile(UploadedFile $file, bool $createSnapshot = true, ?int $userId = null): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['zip', 'sql'])) {
            throw new Exception("Invalid file extension: .{$ext}. Only .zip and .sql are allowed.");
        }

        $tempPath = storage_path('app/temp_upload_restore_' . time() . '.' . $ext);
        $file->move(storage_path('app'), basename($tempPath));

        try {
            return $this->restoreFromFile($tempPath, $createSnapshot, $userId, 'Uploaded Backup File (' . $file->getClientOriginalName() . ')');
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Restore from an existing local backup record.
     */
    public function restoreFromRecord(int $id, bool $createSnapshot = true, ?int $userId = null): array
    {
        $record = BackupRecord::findOrFail($id);
        $filePath = $record->file_path;

        // If file doesn't exist locally, check if available in cloud
        if (!file_exists($filePath)) {
            if ($record->cloud_status === 'uploaded' && $this->cloudManager->isConfigured()) {
                $this->cloudManager->download($record->filename, $filePath);
            } else {
                throw new Exception("Backup file not found on server or cloud: {$record->filename}");
            }
        }

        $result = $this->restoreFromFile($filePath, $createSnapshot, $userId, $record->name);
        $record->update(['last_restored_at' => now()]);

        return $result;
    }

    /**
     * Restore from a file in Cloud Storage.
     */
    public function restoreFromCloud(string $cloudFilename, bool $createSnapshot = true, ?int $userId = null): array
    {
        if (!$this->cloudManager->isConfigured()) {
            throw new Exception("Cloud storage is not configured.");
        }

        $tempPath = storage_path('app/temp_cloud_restore_' . time() . '_' . $cloudFilename);
        $this->cloudManager->download($cloudFilename, $tempPath);

        try {
            return $this->restoreFromFile($tempPath, $createSnapshot, $userId, 'Cloud Backup (' . $cloudFilename . ')');
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Core restore method.
     */
    protected function restoreFromFile(string $filePath, bool $createSnapshot = true, ?int $userId = null, ?string $contextName = null): array
    {
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        // Optional pre-restore snapshot
        $snapshotRecord = null;
        if ($createSnapshot) {
            try {
                $snapshotRecord = $this->createBackup([
                    'type'         => 'full',
                    'name'         => 'Pre-Restore Auto Snapshot before restoring ' . ($contextName ?: 'backup'),
                    'notes'        => 'Automatically created safety backup before restore execution.',
                    'upload_cloud' => false,
                ], $userId);
            } catch (\Throwable $e) {
                // If snapshot fails, proceed with care or throw
            }
        }

        // Perform restore of database and files
        $result = $this->fileService->restore($filePath, $this->dbService);

        // Clear all Laravel caches
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {}

        return [
            'success'           => true,
            'database_restored' => $result['database_restored'] ?? false,
            'files_restored'    => $result['files_restored'] ?? 0,
            'statements'        => $result['statements'] ?? 0,
            'snapshot_created'  => $snapshotRecord ? $snapshotRecord->filename : null,
        ];
    }

    /**
     * Download a local backup record to browser.
     */
    public function downloadRecord(int $id): BinaryFileResponse
    {
        $record = BackupRecord::findOrFail($id);
        $filePath = $record->file_path;

        if (!file_exists($filePath)) {
            if ($record->cloud_status === 'uploaded' && $this->cloudManager->isConfigured()) {
                $this->cloudManager->download($record->filename, $filePath);
            } else {
                throw new Exception("File not found on server.");
            }
        }

        return response()->download($filePath, $record->filename);
    }

    /**
     * Download a cloud backup file to browser.
     */
    public function downloadCloud(string $cloudFilename): StreamedResponse
    {
        return $this->cloudManager->streamDownload($cloudFilename);
    }

    /**
     * Upload an existing local backup record to Cloud Storage.
     */
    public function uploadRecordToCloud(int $id): bool
    {
        $record = BackupRecord::findOrFail($id);
        if (!file_exists($record->file_path)) {
            throw new Exception("Local backup file not found: {$record->file_path}");
        }

        $this->cloudManager->upload($record->file_path, $record->filename);

        $record->update([
            'disk'              => ($record->disk === 'local' ? 'both' : $record->disk),
            'cloud_status'      => 'uploaded',
            'cloud_uploaded_at' => now(),
        ]);

        return true;
    }

    /**
     * Delete local backup record.
     */
    public function deleteRecord(int $id, bool $deleteFromCloud = false): bool
    {
        $record = BackupRecord::findOrFail($id);

        if (file_exists($record->file_path)) {
            @unlink($record->file_path);
        }

        if ($deleteFromCloud && $record->cloud_status === 'uploaded') {
            try {
                $this->cloudManager->delete($record->filename);
            } catch (\Throwable $e) {}
        }

        return (bool) $record->delete();
    }

    /**
     * Delete a file directly from Cloud Storage.
     */
    public function deleteCloud(string $cloudFilename): bool
    {
        $deleted = $this->cloudManager->delete($cloudFilename);

        // Update corresponding record if one exists
        $record = BackupRecord::where('filename', $cloudFilename)->first();
        if ($record) {
            $record->update([
                'disk'         => 'local',
                'cloud_status' => 'none',
            ]);
        }

        return $deleted;
    }

    /**
     * Prune older local backup files if count exceeds maximum.
     */
    protected function pruneOldBackups(int $max): void
    {
        if ($max <= 0) return;

        $records = BackupRecord::orderBy('created_at', 'desc')->get();
        if ($records->count() > $max) {
            $toDelete = $records->slice($max);
            foreach ($toDelete as $oldRecord) {
                if (file_exists($oldRecord->file_path)) {
                    @unlink($oldRecord->file_path);
                }
                // If it exists in cloud, keep DB record marked as cloud only, else delete
                if ($oldRecord->cloud_status === 'uploaded') {
                    $oldRecord->update(['disk' => 'cloud']);
                } else {
                    $oldRecord->delete();
                }
            }
        }
    }

    /**
     * Get system storage metrics.
     */
    public function getMetrics(): array
    {
        $backupCount = BackupRecord::count();
        $totalBackupBytes = BackupRecord::sum('size_bytes');

        // Estimate DB size
        $dbSize = 0;
        try {
            $dbName = DB::connection()->getDatabaseName();
            $res = DB::select("SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            $dbSize = (int) ($res[0]->size ?? 0);
        } catch (\Throwable $e) {}

        // Estimate uploads size
        $uploadsSize = 0;
        $uploadsCount = 0;
        $uploadsDir = public_path('uploads');
        if (is_dir($uploadsDir)) {
            $dirIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadsDir, \FilesystemIterator::SKIP_DOTS));
            foreach ($dirIterator as $file) {
                if ($file->isFile()) {
                    $uploadsSize += $file->getSize();
                    $uploadsCount++;
                }
            }
        }

        $cloudConfigured = $this->cloudManager->isConfigured();
        $lastBackup = BackupRecord::orderBy('created_at', 'desc')->first();

        // Dynamic Machine & Server Environment Detection
        $hostName    = @gethostname() ?: (php_uname('n') ?: 'Server');
        $currentHost = request() ? request()->getHost() : 'localhost';
        $isLocalHost = in_array(strtolower($currentHost), ['localhost', '127.0.0.1', '::1', 'pos.test', 'pos.local'], true);

        // Check if explicitly running on a remote server/hosting infrastructure
        $isRemoteHosting = str_contains(strtolower($hostName), 'hosting')
            || str_contains(strtolower($hostName), 'hostinger')
            || str_contains(strtolower($hostName), 'web')
            || str_contains(strtolower($hostName), 'server')
            || str_contains(strtolower($hostName), 'vps')
            || str_contains(strtolower($hostName), 'cloud')
            || (!empty($currentHost) && !$isLocalHost && !str_ends_with($currentHost, '.test') && !str_ends_with($currentHost, '.local'));

        // It is local only if it's genuinely on localhost or a local developer machine and NOT remote hosting
        $isLocal = !$isRemoteHosting && (
            $isLocalHost
            || str_ends_with(strtolower($hostName), '.local')
            || (PHP_OS_FAMILY === 'Darwin' && !str_contains(strtolower($hostName), 'server'))
            || (PHP_OS_FAMILY === 'Windows' && !str_contains(strtolower($hostName), 'server'))
            || (app()->environment('local') && $isLocalHost)
        );

        $environmentName = $isLocal ? 'Local Machine' : 'Production Server';
        $osFamily        = PHP_OS_FAMILY === 'Darwin' ? 'macOS' : PHP_OS_FAMILY;

        // Dynamic Filesystem Storage Check on the current machine/server
        $diskPath = base_path();
        $physicalTotalBytes = (int) @disk_total_space($diskPath);
        $physicalFreeBytes  = (int) @disk_free_space($diskPath);

        if ($isLocal) {
            // ==========================================
            // 1. LOCAL MACHINE (Mac / Windows Dev PC)
            // ==========================================
            // Measure developer machine physical hard drive
            $totalDiskBytes = $physicalTotalBytes ?: (500 * 1024 * 1024 * 1024);
            $freeDiskBytes  = $physicalFreeBytes ?: 0;
            $usedDiskBytes  = max(0, $totalDiskBytes - $freeDiskBytes);
            $otherDiskBytes = max(0, $usedDiskBytes - ($dbSize + $uploadsSize + $totalBackupBytes));
        } else {
            // ==========================================
            // 2. SERVER (Hostinger / VPS / Cloud)
            // ==========================================
            // Resolve hosting plan quota limit (e.g. 50 GB)
            $totalDiskBytes = $this->resolveServerQuotaBytes($hostName, $physicalTotalBytes);

            // Measure actual project files on the hosting account
            $projectFilesBytes = $this->getProjectFilesSize();
            $usedDiskBytes  = $projectFilesBytes + $dbSize;
            $freeDiskBytes  = max(0, $totalDiskBytes - $usedDiskBytes);
            $otherDiskBytes = max(0, $projectFilesBytes - ($uploadsSize + $totalBackupBytes));
        }

        $usedPercentage = $totalDiskBytes > 0 ? round(($usedDiskBytes / $totalDiskBytes) * 100, 1) : 0;
        $freePercentage = $totalDiskBytes > 0 ? round(($freeDiskBytes / $totalDiskBytes) * 100, 1) : 0;

        $dbPercent      = $totalDiskBytes > 0 ? round(($dbSize / $totalDiskBytes) * 100, 2) : 0;
        $uploadsPercent = $totalDiskBytes > 0 ? round(($uploadsSize / $totalDiskBytes) * 100, 2) : 0;
        $backupsPercent = $totalDiskBytes > 0 ? round(($totalBackupBytes / $totalDiskBytes) * 100, 2) : 0;
        $otherPercent   = $totalDiskBytes > 0 ? round(($otherDiskBytes / $totalDiskBytes) * 100, 2) : 0;

        $diskStatus = 'healthy';
        if ($usedPercentage >= 90) {
            $diskStatus = 'critical';
        } elseif ($usedPercentage >= 75) {
            $diskStatus = 'warning';
        }

        return [
            'backup_count'          => $backupCount,
            'total_backup_bytes'    => $totalBackupBytes,
            'formatted_backup_size' => $this->formatSize($totalBackupBytes),
            'database_bytes'        => $dbSize,
            'database_size'         => $this->formatSize($dbSize),
            'uploads_bytes'         => $uploadsSize,
            'uploads_size'          => $this->formatSize($uploadsSize),
            'uploads_count'         => $uploadsCount,
            'cloud_configured'      => $cloudConfigured,
            'last_backup_at'        => $lastBackup ? $lastBackup->created_at->format('Y-m-d H:i') : null,

            // Dynamic Storage metrics for current machine (Local or Server)
            'is_local_machine'      => $isLocal,
            'host_name'             => $hostName,
            'environment_name'      => $environmentName,
            'os_family'             => $osFamily,
            'disk_total_bytes'      => $totalDiskBytes,
            'disk_total_formatted'  => $this->formatSize($totalDiskBytes),
            'disk_used_bytes'       => $usedDiskBytes,
            'disk_used_formatted'   => $this->formatSize($usedDiskBytes),
            'disk_free_bytes'       => $freeDiskBytes,
            'disk_free_formatted'   => $this->formatSize($freeDiskBytes),
            'disk_used_percentage'  => $usedPercentage,
            'disk_free_percentage'  => $freePercentage,
            'disk_db_percent'       => $dbPercent,
            'disk_uploads_percent'  => $uploadsPercent,
            'disk_backups_percent'  => $backupsPercent,
            'disk_other_bytes'      => $otherDiskBytes,
            'disk_other_formatted'  => $this->formatSize($otherDiskBytes),
            'disk_other_percent'    => $otherPercent,
            'disk_status'           => $diskStatus,
        ];
    }

    /**
     * Resolve the hosting plan storage quota in bytes.
     */
    protected function resolveServerQuotaBytes(string $hostName, int $physicalTotalBytes): int
    {
        // 1. Check environment variable HOSTING_DISK_QUOTA_GB or DISK_QUOTA_GB
        $envQuota = env('HOSTING_DISK_QUOTA_GB') ?: env('DISK_QUOTA_GB');
        if (!empty($envQuota) && is_numeric($envQuota) && (int)$envQuota > 0) {
            return (int)$envQuota * 1024 * 1024 * 1024;
        }

        // 2. Check BackupSetting database configuration
        try {
            $setting = BackupSetting::getActive();
            if (!empty($setting->disk_quota_gb) && (int)$setting->disk_quota_gb > 0) {
                return (int)$setting->disk_quota_gb * 1024 * 1024 * 1024;
            }
        } catch (\Throwable $e) {}

        // 3. If on Hostinger shared node (e.g. main-hosting.eu, web1415, server1415) or large multi-tenant RAID pool (> 1 TB)
        // Standard Hostinger hosting plan quota is 50 GB
        $isHostingerOrShared = str_contains(strtolower($hostName), 'hosting')
            || str_contains(strtolower($hostName), 'hostinger')
            || str_contains(strtolower($hostName), 'web')
            || str_contains(strtolower($hostName), 'server')
            || ($physicalTotalBytes > 1099511627776); // > 1 TB RAID partition

        if ($isHostingerOrShared) {
            return 50 * 1024 * 1024 * 1024; // 50 GB
        }

        // 4. If VPS with normal partition (e.g. 20GB, 40GB, 80GB), use actual server disk
        return ($physicalTotalBytes > 0 && $physicalTotalBytes < 1099511627776)
            ? $physicalTotalBytes
            : (50 * 1024 * 1024 * 1024);
    }

    /**
     * Calculate actual disk size of project files using pure PHP (no shell_exec).
     */
    protected function getProjectFilesSize(): int
    {
        return \Illuminate\Support\Facades\Cache::remember('backup_project_files_size', 600, function () {
            $size = 0;
            $dirs = ['vendor', 'app', 'public', 'resources', 'storage', 'config', 'database', 'routes', 'lang'];
            foreach ($dirs as $dir) {
                $path = base_path($dir);
                if (is_dir($path)) {
                    try {
                        $it = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                        );
                        foreach ($it as $file) {
                            if ($file->isFile()) {
                                $size += $file->getSize();
                            }
                        }
                    } catch (\Throwable $e) {}
                }
            }
            return $size ?: (200 * 1024 * 1024);
        });
    }

    public function formatSize(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}

