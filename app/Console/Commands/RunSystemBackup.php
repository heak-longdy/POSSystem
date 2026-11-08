<?php

namespace App\Console\Commands;

use App\Models\BackupSetting;
use App\Services\Backup\BackupManager;
use Illuminate\Console\Command;

class RunSystemBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run 
                            {--type=full : Backup type (full or database)} 
                            {--cloud : Force upload to configured cloud storage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an automated backup of the POS system (database and files)';

    /**
     * Execute the console command.
     */
    public function handle(BackupManager $manager): int
    {
        $this->info('Starting automated POS system backup...');

        $type = $this->option('type') ?: 'full';
        $setting = BackupSetting::getActive();
        $uploadCloud = $this->option('cloud') || (bool)$setting->auto_upload_cloud;

        try {
            $record = $manager->createBackup([
                'type'         => $type,
                'name'         => 'Automated Backup (' . date('Y-m-d H:i') . ')',
                'upload_cloud' => $uploadCloud,
                'notes'        => 'Generated via CLI / scheduled cron job.',
            ]);

            $this->info("Backup completed successfully!");
            $this->line(" - Filename: {$record->filename}");
            $this->line(" - Type: {$record->type}");
            $this->line(" - Size: {$record->formatted_size}");
            $this->line(" - Cloud status: {$record->cloud_status}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

