<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsImportTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [
                'Jane Doe',
                'Acme Corp',
                'jane@acme.com',
                '+1 555 0100',
                'WhatsApp',
                'New',
                'Interested in the enterprise plan.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Name', 'Company', 'Email', 'Phone', 'Source', 'Status', 'Notes'];
    }
}
