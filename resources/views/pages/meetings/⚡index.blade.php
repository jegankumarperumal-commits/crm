<?php

use App\Concerns\MeetingValidationRules;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Meetings')] #[Layout('layouts::coolapp')] class extends Component
{
    use MeetingValidationRules, WithPagination;

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $dateFilter = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function meetings(): LengthAwarePaginator
    {
        return Meeting::query()
            ->with(['lead'])
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->dateFilter === 'today', fn ($query) => $query->whereDate('scheduled_at', today()))
            ->orderBy('scheduled_at')
            ->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'today' => Meeting::query()->whereDate('scheduled_at', today())->count(),
            'upcoming' => Meeting::query()->where('status', MeetingStatus::Scheduled)->whereDate('scheduled_at', '>', today())->count(),
            'missed' => Meeting::query()->where('status', MeetingStatus::Scheduled)->where('scheduled_at', '<', now())->count(),
            'completed' => Meeting::query()->where('status', MeetingStatus::Completed)->where('scheduled_at', '>=', now()->startOfWeek())->count(),
        ];
    }

    public function openCreateForm(): void
    {
        $this->dispatch('open-meeting-form');
    }

    public function openEditForm(int $meetingId): void
    {
        $this->dispatch('open-meeting-form', meetingId: $meetingId);
    }

    public function updateStatus(int $meetingId, string $status): void
    {
        try {
            Validator::make(['status' => $status], ['status' => $this->meetingStatusRules()])->validate();
        } catch (ValidationException $e) {
            unset($this->meetings);
            $this->dispatch('toast', variant: 'error', text: collect($e->errors())->flatten()->first());

            return;
        }

        Meeting::findOrFail($meetingId)->update(['status' => MeetingStatus::from($status)]);

        unset($this->meetings, $this->stats);

        $this->dispatch('toast', variant: 'success', text: 'Meeting outcome updated.');
    }

    #[On('meeting-saved')]
    public function onMeetingSaved(): void
    {
        unset($this->meetings, $this->stats);
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Meetings') }}</h1>
        <p class="subtitle">{{ __('Schedule, track, and follow up on client meetings.') }}</p>
    </div>
    <div class="page-header__actions">
        <button type="button" class="btn btn-primary" wire:click="openCreateForm">
            {{ __('Schedule Meeting') }}
        </button>
    </div>
</div>

<div class="row row-tight">
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __("Today's Meetings") }}</p>
                <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-calendar-day" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['today'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Upcoming Meetings') }}</p>
                <span class="stat-card__icon stat-card__icon--c3"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['upcoming'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Missed Meetings') }}</p>
                <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['missed'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Completed This Week') }}</p>
                <span class="stat-card__icon stat-card__icon--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['completed'] }}</p>
        </article>
    </div>
</div>

<section class="m-card" style="margin-top:16px;">
    <div style="display:flex; flex-wrap:wrap; gap:12px; padding:20px 25px 0;">
        <select class="form-select" style="max-width:200px;" wire:model.live="statusFilter" aria-label="{{ __('Filter by status') }}">
            <option value="">{{ __('All statuses') }}</option>
            @foreach (MeetingStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>

        <select class="form-select" style="max-width:160px;" wire:model.live="dateFilter" aria-label="{{ __('Filter by date') }}">
            <option value="">{{ __('Any date') }}</option>
            <option value="today">{{ __('Today') }}</option>
        </select>
    </div>

    <table class="m-table" style="margin-top:20px;">
        <thead>
            <tr>
                <th scope="col">{{ __('Lead / Company') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('When') }}</th>
                <th scope="col">{{ __('Location / Link') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->meetings as $meeting)
                <tr wire:key="meeting-{{ $meeting->id }}">
                    <td>
                        <div>{{ $meeting->lead->name }}</div>
                        @if ($meeting->lead->company)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $meeting->lead->company }}</div>
                        @endif
                    </td>
                    <td>{{ $meeting->type->label() }}</td>
                    <td>{{ $meeting->scheduled_at->format('M j, Y g:i A') }}</td>
                    <td>{{ $meeting->location ?? '—' }}</td>
                    <td>
                        @if ($meeting->isMissed())
                            <span class="badge bg-danger">{{ __('Missed') }}</span>
                        @else
                            <span class="badge {{ $meeting->status->badgeClass() }}">{{ $meeting->status->label() }}</span>
                        @endif
                    </td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#view-meeting-{{ $meeting->id }}">{{ __('View') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditForm({{ $meeting->id }})">{{ __('Edit') }}</button>
                    </td>
                </tr>

                <div class="modal fade" id="view-meeting-{{ $meeting->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Meeting with :name', ['name' => $meeting->lead->name]) }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ __('Lead') }}</dt>
                                    <dd class="col-sm-8">{{ $meeting->lead->name }}{{ $meeting->lead->company ? " ({$meeting->lead->company})" : '' }}</dd>

                                    <dt class="col-sm-4">{{ __('Type') }}</dt>
                                    <dd class="col-sm-8">{{ $meeting->type->label() }}</dd>

                                    <dt class="col-sm-4">{{ __('When') }}</dt>
                                    <dd class="col-sm-8">{{ $meeting->scheduled_at->format('M j, Y g:i A') }}</dd>

                                    <dt class="col-sm-4">{{ __('Location / Link') }}</dt>
                                    <dd class="col-sm-8">{{ $meeting->location ?? '—' }}</dd>

                                    <dt class="col-sm-4">{{ __('Status') }}</dt>
                                    <dd class="col-sm-8">{{ $meeting->status->label() }}</dd>

                                    <dt class="col-sm-4">{{ __('Agenda') }}</dt>
                                    <dd class="col-sm-8">{{ $meeting->agenda ?: '—' }}</dd>
                                </dl>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--m-text-muted); padding:32px 0;">
                        {{ __('No meetings yet.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->meetings->links() }}
    </div>
</section>

<livewire:pages::meetings.form-modal />
</div>
