<?php

use App\Concerns\FollowUpValidationRules;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Follow-ups')] #[Layout('layouts::coolapp')] class extends Component
{
    use FollowUpValidationRules, WithPagination;

    #[Url]
    public string $statusFilter = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function followUps(): LengthAwarePaginator
    {
        return FollowUp::query()
            ->with(['lead'])
            ->when($this->statusFilter === 'overdue', fn ($query) => $query->whereNull('completed_at')->whereDate('due_at', '<', today()))
            ->when($this->statusFilter === 'today', fn ($query) => $query->whereNull('completed_at')->whereDate('due_at', today()))
            ->when($this->statusFilter === 'upcoming', fn ($query) => $query->whereNull('completed_at')->whereDate('due_at', '>', today()))
            ->when($this->statusFilter === 'completed', fn ($query) => $query->whereNotNull('completed_at'))
            ->orderBy('due_at')
            ->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'today' => FollowUp::query()->whereNull('completed_at')->whereDate('due_at', today())->count(),
            'overdue' => FollowUp::query()->whereNull('completed_at')->whereDate('due_at', '<', today())->count(),
            'upcoming' => FollowUp::query()->whereNull('completed_at')->whereDate('due_at', '>', today())->count(),
            'no_next_action' => Lead::query()->whereDoesntHave('followUps', fn ($query) => $query->whereNull('completed_at'))->count(),
        ];
    }

    public function openCreateForm(): void
    {
        $this->dispatch('open-follow-up-form');
    }

    public function openEditForm(int $followUpId): void
    {
        $this->dispatch('open-follow-up-form', followUpId: $followUpId);
    }

    public function markDone(int $followUpId): void
    {
        FollowUp::findOrFail($followUpId)->update(['completed_at' => Carbon::now()]);

        unset($this->followUps, $this->stats);

        $this->dispatch('toast', variant: 'success', text: 'Follow-up marked done.');
    }

    #[On('follow-up-saved')]
    public function onFollowUpSaved(): void
    {
        unset($this->followUps, $this->stats);
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Follow-ups') }}</h1>
        <p class="subtitle">{{ __('Every lead needs a next action — nothing should sit without one.') }}</p>
    </div>
    <div class="page-header__actions">
        <button type="button" class="btn btn-primary" wire:click="openCreateForm">
            {{ __('Add Follow-up') }}
        </button>
    </div>
</div>

<div class="row row-tight">
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __("Today's Follow-ups") }}</p>
                <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-calendar-day" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['today'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Overdue Follow-ups') }}</p>
                <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['overdue'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Upcoming Follow-ups') }}</p>
                <span class="stat-card__icon stat-card__icon--c3"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['upcoming'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('No Next Action') }}</p>
                <span class="stat-card__icon stat-card__icon--warning"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['no_next_action'] }}</p>
        </article>
    </div>
</div>

<section class="m-card" style="margin-top:16px;">
    <div style="display:flex; flex-wrap:wrap; gap:12px; padding:20px 25px 0;">
        <select class="form-select" style="max-width:200px;" wire:model.live="statusFilter" aria-label="{{ __('Filter by status') }}">
            <option value="">{{ __('All statuses') }}</option>
            <option value="overdue">{{ __('Overdue') }}</option>
            <option value="today">{{ __('Today') }}</option>
            <option value="upcoming">{{ __('Upcoming') }}</option>
            <option value="completed">{{ __('Completed') }}</option>
        </select>
    </div>

    <table class="m-table" style="margin-top:20px;">
        <thead>
            <tr>
                <th scope="col">{{ __('Lead / Company') }}</th>
                <th scope="col">{{ __('Next Action') }}</th>
                <th scope="col">{{ __('Due') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->followUps as $followUp)
                <tr wire:key="follow-up-{{ $followUp->id }}">
                    <td>
                        <div>{{ $followUp->lead->name }}</div>
                        @if ($followUp->lead->company)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $followUp->lead->company }}</div>
                        @endif
                    </td>
                    <td>{{ $followUp->next_action->label() }}</td>
                    <td>{{ $followUp->due_at->format('M j, Y g:i A') }}</td>
                    <td>
                        @if ($followUp->completed_at)
                            <span class="badge bg-success">{{ __('Done') }}</span>
                        @elseif ($followUp->isOverdue())
                            <span class="badge bg-danger">{{ __('Overdue') }}</span>
                        @elseif ($followUp->isDueToday())
                            <span class="badge bg-warning">{{ __('Today') }}</span>
                        @else
                            <span class="badge bg-info">{{ __('Upcoming') }}</span>
                        @endif
                    </td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#view-follow-up-{{ $followUp->id }}">{{ __('View') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditForm({{ $followUp->id }})">{{ __('Edit') }}</button>
                        @unless ($followUp->completed_at)
                            <button type="button" class="btn btn-sm btn-primary" wire:click="markDone({{ $followUp->id }})">{{ __('Mark done') }}</button>
                        @endunless
                    </td>
                </tr>

                <div class="modal fade" id="view-follow-up-{{ $followUp->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Follow-up for :name', ['name' => $followUp->lead->name]) }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ __('Lead') }}</dt>
                                    <dd class="col-sm-8">{{ $followUp->lead->name }}{{ $followUp->lead->company ? " ({$followUp->lead->company})" : '' }}</dd>

                                    <dt class="col-sm-4">{{ __('Next action') }}</dt>
                                    <dd class="col-sm-8">{{ $followUp->next_action->label() }}</dd>

                                    <dt class="col-sm-4">{{ __('Due') }}</dt>
                                    <dd class="col-sm-8">{{ $followUp->due_at->format('M j, Y g:i A') }}</dd>

                                    <dt class="col-sm-4">{{ __('Completed') }}</dt>
                                    <dd class="col-sm-8">{{ $followUp->completed_at?->format('M j, Y g:i A') ?? __('Not yet') }}</dd>

                                    <dt class="col-sm-4">{{ __('Notes') }}</dt>
                                    <dd class="col-sm-8">{{ $followUp->notes ?: __('—') }}</dd>
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
                    <td colspan="5" style="text-align:center; color:var(--m-text-muted); padding:32px 0;">
                        {{ __('No follow-ups yet.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->followUps->links() }}
    </div>
</section>

<livewire:pages::follow-ups.form-modal />
</div>
