<?php

namespace App\Enums;

enum MeetingStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Rescheduled = 'rescheduled';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Completed => 'Completed',
            self::Rescheduled => 'Rescheduled',
            self::NoShow => 'Client No Show',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-info',
            self::Completed => 'bg-success',
            self::Rescheduled => 'bg-warning',
            self::NoShow, self::Cancelled => 'bg-secondary',
        };
    }
}
