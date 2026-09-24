<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CurePatientsExport implements FromCollection, WithHeadings, WithMapping
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
        return ['ID', 'LTBI No', 'CC No', 'TR No', 'Access Code', 'File', 'Uploaded At'];
    }

    public function map($record): array
    {
        return [
            $record->id,
            $record->ltbi_no,
            $record->cc_no,
            $record->tr_no,
            $record->access_code,
            basename((string) $record->file_path),
            optional($record->created_at)->format('d-m-Y h:i A'),
        ];
    }
}
