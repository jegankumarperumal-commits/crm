<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lead_id
 * @property string|null $quotation_number
 * @property string $amount
 * @property QuotationStatus $status
 * @property Carbon|null $sent_at
 * @property int|null $assigned_to
 * @property int $created_by
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lead_id', 'quotation_number', 'amount', 'status', 'sent_at', 'assigned_to', 'created_by', 'notes'])]
class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
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
            'status' => QuotationStatus::class,
            'sent_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Quotation $quotation) {
            if (blank($quotation->quotation_number)) {
                $quotation->updateQuietly(['quotation_number' => 'QT-'.(1000 + $quotation->id)]);
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
