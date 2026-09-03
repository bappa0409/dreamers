<?php

namespace Database\Seeders;

use App\Models\WebsiteSection;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'section_key' => 'hero',
                'title' => 'একসাথে স্বপ্ন দেখি,|একসাথে এগিয়ে যাই।',
                'subtitle' => 'একটি ঐক্যবদ্ধ ও স্বচ্ছ উদ্যোগ',
                'content' => 'একটি ঐক্যবদ্ধ সংগঠন, যেখানে সদস্যদের সম্মিলিত সঞ্চয়, বিনিয়োগ ও পরিকল্পনার মাধ্যমে দীর্ঘমেয়াদি আর্থিক উন্নয়নের সুযোগ তৈরি করা হয়।',
                'button_text' => 'আমাদের সম্পর্কে জানুন',
                'button_url' => '/about',
                'sort_order' => 1,
                'is_active' => true,
                'settings' => [
                    'secondary_button_text' => 'আমাদের কার্যক্রম',
                    'secondary_button_url' => '/activities',
                ],
            ],
            [
                'section_key' => 'stats',
                'title' => null,
                'subtitle' => null,
                'content' => null,
                'button_text' => null,
                'button_url' => null,
                'sort_order' => 2,
                'is_active' => true,
                'settings' => [
                    'items' => [
                        ['value' => '25', 'suffix' => '+', 'label' => 'সক্রিয় সদস্য'],
                        ['value' => '10', 'suffix' => '+', 'label' => 'চলমান পরিকল্পনা'],
                        ['value' => '100', 'suffix' => '%', 'label' => 'স্বচ্ছতার অঙ্গীকার'],
                        ['value' => 'Long', 'suffix' => '', 'label' => 'দীর্ঘমেয়াদি লক্ষ্য'],
                    ],
                ],
            ],
            [
                'section_key' => 'about',
                'title' => 'আমাদের স্বপ্ন,|আমাদের সম্মিলিত শক্তি।',
                'subtitle' => 'About Us',
                'content' => 'এমন একটি সংগঠন যেখানে সদস্যদের সম্মিলিত প্রচেষ্টা, নিয়মিত সঞ্চয় এবং পরিকল্পিত বিনিয়োগের মাধ্যমে ভবিষ্যতের জন্য একটি শক্তিশালী ভিত্তি তৈরি করার চেষ্টা করা হয়।',
                'button_text' => 'বিস্তারিত জানুন',
                'button_url' => '/about',
                'sort_order' => 3,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'section_key' => 'activities',
                'title' => 'আমাদের প্রধান কার্যক্রম',
                'subtitle' => 'Our Activities',
                'content' => 'সংগঠনের লক্ষ্য ও সদস্যদের দীর্ঘমেয়াদি কল্যাণকে সামনে রেখে বিভিন্ন সম্ভাবনাময় উদ্যোগ নিয়ে কাজ করা হয়।',
                'button_text' => 'সব কার্যক্রম বিস্তারিত দেখুন',
                'button_url' => '/activities',
                'sort_order' => 4,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'section_key' => 'transparency',
                'title' => 'হিসাব থাকবে পরিষ্কার,|সিদ্ধান্ত হবে স্বচ্ছ।',
                'subtitle' => 'Transparency',
                'content' => 'একটি সংগঠনের জন্য আর্থিক স্বচ্ছতা ও সঠিক হিসাবরক্ষণ অত্যন্ত গুরুত্বপূর্ণ। তাই প্রতিটি লেনদেন, আয়-ব্যয় এবং বিনিয়োগের তথ্য যথাযথভাবে সংরক্ষণ ও পর্যালোচনা করার ব্যবস্থা রাখা হয়।',
                'button_text' => 'স্বচ্ছতা সম্পর্কে বিস্তারিত জানুন',
                'button_url' => '/transparency',
                'sort_order' => 5,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'section_key' => 'faq',
                'title' => 'সাধারণ কিছু প্রশ্ন',
                'subtitle' => 'Frequently Asked Questions',
                'content' => 'সম্পর্কে প্রাথমিক কিছু প্রশ্নের সহজ ও সংক্ষিপ্ত উত্তর।',
                'button_text' => 'সব প্রশ্নোত্তর দেখুন',
                'button_url' => '/faq',
                'sort_order' => 6,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'section_key' => 'cta',
                'title' => 'আমাদের স্বপ্নের অংশ হোন',
                'subtitle' => null,
                'content' => 'একটি শক্তিশালী কমিউনিটি গড়ে তুলতে ঐক্য, আস্থা ও দীর্ঘমেয়াদি পরিকল্পনার বিকল্প নেই।',
                'button_text' => 'যোগাযোগ করুন',
                'button_url' => '#contact',
                'sort_order' => 7,
                'is_active' => true,
                'settings' => null,
            ],
            [
                'section_key' => 'contact',
                'title' => 'যোগাযোগ করুন',
                'subtitle' => 'Contact',
                'content' => 'সম্পর্কে জানতে বা কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন।',
                'button_text' => null,
                'button_url' => null,
                'sort_order' => 8,
                'is_active' => true,
                'settings' => null,
            ],
        ];

        foreach ($sections as $section) {
            WebsiteSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }
    }
}
