<?php

use App\Concerns\LeadValidationRules;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use LeadValidationRules;

    public ?int $leadId = null;

    public string $name = '';

    public ?string $company = null;

    public ?string $email = null;

    public ?string $phone = null;

    public string $source = '';

    public string $status = '';

    public ?string $notes = null;

    #[Computed]
    public function isEditing(): bool
    {
        return $this->leadId !== null;
    }

    #[On('open-lead-form')]
    public function open(?int $leadId = null): void
    {
        $this->reset(['leadId', 'name', 'company', 'email', 'phone', 'source', 'status', 'notes']);
        $this->resetErrorBag();

        $this->source = LeadSource::ManualEntry->value;
        $this->status = LeadStatus::New->value;

        if ($leadId !== null) {
            $lead = Lead::findOrFail($leadId);

            $this->leadId = $lead->id;
            $this->name = $lead->name;
            $this->company = $lead->company;
            $this->email = $lead->email;
            $this->phone = $lead->phone;
            $this->source = $lead->source->value;
            $this->status = $lead->status->value;
            $this->notes = $lead->notes;
        }

        $this->dispatch('show-lead-form-modal');
    }

    public function save(): void
    {
        // Livewire properties don't pass through the ConvertEmptyStringsToNull
        // HTTP middleware, so blank optional fields stay "" instead of null.
        foreach (['company', 'email', 'phone', 'notes'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        $validated = $this->validate($this->leadRules());

        $this->assertLeadIsNotDuplicate($validated['email'], $validated['phone'], $this->leadId);

        if ($this->leadId) {
            Lead::findOrFail($this->leadId)->update($validated);
        } else {
            Lead::create([...$validated, 'created_by' => Auth::id()]);
        }

        $this->dispatch('hide-lead-form-modal');

        $this->dispatch('lead-saved');

        $this->dispatch('toast', variant: 'success', text: $this->leadId ? 'Lead updated.' : 'Lead created.');
    }
}; ?>

{{--
    Alpine-driven popup instead of a Bootstrap JS modal: Bootstrap tracks the
    open/close lifecycle via an async CSS-transition callback and a backdrop
    node appended outside this component's DOM, invisible to Livewire. A click
    landing mid-transition (or racing a Livewire re-render) can leave that
    backdrop stuck forever, silently blocking every click on the page. Alpine's
    `open` is a plain boolean Livewire's morphing already knows not to stomp on,
    so there's no separate cleanup step that can fail.
--}}
<div x-data="{ open: false }" x-on:show-lead-form-modal.window="open = true" x-on:hide-lead-form-modal.window="open = false" x-on:keydown.escape.window="open = false" x-effect="document.body.classList.toggle('modal-open', open)">
    <div id="lead-form-modal" class="modal fade show" x-show="open" x-cloak tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="lead-form-modal-title">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="lead-form-modal-title">{{ $this->isEditing ? __('Edit lead') : __('Add lead') }}</h5>
                    <button type="button" class="btn-close" x-on:click="open = false" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="lead-name">{{ __('Name') }}</label>
                        <input wire:model="name" type="text" id="lead-name" class="form-control" required autofocus>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="lead-company">{{ __('Company') }}</label>
                        <input wire:model="company" type="text" id="lead-company" class="form-control">
                        @error('company') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="lead-email">{{ __('Email') }}</label>
                            <input wire:model="email" type="email" id="lead-email" class="form-control">
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="lead-phone">{{ __('Phone') }}</label>
                            <input wire:model="phone" type="text" id="lead-phone" class="form-control">
                            @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="lead-source">{{ __('Source') }}</label>
                            <select wire:model="source" id="lead-source" class="form-select">
                                @foreach (LeadSource::cases() as $sourceOption)
                                    <option value="{{ $sourceOption->value }}">{{ $sourceOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="lead-status">{{ __('Status') }}</label>
                            <select wire:model="status" id="lead-status" class="form-select">
                                @foreach (LeadStatus::cases() as $statusOption)
                                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lead-notes">{{ __('Notes') }}</label>
                        <textarea wire:model="notes" id="lead-notes" class="form-control" rows="3"></textarea>
                        @error('notes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" x-on:click="open = false">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $this->isEditing ? __('Save changes') : __('Create lead') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    <div class="modal-backdrop fade show" x-show="open" x-cloak x-on:click="open = false"></div>
</div>
