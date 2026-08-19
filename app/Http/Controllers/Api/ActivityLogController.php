<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest();

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->user_id
            );
        }

        if ($request->filled('module')) {
            $query->where(
                'module',
                $request->module
            );
        }

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->action
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        return response()->json(
            $query->paginate($request->input('per_page', 20))
        );
    }

    public function show(ActivityLog $activityLog)
    {
        return response()->json(
            $activityLog->load([
                'user',
                'subject',
            ])
        );
    }

    /**
     * Distinct filter options for the audit log screen — modules,
     * actions and the users who actually appear in the log, so the
     * filter dropdowns only ever offer values that return results.
     */
    public function filters()
    {
        return response()->json([

            'modules' => ActivityLog::query()
                ->whereNotNull('module')
                ->distinct()
                ->orderBy('module')
                ->pluck('module'),

            'actions' => ActivityLog::query()
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),

            'users' => User::query()
                ->whereHas('activityLogs')
                ->orderBy('name')
                ->get(['id', 'name']),

        ]);
    }
}
