<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Member;
use App\Models\Tour;
use App\Models\TourExpense;
use App\Models\TourParticipant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourService
{
    public function __construct(
        protected AccountingService $accounting,
        protected NotificationService $notificationService,
        protected NumberSequenceService $numberSequence
    ){}

    public function create(array $data,?int $userId=null): Tour
    {
        return DB::transaction(function()use($data,$userId){
            $tour=Tour::create([
                'tour_no'=>$this->generateTourNo(),
                'title'=>$data['title'],
                'destination'=>$data['destination'],
                'description'=>$data['description']??null,
                'start_date'=>$data['start_date'],
                'end_date'=>$data['end_date'],
                'budget_amount'=>$data['budget_amount']??0,
                'status'=>'draft',
                'created_by'=>$userId??auth()->id(),
                'notes'=>$data['notes']??null
            ]);

            $this->forgetCache();

            return $this->fresh($tour);
        });
    }

    public function update(Tour $tour,array $data): Tour
    {
        return DB::transaction(function()use($tour,$data){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($tour->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'tour'=>['Completed or cancelled tour cannot be edited.']
                ]);
            }

            $old=[
                'title'=>$tour->title,
                'destination'=>$tour->destination,
                'start_date'=>$tour->start_date?->toDateString(),
                'end_date'=>$tour->end_date?->toDateString()
            ];

            $tour->update([
                'title'=>$data['title'],
                'destination'=>$data['destination'],
                'description'=>$data['description']??null,
                'start_date'=>$data['start_date'],
                'end_date'=>$data['end_date'],
                'budget_amount'=>$data['budget_amount']??0,
                'notes'=>$data['notes']??null
            ]);

            $changed=
                $old['title']!==$tour->title||
                $old['destination']!==$tour->destination||
                $old['start_date']!==$tour->start_date?->toDateString()||
                $old['end_date']!==$tour->end_date?->toDateString();

            if(
                $changed&&
                in_array($tour->status,['approved','upcoming','ongoing'],true)
            ){
                $this->afterCommitNotification([
                    'title'=>'Tour Information Updated',
                    'message'=>"{$tour->title} tour information has been updated. Please review the latest schedule and details.",
                    'type'=>'info',
                    'audience_type'=>'all_active_members',
                    'action_url'=>$this->memberTourUrl($tour)
                ]);
            }

            $this->forgetCache();

            return $this->fresh($tour);
        });
    }

    /**
     * Finalize a tour approval. Called by ApprovalService::executeApprovedAction()
     * once the module=Tour, action=approve workflow has been fully signed off.
     * $decisionData is accepted for consistency with the other modules but this
     * module doesn't currently need any approver-supplied fields.
     */
    public function finalizeApproval(Tour $tour,array $decisionData,int $userId): Tour
    {
        return DB::transaction(function()use($tour,$userId){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($tour->status!=='draft'){
                throw ValidationException::withMessages([
                    'tour'=>['Only draft tours can be approved.']
                ]);
            }

            $status=$tour->start_date->isFuture()
                ?'upcoming'
                :'approved';

            $tour->update([
                'status'=>$status,
                'approved_by'=>$userId,
                'approved_at'=>now()
            ]);

            $this->afterCommitNotification([
                'title'=>'New Tour Announced',
                'message'=>"{$tour->title} tour to {$tour->destination} has been announced. Tour starts on {$tour->start_date->format('d M Y')}.",
                'type'=>'info',
                'audience_type'=>'all_active_members',
                'action_url'=>$this->memberTourUrl($tour),
                'sent_by'=>$userId
            ]);

            $this->forgetCache();

            return $this->fresh($tour);
        });
    }

    /**
     * Finalize a tour rejection. Called by ApprovalService::executeRejectedAction().
     * The Tour table has no rejection_reason column, so the reason is appended
     * to notes (matching how LoanService::cancel() already records its actor note).
     */
    public function finalizeRejection(
        Tour $tour,
        string $reason,
        int $userId
    ): Tour{
        return DB::transaction(function()use($tour,$reason,$userId){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($tour->status!=='draft'){
                throw ValidationException::withMessages([
                    'tour'=>['Only draft tours can be rejected.']
                ]);
            }

            $tour->update([
                'status'=>'cancelled',
                'notes'=>trim(
                    ($tour->notes?($tour->notes."\n"):'').
                    'Rejected by user #'.$userId.' on '.now().
                    ($reason?(': '.$reason):'')
                )
            ]);

            $this->forgetCache();

            return $this->fresh($tour);
        });
    }

    public function changeStatus(Tour $tour,string $status): Tour
    {
        return DB::transaction(function()use($tour,$status){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            $transitions=[
                'draft'=>['cancelled'],
                'approved'=>['upcoming','ongoing','cancelled'],
                'upcoming'=>['ongoing','cancelled'],
                'ongoing'=>['completed','cancelled'],
                'completed'=>[],
                'cancelled'=>[]
            ];

            if(!in_array($status,$transitions[$tour->status]??[],true)){
                throw ValidationException::withMessages([
                    'status'=>["Invalid status transition: {$tour->status} → {$status}."]
                ]);
            }

            if(
                $status==='cancelled'&&
                $tour->expenses()->where('status','posted')->exists()
            ){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Cancel/reverse posted tour expenses before cancelling the tour.'
                    ]
                ]);
            }

            $tour->update([
                'status'=>$status
            ]);

            $notification=$this->statusNotification($tour,$status);

            if($notification){
                $this->afterCommitNotification([
                    ...$notification,
                    'audience_type'=>'all_active_members',
                    'action_url'=>$this->memberTourUrl($tour)
                ]);
            }

            $this->forgetCache();

            return $this->fresh($tour);
        });
    }

    public function delete(Tour $tour): void
    {
        DB::transaction(function()use($tour){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($tour->expenses()->exists()){
                throw ValidationException::withMessages([
                    'tour'=>['Tour with expense history cannot be deleted.']
                ]);
            }

            if(!in_array($tour->status,['draft','cancelled'],true)){
                throw ValidationException::withMessages([
                    'tour'=>['Only draft or cancelled tours can be deleted.']
                ]);
            }

            $tour->delete();

            $this->forgetCache();
        });
    }

    public function addParticipant(
        Tour $tour,
        Member $member,
        array $data=[]
    ): TourParticipant{
        return DB::transaction(function()use($tour,$member,$data){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($tour->status,['completed','cancelled'],true)){
                throw ValidationException::withMessages([
                    'tour'=>['Participants cannot be added to this tour.']
                ]);
            }

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member_id'=>['Only active members can participate.']
                ]);
            }

            $existing=TourParticipant::query()
                ->where('tour_id',$tour->id)
                ->where('member_id',$member->id)
                ->first();

            $participant=TourParticipant::updateOrCreate([
                'tour_id'=>$tour->id,
                'member_id'=>$member->id
            ],[
                'status'=>$data['status']??'registered',
                'notes'=>$data['notes']??null
            ]);

            if(!$existing&&$member->user_id){
                $this->afterCommitNotification([
                    'title'=>'Added to Tour',
                    'message'=>"You have been added to {$tour->title} tour to {$tour->destination}.",
                    'type'=>'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$member->user_id],
                    'action_url'=>$this->memberTourUrl($tour)
                ]);
            }

            $this->forgetCache();

            return $participant->fresh('member.user');
        });
    }

    public function updateParticipant(
        TourParticipant $participant,
        array $data
    ): TourParticipant{
        return DB::transaction(function()use($participant,$data){
            $participant=TourParticipant::query()
                ->with([
                    'tour:id,title,destination',
                    'member:id,user_id,member_code'
                ])
                ->whereKey($participant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus=$participant->status;

            $participant->update([
                'status'=>$data['status'],
                'notes'=>$data['notes']??$participant->notes
            ]);

            if(
                $oldStatus!==$participant->status&&
                $participant->member?->user_id
            ){
                $message=match($participant->status){
                    'confirmed'=>"Your participation in {$participant->tour->title} has been confirmed.",
                    'attended'=>"Your attendance for {$participant->tour->title} has been recorded.",
                    'absent'=>"Your attendance status for {$participant->tour->title} has been marked absent.",
                    'cancelled'=>"Your participation in {$participant->tour->title} has been cancelled.",
                    default=>"Your participation status for {$participant->tour->title} has been updated."
                };

                $this->afterCommitNotification([
                    'title'=>'Tour Participation Updated',
                    'message'=>$message,
                    'type'=>$participant->status==='cancelled'?'warning':'info',
                    'audience_type'=>'users',
                    'user_ids'=>[$participant->member->user_id],
                    'action_url'=>$this->memberTourUrl($participant->tour)
                ]);
            }

            $this->forgetCache();

            return $participant->fresh('member.user');
        });
    }

    public function removeParticipant(TourParticipant $participant): void
    {
        DB::transaction(function()use($participant){
            $participant=TourParticipant::query()
                ->with([
                    'tour:id,title',
                    'member:id,user_id'
                ])
                ->whereKey($participant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $userId=$participant->member?->user_id;
            $tour=$participant->tour;

            $participant->delete();

            if($userId&&$tour){
                $this->afterCommitNotification([
                    'title'=>'Removed from Tour',
                    'message'=>"You have been removed from {$tour->title}.",
                    'type'=>'warning',
                    'audience_type'=>'users',
                    'user_ids'=>[$userId],
                    'action_url'=>$this->memberTourUrl($tour)
                ]);
            }

            $this->forgetCache();
        });
    }

    public function addExpense(
        Tour $tour,
        array $data,
        ?int $userId=null
    ): TourExpense{
        return DB::transaction(function()use($tour,$data,$userId){
            $tour=Tour::query()
                ->whereKey($tour->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array($tour->status,['draft','cancelled','completed'],true)){
                throw ValidationException::withMessages([
                    'tour'=>['Expenses cannot be posted to this tour.']
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
                    'amount'=>['Expense amount must be greater than zero.']
                ]);
            }

            $expense=TourExpense::create([
                'tour_id'=>$tour->id,
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
                'created_by'=>$userId??auth()->id()
            ]);

            $transaction=$this->accounting->post([
                'transaction_date'=>$expense->expense_date->toDateString(),
                'type'=>'tour_expense',
                'source_module'=>'tour',
                'source_id'=>$tour->id,
                'reference_type'=>TourExpense::class,
                'reference_id'=>$expense->id,
                'description'=>"Tour expense {$expense->expense_no} - {$tour->title}",
                'user_id'=>$userId??auth()->id(),
                'entries'=>[
                    [
                        'account_id'=>$expenseAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>"Tour expense - {$expense->category}"
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>"Tour payment - {$expense->expense_no}"
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
        TourExpense $expense,
        ?int $userId=null
    ): TourExpense{
        return DB::transaction(function()use($expense,$userId){
            $expense=TourExpense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($expense->status==='cancelled'){
                throw ValidationException::withMessages([
                    'expense'=>['Expense is already cancelled.']
                ]);
            }

            if($expense->finance_transaction_id){
                $transaction=$expense
                    ->financeTransaction()
                    ->with('entries')
                    ->firstOrFail();

                $this->accounting->post([
                    'transaction_date'=>$this->reversalDate($expense->expense_date),
                    'type'=>'tour_expense_reversal',
                    'source_module'=>'tour',
                    'source_id'=>$expense->tour_id,
                    'reference_type'=>TourExpense::class,
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
        return Cache::remember('tour:statistics',300,function(){
            $tour=Tour::query()
                ->selectRaw("
                    COUNT(*) total,
                    SUM(CASE WHEN status='upcoming' THEN 1 ELSE 0 END) upcoming,
                    SUM(CASE WHEN status='ongoing' THEN 1 ELSE 0 END) ongoing,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                    COALESCE(SUM(CASE WHEN status!='cancelled' THEN budget_amount ELSE 0 END),0) total_budget
                ")
                ->first();

            $expense=round(
                (float)TourExpense::query()
                    ->where('status','posted')
                    ->sum('amount'),
                2
            );

            $budget=round(
                (float)($tour->total_budget??0),
                2
            );

            return[
                'total'=>(int)($tour->total??0),
                'upcoming'=>(int)($tour->upcoming??0),
                'ongoing'=>(int)($tour->ongoing??0),
                'completed'=>(int)($tour->completed??0),
                'total_budget'=>$budget,
                'actual_expense'=>$expense,
                'variance'=>round($budget-$expense,2)
            ];
        });
    }

    protected function statusNotification(Tour $tour,string $status): ?array
    {
        return match($status){
            'approved'=>[
                'title'=>'Tour Approved',
                'message'=>"{$tour->title} tour has been approved.",
                'type'=>'info'
            ],
            'upcoming'=>[
                'title'=>'Upcoming Tour',
                'message'=>"{$tour->title} tour to {$tour->destination} is upcoming. It starts on {$tour->start_date->format('d M Y')}.",
                'type'=>'info'
            ],
            'ongoing'=>[
                'title'=>'Tour Started',
                'message'=>"{$tour->title} tour is now ongoing.",
                'type'=>'info'
            ],
            'completed'=>[
                'title'=>'Tour Completed',
                'message'=>"{$tour->title} tour has been completed.",
                'type'=>'success'
            ],
            'cancelled'=>[
                'title'=>'Tour Cancelled',
                'message'=>"{$tour->title} tour has been cancelled.",
                'type'=>'warning'
            ],
            default=>null
        };
    }

    protected function memberTourUrl(Tour $tour): string
    {
        return route('member.tours',[
            'tour'=>$tour->id
        ]);
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

    protected function generateTourNo(): string
    {
        $year=now()->format('Y');
        $prefix="TOUR-{$year}-";

        return $this->numberSequence->next(
            "tour:{$year}",
            $prefix,
            4,
            function()use($prefix){
                $last=Tour::query()
                    ->where('tour_no','like',$prefix.'%')
                    ->orderByDesc('id')
                    ->value('tour_no');

                return $last
                    ?(int)substr($last,-4)
                    :0;
            }
        );
    }

    protected function generateExpenseNo(): string
    {
        $year=now()->format('Y');
        $prefix="TEXP-{$year}-";

        return $this->numberSequence->next(
            "tour-expense:{$year}",
            $prefix,
            6,
            function()use($prefix){
                $last=TourExpense::query()
                    ->where('expense_no','like',$prefix.'%')
                    ->orderByDesc('id')
                    ->value('expense_no');

                return $last
                    ?(int)substr($last,-6)
                    :0;
            }
        );
    }

    public function fresh(Tour $tour): Tour
    {
        return $tour->fresh([
            'creator:id,name,email',
            'approver:id,name,email',
            'participants.member.user:id,name,email',
            'expenses'=>fn($query)=>
                $query
                    ->with([
                        'expenseAccount:id,code,name',
                        'paymentAccount:id,code,name',
                        'financeTransaction.entries.account'
                    ])
                    ->latest('expense_date')
        ]);
    }

    protected function forgetCache(): void
    {
        Cache::forget('tour:statistics');
    }
}