<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasFactory, LogsActivity;

    protected string $activityLogModule='Subscription Payment';
    protected string $activityLogLabelColumn='payment_no';

    protected $fillable=[
        'payment_no',
        'member_id',
        'subscription_due_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'status',
        'paid_at',
        'verified_by',
        'verified_at',
        'verification_note',
        'finance_transaction_id',
    ];

    protected function casts(): array
    {
        return[
            'amount'=>'decimal:2',
            'paid_at'=>'datetime',
            'verified_at'=>'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(
            Member::class
        );
    }

    public function due(): BelongsTo
    {
        return $this->belongsTo(
            SubscriptionDue::class,
            'subscription_due_id'
        );
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'finance_transaction_id'
        );
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status==='pending';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->status==='verified';
    }

    /**
     * Data payload for the printable payment receipt.
     * Expects member.user, due and verifier to be loaded/eager-loaded.
     */
    public function toReceiptData(): array
    {
        return[
            'title'=>'Subscription Payment Receipt',

            'receipt_no'=>$this->payment_no,

            'status'=>ucfirst($this->status),

            'issued_at'=>app_datetime($this->paid_at),

            'bill_to'=>[
                'name'=>$this->member?->user?->name,

                'lines'=>[
                    'Member Code: '
                        .($this->member?->member_code??'-'),

                    $this->member?->user?->mobile,

                    $this->member?->user?->email,
                ],
            ],

            'meta'=>[
                [
                    'label'=>'Subscription Period',
                    'value'=>$this->due
                        ?date(
                            'F Y',
                            mktime(
                                0,
                                0,
                                0,
                                (int)$this->due->month,
                                1,
                                (int)$this->due->year
                            )
                        )
                        :'-',
                ],
                [
                    'label'=>'Payment Method',
                    'value'=>ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $this->payment_method
                        )
                    ),
                ],
                [
                    'label'=>'Transaction Reference',
                    'value'=>$this->transaction_reference??'-',
                ],
                [
                    'label'=>'Verified By',
                    'value'=>$this->verifier?->name??'-',
                ],
                [
                    'label'=>'Verified At',
                    'value'=>app_datetime($this->verified_at)??'-',
                ],
            ],

            'items'=>[
                [
                    'label'=>'Subscription Payment',
                    'value'=>money($this->amount),
                ],
            ],

            'total_label'=>'Total Paid',

            'total_amount'=>money($this->amount),

            'notes'=>$this->verification_note,
        ];
    }
}