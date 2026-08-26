<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestMemberSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function(){
            $memberRole=Role::where('name','member')->firstOrFail();

            $user=User::updateOrCreate(
                ['email'=>'member@test.com'],
                [
                    'name'=>'Test Member',
                    'mobile'=>'01700000001',
                    'language'=>'en',
                    'password'=>Hash::make('Member@123'),
                    'is_active'=>true,
                ]
            );

            $user->assignRole($memberRole);

            $member=Member::updateOrCreate(
                ['user_id'=>$user->id],
                [
                    'member_code'=>'DA-900001',
                    'phone'=>'01700000001',
                    'alternate_phone'=>'01800000001',
                    'date_of_birth'=>'1998-01-15',
                    'gender'=>'male',
                    'address'=>'Dhaka, Bangladesh',
                    'city'=>'Dhaka',
                    'district'=>'Dhaka',
                    'joining_date'=>now()->toDateString(),
                    'status'=>'active',
                    'profile_photo'=>null,
                    'notes'=>'Seeded active member for Member Portal testing.',
                ]
            );

            $plan=SubscriptionPlan::query()
                ->where('is_default',true)
                ->where('is_active',true)
                ->first()
                ??SubscriptionPlan::query()
                    ->where('is_active',true)
                    ->orderBy('id')
                    ->first();

            if($plan){
                MemberSubscription::updateOrCreate(
                    [
                        'member_id'=>$member->id,
                        'subscription_plan_id'=>$plan->id,
                    ],
                    [
                        'start_date'=>$member->joining_date
                            ??now()->toDateString(),
                        'end_date'=>null,
                        'is_active'=>true,
                    ]
                );
            }
        });
    }
}