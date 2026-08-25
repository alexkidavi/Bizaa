<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Schedule;
Artisan::command('backup:daily-scheduled', function () {
    info('['.now().'] Triggering custom hourly backup via service...');

    try {
        // Define your disk and directory
        $disk = Storage::disk('local'); 
        $backupDirectory = 'backups'; 

        // 1. Check if folder exists, if not, create it
        if (!$disk->exists($backupDirectory)) {
            $disk->makeDirectory($backupDirectory);
            info('['.now().'] Created missing backup directory: ' . $backupDirectory);
        }

        info('['.now().'] Instantiating service...');
        $backupService = new DatabaseBackupService();
        
        // 2. Run the backup
        $filePath = $backupService->run(); 
        info('['.now().'] Backup completed: '.$filePath);

        // 3. Clean up older backups on success
        info('['.now().'] Cleaning up older backups...');
        
        // Get all files in that directory
        $files = $disk->files($backupDirectory);

        foreach ($files as $file) {
            // Make sure we don't delete the file we JUST created
            if ($file !== $filePath) {
                $disk->delete($file);
                info('['.now().'] Deleted old backup: '.$file);
            }
        }

    } catch (\Exception $e) {
        info('['.now().'] Backup failed: '.$e->getMessage());
    }
    
})->hourly();
// Schedule::call(function () {
//     info('testing here');
// })->everyMinute();