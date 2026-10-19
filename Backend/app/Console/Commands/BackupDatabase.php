<?php
namespace App\Console\Commands;

use App\Mail\DatabaseBackupMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'backup:daily';

    protected $description = 'Back up the database and email the SQL file';

    public function handle(): int
    {
        $this->info('[' . now() . '] Starting database backup...');

        $backupPath = storage_path('app/backups');
        $optionFile = null;
        $backupFile = null;

        try {
            $dbHost = config('database.connections.mysql.host');
            $dbPort = config('database.connections.mysql.port', 3306);
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            $recipient = env(
                'DB_BACKUP_EMAIL',
                'georgemuemah@gmail.com'
            );

            if (!$dbHost || !$dbName || !$dbUser) {
                throw new RuntimeException(
                    'Database configuration is incomplete.'
                );
            }

            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    'The backup recipient email is invalid.'
                );
            }

            if (!is_dir($backupPath) &&
                !mkdir($backupPath, 0750, true) &&
                !is_dir($backupPath)) {
                throw new RuntimeException(
                    'Could not create the backup directory.'
                );
            }

            $fileName = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
            $backupFile = $backupPath . DIRECTORY_SEPARATOR . $fileName;

            $optionFile = tempnam(sys_get_temp_dir(), 'db_backup_');

            if ($optionFile === false) {
                throw new RuntimeException(
                    'Could not create the temporary MySQL option file.'
                );
            }

            chmod($optionFile, 0600);

            $optionContents =
                "[client]\n" .
                'host=' . $this->mysqlOption($dbHost) . "\n" .
                'port=' . (int) $dbPort . "\n" .
                'user=' . $this->mysqlOption($dbUser) . "\n" .
                'password=' . $this->mysqlOption($dbPass ?? '') . "\n";

            if (file_put_contents($optionFile, $optionContents) === false) {
                throw new RuntimeException(
                    'Could not write the MySQL option file.'
                );
            }

            $command = 'mysqldump'
                . ' --defaults-extra-file=' . escapeshellarg($optionFile)
                . ' --single-transaction'
                . ' --quick'
                . ' --routines'
                . ' --triggers'
                . ' --events'
                . ' --no-tablespaces'
                . ' ' . escapeshellarg($dbName)
                . ' > ' . escapeshellarg($backupFile)
                . ' 2>&1';

            exec($command, $output, $exitCode);

            @unlink($optionFile);
            $optionFile = null;

            if ($exitCode !== 0 ||
                !is_file($backupFile) ||
                filesize($backupFile) === 0) {
                throw new RuntimeException(
                    'Database dump failed: ' . implode("\n", $output)
                );
            }

            $this->info('Backup created: ' . $backupFile);
            $this->info('Sending backup email to ' . $recipient . '...');

            Mail::to($recipient)->send(
                new DatabaseBackupMail($backupFile, $fileName)
            );

            $this->info('Backup email sent successfully.');

            // Remove local copy only after the email sends successfully.
            if (!unlink($backupFile)) {
                $this->warn(
                    'Email sent, but the local backup could not be deleted.'
                );
            }

            $backupFile = null;

            return self::SUCCESS;

        } catch (Throwable $e) {
            report($e);

            $this->error(
                '[' . now() . '] Backup failed: ' . $e->getMessage()
            );

            return self::FAILURE;

        } finally {
            if ($optionFile && is_file($optionFile)) {
                @unlink($optionFile);
            }
        }
    }

    private function mysqlOption(string $value): string
    {
        return '"' . str_replace(
            ['\\', '"', "\n", "\r"],
            ['\\\\', '\\"', '\\n', '\\r'],
            $value
        ) . '"';
    }
}
