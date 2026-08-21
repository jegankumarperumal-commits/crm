<?php

use App\Concerns\ClientValidationRules;
use App\Enums\LeadStatus;
use App\Models\Client;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use ClientValidationRules;

    public string $lead_id = '';

    public ?string $notes = null;

    #[Computed]
    public function convertibleLeads(): Collection
    {
        return Lead::query()
            ->where('status', LeadStatus::Won)
            ->whereDoesntHave('client')
            ->orderBy('name')
            ->get();
    }

    #[On('open-client-form')]
    public function open(): void
    {
        $this->reset(['lead_id', 'notes']);
        $this->resetErrorBag();

        $this->dispatch('show-bs-modal', id: 'client-form-modal');
    }

    public function save(): void
    {
        if ($this->notes === '') {
            $this->notes = null;
        }

        $validated = $this->validate($this->clientRules());

        Client::create([
            ...$validated,
            'converted_at' => today(),
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('hide-bs-modal', id: 'client-form-modal');

        $this->dispatch('client-saved');

        $this->dispatch('toast', variant: 'success', text: 'Lead converted to client.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="client-form-modal" tabindex="-1" aria-labelledby="client-form-modal-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="client-form-modal-title">{{ __('Convert lead to client') }}</h5>
                        <p class="m-card__subtitle" style="margin:2px 0 0;">{{ __('Only Won leads that are not already clients can be converted.') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="client-lead">{{ __('Won lead') }}</label>
                        <select wire:model="lead_id" id="client-lead" class="form-select" required>
                            <option value="">{{ __('Select a lead…') }}</option>
                            @foreach ($this->convertibleLeads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? " ({$lead->company})" : '' }}</option>
                            @endforeach
                        </select>
                        @error('lead_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="client-notes">{{ __('Notes') }}</label>
                        <textarea wire:model="notes" id="client-notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Convert to client') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
