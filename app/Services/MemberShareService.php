<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberShare;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberShareService
{
    public function __construct(
        protected AccountingService $accounting,
        protected NumberSequenceService $numberSequenceService
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

            $configuredShareValue=round(
                (float)setting(
                    'default_share_value',
                    0
                ),
                2
            );

            if($configuredShareValue<=0){
                throw ValidationException::withMessages([
                    'purchase_amount'=>[
                        'Share value is not configured.'
                    ],
                ]);
            }

            $amount=round(
                (float)($data['purchase_amount']??0),
                2
            );

            if($amount<=0){
                throw ValidationException::withMessages([
                    'purchase_amount'=>[
                        'Share purchase amount must be greater than zero.'
                    ],
                ]);
            }

            if(
                abs(
                    $amount-$configuredShareValue
                )>0.001
            ){
                throw ValidationException::withMessages([
                    'purchase_amount'=>[
                        'Each share price is '.
                        number_format(
                            $configuredShareValue,
                            2
                        ).
                        '.'
                    ],
                ]);
            }

            $paymentMethod=
                $data['payment_method']??null;

            if(
                !in_array(
                    $paymentMethod,
                    [
                        'cash',
                        'bank',
                        'mobile_banking',
                        'online',
                    ],
                    true
                )
            ){
                throw ValidationException::withMessages([
                    'payment_method'=>[
                        'Invalid payment method.'
                    ],
                ]);
            }

            return MemberShare::create([
                'member_id'=>$member->id,
                'share_no'=>$this->generateShareNumber(),
                'purchase_amount'=>$amount,
                'acquired_date'=>null,
                'payment_method'=>$paymentMethod,
                'transaction_reference'=>
                    $this->nullableString(
                        $data['transaction_reference']??null
                    ),
                'status'=>'pending',
                'created_by'=>$userId??auth()->id(),
                'notes'=>$this->nullableString(
                    $data['notes']??null
                ),
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

            if($amount<=0){
                throw ValidationException::withMessages([
                    'share'=>[
                        'Share purchase amount is invalid.'
                    ],
                ]);
            }

            $receiveAccount=
                $this->resolveReceiveAccount(
                    $share->payment_method
                );

            $capitalAccount=$this->accounting->account('member_equity');

            $memberName=
                $member->user?->name
                ??$member->member_code
                ??'Member';

            $transaction=$this->accounting->post([
                'idempotency_key'=>
                    "member-share:verify:{$share->id}",

                'transaction_date'=>
                    now()->toDateString(),

                'type'=>
                    'member_share_purchase',

                'source_module'=>
                    'member_share',

                'source_id'=>
                    $share->id,

                'reference_type'=>
                    MemberShare::class,

                'reference_id'=>
                    $share->id,

                'description'=>
                    "Share purchase {$share->share_no} - {$memberName}",

                'user_id'=>
                    $verifiedBy,

                'entries'=>[
                    [
                        'account_id'=>$receiveAccount->id,
                        'debit'=>$amount,
                        'credit'=>0,
                        'description'=>
                            "Share purchase received - {$share->share_no}",
                    ],
                    [
                        'account_id'=>$capitalAccount->id,
                        'debit'=>0,
                        'credit'=>$amount,
                        'description'=>
                            "Association capital - {$share->share_no}",
                    ],
                ],
            ]);

            $share->update([
                'status'=>'active',
                'acquired_date'=>now()->toDateString(),
                'verified_by'=>$verifiedBy,
                'verified_at'=>now(),
                'verification_note'=>
                    $this->nullableString($note),
                'finance_transaction_id'=>
                    $transaction->id,
            ]);

            return $share->fresh([
                'member.user',
                'creator',
                'verifier',
                'financeTransaction.entries.account',
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

            if($share->finance_transaction_id){
                throw ValidationException::withMessages([
                    'share'=>[
                        'Posted share history cannot be rejected.'
                    ],
                ]);
            }

            $note=$this->nullableString($note);

            if(!$note){
                throw ValidationException::withMessages([
                    'note'=>[
                        'Rejection note is required.'
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
                'financeTransaction:id,transaction_no,status',
            ])
            ->latest('id')
            ->get();
    }

    public function memberSharesPaginated(
        Member $member,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        $perPage = min(max($perPage, 5), 50);

        $search=$this->nullableString($search);

        return $member->shares()
            ->with([
                'creator:id,name,email',
                'verifier:id,name,email',
                'financeTransaction:id,transaction_no,status',
            ])
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $search,
                function($query)use($search){
                    $query->where(function($q)use($search){
                        $q->where('share_no','like',"%{$search}%")
                            ->orWhere('payment_method','like',"%{$search}%")
                            ->orWhere('transaction_reference','like',"%{$search}%")
                            ->orWhere('verification_note','like',"%{$search}%")
                            ->orWhere('notes','like',"%{$search}%");
                    });
                }
            )
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function summary(Member $member): array
    {
        $base=$member->shares();

        $summary=(clone $base)
            ->selectRaw("
                COUNT(*) total_shares,
                SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active_shares,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending_shares,
                SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) rejected_shares,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled_shares,
                SUM(CASE WHEN status='transferred' THEN 1 ELSE 0 END) transferred_shares,
                SUM(CASE WHEN status='retired' THEN 1 ELSE 0 END) retired_shares,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status='active'
                            THEN purchase_amount
                            ELSE 0
                        END
                    ),
                    0
                ) active_share_value,
                COALESCE(
                    SUM(
                        CASE
                            WHEN status='pending'
                            THEN purchase_amount
                            ELSE 0
                        END
                    ),
                    0
                ) pending_share_value
            ")
            ->first();

        return[
            'total_shares'=>
                (int)($summary->total_shares??0),

            'active_shares'=>
                (int)($summary->active_shares??0),

            'pending_shares'=>
                (int)($summary->pending_shares??0),

            'rejected_shares'=>
                (int)($summary->rejected_shares??0),

            'cancelled_shares'=>
                (int)($summary->cancelled_shares??0),

            'transferred_shares'=>
                (int)($summary->transferred_shares??0),

            'retired_shares'=>
                (int)($summary->retired_shares??0),

            'active_share_value'=>round(
                (float)(
                    $summary->active_share_value??0
                ),
                2
            ),

            'pending_share_value'=>round(
                (float)(
                    $summary->pending_share_value??0
                ),
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
                $this->accounting->account(
                    'bank'
                ),

            'cash'=>
                $this->accounting->account(
                    'cash'
                ),

            default=>
                throw ValidationException::withMessages([
                    'payment_method'=>[
                        'Invalid payment method.'
                    ],
                ]),
        };
    }

    protected function ensureShareEnabled(): void
    {
        if(
            !filter_var(
                setting(
                    'share_enabled',
                    false
                ),
                FILTER_VALIDATE_BOOLEAN
            )
        ){
            throw ValidationException::withMessages([
                'share'=>[
                    'Share purchasing is currently disabled.'
                ],
            ]);
        }
    }

    protected function nullableString(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim(
            (string)$value
        );

        return $value===''
            ?null
            :$value;
    }

    protected function generateShareNumber(): string
    {
        return $this->numberSequenceService->next(
            key:'member-share',
            prefix:'SH-',
            digits:6,
            initialValue:function(){
                $last=MemberShare::query()
                    ->where(
                        'share_no',
                        'like',
                        'SH-%'
                    )
                    ->orderByDesc('id')
                    ->value('share_no');

                return $last
                    ?(int)substr(
                        $last,
                        -6
                    )
                    :0;
            }
        );
    }
}