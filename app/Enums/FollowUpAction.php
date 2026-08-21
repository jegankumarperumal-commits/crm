<?php

namespace App\Enums;

enum FollowUpAction: string
{
    case Call = 'call';
    case WhatsApp = 'whatsapp';
    case SendQuotation = 'send_quotation';
    case ScheduleMeeting = 'schedule_meeting';
    case FollowUp = 'follow_up';
    case WaitForClient = 'wait_for_client';
    case CloseLead = 'close_lead';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::WhatsApp => 'WhatsApp',
            self::SendQuotation => 'Send Quotation',
            self::ScheduleMeeting => 'Schedule Meeting',
            self::FollowUp => 'Follow-up',
            self::WaitForClient => 'Wait for Client',
            self::CloseLead => 'Close Lead',
        };
    }
}
