<?php

use App\Concerns\LeadValidationRules;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
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

new #[Title('Leads')] #[Layout('layouts::coolapp')] class extends Component
{
    use LeadValidationRules, WithPagination;

    public string $search = '';

    /**
     * Also accepts the pseudo-value "active" (every status except Won/Lost),
     * so dashboard links can deep-link into the whole open pipeline.
     */
    #[Url]
    public string $statusFilter = '';

    public string $sourceFilter = '';

    public ?int $viewingLeadId = null;

    #[Computed]
    public function viewingLead(): ?Lead
    {
        return $this->viewingLeadId
            ? Lead::find($this->viewingLeadId)
            : null;
    }

    public function openView(int $leadId): void
    {
        $this->viewingLeadId = $leadId;

        $this->dispatch('show-view-lead-modal');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSourceFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function leads(): LengthAwarePaginator
    {
        return Lead::query()
            ->with(['createdBy'])
            ->when($this->search, fn ($query) => $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter === 'active', fn ($query) => $query->whereNotIn('status', [LeadStatus::Won, LeadStatus::Lost]))
            ->when($this->statusFilter && $this->statusFilter !== 'active', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->sourceFilter, fn ($query) => $query->where('source', $this->sourceFilter))
            ->latest()
            ->paginate(15);
    }

    public function updateStatus(int $leadId, string $status): void
    {
        try {
            Validator::make(['status' => $status], ['status' => $this->leadRules()['status']])->validate();
        } catch (ValidationException $e) {
            unset($this->leads);
            $this->dispatch('toast', variant: 'error', text: collect($e->errors())->flatten()->first());

            return;
        }

        Lead::findOrFail($leadId)->update(['status' => LeadStatus::from($status)]);

        $this->dispatch('toast', variant: 'success', text: 'Status updated.');
    }

    public function openCreateForm(): void
    {
        $this->dispatch('open-lead-form');
    }

    public function openImport(): void
    {
        $this->dispatch('open-lead-import');
    }

    public function openEditForm(int $leadId): void
    {
        $this->dispatch('open-lead-form', leadId: $leadId);
    }

    public function deleteLead(int $leadId): void
    {
        Lead::findOrFail($leadId)->delete();

        unset($this->leads);

        $this->dispatch('toast', variant: 'success', text: 'Lead deleted.');
    }

    #[On('lead-saved')]
    public function onLeadSaved(): void
    {
        //
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Leads') }}</h1>
        <p class="subtitle">{{ __('Capture and track leads through the pipeline.') }}</p>
    </div>
    <div class="page-header__actions">
        <button type="button" class="btn btn-outline-secondary" wire:click="openImport">
            {{ __('Import') }}
        </button>
        <button type="button" class="btn btn-primary" wire:click="openCreateForm">
            {{ __('Add Lead') }}
        </button>
    </div>
</div>

<section class="m-card">
    <div style="display:flex; flex-wrap:wrap; gap:12px; padding:20px 25px 0;">
        <input
            type="search"
            class="form-control"
            style="max-width:240px;"
            placeholder="{{ __('Search name, email, phone…') }}"
            wire:model.live.debounce.300ms="search"
            aria-label="{{ __('Search leads') }}"
        >

        <select class="form-select" style="max-width:200px;" wire:model.live="statusFilter" aria-label="{{ __('Filter by status') }}">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active">{{ __('Active (open pipeline)') }}</option>
            @foreach (LeadStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>

        <select class="form-select" style="max-width:200px;" wire:model.live="sourceFilter" aria-label="{{ __('Filter by source') }}">
            <option value="">{{ __('All sources') }}</option>
            @foreach (LeadSource::cases() as $source)
                <option value="{{ $source->value }}">{{ $source->label() }}</option>
            @endforeach
        </select>
    </div>

    <table class="m-table" style="margin-top:20px;">
        <thead>
            <tr>
                <th scope="col">{{ __('Name / Company') }}</th>
                <th scope="col">{{ __('Contact') }}</th>
                <th scope="col">{{ __('Source') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Created') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->leads as $lead)
                <tr wire:key="lead-{{ $lead->id }}">
                    <td>
                        <div>{{ $lead->name }}</div>
                        @if ($lead->company)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $lead->company }}</div>
                        @endif
                    </td>
                    <td>
                        @if ($lead->phone)
                            <div>{{ $lead->phone }}</div>
                        @endif
                        @if ($lead->email)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $lead->email }}</div>
                        @endif
                    </td>
                    <td>{{ $lead->source->label() }}</td>
                    <td><span class="badge {{ $lead->status->badgeClass() }}">{{ $lead->status->label() }}</span></td>
                    <td>{{ $lead->created_at?->format('M j, Y') }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openView({{ $lead->id }})">{{ __('View') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditForm({{ $lead->id }})">{{ __('Edit') }}</button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="deleteLead({{ $lead->id }})" wire:confirm="{{ __('Delete :name? This also removes its follow-ups, meetings, and quotations.', ['name' => $lead->name]) }}">{{ __('Delete') }}</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--m-text-muted); padding:32px 0;">
                        {{ __('No leads yet.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->leads->links() }}
    </div>
</section>

{{--
    Alpine-driven popup instead of a Bootstrap JS modal: Bootstrap tracks the
    open/close lifecycle via an async CSS-transition callback and a backdrop
    node appended outside this component's DOM, invisible to Livewire. A click
    landing mid-transition (or racing a Livewire re-render) can leave that
    backdrop stuck forever, silently blocking every click on the page. Alpine's
    `open` is a plain boolean Livewire's morphing already knows not to stomp on,
    so there's no separate cleanup step that can fail.
--}}
<div x-data="{ open: false }" x-on:show-view-lead-modal.window="open = true" x-on:keydown.escape.window="open = false" x-effect="document.body.classList.toggle('modal-open', open)">
    <div id="view-lead-modal" class="modal fade show" x-show="open" x-cloak tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="view-lead-modal-title">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view-lead-modal-title">{{ $this->viewingLead?->name }}</h5>
                    <button type="button" class="btn-close" x-on:click="open = false" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    @if ($this->viewingLead)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ __('Company') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->company ?? '—' }}</dd>

                            <dt class="col-sm-4">{{ __('Email') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->email ?? '—' }}</dd>

                            <dt class="col-sm-4">{{ __('Phone') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->phone ?? '—' }}</dd>

                            <dt class="col-sm-4">{{ __('Source') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->source->label() }}</dd>

                            <dt class="col-sm-4">{{ __('Status') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->status->label() }}</dd>

                            <dt class="col-sm-4">{{ __('Created') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->created_at?->format('M j, Y g:i A') }}</dd>

                            <dt class="col-sm-4">{{ __('Notes') }}</dt>
                            <dd class="col-sm-8">{{ $this->viewingLead->notes ?: __('—') }}</dd>
                        </dl>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" x-on:click="open = false">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="open" x-cloak x-on:click="open = false"></div>
</div>

<livewire:pages::leads.form-modal />
<livewire:pages::leads.import-modal />
</div>
