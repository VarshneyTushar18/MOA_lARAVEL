<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $contacts)
    {
    }

    public function collection()
    {
        return $this->contacts;
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Message', 'Submitted At'];
    }

    public function map($contact): array
    {
        return [
            $contact->id,
            $contact->full_name,
            $contact->email,
            $contact->message,
            optional($contact->created_at)->format('d-m-Y h:i A'),
        ];
    }
}
