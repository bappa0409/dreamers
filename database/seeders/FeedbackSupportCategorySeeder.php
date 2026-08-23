<?php

namespace Database\Seeders;

use App\Models\FeedbackSupportCategory;
use Illuminate\Database\Seeder;

class FeedbackSupportCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories=[
            [
                'name'=>'Accounts & Finance',
                'slug'=>'accounts-finance',
                'sort_order'=>10
            ],
            [
                'name'=>'Membership',
                'slug'=>'membership',
                'sort_order'=>20
            ],
            [
                'name'=>'Loan',
                'slug'=>'loan',
                'sort_order'=>30
            ],
            [
                'name'=>'Welfare',
                'slug'=>'welfare',
                'sort_order'=>40
            ],
            [
                'name'=>'Committee',
                'slug'=>'committee',
                'sort_order'=>50
            ],
            [
                'name'=>'Technical Support',
                'slug'=>'technical-support',
                'sort_order'=>60
            ],
            [
                'name'=>'Service Issue',
                'slug'=>'service-issue',
                'sort_order'=>70
            ],
            [
                'name'=>'Suggestion',
                'slug'=>'suggestion',
                'sort_order'=>80
            ],
            [
                'name'=>'Other',
                'slug'=>'other',
                'sort_order'=>100
            ]
        ];

        foreach($categories as $category){
            FeedbackSupportCategory::updateOrCreate(
                ['slug'=>$category['slug']],
                $category+[
                    'is_active'=>true
                ]
            );
        }
    }
}