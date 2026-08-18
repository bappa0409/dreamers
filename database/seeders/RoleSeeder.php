<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | System Analyst
        |--------------------------------------------------------------------------
        */

        $systemAnalyst = Role::updateOrCreate(
            [
                'name' => 'system_analyst',
            ],
            [
                'display_name' => 'System Analyst',
                'description' => 'Full system access for development, configuration and maintenance.',
                'is_system' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $admin = Role::updateOrCreate(
            [
                'name' => 'admin',
            ],
            [
                'display_name' => 'Admin',
                'description' => 'Administrative management role.',
                'is_system' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Teller
        |--------------------------------------------------------------------------
        */

        $teller = Role::updateOrCreate(
            [
                'name' => 'teller',
            ],
            [
                'display_name' => 'Teller',
                'description' => 'Financial collection and teller related role.',
                'is_system' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Member
        |--------------------------------------------------------------------------
        */

        $member = Role::updateOrCreate(
            [
                'name' => 'member',
            ],
            [
                'display_name' => 'Member',
                'description' => 'Standard association member role.',
                'is_system' => true,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | System Analyst receives all current permissions
        |--------------------------------------------------------------------------
        */

        $systemAnalyst->permissions()->sync(
            Permission::pluck('id')->all()
        );
    }
}