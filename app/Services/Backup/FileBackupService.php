<?php

namespace App\Services\Backup;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class FileBackupService
{
    /**
     * Create a ZIP archive containing database SQL, uploads files, and manifest.
     */
    public function archive(string $zipPath, string $sqlFilePath, bool $includeFiles = true, array $meta = []): array
    {
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        $zip = new ZipArchive();
        $status = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($status !== true) {
            throw new Exception("Cannot create ZIP file at {$zipPath} (Error Code: {$status})");
        }

        // Add database.sql
        if (file_exists($sqlFilePath)) {
            $zip->addFile($sqlFilePath, 'database.sql');
        }

        $filesCount = 0;
        $uploadsDir = public_path('uploads');

        if ($includeFiles && is_dir($uploadsDir)) {
            $dirIterator = new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($uploadsDir) + 1);

                    // Skip temporary/backup folders inside uploads if any
                    if (str_starts_with($relativePath, 'backups/') || str_starts_with($relativePath, 'temp/')) {
                        continue;
                    }

                    $zip->addFile($filePath, 'uploads/' . $relativePath);
                    $filesCount++;
                }
            }
        }

        // Manifest metadata
        $manifest = [
            'app'          => config('app.name', 'POS System'),
            'version'      => '1.0',
            'created_at'   => date('Y-m-d H:i:s'),
            'type'         => $includeFiles ? 'full' : 'database',
            'tables_count' => $meta['tables_count'] ?? 0,
            'files_count'  => $filesCount,
            'db_size'      => file_exists($sqlFilePath) ? filesize($sqlFilePath) : 0,
            'checksum'     => file_exists($sqlFilePath) ? hash_file('sha256', $sqlFilePath) : null,
        ];

        $zip->addFromString('backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        return [
            'files_count' => $filesCount,
            'zip_size'    => file_exists($zipPath) ? filesize($zipPath) : 0,
            'manifest'    => $manifest,
        ];
    }

    /**
     * Inspect a backup package before restore.
     */
    public function inspect(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'sql') {
            return [
                'type'         => 'database',
                'has_database' => true,
                'has_files'    => false,
                'files_count'  => 0,
                'size_bytes'   => filesize($filePath),
            ];
        }

        if ($ext === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception("Unable to open ZIP backup file.");
            }

            $hasDatabase = ($zip->locateName('database.sql') !== false);
            if (!$hasDatabase) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    if (str_ends_with(strtolower($stat['name']), '.sql')) {
                        $hasDatabase = true;
                        break;
                    }
                }
            }
            $hasManifest = ($zip->locateName('backup_manifest.json') !== false);
            $filesCount = 0;
            $manifestData = null;

            if ($hasManifest) {
                $rawManifest = $zip->getFromName('backup_manifest.json');
                $manifestData = json_decode($rawManifest, true);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (str_starts_with($stat['name'], 'uploads/') && !str_ends_with($stat['name'], '/')) {
                    $filesCount++;
                }
            }

            $zip->close();

            return [
                'type'         => ($hasDatabase && $filesCount > 0) ? 'full' : ($hasDatabase ? 'database' : 'unknown'),
                'has_database' => $hasDatabase,
                'has_files'    => ($filesCount > 0),
                'files_count'  => $filesCount,
                'size_bytes'   => filesize($filePath),
                'manifest'     => $manifestData,
            ];
        }

        throw new Exception("Unsupported backup file extension: .{$ext}. Only .zip and .sql are supported.");
    }

    /**
     * Extract and restore files and database from a backup package.
     */
    public function restore(string $filePath, DatabaseBackupService $dbService): array
    {
        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // If it's a direct .sql file
        if ($ext === 'sql') {
            $dbResult = $dbService->restore($filePath);
            return [
                'database_restored' => true,
                'files_restored'    => 0,
                'statements'        => $dbResult['statements_executed'] ?? 0,
            ];
        }

        // If it's a .zip file
        $tempExtractDir = storage_path('app/temp_restore_' . time() . '_' . uniqid());
        if (!is_dir($tempExtractDir)) {
            mkdir($tempExtractDir, 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Cannot open backup ZIP package.");
        }

        $zip->extractTo($tempExtractDir);
        $zip->close();

        $databaseRestored = false;
        $statementsCount = 0;
        $filesRestored = 0;

        try {
            // Restore database if database.sql or any SQL dump is present
            $sqlFile = $tempExtractDir . '/database.sql';
            if (!file_exists($sqlFile)) {
                $candidates = glob($tempExtractDir . '/*.sql');
                if (empty($candidates)) {
                    $candidates = glob($tempExtractDir . '/*/*.sql');
                }
                if (!empty($candidates)) {
                    $sqlFile = $candidates[0];
                }
            }

            if (file_exists($sqlFile)) {
                $dbResult = $dbService->restore($sqlFile);
                $databaseRestored = true;
                $statementsCount = $dbResult['statements_executed'] ?? 0;
            } else {
                throw new Exception("No database SQL dump file (.sql) found inside the backup package.");
            }

            // Restore uploads files if uploads directory is present
            $extractedUploads = $tempExtractDir . '/uploads';
            if (is_dir($extractedUploads)) {
                $targetUploads = public_path('uploads');
                if (!is_dir($targetUploads)) {
                    mkdir($targetUploads, 0755, true);
                }

                $dirIterator = new RecursiveDirectoryIterator($extractedUploads, RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

                foreach ($iterator as $item) {
                    $subPath = substr($item->getPathname(), strlen($extractedUploads) + 1);
                    $destPath = $targetUploads . '/' . $subPath;

                    if ($item->isDir()) {
                        if (!is_dir($destPath)) {
                            mkdir($destPath, 0755, true);
                        }
                    } else {
                        $parentDir = dirname($destPath);
                        if (!is_dir($parentDir)) {
                            mkdir($parentDir, 0755, true);
                        }
                        copy($item->getPathname(), $destPath);
                        $filesRestored++;
                    }
                }
            }
        } finally {
            // Cleanup extracted temporary directory
            $this->deleteDirectory($tempExtractDir);
        }

        return [
            'database_restored' => $databaseRestored,
            'files_restored'    => $filesRestored,
            'statements'        => $statementsCount,
        ];
    }

    /**
     * Recursively delete directory.
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
        rmdir($dir);
    }
}

