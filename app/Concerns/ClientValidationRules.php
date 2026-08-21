<?php

namespace App\Concerns;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ClientValidationRules
{
    /**
     * Get the validation rules used to convert a lead into a client.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function clientRules(): array
    {
        return [
            'lead_id' => [
                'required',
                Rule::exists('leads', 'id'),
                Rule::unique('clients', 'lead_id'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $lead = Lead::find($value);

                    if ($lead && $lead->status !== LeadStatus::Won) {
                        $fail('Only leads marked Won can be converted to a client.');
                    }
                },
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
