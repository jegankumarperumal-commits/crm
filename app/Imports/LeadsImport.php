<?php

namespace App\Imports;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LeadsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    public int $importedCount = 0;

    /**
     * @var array<int, string>
     */
    private array $seenEmails = [];

    /**
     * @var array<int, string>
     */
    private array $seenPhones = [];

    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Lead
    {
        $email = filled($row['email'] ?? null) ? trim((string) $row['email']) : null;
        $phone = filled($row['phone'] ?? null) ? trim((string) $row['phone']) : null;

        $this->importedCount++;

        return new Lead([
            'name' => trim((string) $row['name']),
            'company' => filled($row['company'] ?? null) ? trim((string) $row['company']) : null,
            'email' => $email,
            'phone' => $phone,
            'source' => $this->resolveSource($row['source'] ?? null),
            'status' => $this->resolveStatus($row['status'] ?? null),
            'notes' => filled($row['notes'] ?? null) ? trim((string) $row['notes']) : null,
            'created_by' => Auth::id(),
        ]);
    }

    private function resolveSource(?string $value): LeadSource
    {
        $value = trim((string) $value);

        if ($value === '') {
            return LeadSource::BulkImport;
        }

        foreach (LeadSource::cases() as $source) {
            if (Str::lower($source->label()) === Str::lower($value) || $source->value === Str::lower(str_replace(' ', '_', $value))) {
                return $source;
            }
        }

        return LeadSource::BulkImport;
    }

    private function resolveStatus(?string $value): LeadStatus
    {
        $value = trim((string) $value);

        if ($value === '') {
            return LeadStatus::New;
        }

        foreach (LeadStatus::cases() as $status) {
            if (Str::lower($status->label()) === Str::lower($value) || $status->value === Str::lower(str_replace(' ', '_', $value))) {
                return $status;
            }
        }

        return LeadStatus::New;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * maatwebsite validates the whole batch of rows in one pass (rules are
     * applied as `*.attribute` wildcards against the full row set), so
     * `$validator->getData()` here is every row keyed by its index, not a
     * single row — duplicate checks (both against the database and against
     * earlier rows in the same file) have to loop over that batch themselves.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($validator->getData() as $index => $row) {
                $email = filled($row['email'] ?? null) ? Str::lower(trim((string) $row['email'])) : null;
                $phone = filled($row['phone'] ?? null) ? trim((string) $row['phone']) : null;

                if (! $email && ! $phone) {
                    continue;
                }

                if (($email && in_array($email, $this->seenEmails, true)) || ($phone && in_array($phone, $this->seenPhones, true))) {
                    $validator->errors()->add("{$index}.email", 'Duplicate of another row already processed in this file.');

                    continue;
                }

                $duplicate = Lead::query()
                    ->where(fn ($query) => $query
                        ->when($email, fn ($q) => $q->orWhereRaw('LOWER(email) = ?', [$email]))
                        ->when($phone, fn ($q) => $q->orWhere('phone', $phone)))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add("{$index}.email", 'A lead with this email or phone already exists.');

                    continue;
                }

                if ($email) {
                    $this->seenEmails[] = $email;
                }
                if ($phone) {
                    $this->seenPhones[] = $phone;
                }
            }
        });
    }
}
