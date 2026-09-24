<?php

namespace App\Exports;

use App\Models\Patient;
use App\Support\OpdPatientFields;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PatientsExport implements FromCollection, WithHeadings
{
    protected $uhid;

    public function __construct($uhid)
    {
        $this->uhid = $uhid;
    }

    public function headings(): array
    {
        return OpdPatientFields::exportHeadings();
    }

    public function collection()
    {
        return Patient::where('uhid_no', $this->uhid)
            ->orderBy('id')
            ->get()
            ->values()
            ->map(function (Patient $patient, int $index) {
                $row = [$index + 1];

                foreach (OpdPatientFields::definitions() as $field) {
                    $row[] = $patient->{$field['key']};
                }

                return $row;
            });
    }
}
