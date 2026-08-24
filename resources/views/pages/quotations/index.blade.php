<?php

use App\Concerns\QuotationValidationRules;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Quotations')] #[Layout('layouts::coolapp')] class extends Component
{
    use QuotationValidationRules, WithPagination;

    /**
     * Also accepts the pseudo-value "pending" (not yet Accepted/Rejected),
     * so dashboard links can deep-link into everything still in play.
     */
    #[Url]
    public string $statusFilter = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function quotations(): LengthAwarePaginator
    {
        return Quotation::query()
            ->with(['lead'])
            ->when($this->statusFilter === 'pending', fn ($query) => $query->whereNotIn('status', [QuotationStatus::Accepted, QuotationStatus::Rejected]))
            ->when($this->statusFilter && $this->statusFilter !== 'pending', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending' => Quotation::query()->whereIn('status', [QuotationStatus::Required, QuotationStatus::Prepared])->count(),
            'sent' => Quotation::query()->where('status', QuotationStatus::Sent)->count(),
            'accepted' => Quotation::query()->where('status', QuotationStatus::Accepted)->count(),
            'negotiation' => Quotation::query()->where('status', QuotationStatus::Negotiation)->count(),
        ];
    }

    public function openCreateForm(): void
    {
        $this->dispatch('open-quotation-form');
    }

    public function openEditForm(int $quotationId): void
    {
        $this->dispatch('open-quotation-form', quotationId: $quotationId);
    }

    public function updateStatus(int $quotationId, string $status): void
    {
        try {
            Validator::make(['status' => $status], ['status' => ['required', Rule::enum(QuotationStatus::class)]])->validate();
        } catch (ValidationException $e) {
            unset($this->quotations);
            $this->dispatch('toast', variant: 'error', text: collect($e->errors())->flatten()->first());

            return;
        }

        $quotation = Quotation::findOrFail($quotationId);
        $newStatus = QuotationStatus::from($status);

        $attributes = ['status' => $newStatus];

        if ($quotation->sent_at === null && $newStatus !== QuotationStatus::Required && $newStatus !== QuotationStatus::Prepared) {
            $attributes['sent_at'] = now();
        }

        $quotation->update($attributes);

        unset($this->quotations, $this->stats);

        $this->dispatch('toast', variant: 'success', text: 'Quotation status updated.');
    }

    public function deleteQuotation(int $quotationId): void
    {
        Quotation::findOrFail($quotationId)->delete();

        unset($this->quotations, $this->stats);

        $this->dispatch('toast', variant: 'success', text: 'Quotation deleted.');
    }

    #[On('quotation-saved')]
    public function onQuotationSaved(): void
    {
        unset($this->quotations, $this->stats);
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Quotations') }}</h1>
        <p class="subtitle">{{ __('Track quotations from request through to acceptance.') }}</p>
    </div>
    <div class="page-header__actions">
        <button type="button" class="btn btn-primary" wire:click="openCreateForm">
            {{ __('New Quotation') }}
        </button>
    </div>
</div>

<div class="row row-tight">
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Pending') }}</p>
                <span class="stat-card__icon stat-card__icon--warning"><i class="fa-solid fa-file-circle-question" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['pending'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Sent') }}</p>
                <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['sent'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Accepted') }}</p>
                <span class="stat-card__icon stat-card__icon--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['accepted'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('In Negotiation') }}</p>
                <span class="stat-card__icon stat-card__icon--c3"><i class="fa-solid fa-handshake" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['negotiation'] }}</p>
        </article>
    </div>
</div>

<section class="m-card" style="margin-top:16px;">
    <div style="display:flex; flex-wrap:wrap; gap:12px; padding:20px 25px 0;">
        <select class="form-select" style="max-width:200px;" wire:model.live="statusFilter" aria-label="{{ __('Filter by status') }}">
            <option value="">{{ __('All statuses') }}</option>
            <option value="pending">{{ __('Pending (not resolved)') }}</option>
            @foreach (QuotationStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    <table class="m-table" style="margin-top:20px;">
        <thead>
            <tr>
                <th scope="col">{{ __('Lead / Company') }}</th>
                <th scope="col">{{ __('Quotation #') }}</th>
                <th scope="col">{{ __('Amount') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Sent') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->quotations as $quotation)
                <tr wire:key="quotation-{{ $quotation->id }}">
                    <td>
                        <div>{{ $quotation->lead->name }}</div>
                        @if ($quotation->lead->company)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $quotation->lead->company }}</div>
                        @endif
                    </td>
                    <td>{{ $quotation->quotation_number ?? '—' }}</td>
                    <td>₹{{ number_format((float) $quotation->amount, 2) }}</td>
                    <td><span class="badge {{ $quotation->status->badgeClass() }}">{{ $quotation->status->label() }}</span></td>
                    <td>{{ $quotation->sent_at?->format('M j, Y') ?? '—' }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#view-quotation-{{ $quotation->id }}">{{ __('View') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditForm({{ $quotation->id }})">{{ __('Edit') }}</button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="deleteQuotation({{ $quotation->id }})" wire:confirm="{{ __('Delete the quotation for :name?', ['name' => $quotation->lead->name]) }}">{{ __('Delete') }}</button>
                    </td>
                </tr>

                <div class="modal fade" id="view-quotation-{{ $quotation->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Quotation :number', ['number' => $quotation->quotation_number ?? '—']) }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ __('Lead') }}</dt>
                                    <dd class="col-sm-8">{{ $quotation->lead->name }}{{ $quotation->lead->company ? " ({$quotation->lead->company})" : '' }}</dd>

                                    <dt class="col-sm-4">{{ __('Amount') }}</dt>
                                    <dd class="col-sm-8">₹{{ number_format((float) $quotation->amount, 2) }}</dd>

                                    <dt class="col-sm-4">{{ __('Status') }}</dt>
                                    <dd class="col-sm-8">{{ $quotation->status->label() }}</dd>

                                    <dt class="col-sm-4">{{ __('Sent') }}</dt>
                                    <dd class="col-sm-8">{{ $quotation->sent_at?->format('M j, Y') ?? '—' }}</dd>

                                    <dt class="col-sm-4">{{ __('Notes') }}</dt>
                                    <dd class="col-sm-8">{{ $quotation->notes ?: __('—') }}</dd>
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
                        {{ __('No quotations yet.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->quotations->links() }}
    </div>
</section>

<livewire:pages::quotations.form-modal />
</div>
