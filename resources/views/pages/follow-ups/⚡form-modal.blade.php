<?php

use App\Concerns\FollowUpValidationRules;
use App\Enums\FollowUpAction;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use FollowUpValidationRules;

    public ?int $followUpId = null;

    public string $lead_id = '';

    public string $next_action = '';

    public string $due_at = '';

    public ?string $notes = null;

    #[Computed]
    public function isEditing(): bool
    {
        return $this->followUpId !== null;
    }

    #[Computed]
    public function leads(): Collection
    {
        return Lead::query()->orderBy('name')->get();
    }

    #[On('open-follow-up-form')]
    public function open(?int $followUpId = null): void
    {
        $this->reset(['followUpId', 'lead_id', 'next_action', 'due_at', 'notes']);
        $this->resetErrorBag();

        $this->next_action = FollowUpAction::FollowUp->value;
        $this->due_at = now()->addDay()->format('Y-m-d\TH:i');

        if ($followUpId !== null) {
            $followUp = FollowUp::findOrFail($followUpId);

            $this->followUpId = $followUp->id;
            $this->lead_id = (string) $followUp->lead_id;
            $this->next_action = $followUp->next_action->value;
            $this->due_at = $followUp->due_at->format('Y-m-d\TH:i');
            $this->notes = $followUp->notes;
        }

        $this->dispatch('show-bs-modal', id: 'follow-up-form-modal');
    }

    public function save(): void
    {
        $validated = $this->validate($this->followUpRules());

        if ($this->followUpId) {
            FollowUp::findOrFail($this->followUpId)->update($validated);
        } else {
            FollowUp::create([...$validated, 'created_by' => Auth::id()]);
        }

        $this->dispatch('hide-bs-modal', id: 'follow-up-form-modal');

        $this->dispatch('follow-up-saved');

        $this->dispatch('toast', variant: 'success', text: $this->followUpId ? 'Follow-up updated.' : 'Follow-up created.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="follow-up-form-modal" tabindex="-1" aria-labelledby="follow-up-form-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="follow-up-form-modal-title">{{ $this->isEditing ? __('Edit follow-up') : __('Add follow-up') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="follow-up-lead">{{ __('Lead') }}</label>
                        <select wire:model="lead_id" id="follow-up-lead" class="form-select" required>
                            <option value="">{{ __('Select a lead…') }}</option>
                            @foreach ($this->leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? " ({$lead->company})" : '' }}</option>
                            @endforeach
                        </select>
                        @error('lead_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="follow-up-next-action">{{ __('Next action') }}</label>
                            <select wire:model="next_action" id="follow-up-next-action" class="form-select">
                                @foreach (FollowUpAction::cases() as $actionOption)
                                    <option value="{{ $actionOption->value }}">{{ $actionOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="follow-up-due-at">{{ __('Due') }}</label>
                            <input wire:model="due_at" type="datetime-local" id="follow-up-due-at" class="form-control" required>
                            @error('due_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="follow-up-notes">{{ __('Notes') }}</label>
                        <textarea wire:model="notes" id="follow-up-notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $this->isEditing ? __('Save changes') : __('Create follow-up') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
