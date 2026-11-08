<?php

namespace App\Services\Backup\Cloud;

use App\Models\BackupSetting;
use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CloudStorageManager
{
    protected BackupSetting $setting;
    protected ?S3RestDriver $driver = null;

    public function __construct(?BackupSetting $setting = null)
    {
        $this->setting = $setting ?? BackupSetting::getActive();
    }

    /**
     * Get the active S3 driver if configured.
     */
    public function getDriver(): ?S3RestDriver
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        if (!$this->isConfigured()) {
            return null;
        }

        $this->driver = new S3RestDriver(
            $this->setting->s3_access_key ?? '',
            $this->setting->s3_secret_key ?? '',
            $this->setting->s3_bucket ?? '',
            $this->setting->s3_region ?? 'us-east-1',
            $this->setting->s3_endpoint,
            (bool) ($this->setting->s3_use_path_style ?? false)
        );

        return $this->driver;
    }

    public function isConfigured(): bool
    {
        return $this->setting->isCloudConfigured();
    }

    /**
     * Test connection to cloud storage.
     */
    public function testConnection(?array $customParams = null): array
    {
        if ($customParams) {
            $driver = new S3RestDriver(
                $customParams['s3_access_key'] ?? '',
                $customParams['s3_secret_key'] ?? '',
                $customParams['s3_bucket'] ?? '',
                $customParams['s3_region'] ?? 'us-east-1',
                $customParams['s3_endpoint'] ?? null,
                !empty($customParams['s3_use_path_style'])
            );
            return $driver->testConnection();
        }

        $driver = $this->getDriver();
        if (!$driver) {
            return ['success' => false, 'message' => 'Cloud storage credentials are not configured.'];
        }

        return $driver->testConnection();
    }

    /**
     * Upload local file to cloud storage.
     */
    public function upload(string $localFilePath, string $filename): bool
    {
        $driver = $this->getDriver();
        if (!$driver) {
            throw new Exception("Cloud storage is not configured.");
        }

        $prefix = trim($this->setting->s3_path_prefix ?? 'backups/', '/');
        $cloudKey = ($prefix !== '' ? $prefix . '/' : '') . $filename;

        return $driver->putObject($localFilePath, $cloudKey);
    }

    /**
     * Download file from cloud storage to local path.
     */
    public function download(string $filename, string $localSavePath): bool
    {
        $driver = $this->getDriver();
        if (!$driver) {
            throw new Exception("Cloud storage is not configured.");
        }

        $prefix = trim($this->setting->s3_path_prefix ?? 'backups/', '/');
        $cloudKey = ($prefix !== '' ? $prefix . '/' : '') . $filename;

        return $driver->getObject($cloudKey, $localSavePath);
    }

    /**
     * List all backup files in cloud storage.
     */
    public function list(): array
    {
        $driver = $this->getDriver();
        if (!$driver) {
            return [];
        }

        $prefix = trim($this->setting->s3_path_prefix ?? 'backups/', '/');
        return $driver->listObjects($prefix !== '' ? $prefix . '/' : '');
    }

    /**
     * Delete file from cloud storage.
     */
    public function delete(string $filename): bool
    {
        $driver = $this->getDriver();
        if (!$driver) {
            return false;
        }

        $prefix = trim($this->setting->s3_path_prefix ?? 'backups/', '/');
        $cloudKey = ($prefix !== '' ? $prefix . '/' : '') . $filename;

        return $driver->deleteObject($cloudKey);
    }

    /**
     * Stream a cloud file download directly to browser.
     */
    public function streamDownload(string $filename): StreamedResponse
    {
        $tempPath = storage_path('app/temp_download_' . time() . '_' . $filename);
        $this->download($filename, $tempPath);

        $mime = str_ends_with(strtolower($filename), '.zip') ? 'application/zip' : 'application/sql';

        return response()->streamDownload(function () use ($tempPath) {
            $stream = fopen($tempPath, 'rb');
            while (!feof($stream)) {
                echo fread($stream, 1024 * 64);
                flush();
            }
            fclose($stream);
            @unlink($tempPath);
        }, $filename, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

