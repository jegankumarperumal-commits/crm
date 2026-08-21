<?php

namespace Database\Factories;

use App\Enums\FollowUpAction;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FollowUp>
 */
class FollowUpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'next_action' => fake()->randomElement(FollowUpAction::cases()),
            'due_at' => fake()->dateTimeBetween('-3 days', '+7 days'),
            'completed_at' => null,
            'assigned_to' => null,
            'created_by' => User::factory(),
            'notes' => null,
        ];
    }

    /**
     * Mark the follow-up as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    /**
     * Set the follow-up as overdue and not yet completed.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => fake()->dateTimeBetween('-7 days', '-1 days'),
            'completed_at' => null,
        ]);
    }
}
