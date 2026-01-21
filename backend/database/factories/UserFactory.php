<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => Role::findBySlug(Role::USER)?->id ?? 1,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a webadmin user.
     */
    public function webadmin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_id' => Role::findBySlug(Role::WEBADMIN)?->id ?? 1,
        ]);
    }

    /**
     * Create a regular user.
     */
    public function regularUser(): static
    {
        return $this->state(fn(array $attributes) => [
            'role_id' => Role::findBySlug(Role::USER)?->id ?? 2,
        ]);
    }
}
