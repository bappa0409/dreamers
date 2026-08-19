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

            // Branding
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'group' => 'branding',
                'description' => 'Organization logo',
                'is_public' => true,
            ],

            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'group' => 'branding',
                'description' => 'Browser favicon',
                'is_public' => true,
            ],


            // Mail / SMTP
            [
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Mail driver (smtp, log, sendmail)',
                'is_public' => false,
            ],

            [
                'key' => 'smtp_host',
                'value' => null,
                'type' => 'string',
                'group' => 'mail',
                'description' => 'SMTP host',
                'is_public' => false,
            ],

            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'integer',
                'group' => 'mail',
                'description' => 'SMTP port',
                'is_public' => false,
            ],

            [
                'key' => 'smtp_username',
                'value' => null,
                'type' => 'string',
                'group' => 'mail',
                'description' => 'SMTP username',
                'is_public' => false,
            ],

            [
                'key' => 'smtp_password',
                'value' => null,
                'type' => 'password',
                'group' => 'mail',
                'description' => 'SMTP password',
                'is_public' => false,
            ],

            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'SMTP encryption (tls, ssl, none)',
                'is_public' => false,
            ],

            [
                'key' => 'mail_from_address',
                'value' => null,
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Default "from" email address',
                'is_public' => false,
            ],

            [
                'key' => 'mail_from_name',
                'value' => 'Dreamers Association',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Default "from" name',
                'is_public' => false,
            ],


            // Security
            [
                'key' => 'session_lifetime_minutes',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Session lifetime in minutes',
                'is_public' => false,
            ],

            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Max failed login attempts before lockout',
                'is_public' => false,
            ],


            // Maintenance
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'maintenance',
                'description' => 'Put the site into maintenance mode',
                'is_public' => false,
            ],

            [
                'key' => 'maintenance_message',
                'value' => 'We are performing scheduled maintenance. Please check back shortly.',
                'type' => 'string',
                'group' => 'maintenance',
                'description' => 'Message shown to visitors during maintenance',
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