<?php

namespace App\Models;

use App\Enums\FollowUpAction;
use Database\Factories\FollowUpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lead_id
 * @property FollowUpAction $next_action
 * @property Carbon $due_at
 * @property Carbon|null $completed_at
 * @property int|null $assigned_to
 * @property int $created_by
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lead_id', 'next_action', 'due_at', 'completed_at', 'assigned_to', 'created_by', 'notes'])]
class FollowUp extends Model
{
    /** @use HasFactory<FollowUpFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_action' => FollowUpAction::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
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

    public function isOverdue(): bool
    {
        return $this->completed_at === null && $this->due_at->isPast() && ! $this->due_at->isToday();
    }

    public function isDueToday(): bool
    {
        return $this->completed_at === null && $this->due_at->isToday();
    }
}
