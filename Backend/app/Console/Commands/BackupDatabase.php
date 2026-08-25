<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:daily';
    protected $description = 'Run a custom daily database backup';

    public function handle()
    {
        $this->info('['.now().'] Starting custom backup...');

        try {
            // Database connection info from .env
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            // Backup folder
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // File name
            $fileName = 'backup-' . date('Y-m-d_H-i-s') . '.sql';

            // mysqldump command
            $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} --port={$dbPort} {$dbName} > {$backupPath}/{$fileName}";

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $this->error('['.now().'] Backup failed! Check MySQL credentials and permissions.');
                return 1;
            }

            $this->info('['.now().'] Backup completed: '.$backupPath.'/'.$fileName);

        } catch (\Exception $e) {
            $this->error('['.now().'] Backup failed: '.$e->getMessage());
            return 1;
        }

        return 0;
    }
}