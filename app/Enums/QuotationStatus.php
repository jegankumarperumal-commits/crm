<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Required = 'required';
    case Prepared = 'prepared';
    case Sent = 'sent';
    case Negotiation = 'negotiation';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Required => 'Required',
            self::Prepared => 'Prepared',
            self::Sent => 'Sent',
            self::Negotiation => 'Negotiation',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Required, self::Prepared => 'bg-secondary',
            self::Sent => 'bg-info',
            self::Negotiation => 'bg-warning',
            self::Accepted => 'bg-success',
            self::Rejected => 'bg-danger',
        };
    }
}
