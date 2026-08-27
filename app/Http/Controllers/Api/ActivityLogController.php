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
        $validated=$request->validate([
            'user_id'=>'nullable|integer|exists:users,id',
            'module'=>'nullable|string|max:100',
            'action'=>'nullable|string|max:100',
            'search'=>'nullable|string|max:150',
            'from'=>'nullable|date_format:Y-m-d',
            'to'=>'nullable|date_format:Y-m-d|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query = ActivityLog::with('user:id,name,email')
            ->latest('id');

        if (!empty($validated['user_id'])) {
            $query->where('user_id',$validated['user_id']);
        }

        if (!empty($validated['module'])) {
            $query->where('module',$validated['module']);
        }

        if (!empty($validated['action'])) {
            $query->where('action',$validated['action']);
        }

        $search=trim((string)($validated['search']??''));

        if ($search!=='') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email','like',"%{$search}%");
                    });
            });
        }

        if (!empty($validated['from'])) {
            $query->whereDate('created_at','>=',$validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate('created_at','<=',$validated['to']);
        }

        return response()->json(
            $query->paginate((int)($validated['per_page']??20))
        );
    }

    public function show(ActivityLog $activityLog)
    {
        // The UI only needs subject_type/subject_id. Loading the full
        // polymorphic subject adds an unnecessary query and can serialize
        // fields from the source model that do not belong in an audit response.
        return response()->json(
            $activityLog->load('user:id,name,email')
        );
    }

    /**
     * Distinct filter options for the audit log screen — modules,
     * actions and the users who actually appear in the log.
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
