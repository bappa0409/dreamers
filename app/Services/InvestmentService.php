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
        protected AccountingService $accountingService
    ){}

    public function create(array $data,int $userId): Investment
    {
        return DB::transaction(function()use($data,$userId){
            $paymentAccount=$this->cashBankAccount((int)$data['payment_account_id'],'payment_account_id');
            $investmentAccount=$this->accountingService->account('investment');

            $investment=Investment::create([
                'investment_no'=>$this->generateInvestmentNo(),
                'member_id'=>$data['member_id'],
                'payment_account_id'=>$paymentAccount->id,
                'title'=>$data['title'],
                'description'=>$data['description']??null,
                'amount'=>$data['amount'],
                'expected_return'=>$data['expected_return']??0,
                'investment_date'=>$data['investment_date'],
                'maturity_date'=>$data['maturity_date']??null,
                'status'=>$data['status']??'active'
            ]);

            $journal=$this->accountingService->post([
                'transaction_date'=>$investment->investment_date->toDateString(),
                'type'=>'investment',
                'source_module'=>'investment',
                'source_id'=>$investment->id,
                'reference_type'=>Investment::class,
                'reference_id'=>$investment->id,
                'description'=>$investment->description?:"Investment purchase {$investment->investment_no}",
                'user_id'=>$userId,
                'entries'=>[
                    [
                        'account_id'=>$investmentAccount->id,
                        'debit'=>$investment->amount,
                        'credit'=>0,
                        'description'=>'Investment asset'
                    ],
                    [
                        'account_id'=>$paymentAccount->id,
                        'debit'=>0,
                        'credit'=>$investment->amount,
                        'description'=>'Investment payment'
                    ]
                ]
            ]);

            $investment->update([
                'finance_transaction_id'=>$journal->id
            ]);

            $this->forgetCaches();

            return $this->freshInvestment($investment);
        });
    }

    public function update(Investment $investment,array $data): Investment
    {
        return DB::transaction(function()use($investment,$data){
            $investment=Investment::query()
                ->whereKey($investment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->finance_transaction_id){
                foreach(['member_id','payment_account_id','amount','investment_date'] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed($investment->{$field},$data[$field])
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Posted investment financial information cannot be changed directly.'
                            ]
                        ]);
                    }
                }

                if(
                    array_key_exists('status',$data)&&
                    $data['status']==='cancelled'&&
                    $investment->status!=='cancelled'
                ){
                    throw ValidationException::withMessages([
                        'status'=>[
                            'Use the cancel action to cancel a posted investment.'
                        ]
                    ]);
                }
            }

            $investment->update($data);

            $this->syncInvestmentStatus($investment);
            $this->forgetCaches();

            return $this->freshInvestment($investment);
        });
    }

    public function cancel(Investment $investment,int $userId): Investment
    {
        return DB::transaction(function()use($investment,$userId){
            $investment=Investment::query()
                ->whereKey($investment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->status==='cancelled'){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Investment is already cancelled.'
                    ]
                ]);
            }

            if(
                $investment->returns()
                    ->where('status','paid')
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Investment with paid returns cannot be cancelled directly.'
                    ]
                ]);
            }

            if($investment->finance_transaction_id){
                $original=$investment->financeTransaction()
                    ->with('entries')
                    ->firstOrFail();

                $this->accountingService->post([
                    'transaction_date'=>now()->toDateString(),
                    'type'=>'investment_reversal',
                    'source_module'=>'investment',
                    'source_id'=>$investment->id,
                    'reference_type'=>Investment::class,
                    'reference_id'=>$investment->id,
                    'description'=>"Reversal of {$original->transaction_no} - {$investment->investment_no}",
                    'user_id'=>$userId,
                    'entries'=>$original->entries->map(fn($entry)=>[
                        'account_id'=>$entry->account_id,
                        'debit'=>(float)$entry->credit,
                        'credit'=>(float)$entry->debit,
                        'description'=>'Investment cancellation reversal'
                    ])->all()
                ]);
            }

            $investment->update([
                'status'=>'cancelled'
            ]);

            $this->forgetCaches();

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
                    ]
                ]);
            }

            if($investment->returns()->exists()){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Investment with return history cannot be deleted.'
                    ]
                ]);
            }

            $investment->delete();

            $this->forgetCaches();
        });
    }

    public function addReturn(
        Investment $investment,
        array $data,
        int $userId
    ): InvestmentReturn{
        return DB::transaction(function()use($investment,$data,$userId){
            $investment=Investment::query()
                ->whereKey($investment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->status==='cancelled'){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Return cannot be added to a cancelled investment.'
                    ]
                ]);
            }

            $returnType=$data['return_type']??'income';

            if($returnType==='principal'){
                $this->validatePrincipalAmount(
                    $investment,
                    (float)$data['amount']
                );
            }

            $receiveAccount=null;

            if(($data['status']??'pending')==='paid'){
                if(empty($data['receive_account_id'])){
                    throw ValidationException::withMessages([
                        'receive_account_id'=>[
                            'Receive account is required for a paid return.'
                        ]
                    ]);
                }

                $receiveAccount=$this->cashBankAccount(
                    (int)$data['receive_account_id'],
                    'receive_account_id'
                );
            }

            $return=$investment->returns()->create([
                'return_type'=>$returnType,
                'receive_account_id'=>$receiveAccount?->id??($data['receive_account_id']??null),
                'amount'=>$data['amount'],
                'return_date'=>$data['return_date'],
                'description'=>$data['description']??null,
                'status'=>$data['status']??'pending'
            ]);

            if($return->status==='paid'){
                $this->postReturn($return,$userId);
            }

            $this->syncInvestmentStatus($investment);
            $this->forgetCaches();

            return $this->freshReturn($return);
        });
    }

    public function updateReturn(
        InvestmentReturn $return,
        array $data,
        int $userId
    ): InvestmentReturn{
        return DB::transaction(function()use($return,$data,$userId){
            $return=InvestmentReturn::query()
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($return->finance_transaction_id){
                throw ValidationException::withMessages([
                    'investment_return'=>[
                        'Posted return cannot be edited.'
                    ]
                ]);
            }

            $investment=Investment::query()
                ->whereKey($return->investment_id)
                ->lockForUpdate()
                ->firstOrFail();

            if($investment->status==='cancelled'){
                throw ValidationException::withMessages([
                    'investment'=>[
                        'Return cannot be updated for a cancelled investment.'
                    ]
                ]);
            }

            $type=$data['return_type']??$return->return_type;
            $amount=(float)($data['amount']??$return->amount);

            if($type==='principal'){
                $this->validatePrincipalAmount(
                    $investment,
                    $amount,
                    $return->id
                );
            }

            $status=$data['status']??$return->status;

            if($status==='paid'){
                $accountId=$data['receive_account_id']??$return->receive_account_id;

                if(!$accountId){
                    throw ValidationException::withMessages([
                        'receive_account_id'=>[
                            'Receive account is required for a paid return.'
                        ]
                    ]);
                }

                $data['receive_account_id']=$this->cashBankAccount(
                    (int)$accountId,
                    'receive_account_id'
                )->id;
            }

            $return->update($data);

            if($return->status==='paid'){
                $this->postReturn($return,$userId);
            }

            $this->syncInvestmentStatus($investment);
            $this->forgetCaches();

            return $this->freshReturn($return);
        });
    }

    public function deleteReturn(InvestmentReturn $return): void
    {
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
                    ]
                ]);
            }

            $investment=$return->investment;

            $return->delete();

            if($investment){
                $this->syncInvestmentStatus($investment);
            }

            $this->forgetCaches();
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
                        COALESCE(SUM(amount),0) total_amount,
                        COALESCE(SUM(expected_return),0) expected_return,
                        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
                        SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active,
                        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
                        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled
                    ")
                    ->first();

                $income=(float)InvestmentReturn::query()
                    ->where('status','paid')
                    ->where('return_type','income')
                    ->sum('amount');

                $principal=(float)InvestmentReturn::query()
                    ->where('status','paid')
                    ->where('return_type','principal')
                    ->sum('amount');

                return[
                    'total'=>(int)($row->total??0),
                    'total_amount'=>(float)($row->total_amount??0),
                    'expected_return'=>(float)($row->expected_return??0),
                    'paid_return'=>$income,
                    'income_received'=>$income,
                    'principal_received'=>$principal,
                    'pending'=>(int)($row->pending??0),
                    'active'=>(int)($row->active??0),
                    'completed'=>(int)($row->completed??0),
                    'cancelled'=>(int)($row->cancelled??0)
                ];
            }
        );
    }

    protected function postReturn(
        InvestmentReturn $return,
        int $userId
    ): void{
        if($return->finance_transaction_id){
            return;
        }

        $receive=$this->cashBankAccount(
            (int)$return->receive_account_id,
            'receive_account_id'
        );

        $credit=$return->return_type==='principal'
            ?$this->accountingService->account('investment')
            :$this->accountingService->account('investment_income');

        $journal=$this->accountingService->post([
            'transaction_date'=>$return->return_date->toDateString(),
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
                    'debit'=>$return->amount,
                    'credit'=>0,
                    'description'=>'Investment return received'
                ],
                [
                    'account_id'=>$credit->id,
                    'debit'=>0,
                    'credit'=>$return->amount,
                    'description'=>$return->return_type==='principal'
                        ?'Investment principal returned'
                        :'Investment income recognized'
                ]
            ]
        ]);

        $return->update([
            'finance_transaction_id'=>$journal->id
        ]);
    }

    protected function validatePrincipalAmount(
        Investment $investment,
        float $amount,
        ?int $ignoreReturnId=null
    ): void{
        $query=$investment->returns()
            ->where('status','paid')
            ->where('return_type','principal');

        if($ignoreReturnId){
            $query->where('id','!=',$ignoreReturnId);
        }

        $returned=(float)$query->sum('amount');

        if(
            round($returned+$amount,2)>
            round((float)$investment->amount,2)
        ){
            throw ValidationException::withMessages([
                'amount'=>[
                    'Principal return cannot exceed remaining investment principal.'
                ]
            ]);
        }
    }

    protected function cashBankAccount(
        int $accountId,
        string $field
    ): Account{
        $account=Account::query()
            ->whereKey($accountId)
            ->whereIn('sub_type',['cash','bank'])
            ->where('is_active',true)
            ->first();

        if(!$account){
            throw ValidationException::withMessages([
                $field=>[
                    'Selected account must be an active Cash or Bank account.'
                ]
            ]);
        }

        return $account;
    }

    protected function syncInvestmentStatus(Investment $investment): void
    {
        if($investment->status==='cancelled'){
            return;
        }

        $expected=(float)$investment->expected_return;

        if($expected<=0){
            return;
        }

        $paid=(float)$investment->returns()
            ->where('status','paid')
            ->where('return_type','income')
            ->sum('amount');

        if(
            $paid>=$expected&&
            $investment->status!=='completed'
        ){
            $investment->update([
                'status'=>'completed'
            ]);
        }
    }

    protected function generateInvestmentNo(): string
    {
        $last=Investment::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next=$last?$last->id+1:1;

        $prefix=strtoupper(
            trim((string)setting(
                'investment_code_prefix',
                'INV'
            ))
        )?:'INV';

        return $prefix.'-'.str_pad(
            (string)$next,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function changed(mixed $current,mixed $new): bool
    {
        if($current instanceof \DateTimeInterface){
            return $current->format('Y-m-d')!==(string)$new;
        }

        if(is_numeric($current)&&is_numeric($new)){
            return round((float)$current,2)!==
                round((float)$new,2);
        }

        return (string)$current!==(string)$new;
    }

    protected function freshInvestment(Investment $investment): Investment
    {
        return $investment->fresh([
            'member.user',
            'paymentAccount',
            'financeTransaction.entries.account',
            'returns.receiveAccount',
            'returns.financeTransaction.entries.account'
        ]);
    }

    protected function freshReturn(
        InvestmentReturn $return
    ): InvestmentReturn{
        return $return->fresh([
            'investment',
            'receiveAccount',
            'financeTransaction.entries.account'
        ]);
    }

    public function forgetCaches(): void
    {
        Cache::forget('investments:statistics');
        Cache::forget('dashboard.investments.summary');
        Cache::forget('dashboard.investments.total');

        $this->dashboardService->forgetDashboardCaches();
    }
}