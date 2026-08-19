<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationCampaign;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationCampaignController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
        protected ActivityLogService $activityLogService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:pending,sending,sent,failed',
            'type'=>'nullable|in:info,success,warning,danger',
            'page'=>'nullable|integer|min:1',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=NotificationCampaign::query()
            ->with('sender:id,name,email')
            ->latest('id');

        if(!empty($validated['status'])){
            $query->where('status',$validated['status']);
        }

        if(!empty($validated['type'])){
            $query->where('type',$validated['type']);
        }

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhere('message','like',"%{$search}%");
            });
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??20),100)
            )
        ]);
    }

    public function statistics()
    {
        $data=Cache::remember(
            'notifications:campaigns:statistics',
            now()->addMinutes(5),
            function(){
                $row=NotificationCampaign::query()
                    ->selectRaw("
                        COUNT(*) AS total,
                        SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) AS sent,
                        SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) AS failed,
                        COALESCE(SUM(recipients_count),0) AS recipients
                    ")
                    ->first();

                return [
                    'total'=>(int)($row->total??0),
                    'sent'=>(int)($row->sent??0),
                    'failed'=>(int)($row->failed??0),
                    'recipients'=>(int)($row->recipients??0),
                ];
            }
        );

        return response()->json([
            'success'=>true,
            'data'=>$data
        ]);
    }

    public function send(Request $request)
    {
        $validated=$request->validate([
            'title'=>'required|string|max:200',
            'message'=>'required|string|max:5000',
            'type'=>'required|in:info,success,warning,danger',
            'action_url'=>'nullable|string|max:500',
            'audience_type'=>'required|in:all_active_members,role,users',
            'role_id'=>'required_if:audience_type,role|nullable|integer|exists:roles,id',
            'user_ids'=>'required_if:audience_type,users|nullable|array|min:1|max:500',
            'user_ids.*'=>'integer|distinct|exists:users,id',
        ]);

        $campaign=$this->notificationService->send(
            $validated,
            auth()->id()
        );

        $this->activityLogService->log(
            action:'sent',
            module:'Notification',
            description:"Sent notification \"{$campaign->title}\" to {$campaign->recipients_count} recipient(s).",
            subject:$campaign
        );

        return response()->json([
            'success'=>true,
            'message'=>'Notification sent successfully.',
            'data'=>$campaign
        ],201);
    }

    public function show(NotificationCampaign $campaign)
    {
        return response()->json([
            'success'=>true,
            'data'=>$campaign->load(
                'sender:id,name,email'
            )
        ]);
    }

    public function recipients(Request $request)
    {
        $search=trim((string)$request->input('search',''));

        $roles=Role::query()
            ->select('id','name','display_name')
            ->orderBy('display_name')
            ->get();

        $users=User::query()
            ->select('id','name','email','mobile')
            ->where('is_active',true)
            ->when($search,function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where('name','like',"%{$search}%")
                        ->orWhere('email','like',"%{$search}%")
                        ->orWhere('mobile','like',"%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>[
                'roles'=>$roles,
                'users'=>$users
            ]
        ]);
    }
}