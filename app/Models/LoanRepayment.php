<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory, LogsActivity;

    protected string $activityLogModule='Loan';

    protected $fillable=[
        'loan_id',
        'receive_account_id',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'total_amount',
        'repayment_date',
        'notes',
        'finance_transaction_id',
        'received_by'
    ];

    protected function casts(): array
    {
        return[
            'principal_amount'=>'decimal:2',
            'interest_amount'=>'decimal:2',
            'penalty_amount'=>'decimal:2',
            'total_amount'=>'decimal:2',
            'repayment_date'=>'date'
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function receiveAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class,'receive_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class,'finance_transaction_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class,'received_by');
    }

    /**
     * Data payload for the printable repayment receipt.
     * Expects loan.member.user, receiveAccount and receiver to be
     * loaded/eager-loaded.
     */
    public function toReceiptData(): array
    {
        return[
            'title'=>'Loan Repayment Receipt',

            'receipt_no'=>($this->loan?->loan_no??'LOAN')
                .'-R'
                .str_pad((string)$this->id,4,'0',STR_PAD_LEFT),

            'status'=>'Received',

            'issued_at'=>app_date($this->repayment_date),

            'bill_to'=>[
                'name'=>$this->loan?->member?->user?->name,

                'lines'=>[
                    'Member Code: '
                        .($this->loan?->member?->member_code??'-'),

                    $this->loan?->member?->user?->mobile,

                    $this->loan?->member?->user?->email,
                ],
            ],

            'meta'=>[
                [
                    'label'=>'Loan No',
                    'value'=>$this->loan?->loan_no??'-',
                ],
                [
                    'label'=>'Received Into',
                    'value'=>$this->receiveAccount?->name??'-',
                ],
                [
                    'label'=>'Received By',
                    'value'=>$this->receiver?->name??'-',
                ],
            ],

            'items'=>[
                [
                    'label'=>'Principal Amount',
                    'value'=>money($this->principal_amount),
                ],
                [
                    'label'=>'Interest Amount',
                    'value'=>money($this->interest_amount),
                ],
                [
                    'label'=>'Penalty Amount',
                    'value'=>money($this->penalty_amount),
                ],
            ],

            'total_label'=>'Total Repaid',

            'total_amount'=>money($this->total_amount),

            'notes'=>$this->notes,
        ];
    }
}