<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $systemAnalystRole = Role::where(
            'name',
            'system_analyst'
        )->first();

        if (!$systemAnalystRole) {
            $this->command->error(
                'System Analyst role not found. Run RoleSeeder first.'
            );

            return;
        }

        DB::transaction(function () use ($systemAnalystRole) {

            /*
            |--------------------------------------------------------------------------
            | Create / Update User Account
            |--------------------------------------------------------------------------
            */

            $user = User::updateOrCreate(
                [
                    'email' => 'admin@gmail.com',
                ],
                [
                    'name' => 'System Analyst',
                    'mobile' => '01928040976',
                    'password' => Hash::make('admin@gmail.com'),
                    'language' => 'en',
                    'is_active' => true,

                    /*
                     * Temporary compatibility.
                     *
                     * Old code may still use users.role_id.
                     * We will remove this later.
                     */
                    'role_id' => $systemAnalystRole->id,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Assign System Analyst Role
            |--------------------------------------------------------------------------
            */

            $user->roles()
                ->syncWithoutDetaching([
                    $systemAnalystRole->id,
                ]);

                $user->forgetAuthorizationCache();
        });

        $this->command->info(
            'System Analyst member account created successfully.'
        );
    }
}