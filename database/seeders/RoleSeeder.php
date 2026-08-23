<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $systemAnalyst=Role::updateOrCreate(
            ['name'=>'system_analyst'],
            [
                'display_name'=>'System Analyst',
                'description'=>'Full system access for development, configuration and maintenance.',
                'is_system'=>true,
            ]
        );

        $admin=Role::updateOrCreate(
            ['name'=>'admin'],
            [
                'display_name'=>'Admin',
                'description'=>'Administrative management role.',
                'is_system'=>true,
            ]
        );

        $teller=Role::updateOrCreate(
            ['name'=>'teller'],
            [
                'display_name'=>'Teller',
                'description'=>'Financial collection and teller related role.',
                'is_system'=>true,
            ]
        );

        $member=Role::updateOrCreate(
            ['name'=>'member'],
            [
                'display_name'=>'Member',
                'description'=>'Standard association member role.',
                'is_system'=>true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | System Analyst
        |--------------------------------------------------------------------------
        */

        $systemAnalyst->permissions()->sync(
            Permission::pluck('id')->all()
        );

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        |
        | Full application administration.
        | Backup import can remain System Analyst only.
        |
        */

        $adminPermissions=Permission::query()
            ->where('name','!=','Backup.import')
            ->pluck('id')
            ->all();

        $admin->permissions()->sync(
            $adminPermissions
        );

        /*
        |--------------------------------------------------------------------------
        | Teller
        |--------------------------------------------------------------------------
        */

        $tellerPermissions=Permission::query()
            ->whereIn('name',[
                'Finance.view',
                'Finance.create',
                'Finance.update',

                'Member.view',

                'Investment.view',

                'Report.view',
            ])
            ->pluck('id')
            ->all();

        $teller->permissions()->sync(
            $tellerPermissions
        );

        /*
        |--------------------------------------------------------------------------
        | Member
        |--------------------------------------------------------------------------
        |
        | Member portal uses auth + member middleware.
        | No admin permission is required.
        |
        */

        $member->permissions()->sync([]);

        Cache::forget('rbac:permissions:list');
    }
}