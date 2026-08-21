<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
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
            'amount' => fake()->randomFloat(2, 5000, 500000),
            'status' => QuotationStatus::Required,
            'sent_at' => null,
            'assigned_to' => null,
            'created_by' => User::factory(),
            'notes' => null,
        ];
    }

    /**
     * Set the quotation's status.
     */
    public function withStatus(QuotationStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'sent_at' => in_array($status, [QuotationStatus::Sent, QuotationStatus::Negotiation, QuotationStatus::Accepted, QuotationStatus::Rejected], true)
                ? fake()->dateTimeBetween('-2 weeks', 'now')
                : null,
        ]);
    }
}
