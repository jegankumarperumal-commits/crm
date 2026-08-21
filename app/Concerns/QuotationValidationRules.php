<?php

namespace App\Concerns;

use App\Enums\QuotationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait QuotationValidationRules
{
    /**
     * Get the validation rules used to validate a quotation.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function quotationRules(): array
    {
        return [
            'lead_id' => ['required', Rule::exists('leads', 'id')],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(QuotationStatus::class)],
            'sent_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
