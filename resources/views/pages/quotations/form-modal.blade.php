<?php

use App\Concerns\QuotationValidationRules;
use App\Enums\QuotationStatus;
use App\Models\Lead;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use QuotationValidationRules;

    public ?int $quotationId = null;

    public string $lead_id = '';

    public string $amount = '';

    public string $status = '';

    public ?string $sent_at = null;

    public ?string $notes = null;

    #[Computed]
    public function isEditing(): bool
    {
        return $this->quotationId !== null;
    }

    #[Computed]
    public function leads(): Collection
    {
        return Lead::query()->orderBy('name')->get();
    }

    #[On('open-quotation-form')]
    public function open(?int $quotationId = null): void
    {
        $this->reset(['quotationId', 'lead_id', 'amount', 'status', 'sent_at', 'notes']);
        $this->resetErrorBag();

        $this->status = QuotationStatus::Required->value;

        if ($quotationId !== null) {
            $quotation = Quotation::findOrFail($quotationId);

            $this->quotationId = $quotation->id;
            $this->lead_id = (string) $quotation->lead_id;
            $this->amount = (string) $quotation->amount;
            $this->status = $quotation->status->value;
            $this->sent_at = $quotation->sent_at?->format('Y-m-d');
            $this->notes = $quotation->notes;
        }

        $this->dispatch('show-bs-modal', id: 'quotation-form-modal');
    }

    public function save(): void
    {
        foreach (['sent_at', 'notes'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        $validated = $this->validate($this->quotationRules());

        if ($this->quotationId) {
            Quotation::findOrFail($this->quotationId)->update($validated);
        } else {
            Quotation::create([...$validated, 'created_by' => Auth::id()]);
        }

        $this->dispatch('hide-bs-modal', id: 'quotation-form-modal');

        $this->dispatch('quotation-saved');

        $this->dispatch('toast', variant: 'success', text: $this->quotationId ? 'Quotation updated.' : 'Quotation created.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="quotation-form-modal" tabindex="-1" aria-labelledby="quotation-form-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="quotation-form-modal-title">{{ $this->isEditing ? __('Edit quotation') : __('New quotation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="quotation-lead">{{ __('Lead') }}</label>
                        <select wire:model="lead_id" id="quotation-lead" class="form-select" required>
                            <option value="">{{ __('Select a lead…') }}</option>
                            @foreach ($this->leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? " ({$lead->company})" : '' }}</option>
                            @endforeach
                        </select>
                        @error('lead_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="quotation-amount">{{ __('Amount (₹)') }}</label>
                            <input wire:model="amount" type="number" step="0.01" min="0" id="quotation-amount" class="form-control" required>
                            @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="quotation-status">{{ __('Status') }}</label>
                            <select wire:model="status" id="quotation-status" class="form-select">
                                @foreach (QuotationStatus::cases() as $statusOption)
                                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="quotation-notes">{{ __('Notes') }}</label>
                        <textarea wire:model="notes" id="quotation-notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $this->isEditing ? __('Save changes') : __('Create quotation') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
