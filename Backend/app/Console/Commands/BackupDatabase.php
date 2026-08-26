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

            // Credentials go in a temporary MySQL option file rather than
            // directly on the command line — a shell command's arguments
            // (including any password interpolated into it) are visible to
            // every other user on the box via `ps aux` for as long as
            // mysqldump runs. The option file is created 0600 (owner-only)
            // and removed immediately after, win or lose.
            $optionFile = tempnam(sys_get_temp_dir(), 'bizaa_backup_');
            file_put_contents(
                $optionFile,
                "[client]\nuser={$dbUser}\npassword={$dbPass}\nhost={$dbHost}\nport={$dbPort}\n"
            );
            chmod($optionFile, 0600);

            $command = 'mysqldump --defaults-extra-file=' . escapeshellarg($optionFile)
                . ' ' . escapeshellarg($dbName)
                . ' > ' . escapeshellarg($backupPath . '/' . $fileName);

            exec($command, $output, $returnVar);

            unlink($optionFile);

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