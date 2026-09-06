<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::whereIn('key',[
            'member_approval_type',
            'theme_mode'
        ])->delete();

        $settings=[
            /*
            |--------------------------------------------------------------------------
            | Organization
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'organization_name',
                'value'=>'Dreamers Association',
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Organization name',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_type',
                'value'=>'association',
                'type'=>'select',
                'options'=>[
                    'association',
                    'cooperative',
                    'society',
                    'club',
                    'foundation',
                    'trust',
                    'ngo',
                    'non_profit',
                    'community_organization',
                    'other',
                ],
                'group'=>'organization',
                'description'=>'Organization type',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_registration_no',
                'value'=>null,
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Registration number',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_registration_authority',
                'value'=>null,
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Registration authority',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_registration_date',
                'value'=>null,
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Registration date',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_established_date',
                'value'=>null,
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Established date',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_email',
                'value'=>'info@dreamersassociation.com',
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Organization email',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_phone',
                'value'=>null,
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Organization phone number',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_alternative_phone',
                'value'=>null,
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Alternative phone number',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_website',
                'value'=>'https://dreamersassociation.com',
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Organization website',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_address',
                'value'=>'Haluaghat, Mymemsing-2260',
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'Organization address',
                'is_public'=>true,
            ],
            [
                'key'=>'organization_description',
                'value'=>'ড্রিমার এসোসিয়েশন একটি স্বেচ্ছাসেবী সঞ্চয় ও বিনিয়োগ সংগঠন, যা এর সদস্যদের মাসিক নির্দিষ্ট পরিমাণ চাঁদার মাধ্যমে একটি যৌথ তহবিল গড়ে তোলে। এই তহবিল ব্যবসা, পণ্য কেনাবেচা, জমি ক্রয়সহ বিভিন্ন লাভজনক খাতে বিনিয়োগ করা হয়, যার মূল লক্ষ্য সদস্যদের আর্থিক সচ্ছলতা ও ভবিষ্যৎ নিরাপত্তা নিশ্চিত করা।',
                'type'=>'string',
                'options'=>null,
                'group'=>'organization',
                'description'=>'About organization',
                'is_public'=>true,
            ],

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'default_language',
                'value'=>'en',
                'type'=>'select',
                'options'=>[
                    'bn',
                    'en',
                ],
                'group'=>'system',
                'description'=>'Default application language',
                'is_public'=>true,
            ],
            [
                'key'=>'timezone',
                'value'=>'Asia/Dhaka',
                'type'=>'select',
                'options'=>[
                    'Asia/Dhaka',
                    'Asia/Kolkata',
                    'Asia/Karachi',
                    'UTC',
                ],
                'group'=>'system',
                'description'=>'Application timezone',
                'is_public'=>false,
            ],
            [
                'key'=>'currency',
                'value'=>'BDT',
                'type'=>'select',
                'options'=>[
                    'BDT',
                    'USD',
                    'INR',
                    'EUR',
                    'GBP',
                ],
                'group'=>'system',
                'description'=>'Application currency',
                'is_public'=>true,
            ],
            [
                'key'=>'currency_symbol',
                'value'=>'৳',
                'type'=>'string',
                'options'=>null,
                'group'=>'system',
                'description'=>'Currency symbol',
                'is_public'=>true,
            ],
            [
                'key'=>'date_format',
                'value'=>'d-m-Y',
                'type'=>'select',
                'options'=>[
                    'd-m-Y',
                    'm-d-Y',
                    'Y-m-d',
                    'd M, Y',
                    'M d, Y',
                    'd F, Y',
                    'd M Y',
                    'M d Y',
                    'd F Y',
                ],
                'group'=>'system',
                'description'=>'Date display format',
                'is_public'=>true,
            ],
            [
                'key'=>'time_format',
                'value'=>'12',
                'type'=>'select',
                'options'=>[
                    '12',
                    '24',
                ],
                'group'=>'system',
                'description'=>'Time format (12/24 hour)',
                'is_public'=>true,
            ],
            [
                'key'=>'week_start_day',
                'value'=>'sunday',
                'type'=>'select',
                'options'=>[
                    'saturday',
                    'sunday',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                ],
                'group'=>'system',
                'description'=>'Week start day',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Membership
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'member_code_prefix',
                'value'=>'DA',
                'type'=>'string',
                'options'=>null,
                'group'=>'membership',
                'description'=>'Member code prefix',
                'is_public'=>false,
            ],
            [
                'key'=>'auto_activate_member',
                'value'=>'0',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'membership',
                'description'=>'Automatically activate new members',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Finance
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'financial_year_start',
                'value'=>'01-07',
                'type'=>'select',
                'options'=>[
                    '01-01',
                    '01-04',
                    '01-07',
                    '01-10',
                ],
                'group'=>'finance',
                'description'=>'Financial year start date (dd-mm)',
                'is_public'=>false,
            ],
            [
                'key'=>'allow_negative_balance',
                'value'=>'0',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'finance',
                'description'=>'Allow negative account balance',
                'is_public'=>false,
            ],
            [
                'key'=>'rounding_mode',
                'value'=>'nearest',
                'type'=>'select',
                'options'=>[
                    'nearest',
                    'up',
                    'down',
                ],
                'group'=>'finance',
                'description'=>'Amount rounding mode',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'site_logo',
                'value'=>null,
                'type'=>'image',
                'options'=>null,
                'group'=>'branding',
                'description'=>'Organization logo',
                'is_public'=>true,
            ],
            [
                'key'=>'site_favicon',
                'value'=>null,
                'type'=>'image',
                'options'=>null,
                'group'=>'branding',
                'description'=>'Browser favicon',
                'is_public'=>true,
            ],
            [
                'key'=>'site_logo_other',
                'value'=>null,
                'type'=>'image',
                'options'=>null,
                'group'=>'branding',
                'description'=>'For Receipt, Invoice, Voucher.',
                'is_public'=>true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Mail / SMTP
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'mail_mailer',
                'value'=>'smtp',
                'type'=>'string',
                'options'=>null,
                'group'=>'mail',
                'description'=>'Mail driver (smtp, log, sendmail)',
                'is_public'=>false,
            ],
            [
                'key'=>'smtp_host',
                'value'=>'sandbox.smtp.mailtrap.io',
                'type'=>'string',
                'options'=>null,
                'group'=>'mail',
                'description'=>'SMTP host',
                'is_public'=>false,
            ],
            [
                'key'=>'smtp_port',
                'value'=>'2525',
                'type'=>'string',
                'options'=>null,
                'group'=>'mail',
                'description'=>'SMTP port',
                'is_public'=>false,
            ],
            [
                'key'=>'smtp_username',
                'value'=>'445a7ed487c788',
                'type'=>'string',
                'options'=>null,
                'group'=>'mail',
                'description'=>'SMTP username',
                'is_public'=>false,
            ],
            [
                'key'=>'smtp_password',
                'value'=>Crypt::encryptString('c736ad74e7107e'),
                'type'=>'password',
                'options'=>null,
                'group'=>'mail',
                'description'=>'SMTP password',
                'is_public'=>false,
            ],
            [
                'key'=>'smtp_encryption',
                'value'=>'tls',
                'type'=>'select',
                'options'=>[
                    'tls',
                    'ssl',
                    'none',
                ],
                'group'=>'mail',
                'description'=>'SMTP encryption',
                'is_public'=>false,
            ],
            [
                'key'=>'mail_from_address',
                'value'=>'bappasutradhar947@gmail.com',
                'type'=>'string',
                'options'=>null,
                'group'=>'mail',
                'description'=>'Default "from" email address',
                'is_public'=>false,
            ],
            [
                'key'=>'mail_from_name',
                'value'=>'Dreamers Association',
                'type'=>'string',
                'options'=>null,
                'group'=>'mail',
                'description'=>'Default "from" name',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Security
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'session_lifetime_minutes',
                'value'=>'120',
                'type'=>'integer',
                'options'=>null,
                'group'=>'security',
                'description'=>'Session lifetime in minutes',
                'is_public'=>false,
            ],
            [
                'key'=>'max_login_attempts',
                'value'=>'5',
                'type'=>'integer',
                'options'=>null,
                'group'=>'security',
                'description'=>'Max failed login attempts before lockout',
                'is_public'=>false,
            ],
            [
                'key'=>'password_reset_expiry_minutes',
                'value'=>'60',
                'type'=>'integer',
                'options'=>null,
                'group'=>'security',
                'description'=>'Password reset link expiry in minutes',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Maintenance
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'maintenance_mode',
                'value'=>'0',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'maintenance',
                'description'=>'Put the site into maintenance mode',
                'is_public'=>false,
            ],
            [
                'key'=>'maintenance_message',
                'value'=>'We are performing scheduled maintenance. Please check back shortly.',
                'type'=>'string',
                'options'=>null,
                'group'=>'maintenance',
                'description'=>'Message shown to visitors during maintenance',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Backup
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'backup_frequency',
                'value'=>'daily',
                'type'=>'select',
                'options'=>[
                    'daily',
                    'weekly',
                    'monthly',
                ],
                'group'=>'backup',
                'description'=>'Automatic backup frequency',
                'is_public'=>false,
            ],
            [
                'key'=>'backup_retention_days',
                'value'=>'30',
                'type'=>'integer',
                'options'=>null,
                'group'=>'backup',
                'description'=>'Number of days completed backups are retained',
                'is_public'=>false,
            ],
            [
                'key'=>'backup_max_files',
                'value'=>'20',
                'type'=>'integer',
                'options'=>null,
                'group'=>'backup',
                'description'=>'Maximum number of completed backup files to retain',
                'is_public'=>false,
            ],
            [
                'key'=>'backup_import_enabled',
                'value'=>'0',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'backup',
                'description'=>'Allow database import from admin panel',
                'is_public'=>false,
            ],
            [
                'key'=>'automatic_backup_enabled',
                'value'=>'1',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'backup',
                'description'=>'Enable scheduled automatic database backup',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | General - Code Prefixes
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'investment_code_prefix',
                'value'=>'INV',
                'type'=>'string',
                'options'=>null,
                'group'=>'general',
                'description'=>'Investment number prefix',
                'is_public'=>false,
            ],
            [
                'key'=>'land_code_prefix',
                'value'=>'LAND',
                'type'=>'string',
                'options'=>null,
                'group'=>'general',
                'description'=>'Land code prefix',
                'is_public'=>false,
            ],
            [
                'key'=>'project_code_prefix',
                'value'=>'PROJ',
                'type'=>'string',
                'options'=>null,
                'group'=>'general',
                'description'=>'Project code prefix',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | General - Shares
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'share_enabled',
                'value'=>'1',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'general',
                'description'=>'Enable or disable member share purchasing',
                'is_public'=>false,
            ],
            [
                'key'=>'default_share_value',
                'value'=>'50000',
                'type'=>'float',
                'options'=>null,
                'group'=>'general',
                'description'=>'Default value of one association share',
                'is_public'=>false,
            ],

            /*
            |--------------------------------------------------------------------------
            | General - Subscription Fine
            |--------------------------------------------------------------------------
            */
            [
                'key'=>'subscription_generate_day',
                'value'=>'25',
                'type'=>'integer',
                'options'=>null,
                'group'=>'general',
                'description'=>'Day of month when next month\'s subscription due is auto-generated',
                'is_public'=>false,
            ],
            [
                'key'=>'subscription_fine_enabled',
                'value'=>'1',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'general',
                'description'=>'Enable late fine for overdue monthly subscriptions',
                'is_public'=>false,
            ],


            [
                'key'=>'loan_enabled',
                'value'=>'1',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'loan',
                'description'=>'Enable member loan management',
                'is_public'=>false,
            ],
            [
                'key'=>'loan_default_interest_rate',
                'value'=>'10',
                'type'=>'float',
                'options'=>null,
                'group'=>'loan',
                'description'=>'Default loan interest percentage',
                'is_public'=>false,
            ],
            [
                'key'=>'loan_default_duration_months',
                'value'=>'12',
                'type'=>'integer',
                'options'=>null,
                'group'=>'loan',
                'description'=>'Default loan duration in months',
                'is_public'=>false,
            ],
            [
                'key'=>'loan_max_amount',
                'value'=>'0',
                'type'=>'float',
                'options'=>null,
                'group'=>'loan',
                'description'=>'Maximum loan amount. Zero means no configured global limit.',
                'is_public'=>false,
            ],
            [
                'key'=>'loan_allow_multiple_active',
                'value'=>'0',
                'type'=>'boolean',
                'options'=>null,
                'group'=>'loan',
                'description'=>'Allow one member to have multiple outstanding loans',
                'is_public'=>false,
            ],
        ];

        foreach($settings as $setting){
            Setting::updateOrCreate(
                ['key'=>$setting['key']],
                $setting
            );
        }
    }
}