<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case FollowUp = 'follow_up';
    case Interested = 'interested';
    case MeetingScheduled = 'meeting_scheduled';
    case MeetingCompleted = 'meeting_completed';
    case QuotationSent = 'quotation_sent';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::FollowUp => 'Follow-up',
            self::Interested => 'Interested',
            self::MeetingScheduled => 'Meeting Scheduled',
            self::MeetingCompleted => 'Meeting Completed',
            self::QuotationSent => 'Quotation Sent',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    /**
     * The existing CoolAdmin `.badge.bg-*` class this status renders with.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::New, self::MeetingCompleted => 'bg-info',
            self::Contacted, self::Interested, self::MeetingScheduled => 'bg-primary',
            self::FollowUp, self::QuotationSent, self::Negotiation => 'bg-warning',
            self::Won => 'bg-success',
            self::Lost => 'bg-danger',
        };
    }
}
