<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait InvoiceValidationRules
{
    /**
     * Get the validation rules used to validate an invoice.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function invoiceRules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
