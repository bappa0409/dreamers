<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Parent Accounts
        |--------------------------------------------------------------------------
        */

        $assets=$this->account([
            'code'=>'1000',
            'name'=>'Assets',
            'type'=>'asset',
            'sub_type'=>null,
            'is_system'=>true,
            'description'=>'Root account for all association assets.',
        ]);

        $liabilities=$this->account([
            'code'=>'2000',
            'name'=>'Liabilities',
            'type'=>'liability',
            'sub_type'=>null,
            'is_system'=>true,
            'description'=>'Root account for all association liabilities.',
        ]);

        $equity=$this->account([
            'code'=>'3000',
            'name'=>'Equity',
            'type'=>'equity',
            'sub_type'=>null,
            'is_system'=>true,
            'description'=>'Root account for association equity.',
        ]);

        $income=$this->account([
            'code'=>'4000',
            'name'=>'Income',
            'type'=>'income',
            'sub_type'=>null,
            'is_system'=>true,
            'description'=>'Root account for association income.',
        ]);

        $expenses=$this->account([
            'code'=>'5000',
            'name'=>'Expenses',
            'type'=>'expense',
            'sub_type'=>null,
            'is_system'=>true,
            'description'=>'Root account for association expenses.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Current Assets
        |--------------------------------------------------------------------------
        */

        $currentAssets=$this->account([
            'parent_id'=>$assets->id,
            'code'=>'1100',
            'name'=>'Current Assets',
            'type'=>'asset',
            'sub_type'=>null,
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$currentAssets->id,
            'code'=>'1110',
            'name'=>'Cash',
            'type'=>'asset',
            'sub_type'=>'cash',
            'is_system'=>true,
            'description'=>'Cash in hand.',
        ]);

        $this->account([
            'parent_id'=>$currentAssets->id,
            'code'=>'1120',
            'name'=>'Bank',
            'type'=>'asset',
            'sub_type'=>'bank',
            'is_system'=>true,
            'description'=>'Association bank balances.',
        ]);

        $this->account([
            'parent_id'=>$currentAssets->id,
            'code'=>'1130',
            'name'=>'Accounts Receivable',
            'type'=>'asset',
            'sub_type'=>'receivable',
            'is_system'=>true,
            'description'=>'Amounts receivable from members and other parties.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Investments
        |--------------------------------------------------------------------------
        */

        $investmentAssets=$this->account([
            'parent_id'=>$assets->id,
            'code'=>'1200',
            'name'=>'Investments',
            'type'=>'asset',
            'sub_type'=>null,
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$investmentAssets->id,
            'code'=>'1210',
            'name'=>'Investment Asset',
            'type'=>'asset',
            'sub_type'=>'investment',
            'is_system'=>true,
            'description'=>'Principal amount invested by the association.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Fixed Assets
        |--------------------------------------------------------------------------
        */

        $fixedAssets=$this->account([
            'parent_id'=>$assets->id,
            'code'=>'1300',
            'name'=>'Fixed Assets',
            'type'=>'asset',
            'sub_type'=>null,
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$fixedAssets->id,
            'code'=>'1310',
            'name'=>'Furniture & Equipment',
            'type'=>'asset',
            'sub_type'=>'fixed_asset',
            'is_system'=>true,
            'description'=>'Furniture, equipment and other fixed assets.',
        ]);

        $this->account([
            'parent_id'=>$fixedAssets->id,
            'code'=>'1320',
            'name'=>'Land & Property',
            'type'=>'asset',
            'sub_type'=>'land_asset',
            'is_system'=>true,
            'description'=>'Land and property owned by the association.',
        ]);

        $this->account([
            'parent_id'=>$fixedAssets->id,
            'code'=>'1390',
            'name'=>'Accumulated Depreciation',
            'type'=>'asset',
            'sub_type'=>'accumulated_depreciation',
            'is_system'=>true,
            'description'=>'Contra-asset account for accumulated depreciation.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Liabilities
        |--------------------------------------------------------------------------
        */

        $currentLiabilities=$this->account([
            'parent_id'=>$liabilities->id,
            'code'=>'2100',
            'name'=>'Current Liabilities',
            'type'=>'liability',
            'sub_type'=>null,
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$currentLiabilities->id,
            'code'=>'2110',
            'name'=>'Accounts Payable',
            'type'=>'liability',
            'sub_type'=>'payable',
            'is_system'=>true,
            'description'=>'Amounts payable to vendors and other parties.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Equity
        |--------------------------------------------------------------------------
        */

        $this->account([
            'parent_id'=>$equity->id,
            'code'=>'3100',
            'name'=>'Members Equity',
            'type'=>'equity',
            'sub_type'=>'member_equity',
            'is_system'=>true,
            'description'=>'Association members equity or capital.',
        ]);

        $this->account([
            'parent_id'=>$equity->id,
            'code'=>'3200',
            'name'=>'Retained Surplus',
            'type'=>'equity',
            'sub_type'=>'retained_earnings',
            'is_system'=>true,
            'description'=>'Accumulated retained surplus of the association.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Income
        |--------------------------------------------------------------------------
        */

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4100',
            'name'=>'Membership Income',
            'type'=>'income',
            'sub_type'=>'membership',
            'is_system'=>true,
            'description'=>'Membership related income.',
        ]);

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4110',
            'name'=>'Subscription Income',
            'type'=>'income',
            'sub_type'=>'subscription',
            'is_system'=>true,
            'description'=>'Monthly subscription income.',
        ]);

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4120',
            'name'=>'Charge Income',
            'type'=>'income',
            'sub_type'=>'member_charge',
            'is_system'=>true,
            'description'=>'Income recognized from member charges.',
        ]);

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4200',
            'name'=>'Investment Income',
            'type'=>'income',
            'sub_type'=>'investment_income',
            'is_system'=>true,
            'description'=>'Income earned from investments.',
        ]);

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4300',
            'name'=>'Asset Disposal Gain',
            'type'=>'income',
            'sub_type'=>'asset_disposal_gain',
            'is_system'=>true,
            'description'=>'Gain recognized from disposal or sale of assets.',
        ]);

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4310',
            'name'=>'Gain on Land Sale',
            'type'=>'income',
            'sub_type'=>'gain_on_land_sale',
            'is_system'=>true,
            'description'=>'Realized gain from sale of association-owned land.',
        ]);

        $this->account([
            'parent_id'=>$income->id,
            'code'=>'4900',
            'name'=>'Other Income',
            'type'=>'income',
            'sub_type'=>'other_income',
            'is_system'=>true,
            'description'=>'Other association income.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Expenses
        |--------------------------------------------------------------------------
        */

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5100',
            'name'=>'Office Expense',
            'type'=>'expense',
            'sub_type'=>'office',
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5110',
            'name'=>'Utility Expense',
            'type'=>'expense',
            'sub_type'=>'utility',
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5120',
            'name'=>'Administrative Expense',
            'type'=>'expense',
            'sub_type'=>'administrative',
            'is_system'=>true,
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5200',
            'name'=>'Depreciation Expense',
            'type'=>'expense',
            'sub_type'=>'depreciation',
            'is_system'=>true,
            'description'=>'Periodic depreciation expense of fixed assets.',
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5300',
            'name'=>'Asset Disposal Loss',
            'type'=>'expense',
            'sub_type'=>'asset_disposal_loss',
            'is_system'=>true,
            'description'=>'Loss recognized from asset disposal or sale.',
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5310',
            'name'=>'Loss on Land Sale',
            'type'=>'expense',
            'sub_type'=>'loss_on_land_sale',
            'is_system'=>true,
            'description'=>'Realized loss from sale of association-owned land.',
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5320',
            'name'=>'Land Selling Expense',
            'type'=>'expense',
            'sub_type'=>'land_selling_expense',
            'is_system'=>true,
            'description'=>'Registration, broker, legal and other expenses related to land sale.',
        ]);

        $this->account([
            'parent_id'=>$expenses->id,
            'code'=>'5900',
            'name'=>'Other Expense',
            'type'=>'expense',
            'sub_type'=>'other_expense',
            'is_system'=>true,
        ]);
    }

    private function account(array $data): Account
    {
        return Account::updateOrCreate(
            [
                'code'=>$data['code'],
            ],
            [
                'parent_id'=>$data['parent_id']??null,
                'name'=>$data['name'],
                'type'=>$data['type'],
                'sub_type'=>$data['sub_type']??null,
                'opening_balance'=>0,
                'is_system'=>$data['is_system']??true,
                'is_active'=>$data['is_active']??true,
                'description'=>$data['description']??null,
            ]
        );
    }
}