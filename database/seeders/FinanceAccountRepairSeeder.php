<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class FinanceAccountRepairSeeder extends Seeder
{
    public function run(): void
    {
        $roots=[
            'asset'=>$this->root('1000','Assets','asset'),
            'income'=>$this->root('4000','Income','income'),
            'expense'=>$this->root('5000','Expenses','expense'),
            'equity'=>$this->root('3000','Equity','equity'),
        ];

        $definitions=[
            ['asset','cash','1110','Cash','SYS-CASH'],
            ['asset','bank','1120','Bank','SYS-BANK'],
            ['asset','receivable','1130','Accounts Receivable','SYS-AR'],
            ['asset','member_loan_receivable','1140','Member Loan Receivable','SYS-MEMBER-LOAN-AR'],
            ['asset','investment','1210','Investment Asset','SYS-INVESTMENT'],
            ['asset','land_asset','1320','Land & Property','SYS-LAND-ASSET'],
            ['asset','accumulated_depreciation','1390','Accumulated Depreciation','SYS-ACC-DEPR'],
            ['equity','member_equity','3100','Members Equity','SYS-MEMBER-EQUITY'],
            ['income','subscription_income','4110','Subscription Income','SYS-SUB-INCOME'],
            ['income','investment_income','4200','Investment Income','SYS-INV-INCOME'],
            ['income','gain_on_land_sale','4310','Gain on Land Sale','SYS-LAND-GAIN'],
            ['income','fine_income','4400','Fine Income','SYS-FINE-INCOME'],
            ['income','loan_interest_income','4500','Loan Interest Income','SYS-LOAN-INTEREST'],
            ['expense','depreciation_expense','5200','Depreciation Expense','SYS-DEPR-EXP'],
            ['expense','loss_on_land_sale','5310','Loss on Land Sale','SYS-LAND-LOSS'],
            ['expense','land_selling_expense','5320','Land Selling Expense','SYS-LAND-SELL-EXP'],
        ];

        foreach($definitions as [$type,$subType,$preferredCode,$name,$fallbackCode]){
            $this->ensurePostingAccount(
                type:$type,
                subType:$subType,
                preferredCode:$preferredCode,
                fallbackCode:$fallbackCode,
                name:$name,
                parentId:$roots[$type]->id
            );
        }
    }

    private function root(
        string $preferredCode,
        string $name,
        string $type
    ): Account{
        $existing=Account::query()
            ->where('type',$type)
            ->whereNull('parent_id')
            ->first();

        if($existing){
            return $existing;
        }

        $code=$this->availableCode(
            $preferredCode,
            'SYS-'.strtoupper($type).'-ROOT'
        );

        return Account::create([
            'parent_id'=>null,
            'code'=>$code,
            'name'=>$name,
            'type'=>$type,
            'sub_type'=>null,
            'opening_balance'=>0,
            'is_system'=>true,
            'is_active'=>true,
            'description'=>'System finance root account.',
        ]);
    }

    private function ensurePostingAccount(
        string $type,
        string $subType,
        string $preferredCode,
        string $fallbackCode,
        string $name,
        int $parentId
    ): void{
        $existing=Account::query()
            ->where('type',$type)
            ->where('sub_type',$subType)
            ->where('is_active',true)
            ->whereDoesntHave('children')
            ->first();

        if($existing){
            return;
        }

        $code=$this->availableCode(
            $preferredCode,
            $fallbackCode
        );

        Account::create([
            'parent_id'=>$parentId,
            'code'=>$code,
            'name'=>$name,
            'type'=>$type,
            'sub_type'=>$subType,
            'opening_balance'=>0,
            'is_system'=>true,
            'is_active'=>true,
            'description'=>"System posting account for {$subType}.",
        ]);
    }

    private function availableCode(
        string $preferredCode,
        string $fallbackCode
    ): string{
        if(!Account::query()->where('code',$preferredCode)->exists()){
            return $preferredCode;
        }

        if(!Account::query()->where('code',$fallbackCode)->exists()){
            return $fallbackCode;
        }

        $suffix=2;

        do{
            $candidate="{$fallbackCode}-{$suffix}";
            $suffix++;
        }while(Account::query()->where('code',$candidate)->exists());

        return $candidate;
    }
}
