<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary',
            self::Sent => 'bg-info',
            self::PartiallyPaid => 'bg-warning',
            self::Paid => 'bg-success',
        };
    }
}
