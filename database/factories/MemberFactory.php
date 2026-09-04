<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'member_code' => 'MC-' . fake()->unique()->numerify('######'),
            'father_or_husband_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'phone' => fake()->numerify('01#########'),
            'date_of_birth' => fake()->date('Y-m-d', '-20 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'joining_date' => now()->toDateString(),
            'status' => 'active',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
