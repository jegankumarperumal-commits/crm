<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property string|null $invoice_number
 * @property string $amount
 * @property string $paid_amount
 * @property InvoiceStatus $status
 * @property Carbon|null $due_at
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['client_id', 'invoice_number', 'amount', 'paid_amount', 'status', 'due_at', 'created_by'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'due_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Invoice $invoice) {
            if (blank($invoice->invoice_number)) {
                $invoice->updateQuietly(['invoice_number' => 'INV-'.(2000 + $invoice->id)]);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function balanceDue(): string
    {
        return bcsub($this->amount, $this->paid_amount, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status !== InvoiceStatus::Paid && $this->due_at !== null && $this->due_at->isPast();
    }
}
