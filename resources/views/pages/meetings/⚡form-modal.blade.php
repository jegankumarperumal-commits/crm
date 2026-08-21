<?php

use App\Concerns\MeetingValidationRules;
use App\Enums\MeetingType;
use App\Models\Lead;
use App\Models\Meeting;
use App\Services\MeetingAlertNotifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use MeetingValidationRules;

    public ?int $meetingId = null;

    public string $lead_id = '';

    public string $type = '';

    public string $scheduled_at = '';

    public ?string $location = null;

    public ?string $agenda = null;

    #[Computed]
    public function isEditing(): bool
    {
        return $this->meetingId !== null;
    }

    #[Computed]
    public function leads(): Collection
    {
        return Lead::query()->orderBy('name')->get();
    }

    #[On('open-meeting-form')]
    public function open(?int $meetingId = null): void
    {
        $this->reset(['meetingId', 'lead_id', 'type', 'scheduled_at', 'location', 'agenda']);
        $this->resetErrorBag();

        $this->type = MeetingType::VideoCall->value;
        $this->scheduled_at = now()->addDay()->format('Y-m-d\TH:i');

        if ($meetingId !== null) {
            $meeting = Meeting::findOrFail($meetingId);

            $this->meetingId = $meeting->id;
            $this->lead_id = (string) $meeting->lead_id;
            $this->type = $meeting->type->value;
            $this->scheduled_at = $meeting->scheduled_at->format('Y-m-d\TH:i');
            $this->location = $meeting->location;
            $this->agenda = $meeting->agenda;
        }

        $this->dispatch('show-bs-modal', id: 'meeting-form-modal');
    }

    public function save(): void
    {
        foreach (['location', 'agenda'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        $validated = $this->validate($this->meetingRules());

        $isNew = ! $this->meetingId;

        if ($this->meetingId) {
            $meeting = Meeting::findOrFail($this->meetingId);
            $meeting->fill($validated);
            // Reschedule/edit invalidates any alert already sent for the old plan.
            $meeting->upcoming_alert_sent_at = null;
            $meeting->missed_alert_sent_at = null;
            $meeting->save();
        } else {
            $meeting = Meeting::create([...$validated, 'created_by' => Auth::id()]);
        }

        app(MeetingAlertNotifier::class)->send($meeting, $isNew ? 'created' : 'updated');

        $this->dispatch('hide-bs-modal', id: 'meeting-form-modal');

        $this->dispatch('meeting-saved');

        $this->dispatch('toast', variant: 'success', text: $isNew ? 'Meeting scheduled.' : 'Meeting updated.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="meeting-form-modal" tabindex="-1" aria-labelledby="meeting-form-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="meeting-form-modal-title">{{ $this->isEditing ? __('Edit meeting') : __('Schedule meeting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="meeting-lead">{{ __('Lead') }}</label>
                        <select wire:model="lead_id" id="meeting-lead" class="form-select" required>
                            <option value="">{{ __('Select a lead…') }}</option>
                            @foreach ($this->leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? " ({$lead->company})" : '' }}</option>
                            @endforeach
                        </select>
                        @error('lead_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="meeting-type">{{ __('Type') }}</label>
                            <select wire:model="type" id="meeting-type" class="form-select">
                                @foreach (MeetingType::cases() as $typeOption)
                                    <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="meeting-scheduled-at">{{ __('Date & time') }}</label>
                            <input wire:model="scheduled_at" type="datetime-local" id="meeting-scheduled-at" class="form-control" required>
                            @error('scheduled_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="meeting-location">{{ __('Location / Link') }}</label>
                        <input wire:model="location" type="text" id="meeting-location" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="meeting-agenda">{{ __('Agenda') }}</label>
                        <textarea wire:model="agenda" id="meeting-agenda" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $this->isEditing ? __('Save changes') : __('Schedule meeting') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
