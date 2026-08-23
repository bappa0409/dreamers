<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AccountSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            ChartOfAccountsSeeder::class,
            SubscriptionPlanSeeder::class,
            TestMemberSeeder::class,
            CommitteePositionSeeder::class,
            FeedbackSupportCategorySeeder::class,
        ]);
    }
}
