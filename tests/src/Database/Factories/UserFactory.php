<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use InternetGuru\LaravelUser\Enums\Role;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(Role::cases())->value,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(Role $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role->value,
        ]);
    }
}
