<?php

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Clients & Invoices')] #[Layout('layouts::coolapp')] class extends Component {
    use WithPagination;

    #[Computed]
    public function clients(): LengthAwarePaginator
    {
        return Client::query()->with(['lead'])->withCount('invoices')->latest('converted_at')->paginate(10, pageName: 'clientsPage');
    }

    #[Computed]
    public function invoices(): LengthAwarePaginator
    {
        return Invoice::query()->with(['client.lead'])->latest()->paginate(10, pageName: 'invoicesPage');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'clients' => Client::query()->count(),
            'pending' => Invoice::query()->whereIn('status', [InvoiceStatus::Draft, InvoiceStatus::Sent, InvoiceStatus::PartiallyPaid])->count(),
            'overdue' => Invoice::query()->whereNotNull('due_at')->where('due_at', '<', today())->where('status', '!=', InvoiceStatus::Paid->value)->count(),
            'collected' => Invoice::query()->sum('paid_amount'),
        ];
    }

    public function openConvertForm(): void
    {
        $this->dispatch('open-client-form');
    }

    public function openCreateInvoiceForm(?int $clientId = null): void
    {
        $this->dispatch('open-invoice-form', clientId: $clientId);
    }

    public function openEditInvoiceForm(int $invoiceId): void
    {
        $this->dispatch('open-invoice-form', invoiceId: $invoiceId);
    }

    #[On('client-saved')]
    #[On('invoice-saved')]
    public function onSaved(): void
    {
        unset($this->clients, $this->invoices, $this->stats);
    }
}; ?>

<div>
<div class="page-header">
    <div>
        <h1>{{ __('Clients & Invoices') }}</h1>
        <p class="subtitle">{{ __('Convert won leads to clients and track invoices through to payment.') }}</p>
    </div>
    <div class="page-header__actions" style="display:flex; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" wire:click="openConvertForm">{{ __('Convert Lead') }}</button>
        <button type="button" class="btn btn-primary" wire:click="openCreateInvoiceForm">{{ __('New Invoice') }}</button>
    </div>
</div>

