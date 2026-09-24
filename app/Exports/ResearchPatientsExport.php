<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ResearchPatientsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $records)
    {
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return ['ID', 'LTBIRS No', 'File', 'Uploaded At'];
    }

    public function map($record): array
    {
        return [
            $record->id,
            $record->ltbirs_no,
            basename((string) $record->file_path),
            optional($record->created_at)->format('d-m-Y h:i A'),
        ];
    }
}
