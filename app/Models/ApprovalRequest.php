<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    protected $fillable=[
        'approvable_type',
        'approvable_id',
        'module',
        'action',
        'status',
        'current_step',
        'total_steps',
        'completed_at',
        'requested_by',
        'request_note',
        'decision_data',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $appends=[
        'subject_label',
        'can_current_user_approve',
    ];

    protected function casts(): array
    {
        return[
            'current_step'=>'integer',
            'total_steps'=>'integer',
            'decision_data'=>'array',
            'completed_at'=>'datetime',
            'approved_at'=>'datetime',
            'rejected_at'=>'datetime',
            'cancelled_at'=>'datetime',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class,'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class,'rejected_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class,'cancelled_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(
            ApprovalStep::class,
            'approval_request_id'
        )->orderBy('step_no');
    }

    public function getSubjectLabelAttribute(): string
    {
        $subject=$this->approvable;

        if(!$subject){
            return 'Unavailable';
        }

        return match(class_basename($this->approvable_type)){
            'Member'=>($subject->user?->name??'Member')
                .' ('.($subject->member_code??'#'.$subject->id).')',

            'Project'=>($subject->name??'Project')
                .' ('.($subject->project_code??'#'.$subject->id).')',

            'Investment'=>($subject->title??'Investment')
                .' ('.($subject->investment_no??'#'.$subject->id).')',

            'Land'=>($subject->title??'Land')
                .' ('.($subject->land_code??'#'.$subject->id).')',

            'Notice'=>$subject->title??'Notice #'.$subject->id,

            'Loan'=>($subject->member?->user?->name??'Member')
                .' ('.($subject->loan_no??'#'.$subject->id).')',

            'WelfareRequest'=>($subject->member?->user?->name??'Member')
                .' ('.($subject->request_no??'#'.$subject->id).')',

            'MemberExit'=>($subject->member?->user?->name??'Member')
                .' ('.($subject->exit_no??'#'.$subject->id).')',

            'MemberShare'=>($subject->member?->user?->name??'Member')
                .' ('.($subject->share_no??'#'.$subject->id).')',

            'Tour'=>($subject->title??'Tour')
                .' ('.($subject->tour_no??'#'.$subject->id).')',

            'Income'=>'Income '
                .($subject->income_no??'#'.$subject->id)
                .' ('.number_format((float)($subject->amount??0),2).')',

            'Expense'=>'Expense '
                .($subject->expense_no??'#'.$subject->id)
                .' ('.number_format((float)($subject->amount??0),2).')',

            'SubscriptionPayment'=>'Payment '
                .($subject->payment_no??'#'.$subject->id)
                .' ('.number_format((float)($subject->amount??0),2).')',

            'MemberCharge'=>'Charge '
                .($subject->charge_no??'#'.$subject->id)
                .' ('.number_format((float)($subject->amount??0),2).')',

            'Asset'=>'Asset '
                .($subject->asset_code??'#'.$subject->id)
                .' ('.number_format((float)($subject->purchase_cost??0),2).')',

            'Transaction'=>'Journal '
                .($subject->transaction_no??'#'.$subject->id),

            default=>class_basename($this->approvable_type).' #'.$subject->id,
        };
    }

    public function getCanCurrentUserApproveAttribute(): bool
    {
        $userId=auth()->id();

        if(!$userId||$this->status!=='pending'){
            return false;
        }

        return $this->steps
            ->firstWhere('step_no',$this->current_step)
            ?->approver_user_id===$userId;
    }
}