<?php

namespace App\Exports;

use App\Models\Patient;
use App\Support\OpdPatientFields;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PatientsListExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $patients)
    {
    }

    public function headings(): array
    {
        return OpdPatientFields::exportHeadings();
    }

    public function collection()
    {
        return $this->patients->values()->map(function (Patient $patient, int $index) {
            $row = [$index + 1];

            foreach (OpdPatientFields::definitions() as $field) {
                $row[] = $patient->{$field['key']};
            }

            return $row;
        });
    }
}
