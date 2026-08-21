<?php

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
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
            'type' => fake()->randomElement(MeetingType::cases()),
            'status' => MeetingStatus::Scheduled,
            'scheduled_at' => fake()->dateTimeBetween('-3 days', '+7 days'),
            'location' => fake()->optional()->streetAddress(),
            'agenda' => null,
            'notes' => null,
            'assigned_to' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Set the meeting's outcome status.
     */
    public function withStatus(MeetingStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Set the meeting as scheduled in the past but never marked complete.
     */
    public function missed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MeetingStatus::Scheduled,
            'scheduled_at' => fake()->dateTimeBetween('-7 days', '-1 days'),
        ]);
    }
}
