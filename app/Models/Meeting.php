<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use Carbon\CarbonImmutable;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lead_id
 * @property MeetingType $type
 * @property MeetingStatus $status
 * @property Carbon $scheduled_at
 * @property string|null $location
 * @property string|null $agenda
 * @property string|null $notes
 * @property int|null $assigned_to
 * @property int $created_by
 * @property CarbonImmutable|null $upcoming_alert_sent_at
 * @property CarbonImmutable|null $missed_alert_sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lead_id', 'type', 'status', 'scheduled_at', 'location', 'agenda', 'notes', 'assigned_to', 'created_by'])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MeetingType::class,
            'status' => MeetingStatus::class,
            'scheduled_at' => 'datetime',
            'upcoming_alert_sent_at' => 'datetime',
            'missed_alert_sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
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

    public function isMissed(): bool
    {
        return $this->status === MeetingStatus::Scheduled && $this->scheduled_at->isPast();
    }
}