<div class="row row-tight">
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Total Clients') }}</p>
                <span class="stat-card__icon stat-card__icon--c2"><i class="fa-solid fa-user-check" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['clients'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Invoices Pending') }}</p>
                <span class="stat-card__icon stat-card__icon--warning"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['pending'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Overdue Invoices') }}</p>
                <span class="stat-card__icon stat-card__icon--danger"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">{{ $this->stats['overdue'] }}</p>
        </article>
    </div>
    <div class="col-sm-6 col-lg-3">
        <article class="stat-card">
            <div class="stat-card__head">
                <p class="stat-card__label">{{ __('Total Collected') }}</p>
                <span class="stat-card__icon stat-card__icon--success"><i class="fa-solid fa-sack-dollar" aria-hidden="true"></i></span>
            </div>
            <p class="stat-card__value">₹{{ number_format((float) $this->stats['collected'], 2) }}</p>
        </article>
    </div>
</div>

<section class="m-card" style="margin-top:16px;">
    <header class="m-card__header">
        <div>
            <h2 class="m-card__title">{{ __('Clients') }}</h2>
            <p class="m-card__subtitle">{{ __('Leads that have converted to paying clients.') }}</p>
        </div>
    </header>
    <table class="m-table">
        <thead>
            <tr>
                <th scope="col">{{ __('Client / Company') }}</th>
                <th scope="col">{{ __('Converted') }}</th>
                <th scope="col">{{ __('Invoices') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->clients as $client)
                <tr wire:key="client-{{ $client->id }}">
                    <td>
                        <div>{{ $client->lead->name }}</div>
                        @if ($client->lead->company)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $client->lead->company }}</div>
                        @endif
                    </td>
                    <td>{{ $client->converted_at->format('M j, Y') }}</td>
                    <td>{{ $client->invoices_count }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#view-client-{{ $client->id }}">{{ __('View') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openCreateInvoiceForm({{ $client->id }})">{{ __('New Invoice') }}</button>
                    </td>
                </tr>

                <div class="modal fade" id="view-client-{{ $client->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $client->lead->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ __('Company') }}</dt>
                                    <dd class="col-sm-8">{{ $client->lead->company ?? '—' }}</dd>

                                    <dt class="col-sm-4">{{ __('Converted') }}</dt>
                                    <dd class="col-sm-8">{{ $client->converted_at->format('M j, Y') }}</dd>

                                    <dt class="col-sm-4">{{ __('Invoices') }}</dt>
                                    <dd class="col-sm-8">{{ $client->invoices_count }}</dd>

                                    <dt class="col-sm-4">{{ __('Notes') }}</dt>
                                    <dd class="col-sm-8">{{ $client->notes ?: __('—') }}</dd>
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
                    <td colspan="4" style="text-align:center; color:var(--m-text-muted); padding:32px 0;">
                        {{ __('No clients yet — convert a Won lead to get started.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->clients->links() }}
    </div>
</section>

<section class="m-card" style="margin-top:16px;">
    <header class="m-card__header">
        <div>
            <h2 class="m-card__title">{{ __('Invoices') }}</h2>
            <p class="m-card__subtitle">{{ __('All invoices and their payment status.') }}</p>
        </div>
    </header>
    <table class="m-table">
        <thead>
            <tr>
                <th scope="col">{{ __('Client / Company') }}</th>
                <th scope="col">{{ __('Invoice #') }}</th>
                <th scope="col">{{ __('Amount') }}</th>
                <th scope="col">{{ __('Balance') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Due') }}</th>
                <th scope="col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->invoices as $invoice)
                <tr wire:key="invoice-{{ $invoice->id }}">
                    <td>
                        <div>{{ $invoice->client->lead->name }}</div>
                        @if ($invoice->client->lead->company)
                            <div style="color:var(--m-text-muted); font-size:12.5px;">{{ $invoice->client->lead->company }}</div>
                        @endif
                    </td>
                    <td>{{ $invoice->invoice_number ?? '—' }}</td>
                    <td>₹{{ number_format((float) $invoice->amount, 2) }}</td>
                    <td>₹{{ number_format((float) $invoice->balanceDue(), 2) }}</td>
                    <td>
                        @if ($invoice->isOverdue())
                            <span class="badge bg-danger">{{ __('Overdue') }}</span>
                        @else
                            <span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span>
                        @endif
                    </td>
                    <td>{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                    <td style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#view-invoice-{{ $invoice->id }}">{{ __('View') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openEditInvoiceForm({{ $invoice->id }})">{{ __('Edit') }}</button>
                    </td>
                </tr>

                <div class="modal fade" id="view-invoice-{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Invoice :number', ['number' => $invoice->invoice_number ?? '—']) }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">{{ __('Client') }}</dt>
                                    <dd class="col-sm-8">{{ $invoice->client->lead->name }}{{ $invoice->client->lead->company ? " ({$invoice->client->lead->company})" : '' }}</dd>

                                    <dt class="col-sm-4">{{ __('Amount') }}</dt>
                                    <dd class="col-sm-8">₹{{ number_format((float) $invoice->amount, 2) }}</dd>

                                    <dt class="col-sm-4">{{ __('Paid') }}</dt>
                                    <dd class="col-sm-8">₹{{ number_format((float) $invoice->paid_amount, 2) }}</dd>

                                    <dt class="col-sm-4">{{ __('Balance') }}</dt>
                                    <dd class="col-sm-8">₹{{ number_format((float) $invoice->balanceDue(), 2) }}</dd>

                                    <dt class="col-sm-4">{{ __('Status') }}</dt>
                                    <dd class="col-sm-8">{{ $invoice->isOverdue() ? __('Overdue') : $invoice->status->label() }}</dd>

                                    <dt class="col-sm-4">{{ __('Due') }}</dt>
                                    <dd class="col-sm-8">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</dd>
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
                    <td colspan="7" style="text-align:center; color:var(--m-text-muted); padding:32px 0;">
                        {{ __('No invoices yet.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="padding:16px 25px;">
        {{ $this->invoices->links() }}
    </div>
</section>

<livewire:pages::clients.client-form-modal />
<livewire:pages::clients.invoice-form-modal />
</div>
