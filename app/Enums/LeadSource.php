<?php

namespace App\Enums;

enum LeadSource: string
{
    case VisitingCard = 'visiting_card';
    case MetaAds = 'meta_ads';
    case ManualEntry = 'manual_entry';
    case WebsiteEnquiry = 'website_enquiry';
    case WhatsApp = 'whatsapp';
    case PhoneCall = 'phone_call';
    case Referral = 'referral';
    case BulkImport = 'bulk_import';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::VisitingCard => 'Visiting Card',
            self::MetaAds => 'Meta Ads',
            self::ManualEntry => 'Manual Entry',
            self::WebsiteEnquiry => 'Website Enquiry',
            self::WhatsApp => 'WhatsApp',
            self::PhoneCall => 'Phone Call',
            self::Referral => 'Referral',
            self::BulkImport => 'Bulk Import',
            self::Other => 'Other',
        };
    }
}
