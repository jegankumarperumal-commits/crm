<?php

namespace App\Enums;

enum Permission: string
{
    case ManageLeads = 'manage_leads';
    case ManageFollowUps = 'manage_follow_ups';
    case ManageMeetings = 'manage_meetings';
    case ManageQuotations = 'manage_quotations';
    case ManageClientsInvoices = 'manage_clients_invoices';
    case ViewReports = 'view_reports';
    case ManageTeam = 'manage_team';

    public function label(): string
    {
        return match ($this) {
            self::ManageLeads => 'Manage Leads',
            self::ManageFollowUps => 'Manage Follow-ups',
            self::ManageMeetings => 'Manage Meetings',
            self::ManageQuotations => 'Manage Quotations',
            self::ManageClientsInvoices => 'Manage Clients & Invoices',
            self::ViewReports => 'View Reports',
            self::ManageTeam => 'Manage Team',
        };
    }
}
