<?php

namespace App\Concerns;

use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait MeetingValidationRules
{
    /**
     * Get the validation rules used to validate a meeting.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function meetingRules(): array
    {
        return [
            'lead_id' => ['required', Rule::exists('leads', 'id')],
            'type' => ['required', Rule::enum(MeetingType::class)],
            'scheduled_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function meetingStatusRules(): array
    {
        return ['required', Rule::enum(MeetingStatus::class)];
    }
}
