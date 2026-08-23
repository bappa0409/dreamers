<?php

namespace Database\Seeders;

use App\Models\CommitteePosition;
use Illuminate\Database\Seeder;

class CommitteePositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions=[
            ['code'=>'PRESIDENT','name'=>'President','is_exclusive'=>true,'sort_order'=>10],
            ['code'=>'VICE_PRESIDENT','name'=>'Vice President','is_exclusive'=>true,'sort_order'=>20],
            ['code'=>'SECRETARY','name'=>'Secretary','is_exclusive'=>true,'sort_order'=>30],
            ['code'=>'JOINT_SECRETARY','name'=>'Joint Secretary','is_exclusive'=>true,'sort_order'=>40],
            ['code'=>'TREASURER','name'=>'Treasurer','is_exclusive'=>true,'sort_order'=>50],
            ['code'=>'EXECUTIVE_MEMBER','name'=>'Executive Member','is_exclusive'=>false,'sort_order'=>100]
        ];

        foreach($positions as $position){
            CommitteePosition::updateOrCreate(
                ['code'=>$position['code']],
                $position+['is_active'=>true]
            );
        }
    }
}