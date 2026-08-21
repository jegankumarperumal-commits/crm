<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
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
            'converted_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'created_by' => User::factory(),
            'notes' => null,
        ];
    }
}
