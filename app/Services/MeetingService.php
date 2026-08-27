<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Member;
use App\Models\Meeting;
use App\Models\MeetingAgenda;
use App\Models\MeetingAttendee;
use App\Models\MeetingDecision;
use App\Models\MeetingExpense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MeetingService
{
    public function __construct(
        protected AccountingService $accounting,
        protected NotificationService $notificationService,
        protected NumberSequenceService $numberSequence
    ){}

    public function create(array $data,?int $userId=null): Meeting
    {
        $this->validateTimeRange(
            $data['start_time']??null,
            $data['end_time']??null
        );

        return DB::transaction(function()use($data,$userId){
            $meeting=Meeting::create([
                'meeting_no'=>$this->generateMeetingNo(),
                'title'=>$data['title'],
                'type'=>$data['type']??'general',
                'meeting_date'=>$data['meeting_date'],
                'start_time'=>$data['start_time']??null,
                'end_time'=>$data['end_time']??null,
                'venue'=>$data['venue']??null,
                'description'=>$data['description']??null,
                'budget_amount'=>$data['budget_amount']??0,
                'status'=>'draft',
                'created_by'=>$userId??auth()->id(),
                'notes'=>$data['notes']??null
            ]);

            $this->forgetCache();

            return $this->fresh($meeting);
        });
    }

    public function details(Meeting $meeting): Meeting
    {
        return Meeting::query()
            ->select([
                'id',
                'meeting_no',
                'title',
                'type',
                'meeting_date',
                'start_time',
                'end_time',
                'venue',
                'description',
                'budget_amount',
                'status',
                'created_by',
                'completed_by',
                'completed_at',
                'minutes',
                'notes',
                'created_at',
                'updated_at'
            ])
            ->with([
                'creator:id,name,email',
                'completer:id,name,email',

                'agendas'=>fn($q)=>$q
                    ->select([
                        'id',
                        'meeting_id',
                        'sort_order',
                        'title',
                        'description',
                        'status',
                        'created_at',
                        'updated_at'
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('id'),

                'attendees'=>fn($q)=>$q
                    ->select([
                        'id',
                        'meeting_id',
                        'member_id',
                        'status',
                        'notes',
                        'created_at',
                        'updated_at'
                    ])
                    ->with([
                        'member'=>fn($q)=>$q
                            ->select([
                                'id',
                                'user_id',
                                'member_code',
                                'status'
                            ])
                            ->with('user:id,name,email')
                    ])
                    ->orderBy('id'),

                'decisions'=>fn($q)=>$q
                    ->select([
                        'id',
                        'meeting_id',
                        'meeting_agenda_id',
                        'decision_no',
                        'title',
                        'decision',
                        'result',
                        'votes_for',
                        'votes_against',
                        'votes_abstain',
                        'responsible_user_id',
                        'due_date',
                        'status',
                        'completed_at',
                        'completion_notes',
                        'created_at',
                        'updated_at'
                    ])
                    ->with([
                        'agenda:id,meeting_id,sort_order,title',
                        'responsibleUser:id,name,email'
                    ])
                    ->orderBy('id'),

                'expenses'=>fn($q)=>$q
                    ->select([
                        'id',
                        'meeting_id',
                        'expense_no',
                        'category',
                        'expense_account_id',
                        'payment_account_id',
                        'amount',
                        'expense_date',
                        'payee',
                        'reference_no',
                        'description',
                        'status',
                        'finance_transaction_id',
                        'created_by',
                        'created_at',
                        'updated_at'
                    ])
                    ->with([
                        'expenseAccount:id,code,name',
                        'paymentAccount:id,code,name',
                        'creator:id,name,email',
                        'financeTransaction'=>fn($q)=>$q
                            ->select([
                                'id',
                                'transaction_no',
                                'transaction_date',
                                'type',
                                'status'
                            ])
                            ->with([
                                'entries'=>fn($q)=>$q
                                    ->select([
                                        'id',
                                        'transaction_id',
                                        'account_id',
                                        'debit',
                                        'credit',
                                        'description'
                                    ])
                                    ->with('account:id,code,name')
                            ])
                    ])
                    ->latest('expense_date')
                    ->latest('id')
            ])
            ->findOrFail($meeting->id);
    }

    public function update(Meeting $meeting,array $data): Meeting
    {
        $this->validateTimeRange(
            $data['start_time']??null,
            $data['end_time']??null
        );

        return DB::transaction(function()use($meeting,$data){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($meeting->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Completed or cancelled meeting cannot be edited.'
                    ]
                ]);
            }

            $old=[
                'title'=>$meeting->title,
                'meeting_date'=>$meeting->meeting_date?->toDateString(),
                'start_time'=>$this->normalizeTime($meeting->start_time),
                'end_time'=>$this->normalizeTime($meeting->end_time),
                'venue'=>$meeting->venue
            ];

            $meeting->update([
                'title'=>$data['title'],
                'type'=>$data['type']??$meeting->type,
                'meeting_date'=>$data['meeting_date'],
                'start_time'=>$data['start_time']??null,
                'end_time'=>$data['end_time']??null,
                'venue'=>$data['venue']??null,
                'description'=>$data['description']??null,
                'budget_amount'=>$data['budget_amount']??0,
                'notes'=>$data['notes']??null
            ]);

            $importantChanged=
                $old['title']!==$meeting->title||
                $old['meeting_date']!==$meeting->meeting_date?->toDateString()||
                $old['start_time']!==$this->normalizeTime($meeting->start_time)||
                $old['end_time']!==$this->normalizeTime($meeting->end_time)||
                $old['venue']!==$meeting->venue;

            if(
                $importantChanged&&
                in_array($meeting->status,['scheduled','ongoing'],true)
            ){
                $this->notifyMeetingAudience($meeting,[
                    'title'=>'Meeting Information Updated',
                    'message'=>"{$meeting->title} meeting information has been updated. Please review the latest date, time and venue.",
                    'type'=>'info',
                    'action_url'=>$this->memberMeetingUrl($meeting)
                ]);
            }

            $this->forgetCache();

            return $this->fresh($meeting);
        });
    }

    public function schedule(Meeting $meeting): Meeting
    {
        return DB::transaction(function()use($meeting){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($meeting->status!=='draft'){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Only draft meeting can be scheduled.'
                    ]
                ]);
            }

            $meeting->update([
                'status'=>'scheduled'
            ]);

            $this->notifyMeetingAudience($meeting,[
                'title'=>'Meeting Scheduled',
                'message'=>$this->scheduleMessage($meeting),
                'type'=>'info',
                'action_url'=>$this->memberMeetingUrl($meeting)
            ]);

            $this->forgetCache();

            return $this->fresh($meeting);
        });
    }

    public function changeStatus(
        Meeting $meeting,
        string $status,
        ?int $userId=null
    ): Meeting{
        return DB::transaction(function()use($meeting,$status,$userId){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            $transitions=[
                'draft'=>['scheduled','cancelled'],
                'scheduled'=>['ongoing','cancelled'],
                'ongoing'=>['completed','cancelled'],
                'completed'=>[],
                'cancelled'=>[]
            ];

            if(!in_array($status,$transitions[$meeting->status]??[],true)){
                throw ValidationException::withMessages([
                    'status'=>[
                        "Invalid status transition: {$meeting->status} → {$status}."
                    ]
                ]);
            }

            if(
                $status==='cancelled'&&
                $meeting->expenses()->where('status','posted')->exists()
            ){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Reverse posted expenses before cancelling this meeting.'
                    ]
                ]);
            }

            $payload=[
                'status'=>$status
            ];

            if($status==='completed'){
                $payload['completed_by']=$userId??auth()->id();
                $payload['completed_at']=now();
            }

            $meeting->update($payload);

            $notification=$this->statusNotification($meeting,$status);

            if($notification){
                $this->notifyMeetingAudience($meeting,[
                    ...$notification,
                    'action_url'=>$this->memberMeetingUrl($meeting),
                    'sent_by'=>$userId??auth()->id()
                ]);
            }

            $this->forgetCache();

            return $this->fresh($meeting);
        });
    }

    public function updateMinutes(
        Meeting $meeting,
        ?string $minutes
    ): Meeting{
        return DB::transaction(function()use($meeting,$minutes){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hadMinutes=filled($meeting->minutes);

            $meeting->update([
                'minutes'=>$minutes
            ]);

            if(filled($minutes)&&$meeting->status==='completed'){
                $this->notifyMeetingAudience($meeting,[
                    'title'=>$hadMinutes
                        ?'Meeting Minutes Updated'
                        :'Meeting Minutes Published',
                    'message'=>$hadMinutes
                        ?"Minutes for {$meeting->title} have been updated."
                        :"Minutes for {$meeting->title} are now available.",
                    'type'=>'info',
                    'action_url'=>$this->memberMeetingUrl($meeting)
                ]);
            }

            return $this->fresh($meeting);
        });
    }

    public function delete(Meeting $meeting): void
    {
        DB::transaction(function()use($meeting){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($meeting->expenses()->exists()){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Meeting with expense history cannot be deleted.'
                    ]
                ]);
            }

            if(!in_array($meeting->status,['draft','cancelled'],true)){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Only draft or cancelled meeting can be deleted.'
                    ]
                ]);
            }

            $meeting->delete();

            $this->forgetCache();
        });
    }

    public function addAgenda(Meeting $meeting,array $data): MeetingAgenda
    {
        return DB::transaction(function()use($meeting,$data){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($meeting->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Agenda cannot be added to this meeting.'
                    ]
                ]);
            }

            $sortOrder=$data['sort_order']
                ??((int)$meeting->agendas()->max('sort_order')+1);

            $agenda=$meeting->agendas()->create([
                'sort_order'=>$sortOrder,
                'title'=>$data['title'],
                'description'=>$data['description']??null,
                'status'=>$data['status']??'pending'
            ]);

            return $agenda;
        });
    }

    public function updateAgenda(
        MeetingAgenda $agenda,
        array $data
    ): MeetingAgenda{
        return DB::transaction(function()use($agenda,$data){
            $agenda=MeetingAgenda::query()
                ->with('meeting:id,status')
                ->whereKey($agenda->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($agenda->meeting?->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'agenda'=>[
                        'Agenda cannot be modified for this meeting.'
                    ]
                ]);
            }

            $agenda->update([
                'sort_order'=>$data['sort_order']??$agenda->sort_order,
                'title'=>$data['title']??$agenda->title,
                'description'=>array_key_exists('description',$data)
                    ?$data['description']
                    :$agenda->description,
                'status'=>$data['status']??$agenda->status
            ]);
            
            return $agenda;
        });
    }

    public function deleteAgenda(MeetingAgenda $agenda): void
    {
        DB::transaction(function()use($agenda){
            $agenda=MeetingAgenda::query()
                ->with('meeting:id,status')
                ->whereKey($agenda->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($agenda->meeting?->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'agenda'=>[
                        'Agenda cannot be deleted from this meeting.'
                    ]
                ]);
            }

            if($agenda->decisions()->exists()){
                throw ValidationException::withMessages([
                    'agenda'=>[
                        'Agenda with decisions cannot be deleted.'
                    ]
                ]);
            }

            $agenda->delete();
        });
    }

    public function addAttendee(
        Meeting $meeting,
        Member $member,
        array $data=[]
    ): MeetingAttendee{
        return DB::transaction(function()use($meeting,$member,$data){
            $meeting=Meeting::query()
                ->select([
                    'id',
                    'title',
                    'type',
                    'meeting_date',
                    'start_time',
                    'venue',
                    'status'
                ])
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($meeting->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Attendees cannot be added to this meeting.'
                    ]
                ]);
            }

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member_id'=>[
                        'Only active members can be added.'
                    ]
                ]);
            }

            $attendee=MeetingAttendee::query()->firstOrNew([
                'meeting_id'=>$meeting->id,
                'member_id'=>$member->id
            ]);

            $isNew=!$attendee->exists;

            $attendee->status=$data['status']??'invited';
            $attendee->notes=$data['notes']??null;
            $attendee->save();

            if($isNew&&$member->user_id){
                $this->afterCommitNotification([
                    'title'=>'Meeting Invitation',
                    'message'=>$this->invitationMessage($meeting),
                    'type'=>'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$member->user_id],
                    'action_url'=>$this->memberMeetingUrl($meeting)
                ]);
            }

            return $attendee->load([
                'member'=>fn($q)=>$q
                    ->select([
                        'id',
                        'user_id',
                        'member_code',
                        'status'
                    ])
                    ->with('user:id,name,email')
            ]);
        });
    }

    public function updateAttendee(
        MeetingAttendee $attendee,
        array $data
    ): MeetingAttendee{
        return DB::transaction(function()use($attendee,$data){
            $attendee=MeetingAttendee::query()
                ->with([
                    'meeting:id,title,meeting_date',
                    'member:id,user_id,member_code'
                ])
                ->whereKey($attendee->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus=$attendee->status;

            $attendee->update([
                'status'=>$data['status'],
                'notes'=>$data['notes']??$attendee->notes
            ]);

            if(
                $oldStatus!==$attendee->status&&
                $attendee->member?->user_id
            ){
                $message=match($attendee->status){
                    'invited'=>"You have been invited to {$attendee->meeting->title}.",
                    'present'=>"Your attendance for {$attendee->meeting->title} has been marked present.",
                    'absent'=>"Your attendance for {$attendee->meeting->title} has been marked absent.",
                    'excused'=>"Your attendance for {$attendee->meeting->title} has been marked excused.",
                    default=>"Your attendance status for {$attendee->meeting->title} has been updated."
                };

                $this->afterCommitNotification([
                    'title'=>'Meeting Attendance Updated',
                    'message'=>$message,
                    'type'=>$attendee->status==='absent'?'warning':'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$attendee->member->user_id],
                    'action_url'=>$this->memberMeetingUrl($attendee->meeting)
                ]);
            }

            return $attendee->fresh('member.user');
        });
    }

    public function removeAttendee(MeetingAttendee $attendee): void
    {
        DB::transaction(function()use($attendee){
            $attendee=MeetingAttendee::query()
                ->with([
                    'meeting:id,title',
                    'member:id,user_id'
                ])
                ->whereKey($attendee->id)
                ->lockForUpdate()
                ->firstOrFail();

            $userId=$attendee->member?->user_id;
            $meeting=$attendee->meeting;

            $attendee->delete();

            if($userId&&$meeting){
                $this->afterCommitNotification([
                    'title'=>'Meeting Invitation Removed',
                    'message'=>"You have been removed from {$meeting->title}.",
                    'type'=>'warning',
                    'audience_type'=>'users',
                    'user_ids'=>[$userId],
                    'action_url'=>$this->memberMeetingUrl($meeting)
                ]);
            }
        });
    }

    public function addDecision(
        Meeting $meeting,
        array $data
    ): MeetingDecision{
        return DB::transaction(function()use($meeting,$data){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($meeting->status==='cancelled'){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Decision cannot be added to a cancelled meeting.'
                    ]
                ]);
            }

            if(
                !empty($data['meeting_agenda_id'])&&
                !MeetingAgenda::query()
                    ->whereKey($data['meeting_agenda_id'])
                    ->where('meeting_id',$meeting->id)
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'meeting_agenda_id'=>[
                        'Selected agenda does not belong to this meeting.'
                    ]
                ]);
            }

            $decision=MeetingDecision::create([
                'meeting_id'=>$meeting->id,
                'meeting_agenda_id'=>$data['meeting_agenda_id']??null,
                'decision_no'=>$this->generateDecisionNo(),
                'title'=>$data['title'],
                'decision'=>$data['decision'],
                'result'=>$data['result']??'approved',
                'votes_for'=>$data['votes_for']??0,
                'votes_against'=>$data['votes_against']??0,
                'votes_abstain'=>$data['votes_abstain']??0,
                'responsible_user_id'=>$data['responsible_user_id']??null,
                'due_date'=>$data['due_date']??null,
                'status'=>$data['status']??'pending',
                'completion_notes'=>$data['completion_notes']??null
            ]);

            if($decision->responsible_user_id){
                $this->afterCommitNotification([
                    'title'=>'Meeting Decision Assigned',
                    'message'=>"You have been assigned responsibility for the decision: {$decision->title}.",
                    'type'=>'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$decision->responsible_user_id],
                    'action_url'=>$this->memberMeetingUrl($meeting)
                ]);
            }

            return $decision->fresh([
                'agenda',
                'responsibleUser:id,name,email'
            ]);
        });
    }

    public function updateDecision(
        MeetingDecision $decision,
        array $data
    ): MeetingDecision{
        return DB::transaction(function()use($decision,$data){
            $decision=MeetingDecision::query()
                ->with('meeting:id,title,status')
                ->whereKey($decision->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($decision->meeting?->status==='cancelled'){
                throw ValidationException::withMessages([
                    'decision'=>[
                        'Decision cannot be modified for a cancelled meeting.'
                    ]
                ]);
            }

            if(
                isset($data['meeting_agenda_id'])&&
                $data['meeting_agenda_id']&&
                !MeetingAgenda::query()
                    ->whereKey($data['meeting_agenda_id'])
                    ->where('meeting_id',$decision->meeting_id)
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'meeting_agenda_id'=>[
                        'Selected agenda does not belong to this meeting.'
                    ]
                ]);
            }

            $oldStatus=$decision->status;
            $oldResponsible=$decision->responsible_user_id;

            if(
                ($data['status']??null)==='completed'&&
                $decision->status!=='completed'
            ){
                $data['completed_at']=now();
            }

            if(
                array_key_exists('status',$data)&&
                $data['status']!=='completed'
            ){
                $data['completed_at']=null;
            }

            $decision->update($data);

            if(
                $decision->responsible_user_id&&
                $oldResponsible!==$decision->responsible_user_id
            ){
                $this->afterCommitNotification([
                    'title'=>'Meeting Decision Assigned',
                    'message'=>"You have been assigned responsibility for the decision: {$decision->title}.",
                    'type'=>'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$decision->responsible_user_id],
                    'action_url'=>$this->memberMeetingUrl($decision->meeting)
                ]);
            }elseif(
                $decision->responsible_user_id&&
                $oldStatus!==$decision->status
            ){
                $this->afterCommitNotification([
                    'title'=>'Decision Status Updated',
                    'message'=>"Decision \"{$decision->title}\" is now ".str_replace('_',' ',$decision->status).'.',
                    'type'=>$decision->status==='completed'
                        ?'success'
                        :'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$decision->responsible_user_id],
                    'action_url'=>$this->memberMeetingUrl($decision->meeting)
                ]);
            }

            return $decision->fresh([
                'agenda',
                'responsibleUser:id,name,email'
            ]);
        });
    }

    public function deleteDecision(MeetingDecision $decision): void
    {
        DB::transaction(function()use($decision){
            $decision=MeetingDecision::query()
                ->with('meeting:id,status')
                ->whereKey($decision->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($decision->meeting?->status==='completed'){
                throw ValidationException::withMessages([
                    'decision'=>[
                        'Decision from a completed meeting cannot be deleted.'
                    ]
                ]);
            }

            $decision->delete();
        });
    }

    public function addExpense(
        Meeting $meeting,
        array $data,
        ?int $userId=null
    ): MeetingExpense{
        return DB::transaction(function()use($meeting,$data,$userId){
            $meeting=Meeting::query()
                ->whereKey($meeting->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($meeting->status,['draft','cancelled','completed'],true)){
                throw ValidationException::withMessages([
                    'meeting'=>[
                        'Expense cannot be posted to this meeting.'
                    ]
                ]);
            }

            $expenseAccount=$this->expenseAccount(
                (int)$data['expense_account_id']
            );

            $paymentAccount=$this->paymentAccount(
                (int)$data['payment_account_id']
            );

            $amount=round((float)$data['amount'],2);

            if($amount<=0){
                throw ValidationException::withMessages([
                    'amount'=>[
                        'Expense amount must be greater than zero.'
                    ]
                ]);
            }

            $expense=MeetingExpense::create([
                'meeting_id'=>$meeting->id,
                'expense_no'=>$this->generateExpenseNo(),
                'category'=>$data['category'],
                'expense_account_id'=>$expenseAccount->id,
                'payment_account_id'=>$paymentAccount->id,
                'amount'=>$amount,
                'expense_date'=>$data['expense_date'],
                'payee'=>$data['payee']??null,
                'reference_no'=>$data['reference_no']??null,
                'description'=>$data['description']??null,
                'status'=>'posted',
                'finance_transaction_id'=>null,
                'created_by'=>$userId??auth()->id()
            ]);

            $transaction=$this->accounting->post([
                'transaction_date'=>$expense->expense_date->toDateString(),
                'type'=>'meeting_expense',
                'source_module'=>'meeting',
                'source_id'=>$meeting->id,
                'reference_type'=>MeetingExpense::class,
                'reference_id'=>$expense->id,
                'description'=>"Meeting expense {$expense->expense_no} - {$meeting->title}",
                'user_id'=>$userId??auth()->id(),
                'entries'=>[
                    [
                        'account_id'=>$expenseAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>"Meeting expense - {$expense->category}"
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>"Meeting expense payment - {$expense->expense_no}"
                    ]
                ]
            ]);

            $expense->update([
                'finance_transaction_id'=>$transaction->id
            ]);

            $this->forgetCache();

            return $expense->fresh([
                'expenseAccount',
                'paymentAccount',
                'financeTransaction.entries.account',
                'creator'
            ]);
        });
    }

    public function cancelExpense(
        MeetingExpense $expense,
        ?int $userId=null
    ): MeetingExpense{
        return DB::transaction(function()use($expense,$userId){
            $expense=MeetingExpense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->status==='cancelled'){
                throw ValidationException::withMessages([
                    'expense'=>[
                        'Expense is already cancelled.'
                    ]
                ]);
            }

            if($expense->finance_transaction_id){
                $transaction=$expense
                    ->financeTransaction()
                    ->with('entries')
                    ->firstOrFail();

                $this->accounting->post([
                    'transaction_date'=>$this->reversalDate($expense->expense_date),
                    'type'=>'meeting_expense_reversal',
                    'source_module'=>'meeting',
                    'source_id'=>$expense->meeting_id,
                    'reference_type'=>MeetingExpense::class,
                    'reference_id'=>$expense->id,
                    'description'=>"Reversal of {$transaction->transaction_no}",
                    'user_id'=>$userId??auth()->id(),
                    'entries'=>$transaction->entries
                        ->map(fn($entry)=>[
                            'account_id'=>$entry->account_id,
                            'debit'=>(float)$entry->credit,
                            'credit'=>(float)$entry->debit,
                            'description'=>"Reversal - {$expense->expense_no}"
                        ])
                        ->all()
                ]);
            }

            $expense->update([
                'status'=>'cancelled'
            ]);

            $this->forgetCache();

            return $expense->fresh([
                'expenseAccount',
                'paymentAccount',
                'financeTransaction.entries.account'
            ]);
        });
    }

    public function statistics(): array
    {
        return Cache::remember('meeting:statistics',300,function(){
            $row=Meeting::query()
                ->selectRaw("
                    COUNT(*) total,
                    SUM(CASE WHEN status='scheduled' THEN 1 ELSE 0 END) scheduled,
                    SUM(CASE WHEN status='ongoing' THEN 1 ELSE 0 END) ongoing,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                    COALESCE(SUM(CASE WHEN status!='cancelled' THEN budget_amount ELSE 0 END),0) total_budget
                ")
                ->first();

            $actual=round(
                (float)MeetingExpense::query()
                    ->where('status','posted')
                    ->sum('amount'),
                2
            );

            return[
                'total'=>(int)($row->total??0),
                'scheduled'=>(int)($row->scheduled??0),
                'ongoing'=>(int)($row->ongoing??0),
                'completed'=>(int)($row->completed??0),
                'total_budget'=>round((float)($row->total_budget??0),2),
                'actual_expense'=>$actual
            ];
        });
    }

    protected function notifyMeetingAudience(
        Meeting $meeting,
        array $data
    ): void{
        if($meeting->type==='executive'){
            $userIds=DB::table('meeting_attendees as ma')
                ->join(
                    'members as m',
                    'm.id',
                    '=',
                    'ma.member_id'
                )
                ->where('ma.meeting_id',$meeting->id)
                ->where('m.status','active')
                ->whereNotNull('m.user_id')
                ->distinct()
                ->pluck('m.user_id')
                ->map(fn($id)=>(int)$id)
                ->all();

            if(empty($userIds)){
                return;
            }

            $data['audience_type']='users';
            $data['user_ids']=$userIds;
        }else{
            $data['audience_type']='all_active_members';
        }

        $this->afterCommitNotification($data);
    }

    protected function statusNotification(
        Meeting $meeting,
        string $status
    ): ?array{
        return match($status){
            'scheduled'=>[
                'title'=>'Meeting Scheduled',
                'message'=>$this->scheduleMessage($meeting),
                'type'=>'info'
            ],
            'ongoing'=>[
                'title'=>'Meeting Started',
                'message'=>"{$meeting->title} is now ongoing.",
                'type'=>'info'
            ],
            'completed'=>[
                'title'=>'Meeting Completed',
                'message'=>"{$meeting->title} has been completed. Decisions and minutes are available from the meeting page.",
                'type'=>'success'
            ],
            'cancelled'=>[
                'title'=>'Meeting Cancelled',
                'message'=>"{$meeting->title} scheduled for {$meeting->meeting_date->format('d M Y')} has been cancelled.",
                'type'=>'warning'
            ],
            default=>null
        };
    }

    protected function scheduleMessage(Meeting $meeting): string
    {
        $message="{$meeting->title} has been scheduled for ".
            $meeting->meeting_date->format('d M Y');

        if($meeting->start_time){
            $message.=' at '.$this->displayTime($meeting->start_time);
        }

        if($meeting->venue){
            $message.=" at {$meeting->venue}";
        }

        return $message.'.';
    }

    protected function invitationMessage(Meeting $meeting): string
    {
        return "You have been invited to {$meeting->title} on ".
            $meeting->meeting_date->format('d M Y').
            ($meeting->start_time
                ?' at '.$this->displayTime($meeting->start_time)
                :'').
            ($meeting->venue
                ?" at {$meeting->venue}"
                :'').
            '.';
    }

    protected function memberMeetingUrl(Meeting $meeting): string
    {
        return route('member.meetings',[
            'meeting'=>$meeting->id
        ]);
    }

    protected function displayTime(mixed $value): string
    {
        $time=$this->normalizeTime($value);

        if(!$time){
            return '';
        }

        return date('g:i A',strtotime($time));
    }

    protected function normalizeTime(mixed $value): ?string
    {
        if(!$value){
            return null;
        }

        if(is_object($value)&&method_exists($value,'format')){
            return $value->format('H:i');
        }

        return substr((string)$value,0,5);
    }

    protected function afterCommitNotification(array $data): void
    {
        $data['sent_by']=$data['sent_by']??auth()->id();

        DB::afterCommit(function()use($data){
            try{
                $this->notificationService->sendSystem($data);
            }catch(\Throwable $e){
                report($e);
            }
        });
    }

    protected function validateTimeRange(?string $startTime,?string $endTime): void
    {
        $start=$this->normalizeTime($startTime);
        $end=$this->normalizeTime($endTime);

        if($start&&$end&&$end<=$start){
            throw ValidationException::withMessages([
                'end_time'=>['End time must be after start time.']
            ]);
        }
    }

    protected function reversalDate(\DateTimeInterface|string|null $originalDate): string
    {
        $today=now()->toDateString();

        if(!$originalDate){
            return $today;
        }

        $date=$originalDate instanceof \DateTimeInterface
            ?$originalDate->format('Y-m-d')
            :substr((string)$originalDate,0,10);

        return $date>$today?$date:$today;
    }

    protected function expenseAccount(int $id): Account
    {
        return Account::query()
            ->whereKey($id)
            ->where('type','expense')
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->firstOr(function(){
                throw ValidationException::withMessages([
                    'expense_account_id'=>[
                        'Select an active expense account.'
                    ]
                ]);
            });
    }

    protected function paymentAccount(int $id): Account
    {
        return Account::query()
            ->whereKey($id)
            ->where('type','asset')
            ->whereIn('sub_type',['cash','bank'])
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->firstOr(function(){
                throw ValidationException::withMessages([
                    'payment_account_id'=>[
                        'Select an active Cash or Bank account.'
                    ]
                ]);
            });
    }

    protected function generateMeetingNo(): string
    {
        $year=now()->format('Y');
        $prefix="MTG-{$year}-";

        return $this->numberSequence->next(
            "meeting:{$year}",
            $prefix,
            4,
            function()use($prefix){
                $last=Meeting::query()
                    ->where('meeting_no','like',$prefix.'%')
                    ->orderByDesc('id')
                    ->value('meeting_no');

                return $last
                    ?(int)substr($last,-4)
                    :0;
            }
        );
    }

    protected function generateDecisionNo(): string
    {
        $year=now()->format('Y');
        $prefix="DEC-{$year}-";

        return $this->numberSequence->next(
            "meeting-decision:{$year}",
            $prefix,
            5,
            function()use($prefix){
                $last=MeetingDecision::query()
                    ->where('decision_no','like',$prefix.'%')
                    ->orderByDesc('id')
                    ->value('decision_no');

                return $last
                    ?(int)substr($last,-5)
                    :0;
            }
        );
    }

    protected function generateExpenseNo(): string
    {
        $year=now()->format('Y');
        $prefix="MEXP-{$year}-";

        return $this->numberSequence->next(
            "meeting-expense:{$year}",
            $prefix,
            6,
            function()use($prefix){
                $last=MeetingExpense::query()
                    ->where('expense_no','like',$prefix.'%')
                    ->orderByDesc('id')
                    ->value('expense_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }

    protected function fresh(Meeting $meeting): Meeting
    {
        return $meeting->fresh([
            'creator:id,name,email',
            'completer:id,name,email'
        ]);
    }

    protected function forgetCache(): void
    {
        Cache::forget('meeting:statistics');
    }
}