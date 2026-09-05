<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberShare extends Model
{
    use LogsActivity;

    protected string $activityLogModule='Member Share';
    protected string $activityLogLabelColumn='share_no';

    protected $fillable=[
        'member_id',
        'share_no',
        'purchase_amount',
        'acquired_date',
        'payment_method',
        'transaction_reference',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'verification_note',
        'finance_transaction_id',
        'notes',
    ];

    protected function casts(): array
    {
        return[
            'purchase_amount'=>'decimal:2',
            'acquired_date'=>'date',
            'verified_at'=>'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
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

    public function scopeActive($query)
    {
        return $query->where(
            'status',
            'active'
        );
    }

    /**
     * Data payload for the printable share purchase receipt.
     * Expects member.user and verifier to be loaded/eager-loaded.
     */
    public function toReceiptData(): array
    {
        return [
            'title' => 'Share Purchase Receipt',
            'receipt_no' => $this->share_no,
            'status' => ucfirst($this->status),
            'issued_at' => app_datetime($this->verified_at ?? $this->created_at),
            'bill_to' => [
                'name' => $this->member?->user?->name,
                'lines' => [
                    'Member Code: '.($this->member?->member_code ?? '-'),
                    $this->member?->user?->mobile,
                    $this->member?->user?->email,
                ],
            ],
            'meta' => [
                [
                    'label' => 'Share No',
                    'value' => $this->share_no,
                ],
                [
                    'label' => 'Purchase Date',
                    'value' => app_date($this->acquired_date ?? $this->created_at),
                ],
                [
                    'label' => 'Payment Method',
                    'value' => ucfirst(str_replace('_', ' ', $this->payment_method)),
                ],
                [
                    'label' => 'Transaction Reference',
                    'value' => $this->transaction_reference ?? '-',
                ],
                [
                    'label' => 'Verified By',
                    'value' => $this->verifier?->name ?? '-',
                ],
                [
                    'label' => 'Verified At',
                    'value' => $this->verified_at
                        ? app_datetime($this->verified_at)
                        : '-',
                ],
            ],
            'items' => [
                [
                    'label' => 'Share Purchase',
                    'value' => money($this->purchase_amount),
                ],
            ],
            'total_label' => 'Total Paid',
            'total_amount' => money($this->purchase_amount),
            'notes' => $this->verification_note ?? $this->notes,
        ];
    }

    public function scopePending($query)
    {
        return $query->where(
            'status',
            'pending'
        );
    }
}