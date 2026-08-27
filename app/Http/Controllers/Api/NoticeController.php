<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Services\ActivityLogService;
use App\Services\NoticeService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'expires_at'=>'nullable|date',
            'attachment'=>'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,zip',
            'notify_members'=>'nullable|boolean',
            'audience_type'=>'nullable|required_if:notify_members,true|in:all_active_members,role,users',
            'role_id'=>'nullable|required_if:audience_type,role|integer|exists:roles,id',
            'user_ids'=>'nullable|required_if:audience_type,users|array|min:1|max:500',
            'user_ids.*'=>'integer|distinct|exists:users,id',
        ]);

        $this->validateSchedule($validated);

        $notifyMembers=(bool)($validated['notify_members']??false);

        if($notifyMembers){
            $this->validateImmediateNotification($validated);
        }

        $audience=[
            'audience_type'=>$validated['audience_type']??'all_active_members',
            'role_id'=>$validated['role_id']??null,
            'user_ids'=>$validated['user_ids']??[],
        ];

        unset(
            $validated['notify_members'],
            $validated['audience_type'],
            $validated['role_id'],
            $validated['user_ids']
        );

        $attachmentPath=null;

        if($request->hasFile('attachment')){
            $attachmentPath=$request->file('attachment')
                ->store('notices','public');

            if(!$attachmentPath){
                throw new \RuntimeException('Notice attachment could not be stored.');
            }

            $validated['attachment']=$attachmentPath;
        }

        $validated['created_by']=$request->user()->id;

        try{
            $notice=$this->noticeService->create($validated);
        }catch(\Throwable $e){
            $this->deletePublicFile($attachmentPath);
            throw $e;
        }

        $notificationFailed=false;

        if($notifyMembers){
            try{
                $this->sendNoticeNotification(
                    $notice,
                    $request,
                    $audience
                );
            }catch(\Throwable $e){
                report($e);
                $notificationFailed=true;
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>$notificationFailed
                ?'Notice created successfully, but member notification could not be sent.'
                :'Notice created successfully.',
            'notification_sent'=>$notifyMembers&&!$notificationFailed,
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
            'expires_at'=>'nullable|date',
            'attachment'=>'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,zip',
        ]);

        $this->validateSchedule($validated,$notice);

        $oldAttachment=$notice->attachment;
        $newAttachment=null;

        if($request->hasFile('attachment')){
            $newAttachment=$request->file('attachment')
                ->store('notices','public');

            if(!$newAttachment){
                throw new \RuntimeException('Notice attachment could not be stored.');
            }

            $validated['attachment']=$newAttachment;
        }

        try{
            $notice=$this->noticeService->update(
                $notice,
                $validated
            );
        }catch(\Throwable $e){
            $this->deletePublicFile($newAttachment);
            throw $e;
        }

        if($newAttachment&&$oldAttachment!==$newAttachment){
            $this->deletePublicFile($oldAttachment);
        }

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
        $notifyMembers=$request->boolean('notify_members');
        $audience=null;
        $willPublish=!$notice->is_published;

        if($notifyMembers){
            if(!$willPublish){
                throw ValidationException::withMessages([
                    'notify_members'=>[
                        'Member notification can only be sent when publishing a notice.'
                    ]
                ]);
            }

            if(!$this->isPublishableNow($notice)){
                throw ValidationException::withMessages([
                    'notify_members'=>[
                        'Member notification can only be sent for a notice that is publishable now.'
                    ]
                ]);
            }

            $audience=$request->validate([
                'audience_type'=>'required|in:all_active_members,role,users',
                'role_id'=>'nullable|required_if:audience_type,role|integer|exists:roles,id',
                'user_ids'=>'nullable|required_if:audience_type,users|array|min:1|max:500',
                'user_ids.*'=>'integer|distinct|exists:users,id',
            ]);
        }

        $notice=$this->noticeService->togglePublish($notice);
        $notificationFailed=false;

        if($notice->is_published&&$notifyMembers){
            try{
                $this->sendNoticeNotification(
                    $notice,
                    $request,
                    $audience
                );
            }catch(\Throwable $e){
                report($e);
                $notificationFailed=true;
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>$notificationFailed
                ?'Notice published, but member notification could not be sent.'
                :($notice->is_published
                    ?'Notice published successfully.'
                    :'Notice unpublished successfully.'),
            'notification_sent'=>$notifyMembers&&!$notificationFailed,
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

    protected function validateSchedule(array $data,?Notice $notice=null): void
    {
        if(
            !array_key_exists('publish_at',$data)&&
            !array_key_exists('expires_at',$data)
        ){
            return;
        }

        $publishAt=array_key_exists('publish_at',$data)
            ?$data['publish_at']
            :$notice?->publish_at;

        $expiresAt=array_key_exists('expires_at',$data)
            ?$data['expires_at']
            :$notice?->expires_at;

        if(!$publishAt||!$expiresAt){
            return;
        }

        if(
            Carbon::parse($expiresAt)
                ->lessThanOrEqualTo(Carbon::parse($publishAt))
        ){
            throw ValidationException::withMessages([
                'expires_at'=>['Expiry time must be after publish time.']
            ]);
        }
    }

    protected function validateImmediateNotification(array $data): void
    {
        if(!($data['is_published']??false)){
            throw ValidationException::withMessages([
                'notify_members'=>[
                    'Enable Publish Now before sending member notifications.'
                ]
            ]);
        }

        if(
            !empty($data['publish_at'])&&
            Carbon::parse($data['publish_at'])->isFuture()
        ){
            throw ValidationException::withMessages([
                'notify_members'=>[
                    'Scheduled notices cannot send member notifications immediately.'
                ]
            ]);
        }

        if(
            !empty($data['expires_at'])&&
            Carbon::parse($data['expires_at'])->isPast()
        ){
            throw ValidationException::withMessages([
                'notify_members'=>[
                    'Expired notices cannot send member notifications.'
                ]
            ]);
        }
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

    protected function deletePublicFile(?string $path): void
    {
        if(!$path){
            return;
        }

        try{
            $disk=Storage::disk('public');

            if($disk->exists($path)){
                $disk->delete($path);
            }
        }catch(\Throwable $e){
            report($e);
        }
    }
}
