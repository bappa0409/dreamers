<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberShare;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberShareService
{
    public function __construct(
        protected AccountingService $accounting
    ){}

    public function issue(
        Member $member,
        array $data,
        ?int $userId=null
    ): MemberShare{
        $this->ensureShareEnabled();

        return DB::transaction(function()use(
            $member,
            $data,
            $userId
        ){
            $member=Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member'=>[
                        'Only active members can purchase shares.'
                    ],
                ]);
            }

            $amount=round(
                (float)$data['purchase_amount'],
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'purchase_amount'=>[
                        'Share purchase amount must be greater than zero.'
                    ],
                ]);
            }

            $minimum=(float)setting(
                'minimum_share_purchase_amount',
                0
            );

            if(
                $minimum>0&&
                $amount<$minimum
            ){
                throw ValidationException::withMessages([
                    'purchase_amount'=>[
                        'Minimum share purchase amount is '.
                        number_format($minimum,2).
                        '.'
                    ],
                ]);
            }

            return MemberShare::create([
                'member_id'=>$member->id,
                'share_no'=>$this->generateShareNumber(),
                'purchase_amount'=>$amount,
                'acquired_date'=>null,
                'payment_method'=>$data['payment_method'],
                'transaction_reference'=>
                    $data['transaction_reference']??null,
                'status'=>'pending',
                'created_by'=>$userId??auth()->id(),
                'notes'=>$data['notes']??null,
            ])->load([
                'member.user',
                'creator',
            ]);
        });
    }

    public function verify(
        MemberShare $share,
        int $verifiedBy,
        ?string $note=null
    ): MemberShare{
        $this->ensureShareEnabled();

        return DB::transaction(function()use(
            $share,
            $verifiedBy,
            $note
        ){
            $share=MemberShare::query()
                ->whereKey($share->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($share->status!=='pending'){
                throw ValidationException::withMessages([
                    'share'=>[
                        'Only pending share purchases can be verified.'
                    ],
                ]);
            }

            if($share->finance_transaction_id){
                throw ValidationException::withMessages([
                    'share'=>[
                        'Accounting transaction already exists for this share.'
                    ],
                ]);
            }

            $member=Member::query()
                ->with('user')
                ->whereKey($share->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member'=>[
                        'The member is not currently active.'
                    ],
                ]);
            }

            $amount=round(
                (float)$share->purchase_amount,
                2
            );

            $receiveAccount=
                $this->resolveReceiveAccount(
                    $share->payment_method
                );

            $capitalAccount=
                $this->accounting->account(
                    'capital'
                );

            $memberName=
                $member->user?->name
                ??$member->member_code
                ??'Member';

            $transaction=$this->accounting->post([
                'transaction_date'=>now()->toDateString(),
                'type'=>'member_share_purchase',
                'source_module'=>'member_share',
                'source_id'=>$share->id,
                'reference_type'=>MemberShare::class,
                'reference_id'=>$share->id,
                'description'=>
                    "Share purchase {$share->share_no} - ".
                    $memberName,
                'user_id'=>$verifiedBy,
                'entries'=>[
                    [
                        'account_id'=>$receiveAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>
                            "Share purchase received - ".
                            $share->share_no,
                    ],
                    [
                        'account_id'=>$capitalAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>
                            "Association fund - ".
                            $share->share_no,
                    ],
                ],
            ]);

            $share->update([
                'status'=>'active',
                'acquired_date'=>now()->toDateString(),
                'verified_by'=>$verifiedBy,
                'verified_at'=>now(),
                'verification_note'=>$note,
                'finance_transaction_id'=>$transaction->id,
            ]);

            return $share->fresh([
                'member.user',
                'creator',
                'verifier',
                'financeTransaction',
            ]);
        });
    }

    public function reject(
        MemberShare $share,
        int $rejectedBy,
        ?string $note=null
    ): MemberShare{
        return DB::transaction(function()use(
            $share,
            $rejectedBy,
            $note
        ){
            $share=MemberShare::query()
                ->whereKey($share->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($share->status!=='pending'){
                throw ValidationException::withMessages([
                    'share'=>[
                        'Only pending share purchases can be rejected.'
                    ],
                ]);
            }

            $share->update([
                'status'=>'rejected',
                'verified_by'=>$rejectedBy,
                'verified_at'=>now(),
                'verification_note'=>$note,
            ]);

            return $share->fresh([
                'member.user',
                'creator',
                'verifier',
            ]);
        });
    }

    public function memberShares(Member $member)
    {
        return $member->shares()
            ->with([
                'creator:id,name,email',
                'verifier:id,name,email',
            ])
            ->latest('id')
            ->get();
    }

    public function summary(Member $member): array
    {
        $base=$member->shares();

        $totalShares=(clone $base)->count();

        $activeShares=(clone $base)
            ->where('status','active')
            ->count();

        $pendingShares=(clone $base)
            ->where('status','pending')
            ->count();

        $activeValue=(float)(clone $base)
            ->where('status','active')
            ->sum('purchase_amount');

        $pendingValue=(float)(clone $base)
            ->where('status','pending')
            ->sum('purchase_amount');

        return[
            'total_shares'=>$totalShares,
            'active_shares'=>$activeShares,
            'pending_shares'=>$pendingShares,
            'active_share_value'=>round(
                $activeValue,
                2
            ),
            'pending_share_value'=>round(
                $pendingValue,
                2
            ),
        ];
    }

    protected function resolveReceiveAccount(
        ?string $paymentMethod
    ){
        return match($paymentMethod){
            'bank',
            'mobile_banking',
            'online'=>
                $this->accounting->account('bank'),

            default=>
                $this->accounting->account('cash'),
        };
    }

    protected function ensureShareEnabled(): void
    {
        if(!filter_var(
            setting('share_enabled',false),
            FILTER_VALIDATE_BOOLEAN
        )){
            throw ValidationException::withMessages([
                'share'=>[
                    'Share purchasing is currently disabled.'
                ],
            ]);
        }
    }

    protected function generateShareNumber(): string
    {
        $prefix='SH-';

        $last=MemberShare::query()
            ->where(
                'share_no',
                'like',
                $prefix.'%'
            )
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('share_no');

        $number=$last
            ?((int)substr($last,-6))+1
            :1;

        do{
            $shareNo=
                $prefix.
                str_pad(
                    (string)$number,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $exists=MemberShare::query()
                ->where(
                    'share_no',
                    $shareNo
                )
                ->exists();

            $number++;
        }while($exists);

        return $shareNo;
    }
}