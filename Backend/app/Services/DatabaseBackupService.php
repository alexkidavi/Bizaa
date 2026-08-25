<?php

namespace App\Services;

class DatabaseBackupService
{
    protected string $backupPath;

    protected string $mysqldump;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (! file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }

        // Full path to mysqldump
        $this->mysqldump = '/usr/bin/mysqldump';
    }

    /**
     * Run the database backup
     *
     * @return string Path to the backup file
     *
     * @throws \Exception
     */
    public function run(): string
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $fileName = 'backup-'.date('Y-m-d_H-i-s').'.sql';
        $filePath = $this->backupPath.'/'.$fileName;

        // Escape password and database name safely
        $dbPassEscaped = escapeshellarg($dbPass);
        $dbNameEscaped = escapeshellarg($dbName);

        $command = "{$this->mysqldump} --user={$dbUser} --password={$dbPassEscaped} --host={$dbHost} --port={$dbPort} {$dbNameEscaped} > {$filePath} 2>&1";

        exec($command, $output, $returnVar);

        info('Command: '.$command);
        info('Output: '.implode("\n", $output));
        info('Return code: '.$returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Backup failed! Check MySQL credentials, mysqldump path, and permissions.');
        }

        return $filePath;
    }
}
