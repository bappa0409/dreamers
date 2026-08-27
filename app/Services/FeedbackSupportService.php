<?php

namespace App\Services;

use App\Models\FeedbackSupport;
use App\Models\FeedbackSupportAttachment;
use App\Models\FeedbackSupportCategory;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FeedbackSupportService
{
    public function __construct(
        protected NotificationService $notifications,
        protected NumberSequenceService $numbers
    ){}

    public function create(Member $member,array $data,int $userId): FeedbackSupport
    {
        return DB::transaction(function()use($member,$data,$userId){
            $member=Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($member->status,['rejected','exited','deceased'],true)){
                throw ValidationException::withMessages([
                    'member_id'=>['This member cannot submit Feedback & Support requests.']
                ]);
            }

            FeedbackSupportCategory::query()
                ->whereKey($data['feedback_support_category_id'])
                ->where('is_active',true)
                ->firstOrFail();

            $ticket=FeedbackSupport::create([
                'ticket_no'=>$this->numbers->next(
                    'feedback-support',
                    'FS-',
                    6
                ),
                'member_id'=>$member->id,
                'feedback_support_category_id'=>$data['feedback_support_category_id'],
                'type'=>$data['type'],
                'subject'=>trim($data['subject']),
                'description'=>trim($data['description']),
                'priority'=>$data['priority']??'normal',
                'is_confidential'=>$data['is_confidential']??false,
                'status'=>'submitted',
                'created_by'=>$userId
            ]);

            $this->history(
                $ticket,
                null,
                'submitted',
                'Feedback & Support request submitted.',
                $userId
            );

            $this->forgetCache();

            return $this->fresh($ticket);
        });
    }

    public function review(
        FeedbackSupport $ticket,
        ?string $note,
        int $userId
    ): FeedbackSupport{
        return DB::transaction(function()use($ticket,$note,$userId){
            $ticket=$this->lock($ticket);

            if($ticket->status!=='submitted'){
                throw ValidationException::withMessages([
                    'status'=>['Only submitted requests can be moved to review.']
                ]);
            }

            $this->setStatus(
                $ticket,
                'under_review',
                $note,
                $userId
            );

            return $this->fresh($ticket);
        });
    }

    public function assign(
        FeedbackSupport $ticket,
        User $assignee,
        ?string $note,
        int $userId
    ): FeedbackSupport{
        return DB::transaction(function()use(
            $ticket,$assignee,$note,$userId
        ){
            $ticket=$this->lock($ticket);

            $assignee=User::query()
                ->whereKey($assignee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!in_array(
                $ticket->status,
                ['submitted','under_review','assigned','in_progress'],
                true
            )){
                throw ValidationException::withMessages([
                    'status'=>['This request cannot be assigned at this stage.']
                ]);
            }

            if(!$assignee->is_active){
                throw ValidationException::withMessages([
                    'assigned_to'=>['Assigned user must be active.']
                ]);
            }

            if(
                !$assignee->hasPermission('FeedbackSupport.view')||
                !$assignee->hasAnyPermission([
                    'FeedbackSupport.review',
                    'FeedbackSupport.resolve',
                    'FeedbackSupport.manage',
                ])
            ){
                throw ValidationException::withMessages([
                    'assigned_to'=>[
                        'Assigned user does not have Feedback & Support handling permission.'
                    ]
                ]);
            }

            if(
                $ticket->is_confidential&&
                !$assignee->hasPermission('FeedbackSupport.confidential')
            ){
                throw ValidationException::withMessages([
                    'assigned_to'=>[
                        'Confidential requests can only be assigned to a user with confidential access.'
                    ]
                ]);
            }

            $old=$ticket->status;

            $ticket->update([
                'assigned_to'=>$assignee->id,
                'assigned_by'=>$userId,
                'assigned_at'=>now(),
                'status'=>'assigned'
            ]);

            $this->history(
                $ticket,
                $old,
                'assigned',
                $note,
                $userId
            );

            DB::afterCommit(function()use(
                $ticket,$assignee,$userId
            ){
                $this->notifications->sendSystem([
                    'title'=>'Feedback & Support Assigned',
                    'message'=>
                        "{$ticket->ticket_no} has been assigned to you.",
                    'type'=>'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$assignee->id],
                    'action_url'=>route('admin.feedback-support'),
                    'sent_by'=>$userId
                ]);
            });

            $this->forgetCache();

            return $this->fresh($ticket);
        });
    }

    public function startProgress(
        FeedbackSupport $ticket,
        ?string $note,
        int $userId
    ): FeedbackSupport{
        return DB::transaction(function()use($ticket,$note,$userId){
            $ticket=$this->lock($ticket);

            if($ticket->status!=='assigned'){
                throw ValidationException::withMessages([
                    'status'=>['Only assigned requests can be started.']
                ]);
            }

            $this->setStatus(
                $ticket,
                'in_progress',
                $note,
                $userId
            );

            return $this->fresh($ticket);
        });
    }

    public function internalNote(
        FeedbackSupport $ticket,
        string $message,
        int $userId
    ){
        $message=trim($message);

        if($message===''){
            throw ValidationException::withMessages([
                'message'=>['Internal note is required.']
            ]);
        }

        return $ticket->updates()->create([
            'type'=>'internal_note',
            'message'=>$message,
            'created_by'=>$userId
        ])->load('user:id,name');
    }

    public function supportResponse(
        FeedbackSupport $ticket,
        string $message,
        int $userId
    ){
        return DB::transaction(function()use($ticket,$message,$userId){
            $ticket=$this->lock($ticket);

            if(in_array(
                $ticket->status,
                ['closed','cancelled','rejected'],
                true
            )){
                throw ValidationException::withMessages([
                    'status'=>['Response cannot be added to this request.']
                ]);
            }

            $message=trim($message);

            if($message===''){
                throw ValidationException::withMessages([
                    'message'=>['Response is required.']
                ]);
            }

            $update=$ticket->updates()->create([
                'type'=>'support_response',
                'message'=>$message,
                'created_by'=>$userId
            ]);

            $this->notifyMember(
                $ticket,
                'Feedback & Support Updated',
                "A new response was added to {$ticket->ticket_no}.",
                'info',
                $userId
            );

            return $update->load('user:id,name');
        });
    }

    public function memberFollowUp(
        FeedbackSupport $ticket,
        string $message,
        int $userId
    ){
        return DB::transaction(function()use($ticket,$message,$userId){
            $ticket=$this->lock($ticket);

            if(in_array(
                $ticket->status,
                ['resolved','closed','cancelled','rejected'],
                true
            )){
                throw ValidationException::withMessages([
                    'status'=>['Follow-up cannot be added at this stage.']
                ]);
            }

            $message=trim($message);

            if($message===''){
                throw ValidationException::withMessages([
                    'message'=>['Follow-up is required.']
                ]);
            }

            return $ticket->updates()->create([
                'type'=>'member_follow_up',
                'message'=>$message,
                'created_by'=>$userId
            ]);
        });
    }

    public function resolve(
        FeedbackSupport $ticket,
        string $resolution,
        int $userId
    ): FeedbackSupport{
        return DB::transaction(function()use(
            $ticket,$resolution,$userId
        ){
            $ticket=$this->lock($ticket);

            if(!in_array(
                $ticket->status,
                ['assigned','in_progress'],
                true
            )){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Only assigned or in-progress requests can be resolved.'
                    ]
                ]);
            }

            $resolution=trim($resolution);

            if($resolution===''){
                throw ValidationException::withMessages([
                    'resolution'=>['Resolution is required.']
                ]);
            }

            $old=$ticket->status;

            $ticket->update([
                'status'=>'resolved',
                'resolution'=>$resolution,
                'resolved_by'=>$userId,
                'resolved_at'=>now()
            ]);

            $ticket->updates()->create([
                'type'=>'support_response',
                'message'=>$resolution,
                'created_by'=>$userId
            ]);

            $this->history(
                $ticket,
                $old,
                'resolved',
                $resolution,
                $userId
            );

            $this->notifyMember(
                $ticket,
                'Feedback & Support Resolved',
                "{$ticket->ticket_no} has been resolved.",
                'success',
                $userId
            );

            $this->forgetCache();

            return $this->fresh($ticket);
        });
    }

    public function close(
        FeedbackSupport $ticket,
        ?string $note,
        int $userId
    ): FeedbackSupport{
        return DB::transaction(function()use(
            $ticket,$note,$userId
        ){
            $ticket=$this->lock($ticket);

            if($ticket->status!=='resolved'){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Feedback & Support request must be resolved before closing.'
                    ]
                ]);
            }

            $old=$ticket->status;

            $ticket->update([
                'status'=>'closed',
                'closed_by'=>$userId,
                'closed_at'=>now()
            ]);

            $this->history(
                $ticket,
                $old,
                'closed',
                $note,
                $userId
            );

            $this->forgetCache();

            return $this->fresh($ticket);
        });
    }

    public function cancel(
        FeedbackSupport $ticket,
        int $userId
    ): FeedbackSupport{
        return DB::transaction(function()use($ticket,$userId){
            $ticket=$this->lock($ticket);

            if($ticket->status!=='submitted'){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Only submitted Feedback & Support requests can be cancelled.'
                    ]
                ]);
            }

            $old=$ticket->status;

            $ticket->update([
                'status'=>'cancelled'
            ]);

            $this->history(
                $ticket,
                $old,
                'cancelled',
                'Cancelled by member.',
                $userId
            );

            $this->forgetCache();

            return $this->fresh($ticket);
        });
    }

    public function upload(
        FeedbackSupport $ticket,
        UploadedFile $file,
        int $userId
    ): FeedbackSupportAttachment{
        $filename=
            Str::uuid()->toString().
            '.'.
            strtolower($file->getClientOriginalExtension());

        $path=null;

        try{
            return DB::transaction(function()use(
                $ticket,$file,$userId,$filename,&$path
            ){
                $ticket=$this->lock($ticket);

                if(in_array(
                    $ticket->status,
                    ['resolved','closed','cancelled','rejected'],
                    true
                )){
                    throw ValidationException::withMessages([
                        'status'=>[
                            'Attachments cannot be added to this request at this stage.'
                        ]
                    ]);
                }

                $path=$file->storeAs(
                    "feedback-support/{$ticket->member_id}/{$ticket->id}",
                    $filename,
                    'local'
                );

                return FeedbackSupportAttachment::create([
                    'feedback_support_id'=>$ticket->id,
                    'file_path'=>$path,
                    'original_name'=>$file->getClientOriginalName(),
                    'mime_type'=>$file->getMimeType(),
                    'file_size'=>$file->getSize(),
                    'uploaded_by'=>$userId
                ]);
            });
        }catch(\Throwable $e){
            if($path){
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    public function statistics(
        bool $includeConfidential=false
    ): array{
        $cacheKey='feedback-support:statistics:'.
            ($includeConfidential?'all':'non-confidential');

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function()use($includeConfidential){
                $row=FeedbackSupport::query()
                    ->when(
                        !$includeConfidential,
                        fn($q)=>$q->where('is_confidential',false)
                    )
                    ->selectRaw("
                        COUNT(*) AS total,
                        SUM(CASE WHEN status IN ('submitted','under_review','assigned','in_progress') THEN 1 ELSE 0 END) AS open_count,
                        SUM(CASE WHEN priority='urgent' AND status NOT IN ('closed','cancelled','rejected') THEN 1 ELSE 0 END) AS urgent,
                        SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) AS resolved,
                        SUM(CASE WHEN status='closed' THEN 1 ELSE 0 END) AS closed_count
                    ")
                    ->first();

                return[
                    'total'=>(int)($row->total??0),
                    'open'=>(int)($row->open_count??0),
                    'urgent'=>(int)($row->urgent??0),
                    'resolved'=>(int)($row->resolved??0),
                    'closed'=>(int)($row->closed_count??0),
                ];
            }
        );
    }

    protected function setStatus(
        FeedbackSupport $ticket,
        string $status,
        ?string $note,
        int $userId
    ): void{
        $old=$ticket->status;

        $ticket->update([
            'status'=>$status
        ]);

        $this->history(
            $ticket,
            $old,
            $status,
            $note,
            $userId
        );

        $this->forgetCache();
    }

    protected function history(
        FeedbackSupport $ticket,
        ?string $from,
        string $to,
        ?string $note,
        int $userId
    ): void{
        $ticket->histories()->create([
            'from_status'=>$from,
            'to_status'=>$to,
            'note'=>$note?trim($note):null,
            'changed_by'=>$userId
        ]);
    }

    protected function notifyMember(
        FeedbackSupport $ticket,
        string $title,
        string $message,
        string $type,
        int $senderId
    ): void{
        $ticket->loadMissing('member.user');

        $userId=$ticket->member?->user_id;

        if(!$userId){
            return;
        }

        DB::afterCommit(function()use(
            $title,$message,$type,$senderId,$userId
        ){
            $this->notifications->sendSystem([
                'title'=>$title,
                'message'=>$message,
                'type'=>$type,
                'audience_type'=>'users',
                'user_ids'=>[$userId],
                'action_url'=>route('member.feedback-support'),
                'sent_by'=>$senderId
            ]);
        });
    }

    protected function lock(
        FeedbackSupport $ticket
    ): FeedbackSupport{
        return FeedbackSupport::query()
            ->whereKey($ticket->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function fresh(
        FeedbackSupport $ticket
    ): FeedbackSupport{
        return $ticket->fresh([
            'category',
            'member.user:id,name,email',
            'assignee:id,name,email',
            'assigner:id,name',
            'attachments.uploader:id,name',
            'updates.user:id,name',
            'histories.user:id,name'
        ]);
    }

    protected function forgetCache(): void
    {
        DB::afterCommit(function(){
            Cache::forget('feedback-support:statistics');
            Cache::forget('feedback-support:statistics:all');
            Cache::forget('feedback-support:statistics:non-confidential');
        });
    }
}