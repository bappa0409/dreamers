<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\ActivityLogService;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Throwable;

class BackupController extends Controller
{
    protected BackupService $backupService;
    protected ActivityLogService $activityLogService;

    public function __construct(
        BackupService $backupService,
        ActivityLogService $activityLogService
    ) {
        $this->backupService = $backupService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $backups = Backup::with('creator')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where('filename', 'like', "%{$search}%");
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->string('type'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $backups,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $backup = $this->backupService->run('manual');

            $this->activityLogService->log(
                action: 'created',
                module: 'Backup',
                description: "Created database backup: {$backup->filename}",
                subject: $backup
            );

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully.',
                'data' => $backup->load('creator'),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        if(!auth()->user()->isSystemAnalyst()){
            return response()->json([
                'success'=>false,
                'message'=>'Only the System Analyst can import a database backup.'
            ],403);
        }

        if (!(bool)setting('backup_import_enabled', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Database import is currently disabled from system settings.'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|max:512000'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'sql') {
            return response()->json([
                'success' => false,
                'message' => 'Only .sql files are allowed.'
            ], 422);
        }

        $tempFilename = 'import_'
            . now()->format('Y_m_d_His')
            . '_'
            . str()->lower(str()->random(8))
            . '.sql';

        $tempPath = $file->storeAs(
            'backups/tmp',
            $tempFilename,
            'local'
        );

        $absolutePath = Storage::disk('local')->path($tempPath);

        try {
            $safetyBackup = $this->backupService->run('pre_import');

            $this->activityLogService->log(
                action: 'created',
                module: 'Backup',
                description: "Created automatic pre-import backup: {$safetyBackup->filename}",
                subject: $safetyBackup
            );

            $this->backupService->import($absolutePath);

            Cache::flush();

            $this->activityLogService->log(
                action: 'imported',
                module: 'Backup',
                description: "Imported database from file: {$file->getClientOriginalName()}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Database imported successfully.',
                'data' => [
                    'safety_backup' => $safetyBackup->filename
                ]
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        } finally {
            if (Storage::disk('local')->exists($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }
        }
    }

    public function download(Backup $backup)
    {
        return $this->backupService->downloadResponse($backup);
    }

    public function destroy(Backup $backup)
{
    if($backup->status==='in_progress'){
        return response()->json([
            'success'=>false,
            'message'=>'An in-progress backup cannot be deleted.'
        ],422);
    }

    $filename=$backup->filename;

    $this->backupService->delete($backup);

    $this->activityLogService->log(
        action:'deleted',
        module:'Backup',
        description:"Deleted database backup: {$filename}"
    );

    return response()->json([
        'success'=>true,
        'message'=>'Backup deleted successfully.'
    ]);
}
}