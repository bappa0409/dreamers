<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Services\ActivityLogService;
use App\Services\NoticeService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService,
        protected NotificationService $notificationService,
        protected ActivityLogService $activityLogService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'type'=>'nullable|in:notice,announcement,event,urgent',
            'priority'=>'nullable|in:low,normal,high,urgent',
            'published_only'=>'nullable|boolean',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=Notice::query()
            ->with('creator:id,name,email')
            ->latest('id');

        if($request->boolean('published_only')){
            $query->visible();
        }

        if(!empty($validated['type'])){
            $query->where('type',$validated['type']);
        }

        if(!empty($validated['priority'])){
            $query->where('priority',$validated['priority']);
        }

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhere('content','like',"%{$search}%");
            });
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??15),100)
            )
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'title'=>'required|string|max:255',
            'content'=>'required|string|max:10000',
            'type'=>'required|in:notice,announcement,event,urgent',
            'priority'=>'required|in:low,normal,high,urgent',
            'is_published'=>'nullable|boolean',
            'publish_at'=>'nullable|date',
            'expires_at'=>'nullable|date|after:publish_at',
            'attachment'=>'nullable|file|max:5120',
            'notify_members'=>'nullable|boolean',
            'audience_type'=>'nullable|required_if:notify_members,true|in:all_active_members,role,users',
            'role_id'=>'nullable|required_if:audience_type,role|integer|exists:roles,id',
            'user_ids'=>'nullable|required_if:audience_type,users|array|min:1|max:500',
            'user_ids.*'=>'integer|distinct|exists:users,id',
        ]);

        if($request->hasFile('attachment')){
            $validated['attachment']=$request->file('attachment')
                ->store('notices','public');
        }

        $validated['created_by']=$request->user()->id;

        $notifyMembers=(bool)($validated['notify_members']??false);

        unset(
            $validated['notify_members'],
            $validated['audience_type'],
            $validated['role_id'],
            $validated['user_ids']
        );

        $notice=$this->noticeService->create($validated);

        if(
            $notice->is_published &&
            $this->isPublishableNow($notice) &&
            $notifyMembers
        ){
            $this->sendNoticeNotification(
                $notice,
                $request
            );
        }

        return response()->json([
            'success'=>true,
            'message'=>'Notice created successfully.',
            'data'=>$notice->load('creator:id,name,email')
        ],201);
    }

    public function show(Notice $notice)
    {
        return response()->json([
            'success'=>true,
            'data'=>$notice->load('creator:id,name,email')
        ]);
    }

    public function update(Request $request,Notice $notice)
    {
        $validated=$request->validate([
            'title'=>'sometimes|required|string|max:255',
            'content'=>'sometimes|required|string|max:10000',
            'type'=>'sometimes|required|in:notice,announcement,event,urgent',
            'priority'=>'sometimes|required|in:low,normal,high,urgent',
            'is_published'=>'sometimes|boolean',
            'publish_at'=>'nullable|date',
            'expires_at'=>'nullable|date|after:publish_at',
            'attachment'=>'nullable|file|max:5120',
        ]);

        if($request->hasFile('attachment')){
            if(
                $notice->attachment &&
                Storage::disk('public')->exists($notice->attachment)
            ){
                Storage::disk('public')->delete($notice->attachment);
            }

            $validated['attachment']=$request->file('attachment')
                ->store('notices','public');
        }

        $notice=$this->noticeService->update(
            $notice,
            $validated
        );

        return response()->json([
            'success'=>true,
            'message'=>'Notice updated successfully.',
            'data'=>$notice->load('creator:id,name,email')
        ]);
    }

    public function destroy(Notice $notice)
    {
        $this->noticeService->delete($notice);

        return response()->json([
            'success'=>true,
            'message'=>'Notice deleted successfully.'
        ]);
    }

    public function togglePublish(Request $request,Notice $notice)
    {
        $notice=$this->noticeService->togglePublish($notice);

        if(
            $notice->is_published &&
            $this->isPublishableNow($notice) &&
            $request->boolean('notify_members')
        ){
            $validated=$request->validate([
                'audience_type'=>'required|in:all_active_members,role,users',
                'role_id'=>'nullable|required_if:audience_type,role|integer|exists:roles,id',
                'user_ids'=>'nullable|required_if:audience_type,users|array|min:1|max:500',
                'user_ids.*'=>'integer|distinct|exists:users,id',
            ]);

            $this->sendNoticeNotification(
                $notice,
                $request,
                $validated
            );
        }

        return response()->json([
            'success'=>true,
            'message'=>$notice->is_published
                ?'Notice published successfully.'
                :'Notice unpublished successfully.',
            'data'=>$notice
        ]);
    }

    protected function sendNoticeNotification(
        Notice $notice,
        Request $request,
        ?array $audience=null
    ): void{
        $audience=$audience??[
            'audience_type'=>$request->input(
                'audience_type',
                'all_active_members'
            ),
            'role_id'=>$request->input('role_id'),
            'user_ids'=>$request->input('user_ids',[]),
        ];

        $campaign=$this->notificationService->send([
            'title'=>$notice->title,
            'message'=>$this->notificationMessage($notice),
            'type'=>$this->notificationType($notice),
            'action_url'=>"/member/notices#notice-{$notice->id}",
            'audience_type'=>$audience['audience_type'],
            'role_id'=>$audience['role_id']??null,
            'user_ids'=>$audience['user_ids']??[],
        ],auth()->id());

        $this->activityLogService->log(
            action:'sent',
            module:'Notice',
            description:"Notice \"{$notice->title}\" notification sent to {$campaign->recipients_count} recipient(s).",
            subject:$notice
        );
    }

    protected function notificationMessage(Notice $notice): string
    {
        $content=strip_tags($notice->content);

        return mb_strlen($content)>180
            ?mb_substr($content,0,177).'...'
            :$content;
    }

    protected function notificationType(Notice $notice): string
    {
        return match($notice->priority){
            'urgent'=>'danger',
            'high'=>'warning',
            'low'=>'info',
            default=>'info',
        };
    }

    protected function isPublishableNow(Notice $notice): bool
    {
        if($notice->publish_at&&$notice->publish_at->isFuture()){
            return false;
        }

        if($notice->expires_at&&$notice->expires_at->isPast()){
            return false;
        }

        return true;
    }
}