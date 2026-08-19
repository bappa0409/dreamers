<?php

namespace App\Services;

use App\Models\Land;
use App\Models\LandInvestment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LandService
{
    public function __construct(
        protected DashboardService $dashboardService
    ){}

    public function create(array $data): Land
    {
        return DB::transaction(function()use($data){
            $land=Land::create([
                ...$data,
                'land_code'=>$this->generateLandCode(),
                'status'=>$data['status']??'planned',
                'area_unit'=>$data['area_unit']??'decimal',
                'purchase_price'=>$data['purchase_price']??0,
                'selling_expense'=>$data['selling_expense']??0
            ]);

            $this->forgetCaches();

            return $land;
        });
    }

    public function update(Land $land,array $data): Land
    {
        return DB::transaction(function()use($land,$data){
            $land->update($data);
            $this->forgetCaches();

            return $land->fresh();
        });
    }

    public function sell(Land $land,array $data): Land
    {
        return DB::transaction(function()use($land,$data){
            if($land->status!=='purchased'){
                throw ValidationException::withMessages([
                    'status'=>['Only purchased land can be sold.']
                ]);
            }

            $land->update([
                'sale_price'=>$data['sale_price'],
                'selling_expense'=>$data['selling_expense']??0,
                'sale_date'=>$data['sale_date'],
                'buyer_name'=>$data['buyer_name']??null,
                'buyer_phone'=>$data['buyer_phone']??null,
                'status'=>'sold'
            ]);

            $land->investments()
                ->where('status','active')
                ->update([
                    'status'=>'returned'
                ]);

            $this->forgetCaches();

            return $land->fresh([
                'investments.member.user'
            ]);
        });
    }

    public function addInvestment(
        Land $land,
        array $data
    ): LandInvestment{
        return DB::transaction(function()use($land,$data){
            if(in_array($land->status,['sold','cancelled'],true)){
                throw ValidationException::withMessages([
                    'land'=>['Investment cannot be added to this land.']
                ]);
            }

            $existing=$land->investments()
                ->where('member_id',$data['member_id'])
                ->exists();

            if($existing){
                throw ValidationException::withMessages([
                    'member_id'=>['This member already has an investment in this land.']
                ]);
            }

            $ownership=$data['ownership_percentage']??null;

            if($ownership!==null){
                $total=(float)$land->investments()
                    ->whereNotIn('status',['cancelled','returned'])
                    ->sum('ownership_percentage');

                if(($total+(float)$ownership)>100){
                    throw ValidationException::withMessages([
                        'ownership_percentage'=>[
                            'Total ownership percentage cannot exceed 100%.'
                        ]
                    ]);
                }
            }

            $investment=$land->investments()->create([
                ...$data,
                'status'=>$data['status']??'active'
            ]);

            $this->forgetCaches();

            return $investment;
        });
    }

    public function delete(Land $land): void
    {
        DB::transaction(function()use($land){
            if($land->status==='sold'){
                throw ValidationException::withMessages([
                    'land'=>['Sold land cannot be deleted.']
                ]);
            }

            $land->delete();
            $this->forgetCaches();
        });
    }

    public function statistics(): array
    {
        return Cache::remember(
            'lands:statistics',
            now()->addMinutes(5),
            function(){
                $row=Land::query()
                    ->selectRaw("
                        COUNT(*) total,
                        COALESCE(SUM(purchase_price),0) purchase_value,
                        COALESCE(SUM(current_value),0) current_value,
                        COALESCE(SUM(CASE WHEN status='sold' THEN sale_price ELSE 0 END),0) total_sales,
                        COALESCE(SUM(CASE WHEN status='sold' THEN selling_expense ELSE 0 END),0) selling_expense,
                        SUM(CASE WHEN status='planned' THEN 1 ELSE 0 END) planned,
                        SUM(CASE WHEN status='negotiating' THEN 1 ELSE 0 END) negotiating,
                        SUM(CASE WHEN status='purchased' THEN 1 ELSE 0 END) purchased,
                        SUM(CASE WHEN status='sold' THEN 1 ELSE 0 END) sold,
                        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled
                    ")
                    ->first();

                $sold=Land::query()
                    ->where('status','sold')
                    ->get([
                        'purchase_price',
                        'sale_price',
                        'selling_expense'
                    ]);

                $profit=$sold->sum(
                    fn($land)=>
                        (float)$land->sale_price
                        -(float)$land->selling_expense
                        -(float)$land->purchase_price
                );

                return [
                    'total'=>(int)($row->total??0),
                    'purchase_value'=>(float)($row->purchase_value??0),
                    'current_value'=>(float)($row->current_value??0),
                    'total_sales'=>(float)($row->total_sales??0),
                    'selling_expense'=>(float)($row->selling_expense??0),
                    'profit_loss'=>(float)$profit,
                    'planned'=>(int)($row->planned??0),
                    'negotiating'=>(int)($row->negotiating??0),
                    'purchased'=>(int)($row->purchased??0),
                    'sold'=>(int)($row->sold??0),
                    'cancelled'=>(int)($row->cancelled??0)
                ];
            }
        );
    }

    protected function generateLandCode(): string
    {
        $last=Land::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next=$last?$last->id+1:1;

        $prefix=strtoupper(
            trim((string)setting('land_code_prefix','LAND'))
        );

        if($prefix===''){
            $prefix='LAND';
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
        Cache::forget('lands:statistics');
        Cache::forget('dashboard.lands.summary');
        $this->dashboardService->forgetDashboardCaches();
    }
}