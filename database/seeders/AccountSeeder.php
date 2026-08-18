<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'code' => '1000',
                'name' => 'Cash',
                'type' => 'cash',
            ],
            [
                'code' => '1010',
                'name' => 'Bank',
                'type' => 'bank',
            ],
            [
                'code' => '4000',
                'name' => 'Membership Income',
                'type' => 'income',
            ],
            [
                'code' => '4010',
                'name' => 'Investment Income',
                'type' => 'income',
            ],
            [
                'code' => '5000',
                'name' => 'Office Expense',
                'type' => 'expense',
            ],
            [
                'code' => '5010',
                'name' => 'Utility Expense',
                'type' => 'expense',
            ],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}