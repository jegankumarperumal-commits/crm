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
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = Role::firstOrCreate(
            ['slug' => 'business_analyst'],
            ['name' => 'Business Analyst', 'permissions' => ['manage_leads', 'manage_follow_ups', 'manage_meetings', 'manage_quotations', 'manage_clients_invoices', 'view_reports']],
        );

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => $role->id,
            'permissions' => $role->permissions,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model is a Founder.
     */
    public function founder(): static
    {
        return $this->state(function (array $attributes) {
            $role = Role::firstOrCreate(
                ['slug' => 'founder'],
                ['name' => 'Founder / CEO', 'is_founder' => true, 'permissions' => []],
            );

            return [
                'role_id' => $role->id,
                'permissions' => $role->permissions,
            ];
        });
    }

    /**
     * Set the model's role, seeding permissions from that role's defaults.
     */
    public function withRole(Role $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $role->id,
            'permissions' => $role->permissions,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static {}
}
