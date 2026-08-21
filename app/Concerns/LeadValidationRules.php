<?php

namespace App\Concerns;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait LeadValidationRules
{
    /**
     * Get the validation rules used to validate a lead.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function leadRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'source' => ['required', Rule::enum(LeadSource::class)],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Hard-block duplicate check: fails validation if another lead already
     * has this phone or email. $ignoreLeadId excludes the lead being edited.
     */
    protected function assertLeadIsNotDuplicate(?string $email, ?string $phone, ?int $ignoreLeadId = null): void
    {
        if (Str::of((string) $email)->trim()->isEmpty() && Str::of((string) $phone)->trim()->isEmpty()) {
            return;
        }

        $duplicate = Lead::query()
            ->when($ignoreLeadId, fn ($query) => $query->whereKeyNot($ignoreLeadId))
            ->where(function ($query) use ($email, $phone) {
                $query->when(filled($email), fn ($q) => $q->orWhere('email', $email))
                    ->when(filled($phone), fn ($q) => $q->orWhere('phone', $phone));
            })
            ->first();

        if (! $duplicate) {
            return;
        }

        $field = filled($email) && $duplicate->email === $email ? 'email' : 'phone';

        throw ValidationException::withMessages([
            $field => "This {$field} is already used by lead \"{$duplicate->name}\" (#{$duplicate->id}).",
        ]);
    }
}
