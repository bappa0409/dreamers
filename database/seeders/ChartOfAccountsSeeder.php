<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts=[
            ['1000','Assets','asset',null,null],
            ['1100','Cash','asset','cash','1000'],
            ['1200','Bank','asset','bank','1000'],
            ['1300','Accounts Receivable','asset','receivable','1000'],
            ['1310','Member Loan Receivable','asset','member_loan_receivable','1300'],
            ['1400','Investments','asset','investment','1000'],
            ['1500','Fixed Assets','asset','fixed_asset','1000'],
            ['1510','Furniture & Fixtures','asset','fixed_asset','1500'],
            ['1520','Office Equipment','asset','fixed_asset','1500'],
            ['1530','Computer Equipment','asset','fixed_asset','1500'],
            ['1540','Vehicles','asset','fixed_asset','1500'],
            ['1550','Other Fixed Assets','asset','fixed_asset','1500'],

            ['2000','Liabilities','liability',null,null],
            ['2100','Accounts Payable','liability','payable','2000'],
            ['2200','Other Liabilities','liability','other_liability','2000'],

            ['3000','Equity','equity',null,null],
            ['3100','Association Fund','equity','capital','3000'],
            ['3200','Accumulated Fund','equity','retained_earnings','3000'],
['3300','Member Share Capital','equity','member_equity','3000'],



            ['4000','Income','income',null,null],
            ['4100','Subscription Income','income','subscription_income','4000'],
            ['4200','Investment Income','income','investment_income','4000'],
            ['4300','Donation Income','income','donation_income','4000'],
            ['4400','Fine Income','income','fine_income','4000'],
            ['4410','Late Fine Income','income','late_fine_income','4000'],
            ['4500','Loan Interest Income','income','loan_interest_income','4000'],
            ['4900','Other Income','income','other_income','4000'],
            ['4910','Gain on Asset Disposal','income','asset_disposal_gain','4000'],

            ['5000','Expenses','expense',null,null],
            ['5100','Office Expense','expense','operating_expense','5000'],
            ['5200','Maintenance Expense','expense','operating_expense','5000'],
            ['5300','Utility Expense','expense','operating_expense','5000'],
            ['5400','Welfare Assistance Expense','expense','welfare_assistance_expense','5000'],
            ['5900','Other Expense','expense','other_expense','5000'],
            ['5910','Loss on Asset Disposal','expense','asset_disposal_loss','5000'],
        ];

        foreach($accounts as [$code,$name,$type,$subType,$parentCode]){
            $parentId=$parentCode
                ?Account::where('code',$parentCode)->value('id')
                :null;

            Account::updateOrCreate(
                ['code'=>$code],
                [
                    'parent_id'=>$parentId,
                    'name'=>$name,
                    'type'=>$type,
                    'sub_type'=>$subType,
                    'opening_balance'=>0,
                    'is_system'=>true,
                    'is_active'=>true,
                ]
            );
        }
    }
}