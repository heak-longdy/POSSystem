<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupRecord;
use App\Models\BackupSetting;
use App\Services\Backup\BackupManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class BackupController extends Controller
{
    protected string $layout = 'admin::pages.backup.';
    protected BackupManager $manager;

    public function __construct(BackupManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Display Backup & Restore dashboard.
     */
    public function index(Request $request)
    {
        $setting = BackupSetting::getActive();
        $metrics = $this->manager->getMetrics();

        $localBackups = BackupRecord::orderBy('created_at', 'desc')->get();

        $cloudBackups = [];
        $cloudError = null;
        if ($setting->isCloudConfigured()) {
            try {
                $cloudBackups = $this->manager->getCloudManager()->list();
            } catch (\Throwable $e) {
                $cloudError = $e->getMessage();
            }
        }

        return view($this->layout . 'index', [
            'setting'      => $setting,
            'metrics'      => $metrics,
            'localBackups' => $localBackups,
            'cloudBackups' => $cloudBackups,
            'cloudError'   => $cloudError,
        ]);
    }

    /**
     * Create a new backup.
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'type'         => 'required|in:full,database',
            'name'         => 'nullable|string|max:150',
            'notes'        => 'nullable|string|max:500',
            'upload_cloud' => 'nullable|boolean',
        ]);

        try {
            $record = $this->manager->createBackup([
                'type'         => $request->input('type', 'full'),
                'name'         => $request->input('name'),
                'notes'        => $request->input('notes'),
                'upload_cloud' => $request->boolean('upload_cloud'),
            ], auth()->id());

            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.create_success'),
                'record'  => $record,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download local backup file directly to user's computer.
     */
    public function download(int $id): BinaryFileResponse|JsonResponse
    {
        try {
            return $this->manager->downloadRecord($id);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Download cloud backup file directly to user's computer.
     */
    public function downloadCloud(Request $request): StreamedResponse|JsonResponse
    {
        $filename = $request->query('filename') ?: $request->input('filename');
        if (empty($filename)) {
            return response()->json(['status' => 'error', 'message' => 'Filename is required'], 400);
        }

        try {
            return $this->manager->downloadCloud($filename);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload an existing local backup record to Cloud Storage.
     */
    public function uploadToCloud(int $id): JsonResponse
    {
        try {
            $this->manager->uploadRecordToCloud($id);
            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.upload_cloud_success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore system data from a file uploaded from user's computer.
     */
    public function restoreFromUpload(Request $request): JsonResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:524288', // up to 512MB
            'snapshot'    => 'nullable|boolean',
        ]);

        try {
            $result = $this->manager->restoreFromUploadedFile(
                $request->file('backup_file'),
                $request->boolean('snapshot', true),
                auth()->id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.restore_success'),
                'details' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore system data from an existing local server backup.
     */
    public function restoreFromLocal(int $id, Request $request): JsonResponse
    {
        try {
            $result = $this->manager->restoreFromRecord(
                $id,
                $request->boolean('snapshot', true),
                auth()->id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.restore_success'),
                'details' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore system data from a backup stored in Cloud Storage.
     */
    public function restoreFromCloud(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
            'snapshot' => 'nullable|boolean',
        ]);

        try {
            $result = $this->manager->restoreFromCloud(
                $request->input('filename'),
                $request->boolean('snapshot', true),
                auth()->id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.restore_success'),
                'details' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete local backup file and record.
     */
    public function delete(int $id, Request $request): JsonResponse
    {
        try {
            $this->manager->deleteRecord($id, $request->boolean('delete_cloud', false));
            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.delete_success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a backup file from Cloud Storage.
     */
    public function deleteCloud(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        try {
            $this->manager->deleteCloud($request->input('filename'));
            return response()->json([
                'status'  => 'success',
                'message' => __('backup.messages.delete_cloud_success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Cloud Storage settings.
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cloud_driver'          => 'required|string|max:30',
            's3_endpoint'           => 'nullable|string|max:255',
            's3_region'             => 'required|string|max:50',
            's3_bucket'             => 'required|string|max:100',
            's3_access_key'         => 'required|string',
            's3_secret_key'         => 'nullable|string',
            's3_path_prefix'        => 'nullable|string|max:100',
            's3_use_path_style'     => 'nullable|boolean',
            'auto_backup_enabled'   => 'nullable|boolean',
            'auto_backup_frequency' => 'nullable|string|in:daily,weekly,monthly',
            'auto_upload_cloud'     => 'nullable|boolean',
            'max_local_backups'     => 'required|integer|min:1|max:100',
            'disk_quota_gb'         => 'nullable|integer|min:1|max:50000',
        ]);

        $setting = BackupSetting::getActive();

        // If secret key is not provided or masked, keep existing
        if (empty($data['s3_secret_key']) || str_starts_with($data['s3_secret_key'], '••••')) {
            unset($data['s3_secret_key']);
        }

        $data['s3_use_path_style'] = $request->boolean('s3_use_path_style');
        $data['auto_backup_enabled'] = $request->boolean('auto_backup_enabled');
        $data['auto_upload_cloud'] = $request->boolean('auto_upload_cloud');

        $setting->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => __('backup.messages.settings_saved'),
            'setting' => $setting,
        ]);
    }

    /**
     * Test connection to cloud storage.
     */
    public function testCloudConnection(Request $request): JsonResponse
    {
        $setting = BackupSetting::getActive();

        $params = [
            's3_access_key'     => $request->input('s3_access_key') ?: $setting->s3_access_key,
            's3_secret_key'     => ($request->input('s3_secret_key') && !str_starts_with($request->input('s3_secret_key'), '••••'))
                                    ? $request->input('s3_secret_key')
                                    : $setting->s3_secret_key,
            's3_bucket'         => $request->input('s3_bucket') ?: $setting->s3_bucket,
            's3_region'         => $request->input('s3_region') ?: $setting->s3_region,
            's3_endpoint'       => $request->input('s3_endpoint') ?: $setting->s3_endpoint,
            's3_use_path_style' => $request->boolean('s3_use_path_style', (bool)$setting->s3_use_path_style),
        ];

        $result = $this->manager->getCloudManager()->testConnection($params);

        return response()->json($result);
    }
}

