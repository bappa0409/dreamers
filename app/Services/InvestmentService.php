<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentReturn;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestmentService
{
    public function __construct(
        protected DashboardService $dashboardService
    ){}

    public function create(array $data): Investment
    {
        return DB::transaction(function()use($data){
            $investment=Investment::create([
                ...$data,
                'investment_no'=>$this->generateInvestmentNo()
            ]);

            $this->forgetCaches();

            return $investment;
        });
    }

    public function update(Investment $investment,array $data): Investment
    {
        return DB::transaction(function()use($investment,$data){
            $investment->update($data);

            $this->forgetCaches();

            return $investment->fresh();
        });
    }

    public function delete(Investment $investment): void
    {
        DB::transaction(function()use($investment){
            $investment->delete();
            $this->forgetCaches();
        });
    }

    public function addReturn(Investment $investment,array $data): InvestmentReturn
    {
        return DB::transaction(function()use($investment,$data){
            if($investment->status==='cancelled'){
                throw ValidationException::withMessages([
                    'investment'=>['Return cannot be added to a cancelled investment.']
                ]);
            }

            $return=$investment->returns()->create($data);

            $this->syncInvestmentStatus($investment);
            $this->forgetCaches();

            return $return;
        });
    }

    public function updateReturn(
        InvestmentReturn $investmentReturn,
        array $data
    ): InvestmentReturn{
        return DB::transaction(function()use($investmentReturn,$data){
            $investmentReturn->update($data);

            $investment=$investmentReturn->investment;

            if($investment){
                $this->syncInvestmentStatus($investment);
            }

            $this->forgetCaches();

            return $investmentReturn->fresh();
        });
    }

    public function deleteReturn(InvestmentReturn $investmentReturn): void
    {
        DB::transaction(function()use($investmentReturn){
            $investment=$investmentReturn->investment;

            $investmentReturn->delete();

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

                $paidReturn=InvestmentReturn::query()
                    ->where('status','paid')
                    ->sum('amount');

                return [
                    'total'=>(int)($row->total??0),
                    'total_amount'=>(float)($row->total_amount??0),
                    'expected_return'=>(float)($row->expected_return??0),
                    'paid_return'=>(float)$paidReturn,
                    'pending'=>(int)($row->pending??0),
                    'active'=>(int)($row->active??0),
                    'completed'=>(int)($row->completed??0),
                    'cancelled'=>(int)($row->cancelled??0)
                ];
            }
        );
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
            ->sum('amount');

        if($paid>=$expected){
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
            trim((string)setting('investment_code_prefix','INV'))
        );

        if($prefix===''){
            $prefix='INV';
        }

        return $prefix.'-'.str_pad(
            (string)$next,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    public function forgetCaches(): void
    {
        Cache::forget('investments:statistics');
        Cache::forget('dashboard.investments.summary');
        Cache::forget('dashboard.investments.total');
        $this->dashboardService->forgetDashboardCaches();
    }
}