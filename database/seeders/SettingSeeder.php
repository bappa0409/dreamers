<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Retired settings — remove if they exist from an earlier seed run.
        Setting::whereIn('key', ['member_approval_type', 'theme_mode'])->delete();

        $settings = [

            // Organization
            [
                'key' => 'organization_name',
                'value' => 'Dreamers Association',
                'type' => 'string',
                'options' => null,
                'group' => 'organization',
                'description' => 'Organization name',
                'is_public' => true,
            ],
            [
                'key' => 'organization_email',
                'value' => null,
                'type' => 'string',
                'options' => null,
                'group' => 'organization',
                'description' => 'Organization email',
                'is_public' => true,
            ],
            [
                'key' => 'organization_phone',
                'value' => null,
                'type' => 'string',
                'options' => null,
                'group' => 'organization',
                'description' => 'Organization phone number',
                'is_public' => true,
            ],
            [
                'key' => 'organization_address',
                'value' => null,
                'type' => 'string',
                'options' => null,
                'group' => 'organization',
                'description' => 'Organization address',
                'is_public' => true,
            ],
            [
                'key' => 'organization_type',
                'value' => 'cooperative',
                'type' => 'select',
                'options' => ['cooperative', 'ngo', 'trust', 'club', 'other'],
                'group' => 'organization',
                'description' => 'Organization type',
                'is_public' => true,
            ],

            // System
            [
                'key' => 'default_language',
                'value' => 'en',
                'type' => 'select',
                'options' => ['bn', 'en'],
                'group' => 'system',
                'description' => 'Default application language',
                'is_public' => true,
            ],
            [
                'key' => 'timezone',
                'value' => 'Asia/Dhaka',
                'type' => 'select',
                'options' => ['Asia/Dhaka', 'Asia/Kolkata', 'Asia/Karachi', 'UTC'],
                'group' => 'system',
                'description' => 'Application timezone',
                'is_public' => false,
            ],
            [
                'key' => 'currency',
                'value' => 'BDT',
                'type' => 'select',
                'options' => ['BDT', 'USD', 'INR', 'EUR', 'GBP'],
                'group' => 'system',
                'description' => 'Application currency',
                'is_public' => true,
            ],
            [
                'key' => 'currency_symbol',
                'value' => '৳',
                'type' => 'string',
                'options' => null,
                'group' => 'system',
                'description' => 'Currency symbol',
                'is_public' => true,
            ],
            [
                'key' => 'date_format',
                'value' => 'd-m-Y',
                'type' => 'select',
                'options' => [
                    'd-m-Y',   // 19-08-2026
                    'm-d-Y',   // 08-19-2026
                    'Y-m-d',   // 2026-08-19
                    'd M, Y',  // 19 Aug, 2026
                    'M d, Y',  // Aug 19, 2026
                    'd F, Y',  // 19 August, 2026
                    'd M Y',   // 19 Aug 2026
                    'M d Y',   // Aug 19 2026
                    'd F Y',   // 19 August 2026
                ],
                'group' => 'system',
                'description' => 'Date display format',
                'is_public' => true,
            ],
            [
                'key' => 'time_format',
                'value' => '12',
                'type' => 'select',
                'options' => ['12', '24'],
                'group' => 'system',
                'description' => 'Time format (12/24 hour)',
                'is_public' => true,
            ],
            [
                'key' => 'week_start_day',
                'value' => 'sunday',
                'type' => 'select',
                'options' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'group' => 'system',
                'description' => 'Week start day',
                'is_public' => false,
            ],

            // Membership
            [
                'key' => 'member_code_prefix',
                'value' => 'DA',
                'type' => 'string',
                'options' => null,
                'group' => 'membership',
                'description' => 'Member code prefix',
                'is_public' => false,
            ],
            [
                'key' => 'auto_activate_member',
                'value' => '0',
                'type' => 'boolean',
                'options' => null,
                'group' => 'membership',
                'description' => 'Automatically activate new members',
                'is_public' => false,
            ],

            // Finance
            [
                'key' => 'financial_year_start',
                'value' => '01-07',
                'type' => 'select',
                'options' => ['01-01', '01-04', '01-07', '01-10'],
                'group' => 'finance',
                'description' => 'Financial year start date (dd-mm)',
                'is_public' => false,
            ],
            [
                'key' => 'allow_negative_balance',
                'value' => '0',
                'type' => 'boolean',
                'options' => null,
                'group' => 'finance',
                'description' => 'Allow negative account balance',
                'is_public' => false,
            ],
            [
                'key' => 'rounding_mode',
                'value' => 'nearest',
                'type' => 'select',
                'options' => ['nearest', 'up', 'down'],
                'group' => 'finance',
                'description' => 'Amount rounding mode',
                'is_public' => false,
            ],

            // Branding
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
                'options' => null,
                'group' => 'branding',
                'description' => 'Organization logo',
                'is_public' => true,
            ],
            [
                'key' => 'site_favicon',
                'value' => null,
                'type' => 'image',
                'options' => null,
                'group' => 'branding',
                'description' => 'Browser favicon',
                'is_public' => true,
            ],

            // Mail / SMTP
            [
                'key' => 'mail_mailer',
                'value' => 'smtp',
                'type' => 'string',
                'options' => null,
                'group' => 'mail',
                'description' => 'Mail driver (smtp, log, sendmail)',
                'is_public' => false,
            ],
            [
                'key' => 'smtp_host',
                'value' => null,
                'type' => 'string',
                'options' => null,
                'group' => 'mail',
                'description' => 'SMTP host',
                'is_public' => false,
            ],
            [
                'key' => 'smtp_port',
                'value' => '587',
                'type' => 'string',
                'options' => null,
                'group' => 'mail',
                'description' => 'SMTP port',
                'is_public' => false,
            ],
            [
                'key' => 'smtp_username',
                'value' => null,
                'type' => 'string',
                'options' => null,
                'group' => 'mail',
                'description' => 'SMTP username',
                'is_public' => false,
            ],
            [
                'key' => 'smtp_password',
                'value' => null,
                'type' => 'password',
                'options' => null,
                'group' => 'mail',
                'description' => 'SMTP password',
                'is_public' => false,
            ],
            [
                'key' => 'smtp_encryption',
                'value' => 'tls',
                'type' => 'select',
                'options' => ['tls', 'ssl', 'none'],
                'group' => 'mail',
                'description' => 'SMTP encryption',
                'is_public' => false,
            ],
            [
                'key' => 'mail_from_address',
                'value' => null,
                'type' => 'string',
                'options' => null,
                'group' => 'mail',
                'description' => 'Default "from" email address',
                'is_public' => false,
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Dreamers Association',
                'type' => 'string',
                'options' => null,
                'group' => 'mail',
                'description' => 'Default "from" name',
                'is_public' => false,
            ],

            // Security — all plain input fields
            [
                'key' => 'session_lifetime_minutes',
                'value' => '120',
                'type' => 'string',
                'options' => null,
                'group' => 'security',
                'description' => 'Session lifetime in minutes',
                'is_public' => false,
            ],
            [
                'key' => 'max_login_attempts',
                'value' => '5',
                'type' => 'string',
                'options' => null,
                'group' => 'security',
                'description' => 'Max failed login attempts before lockout',
                'is_public' => false,
            ],
            [
                'key' => 'password_reset_expiry_minutes',
                'value' => '60',
                'type' => 'string',
                'options' => null,
                'group' => 'security',
                'description' => 'Password reset link expiry in minutes',
                'is_public' => false,
            ],

            // Maintenance
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'options' => null,
                'group' => 'maintenance',
                'description' => 'Put the site into maintenance mode',
                'is_public' => false,
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'We are performing scheduled maintenance. Please check back shortly.',
                'type' => 'string',
                'options' => null,
                'group' => 'maintenance',
                'description' => 'Message shown to visitors during maintenance',
                'is_public' => false,
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'select',
                'options' => ['daily', 'weekly', 'monthly'],
                'group' => 'maintenance',
                'description' => 'Automatic backup frequency',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}