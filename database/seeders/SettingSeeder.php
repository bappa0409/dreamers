<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            // Organization
            [
                'key' => 'organization_name',
                'value' => 'Dreamers Association',
                'type' => 'string',
                'group' => 'organization',
                'description' => 'Organization name',
                'is_public' => true,
            ],

            [
                'key' => 'organization_email',
                'value' => null,
                'type' => 'string',
                'group' => 'organization',
                'description' => 'Organization email',
                'is_public' => true,
            ],

            [
                'key' => 'organization_phone',
                'value' => null,
                'type' => 'string',
                'group' => 'organization',
                'description' => 'Organization phone number',
                'is_public' => true,
            ],

            [
                'key' => 'organization_address',
                'value' => null,
                'type' => 'string',
                'group' => 'organization',
                'description' => 'Organization address',
                'is_public' => true,
            ],


            // System
            [
                'key' => 'default_language',
                'value' => 'en',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Default application language',
                'is_public' => true,
            ],

            [
                'key' => 'timezone',
                'value' => 'Asia/Dhaka',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Application timezone',
                'is_public' => false,
            ],

            [
                'key' => 'currency',
                'value' => 'BDT',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Application currency',
                'is_public' => true,
            ],

            [
                'key' => 'currency_symbol',
                'value' => '৳',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Currency symbol',
                'is_public' => true,
            ],


            // Membership
            [
                'key' => 'member_code_prefix',
                'value' => 'DA',
                'type' => 'string',
                'group' => 'membership',
                'description' => 'Member code prefix',
                'is_public' => false,
            ],

            [
                'key' => 'auto_activate_member',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'membership',
                'description' => 'Automatically activate new members',
                'is_public' => false,
            ],


            // Finance
            [
                'key' => 'financial_year_start',
                'value' => '01-07',
                'type' => 'string',
                'group' => 'finance',
                'description' => 'Financial year start date',
                'is_public' => false,
            ],

            [
                'key' => 'allow_negative_balance',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'finance',
                'description' => 'Allow negative account balance',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key']
                ],
                $setting
            );
        }
    }
}