<?php

namespace App\Concerns;

use App\Enums\FollowUpAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait FollowUpValidationRules
{
    /**
     * Get the validation rules used to validate a follow-up.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function followUpRules(): array
    {
        return [
            'lead_id' => ['required', Rule::exists('leads', 'id')],
            'next_action' => ['required', Rule::enum(FollowUpAction::class)],
            'due_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
