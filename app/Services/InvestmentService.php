<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Investment;
use App\Models\InvestmentReturn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestmentService
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected AccountingService $accountingService,
        protected NumberSequenceService $numberSequenceService
    ){}

    public function create(array $data,int $userId): Investment
    {
        return DB::transaction(function()use($data,$userId){
            $paymentAccount=$this->cashBankAccount(
                (int)$data['payment_account_id'],
                'payment_account_id'
            );

            $investment=Investment::create([
                'investment_no'=>$this->generateInvestmentNo(),
                'payment_account_id'=>$paymentAccount->id,
                'title'=>trim($data['title']),
                'description'=>isset($data['description'])
                    ?trim($data['description'])
                    :null,
                'amount'=>round((float)$data['amount'],2),
                'expected_return'=>round(
                    (float)($data['expected_return']??0),
                    2
                ),
                'investment_date'=>$data['investment_date'],
                'maturity_date'=>$data['maturity_date']??null,
                'status'=>$data['status']??'active',
            ]);

            if($investment->status==='active'){
                $this->postInvestment(
                    $investment,
                    $paymentAccount,
                    $userId
                );
            }

            $this->forgetCachesAfterCommit();

            return $this->freshInvestment($investment);
        });
    }

    public function update(
        Investment $investment,
        array $data
    ): Investment{
        return DB::transaction(function()use(
            $investment,
            $data
        ){
            $investment=Investment::query()
                ->whereKey($investment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->status==='cancelled'){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Cancelled investment cannot be updated.'
                    ],
                ]);
            }

            if($investment->finance_transaction_id){
                foreach([
                    'payment_account_id',
                    'amount',
                    'investment_date',
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $investment->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted investment financial information cannot be changed directly.'
                            ],
                        ]);
                    }
                }
            }

            if(
                array_key_exists('status',$data)&&
                $data['status']==='cancelled'
            ){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Use the cancel action to cancel an investment.'
                    ],
                ]);
            }

            if(
                array_key_exists('status',$data)&&
                $data['status']==='completed'
            ){
                throw ValidationException::withMessages([
                    'status'=>[
                        'Completed status is managed automatically from principal returns.'
                    ],
                ]);
            }

            $investmentDate=
                $data['investment_date']
                ??$investment->investment_date->toDateString();

            if(
                !empty($data['maturity_date'])&&
                $data['maturity_date']<$investmentDate
            ){
                throw ValidationException::withMessages([
                    'maturity_date'=>[
                        'Maturity date must be on or after investment date.'
                    ],
                ]);
            }

            $oldStatus=$investment->status;

            $clean=[];

            foreach([
                'payment_account_id',
                'title',
                'description',
                'amount',
                'expected_return',
                'investment_date',
                'maturity_date',
                'status',
            ] as $field){
                if(array_key_exists($field,$data)){
                    $clean[$field]=$data[$field];
                }
            }

            if(array_key_exists('title',$clean)){
                $clean['title']=trim(
                    (string)$clean['title']
                );
            }

            if(array_key_exists('description',$clean)){
                $clean['description']=$clean['description']!==null
                    ?trim((string)$clean['description'])
                    :null;
            }

            if(array_key_exists('amount',$clean)){
                $clean['amount']=round(
                    (float)$clean['amount'],
                    2
                );
            }

            if(array_key_exists('expected_return',$clean)){
                $clean['expected_return']=round(
                    (float)($clean['expected_return']??0),
                    2
                );
            }

            if(array_key_exists('payment_account_id',$clean)){
                $clean['payment_account_id']=$this
                    ->cashBankAccount(
                        (int)$clean['payment_account_id'],
                        'payment_account_id'
                    )
                    ->id;
            }

            $investment->update($clean);

            if(
                $oldStatus==='pending'&&
                $investment->status==='active'&&
                !$investment->finance_transaction_id
            ){
                $paymentAccount=$this->cashBankAccount(
                    (int)$investment->payment_account_id,
                    'payment_account_id'
                );

                $this->postInvestment(
                    $investment,
                    $paymentAccount,
                    auth()->id()
                );
            }

            if(
                $investment->finance_transaction_id&&
                $investment->status==='pending'
            ){
                throw ValidationException::withMessages([
                    'status'=>[
                        'A posted investment cannot be changed back to pending.'
                    ],
                ]);
            }

            $this->syncInvestmentStatus($investment);
            $this->forgetCachesAfterCommit();

            return $this->freshInvestment($investment);
        });
    }

    public function cancel(
        Investment $investment,
        int $userId
    ): Investment{
        return DB::transaction(function()use(
            $investment,
            $userId
        ){
            $investment=Investment::query()
                ->whereKey($investment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->status==='cancelled'){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Investment is already cancelled.'
                    ],
                ]);
            }

            if(
                $investment->returns()
                    ->where('status','paid')
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Investment with paid return history cannot be cancelled directly.'
                    ],
                ]);
            }

            if($investment->finance_transaction_id){
                $original=$investment
                    ->financeTransaction()
                    ->with('entries')
                    ->firstOrFail();

                $this->accountingService->post([
                    'idempotency_key'=>
                        "investment:cancel:{$investment->id}",
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'investment_reversal',
                    'source_module'=>'investment',
                    'source_id'=>$investment->id,
                    'reference_type'=>Investment::class,
                    'reference_id'=>$investment->id,
                    'description'=>
                        "Reversal of {$original->transaction_no} - {$investment->investment_no}",
                    'user_id'=>$userId,
                    'entries'=>$original->entries
                        ->map(fn($entry)=>[
                            'account_id'=>$entry->account_id,
                            'debit'=>(float)$entry->credit,
                            'credit'=>(float)$entry->debit,
                            'description'=>
                                'Investment cancellation reversal',
                        ])
                        ->all(),
                ]);
            }

            $investment->update([
                'status'=>'cancelled',
            ]);

            $this->forgetCachesAfterCommit();

            return $this->freshInvestment($investment);
        });
    }

    public function delete(Investment $investment): void
    {
        DB::transaction(function()use($investment){
            $investment=Investment::query()
                ->whereKey($investment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->finance_transaction_id){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Posted investment cannot be deleted. Cancel it instead.'
                    ],
                ]);
            }

            if($investment->returns()->exists()){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Investment with return history cannot be deleted.'
                    ],
                ]);
            }

            $investment->delete();

            $this->forgetCachesAfterCommit();
        });
    }

    public function addReturn(
    Investment $investment,
    array $data,
    int $userId
): InvestmentReturn{
    return DB::transaction(function()use(
        $investment,
        $data,
        $userId
    ){
        $investment=Investment::query()
            ->whereKey($investment->id)
            ->lockForUpdate()
            ->firstOrFail();

        if(
            !in_array(
                $investment->status,
                ['active','completed'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'investment'=>[
                    'Return can only be added to an active or completed investment.'
                ],
            ]);
        }

        $returnType=$data['return_type']??'income';
        $status=$data['status']??'pending';

        if(
            !in_array(
                $returnType,
                ['income','principal'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'return_type'=>[
                    'Invalid investment return type.'
                ],
            ]);
        }

        if(
            !in_array(
                $status,
                ['pending','paid'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'status'=>[
                    'A new investment return can only be pending or paid.'
                ],
            ]);
        }

        $amount=round(
            (float)($data['amount']??0),
            2
        );

        if($amount<=0){
            throw ValidationException::withMessages([
                'amount'=>[
                    'Return amount must be greater than zero.'
                ],
            ]);
        }

        $returnDate=$data['return_date']??null;

        if(!$returnDate){
            throw ValidationException::withMessages([
                'return_date'=>[
                    'Return date is required.'
                ],
            ]);
        }

        if(
            $returnDate<
            $investment->investment_date->toDateString()
        ){
            throw ValidationException::withMessages([
                'return_date'=>[
                    'Return date cannot be earlier than the investment date.'
                ],
            ]);
        }

        if($returnType==='principal'){
            $this->validatePrincipalAmount(
                $investment,
                $amount
            );
        }

        $receiveAccount=null;

        if(
            !empty(
                $data['receive_account_id']
            )
        ){
            $receiveAccount=$this->cashBankAccount(
                (int)$data['receive_account_id'],
                'receive_account_id'
            );
        }

        if(
            $status==='paid'&&
            !$receiveAccount
        ){
            throw ValidationException::withMessages([
                'receive_account_id'=>[
                    'Receive account is required for a paid return.'
                ],
            ]);
        }

        $return=$investment
            ->returns()
            ->create([
                'return_type'=>$returnType,
                'receive_account_id'=>
                    $receiveAccount?->id,
                'amount'=>$amount,
                'return_date'=>$returnDate,
                'description'=>
                    isset($data['description'])
                        ?trim(
                            (string)$data['description']
                        )
                        :null,
                'status'=>$status,
            ]);

        if($return->status==='paid'){
            $this->postReturn(
                $return,
                $userId
            );
        }

        $this->syncInvestmentStatus(
            $investment
        );

        $this->forgetCachesAfterCommit();

        return $this->freshReturn(
            $return
        );
    });
}

    public function updateReturn(
    InvestmentReturn $return,
    array $data,
    int $userId
): InvestmentReturn{
    return DB::transaction(function()use(
        $return,
        $data,
        $userId
    ){
        $return=InvestmentReturn::query()
            ->whereKey($return->id)
            ->lockForUpdate()
            ->firstOrFail();

        if($return->finance_transaction_id){
            throw ValidationException::withMessages([
                'investment_return'=>[
                    'Posted return cannot be edited.'
                ],
            ]);
        }

        if(
            in_array(
                $return->status,
                ['paid','cancelled'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'investment_return'=>[
                    'Paid or cancelled return cannot be edited.'
                ],
            ]);
        }

        $investment=Investment::query()
            ->whereKey($return->investment_id)
            ->lockForUpdate()
            ->firstOrFail();

        if(
            !in_array(
                $investment->status,
                ['active','completed'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'investment'=>[
                    'Return cannot be updated for this investment.'
                ],
            ]);
        }

        $type=$data['return_type']
            ??$return->return_type;

        if(
            !in_array(
                $type,
                ['income','principal'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'return_type'=>[
                    'Invalid investment return type.'
                ],
            ]);
        }

        $amount=round(
            (float)(
                $data['amount']
                ??$return->amount
            ),
            2
        );

        if($amount<=0){
            throw ValidationException::withMessages([
                'amount'=>[
                    'Return amount must be greater than zero.'
                ],
            ]);
        }

        $returnDate=
            $data['return_date']
            ??$return->return_date->toDateString();

        if(
            $returnDate<
            $investment->investment_date->toDateString()
        ){
            throw ValidationException::withMessages([
                'return_date'=>[
                    'Return date cannot be earlier than the investment date.'
                ],
            ]);
        }

        if($type==='principal'){
            $this->validatePrincipalAmount(
                $investment,
                $amount,
                $return->id
            );
        }

        $status=$data['status']
            ??$return->status;

        if(
            !in_array(
                $status,
                ['pending','paid','cancelled'],
                true
            )
        ){
            throw ValidationException::withMessages([
                'status'=>[
                    'Invalid investment return status.'
                ],
            ]);
        }

        $accountId=array_key_exists(
            'receive_account_id',
            $data
        )
            ?$data['receive_account_id']
            :$return->receive_account_id;

        $receiveAccountId=null;

        if($accountId){
            $receiveAccountId=$this
                ->cashBankAccount(
                    (int)$accountId,
                    'receive_account_id'
                )
                ->id;
        }

        if(
            $status==='paid'&&
            !$receiveAccountId
        ){
            throw ValidationException::withMessages([
                'receive_account_id'=>[
                    'Receive account is required for a paid return.'
                ],
            ]);
        }

        $clean=[
            'return_type'=>$type,
            'amount'=>$amount,
            'return_date'=>$returnDate,
            'status'=>$status,
        ];

        if(
            array_key_exists(
                'receive_account_id',
                $data
            )||
            $status==='paid'
        ){
            $clean['receive_account_id']=
                $receiveAccountId;
        }

        if(
            array_key_exists(
                'description',
                $data
            )
        ){
            $clean['description']=
                $data['description']!==null
                    ?trim(
                        (string)$data['description']
                    )
                    :null;
        }

        $return->update(
            $clean
        );

        if($return->status==='paid'){
            $this->postReturn(
                $return,
                $userId
            );
        }

        $this->syncInvestmentStatus(
            $investment
        );

        $this->forgetCachesAfterCommit();

        return $this->freshReturn(
            $return
        );
    });
}

    public function deleteReturn(
        InvestmentReturn $return
    ): void{
        DB::transaction(function()use($return){
            $return=InvestmentReturn::query()
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(
                $return->finance_transaction_id||
                $return->status==='paid'
            ){
                throw ValidationException::withMessages([
                    'investment_return'=>[
                        'Paid/posted return cannot be deleted.'
                    ],
                ]);
            }

            $investment=Investment::query()
                ->whereKey($return->investment_id)
                ->lockForUpdate()
                ->first();

            $return->delete();

            if($investment){
                $this->syncInvestmentStatus(
                    $investment
                );
            }

            $this->forgetCachesAfterCommit();
        });
    }

    public function statistics(): array
    {
        return Cache::remember(
            'investments:statistics',
            now()->addMinutes(5),
            function(){
                $row=Investment::query()
                    ->selectRaw("
                        COUNT(*) total,
                        COALESCE(SUM(
                            CASE
                                WHEN status!='cancelled'
                                THEN amount
                                ELSE 0
                            END
                        ),0) total_amount,
                        COALESCE(SUM(
                            CASE
                                WHEN status!='cancelled'
                                THEN expected_return
                                ELSE 0
                            END
                        ),0) expected_return,
                        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
                        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active,
                        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled
                    ")
                    ->first();

                $returnTotals=InvestmentReturn::query()
                    ->where('status','paid')
                    ->selectRaw("
                        COALESCE(SUM(
                            CASE
                                WHEN return_type='income'
                                THEN amount
                                ELSE 0
                            END
                        ),0) income,
                        COALESCE(SUM(
                            CASE
                                WHEN return_type='principal'
                                THEN amount
                                ELSE 0
                            END
                        ),0) principal
                    ")
                    ->first();

                $income=round(
                    (float)($returnTotals->income??0),
                    2
                );

                $principal=round(
                    (float)($returnTotals->principal??0),
                    2
                );

                return[
                    'total'=>(int)($row->total??0),
                    'total_amount'=>round(
                        (float)($row->total_amount??0),
                        2
                    ),
                    'expected_return'=>round(
                        (float)($row->expected_return??0),
                        2
                    ),
                    'paid_return'=>$income,
                    'income_received'=>$income,
                    'principal_received'=>$principal,
                    'pending'=>(int)($row->pending??0),
                    'active'=>(int)($row->active??0),
                    'completed'=>(int)($row->completed??0),
                    'cancelled'=>(int)($row->cancelled??0),
                ];
            }
        );
    }

    protected function postInvestment(
        Investment $investment,
        Account $paymentAccount,
        int $userId
    ): void{
        if($investment->finance_transaction_id){
            return;
        }

        $investmentAccount=$this
            ->accountingService
            ->account('investment');

        $journal=$this->accountingService->post([
            'idempotency_key'=>"investment:post:{$investment->id}",
            'transaction_date'=>
                $investment->investment_date->toDateString(),
            'type'=>'investment',
            'source_module'=>'investment',
            'source_id'=>$investment->id,
            'reference_type'=>Investment::class,
            'reference_id'=>$investment->id,
            'description'=>$investment->description
                ?:"Investment purchase {$investment->investment_no}",
            'user_id'=>$userId,
            'entries'=>[
                [
                    'account_id'=>$investmentAccount->id,
                    'debit'=>(float)$investment->amount,
                    'credit'=>0,
                    'description'=>'Investment asset',
                ],
                [
                    'account_id'=>$paymentAccount->id,
                    'debit'=>0,
                    'credit'=>(float)$investment->amount,
                    'description'=>'Investment payment',
                ],
            ],
        ]);

        $investment->update([
            'finance_transaction_id'=>$journal->id,
        ]);
    }

    protected function postReturn(
        InvestmentReturn $return,
        int $userId
    ): void{
        if($return->finance_transaction_id){
            return;
        }

        if(!$return->receive_account_id){
            throw ValidationException::withMessages([
                'receive_account_id'=>[
                    'Receive account is required before posting this return.'
                ],
            ]);
        }

        $receive=$this->cashBankAccount(
            (int)$return->receive_account_id,
            'receive_account_id'
        );

        $credit=$return->return_type==='principal'
            ?$this->accountingService->account('investment')
            :$this->accountingService->account('investment_income');

        $journal=$this->accountingService->post([
            'idempotency_key'=>"investment:return:{$return->id}",
            'transaction_date'=>
                $return->return_date->toDateString(),
            'type'=>$return->return_type==='principal'
                ?'investment_principal_return'
                :'investment_income',
            'source_module'=>'investment',
            'source_id'=>$return->investment_id,
            'reference_type'=>InvestmentReturn::class,
            'reference_id'=>$return->id,
            'description'=>$return->description?:(
                $return->return_type==='principal'
                    ?'Investment principal return'
                    :'Investment income'
            ),
            'user_id'=>$userId,
            'entries'=>[
                [
                    'account_id'=>$receive->id,
                    'debit'=>(float)$return->amount,
                    'credit'=>0,
                    'description'=>'Investment return received',
                ],
                [
                    'account_id'=>$credit->id,
                    'debit'=>0,
                    'credit'=>(float)$return->amount,
                    'description'=>$return->return_type==='principal'
                        ?'Investment principal returned'
                        :'Investment income recognized',
                ],
            ],
        ]);

        $return->update([
            'finance_transaction_id'=>$journal->id,
        ]);
    }

    protected function validatePrincipalAmount(
        Investment $investment,
        float $amount,
        ?int $ignoreReturnId=null
    ): void{
        $query=$investment->returns()
            ->whereIn(
                'status',
                ['pending','paid']
            )
            ->where(
                'return_type',
                'principal'
            );

        if($ignoreReturnId){
            $query->where(
                'id',
                '!=',
                $ignoreReturnId
            );
        }

        $reserved=round(
            (float)$query->sum('amount'),
            2
        );

        $principal=round(
            (float)$investment->amount,
            2
        );

        $available=max(
            round(
                $principal-$reserved,
                2
            ),
            0
        );

        if(
            round($amount,2)>
            $available
        ){
            throw ValidationException::withMessages([
                'amount'=>[
                    'Principal return cannot exceed the available investment principal. Available: '.
                    number_format($available,2)
                ],
            ]);
        }
    }

    protected function cashBankAccount(
        int $accountId,
        string $field
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->whereIn(
                'sub_type',
                ['cash','bank']
            )
            ->where(
                'is_active',
                true
            )
            ->whereDoesntHave('children')
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                $field=>[
                    'Selected account must be an active Cash or Bank posting account.'
                ],
            ]);
        }

        return $account;
    }

    protected function syncInvestmentStatus(
        Investment $investment
    ): void{
        $investment->refresh();

        if(
            in_array(
                $investment->status,
                ['pending','cancelled'],
                true
            )
        ){
            return;
        }

        $principalReturned=round(
            (float)$investment
                ->returns()
                ->where(
                    'status',
                    'paid'
                )
                ->where(
                    'return_type',
                    'principal'
                )
                ->sum('amount'),
            2
        );

        $completed=
            $principalReturned>=
            round(
                (float)$investment->amount,
                2
            );

        if(
            $completed&&
            $investment->status!=='completed'
        ){
            $investment->update([
                'status'=>'completed',
            ]);

            return;
        }

        if(
            !$completed&&
            $investment->status==='completed'
        ){
            $investment->update([
                'status'=>'active',
            ]);
        }
    }

    protected function generateInvestmentNo(): string
    {
        $prefix=strtoupper(
            trim(
                (string)setting(
                    'investment_code_prefix',
                    'INV'
                )
            )
        )?:'INV';

        return $this->numberSequenceService->next(
            key:'investment',
            prefix:$prefix.'-',
            digits:6,
            initialValue:function()use($prefix){
                $last=Investment::query()
                    ->where(
                        'investment_no',
                        'like',
                        $prefix.'-%'
                    )
                    ->orderByDesc('id')
                    ->value('investment_no');

                return $last
                    ?(int)substr(
                        $last,
                        -6
                    )
                    :0;
            }
        );
    }

    protected function changed(
        mixed $current,
        mixed $new
    ): bool{
        if($current instanceof \DateTimeInterface){
            return $current->format('Y-m-d')
                !==(string)$new;
        }

        if(
            is_numeric($current)&&
            is_numeric($new)
        ){
            return round(
                (float)$current,
                2
            )!==round(
                (float)$new,
                2
            );
        }

        return (string)($current??'')!==
            (string)($new??'');
    }

    protected function freshInvestment(
        Investment $investment
    ): Investment{
        return $investment->fresh([
            'paymentAccount',
            'financeTransaction.entries.account',
            'returns'=>fn($q)=>
                $q->with([
                    'receiveAccount',
                    'financeTransaction.entries.account',
                ])
                ->latest('return_date')
                ->latest('id'),
        ]);
    }

    protected function freshReturn(
        InvestmentReturn $return
    ): InvestmentReturn{
        return $return->fresh([
            'investment',
            'receiveAccount',
            'financeTransaction.entries.account',
        ]);
    }

    protected function forgetCachesAfterCommit(): void
    {
        DB::afterCommit(
            fn()=>$this->forgetCaches()
        );
    }

    public function forgetCaches(): void
    {
        Cache::forget(
            'investments:statistics'
        );

        Cache::forget(
            'dashboard.investments.summary'
        );

        Cache::forget(
            'dashboard.investments.total'
        );

        $this->dashboardService
            ->forgetDashboardCaches();
    }
}