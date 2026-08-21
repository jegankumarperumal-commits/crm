<?php

use App\Concerns\InvoiceValidationRules;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use InvoiceValidationRules;

    public ?int $invoiceId = null;

    public string $client_id = '';

    public string $amount = '';

    public string $paid_amount = '0';

    public string $status = '';

    public ?string $due_at = null;

    #[Computed]
    public function isEditing(): bool
    {
        return $this->invoiceId !== null;
    }

    #[Computed]
    public function clients(): Collection
    {
        return Client::query()->with('lead')->get()->sortBy('lead.name');
    }

    #[On('open-invoice-form')]
    public function open(?int $invoiceId = null, ?int $clientId = null): void
    {
        $this->reset(['invoiceId', 'client_id', 'amount', 'paid_amount', 'status', 'due_at']);
        $this->resetErrorBag();

        $this->status = InvoiceStatus::Draft->value;
        $this->client_id = $clientId !== null ? (string) $clientId : '';

        if ($invoiceId !== null) {
            $invoice = Invoice::findOrFail($invoiceId);

            $this->invoiceId = $invoice->id;
            $this->client_id = (string) $invoice->client_id;
            $this->amount = (string) $invoice->amount;
            $this->paid_amount = (string) $invoice->paid_amount;
            $this->status = $invoice->status->value;
            $this->due_at = $invoice->due_at?->format('Y-m-d');
        }

        $this->dispatch('show-bs-modal', id: 'invoice-form-modal');
    }

    public function save(): void
    {
        if ($this->due_at === '') {
            $this->due_at = null;
        }

        $validated = $this->validate([
            ...$this->invoiceRules(),
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:amount'],
        ]);

        if ($this->invoiceId) {
            Invoice::findOrFail($this->invoiceId)->update($validated);
        } else {
            Invoice::create([...$validated, 'created_by' => Auth::id()]);
        }

        $this->dispatch('hide-bs-modal', id: 'invoice-form-modal');

        $this->dispatch('invoice-saved');

        $this->dispatch('toast', variant: 'success', text: $this->invoiceId ? 'Invoice updated.' : 'Invoice created.');
    }
}; ?>

<div wire:ignore.self class="modal fade" id="invoice-form-modal" tabindex="-1" aria-labelledby="invoice-form-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="save">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoice-form-modal-title">{{ $this->isEditing ? __('Edit invoice') : __('New invoice') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="invoice-client">{{ __('Client') }}</label>
                        <select wire:model="client_id" id="invoice-client" class="form-select" required>
                            <option value="">{{ __('Select a client…') }}</option>
                            @foreach ($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->lead->name }}{{ $client->lead->company ? " ({$client->lead->company})" : '' }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="invoice-amount">{{ __('Amount (₹)') }}</label>
                            <input wire:model="amount" type="number" step="0.01" min="0" id="invoice-amount" class="form-control" required>
                            @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="invoice-paid-amount">{{ __('Paid so far (₹)') }}</label>
                            <input wire:model="paid_amount" type="number" step="0.01" min="0" id="invoice-paid-amount" class="form-control" required>
                            @error('paid_amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="invoice-status">{{ __('Status') }}</label>
                            <select wire:model="status" id="invoice-status" class="form-select">
                                @foreach (InvoiceStatus::cases() as $statusOption)
                                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="invoice-due-at">{{ __('Due date') }}</label>
                            <input wire:model="due_at" type="date" id="invoice-due-at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $this->isEditing ? __('Save changes') : __('Create invoice') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
