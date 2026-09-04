<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = fake()->unique()->slug(2, '_');

        return [
            'name' => $name,
            'display_name' => ucwords(str_replace('_', ' ', $name)),
            'description' => fake()->sentence(),
            'is_system' => false,
        ];
    }

    /**
     * The all-powerful role. User::isSystemAnalyst() short-circuits
     * every hasPermission()/hasAnyPermission() check to true, so a
     * user with this role can hit any permission-protected endpoint
     * without seeding the permissions table.
     */
    public function systemAnalyst(): static
    {
        return $this->state(fn () => [
            'name' => 'system_analyst',
            'display_name' => 'System Analyst',
            'description' => 'Full system access for development, configuration and maintenance.',
            'is_system' => true,
        ]);
    }
}
