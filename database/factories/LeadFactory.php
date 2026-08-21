<?php

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'company' => fake()->optional()->company(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->numerify('##########'),
            'source' => fake()->randomElement(LeadSource::cases()),
            'status' => LeadStatus::New,
            'assigned_to' => null,
            'created_by' => User::factory(),
            'notes' => null,
        ];
    }

    /**
     * Assign the lead to the given user.
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    /**
     * Set the lead's pipeline status.
     */
    public function withStatus(LeadStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
