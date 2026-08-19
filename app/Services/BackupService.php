<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BackupService
{
    protected string $disk = 'local';
    protected string $directory = 'backups';

    public function run(string $type = 'manual'): Backup
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Only MySQL backups are currently supported.');
        }

        Storage::disk($this->disk)->makeDirectory($this->directory);

        $filename = 'backup_' . now()->format('Y_m_d_His') . '_' . str()->lower(str()->random(6)) . '.sql';

        $relativePath = "{$this->directory}/{$filename}";
        $absolutePath = Storage::disk($this->disk)->path($relativePath);

        $backup = Backup::create([
            'filename'   => $filename,
            'disk'       => $this->disk,
            'path'       => $relativePath,
            'type'       => $type,
            'status'     => 'in_progress',
            'created_by' => Auth::id(),
            'started_at' => now(),
        ]);

        try {
            $this->dumpMysql($config, $absolutePath);

            $backup->update([
                'status'       => 'completed',
                'size'         => file_exists($absolutePath) ? filesize($absolutePath) : null,
                'completed_at' => now(),
            ]);

            $this->cleanupOldBackups();
        } catch (Throwable $e) {

            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            $backup->update([
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
                'completed_at'   => now(),
            ]);

            throw $e;
        }

        return $backup->fresh();
    }

    private function dumpMysql(array $config, string $absolutePath): void
    {
        $host     = $config['host'] ?? '127.0.0.1';
        $port     = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --single-transaction --quick --no-tablespaces %s > %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($absolutePath)
        );

        $result = Process::timeout(600)
            ->env(['MYSQL_PWD' => $password])
            ->run($command);

        if (!$result->successful()) {
            throw new RuntimeException(
                'Database dump failed: ' . trim($result->errorOutput())
            );
        }
    }

    public function import(string $absolutePath): void
    {
        $this->validateSqlFile($absolutePath);
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Only MySQL restores are currently supported.');
        }

        if (!file_exists($absolutePath)) {
            throw new RuntimeException('Uploaded backup file could not be found.');
        }

        if (!str_ends_with(strtolower($absolutePath), '.sql')) {
            throw new RuntimeException('Only .sql files are supported for import.');
        }

        $host     = $config['host'] ?? '127.0.0.1';
        $port     = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($absolutePath)
        );

        $result = Process::timeout(600)
            ->env(['MYSQL_PWD' => $password])
            ->run($command);

        if (!$result->successful()) {
            throw new RuntimeException(
                'Database import failed: ' . trim($result->errorOutput())
            );
        }
    }

    public function delete(Backup $backup): void
    {
        if (Storage::disk($backup->disk)->exists($backup->path)) {
            Storage::disk($backup->disk)->delete($backup->path);
        }

        $backup->delete();
    }

    public function downloadResponse(Backup $backup)
{
    if(
        $backup->status!=='completed' ||
        !Storage::disk($backup->disk)->exists($backup->path)
    ){
        abort(404,'Backup file not found.');
    }

    return Storage::disk($backup->disk)->download(
        $backup->path,
        $backup->filename,
        [
            'Content-Type'=>'application/sql',
            'X-Content-Type-Options'=>'nosniff',
            'Cache-Control'=>'no-store, private'
        ]
    );
}

    public function cleanupOldBackups(): int
    {
        $retentionDays = max((int)setting('backup_retention_days', 30), 1);
        $maxFiles = max((int)setting('backup_max_files', 20), 1);

        $deleted = 0;

        $expired = Backup::query()
            ->where('status', 'completed')
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->get();

        foreach ($expired as $backup) {
            $this->delete($backup);
            $deleted++;
        }

        $excess = Backup::query()
            ->where('status', 'completed')
            ->latest('id')
            ->skip($maxFiles)
            ->take(1000)
            ->get();

        foreach ($excess as $backup) {
            $this->delete($backup);
            $deleted++;
        }

        return $deleted;
    }

    protected function validateSqlFile(string $absolutePath): void
    {
        if (!file_exists($absolutePath)) {
            throw new RuntimeException('SQL file could not be found.');
        }

        if (filesize($absolutePath) <= 0) {
            throw new RuntimeException('SQL file is empty.');
        }

        $handle = fopen($absolutePath, 'rb');

        if (!$handle) {
            throw new RuntimeException('Unable to read SQL file.');
        }

        $sample = fread($handle, 1024 * 1024);
        fclose($handle);

        if (trim((string)$sample) === '') {
            throw new RuntimeException('SQL file does not contain readable SQL content.');
        }

        $lower = strtolower($sample);

        $blocked = [
            'drop database ',
            'create database ',
            'alter user ',
            'create user ',
            'drop user ',
            'grant all privileges',
            'shutdown'
        ];

        foreach ($blocked as $statement) {
            if (str_contains($lower, $statement)) {
                throw new RuntimeException(
                    'The SQL file contains a restricted database-level statement.'
                );
            }
        }
    }
}
