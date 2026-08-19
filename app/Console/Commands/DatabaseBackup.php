<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature='database:backup';

    protected $description='Create a database backup';

    public function handle(BackupService $backupService): int
    {
        try{
            $backup=$backupService->run('scheduled');

            $this->info(
                "Backup created: {$backup->filename}"
            );

            return self::SUCCESS;

        }catch(\Throwable $e){
            report($e);

            $this->error(
                'Backup failed: '.$e->getMessage()
            );

            return self::FAILURE;
        }
    }
}