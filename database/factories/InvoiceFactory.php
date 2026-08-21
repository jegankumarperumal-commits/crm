<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'amount' => fake()->randomFloat(2, 5000, 500000),
            'paid_amount' => 0,
            'status' => InvoiceStatus::Draft,
            'due_at' => fake()->dateTimeBetween('now', '+3 weeks'),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Set the invoice's status, adjusting paid_amount to match.
     */
    public function withStatus(InvoiceStatus $status): static
    {
        return $this->state(function (array $attributes) use ($status) {
            $amount = $attributes['amount'] ?? fake()->randomFloat(2, 5000, 500000);

            return [
                'status' => $status,
                'paid_amount' => match ($status) {
                    InvoiceStatus::Paid => $amount,
                    InvoiceStatus::PartiallyPaid => round($amount * 0.5, 2),
                    default => 0,
                },
            ];
        });
    }

    /**
     * Set the invoice as overdue (past due date, not fully paid).
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Sent,
            'due_at' => fake()->dateTimeBetween('-3 weeks', '-1 days'),
        ]);
    }
}
