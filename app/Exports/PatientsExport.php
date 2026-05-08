<?php

namespace App\Exports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\FromCollection;

class PatientsExport implements FromCollection
{
    protected $uhid;

    public function __construct($uhid)
    {
        $this->uhid = $uhid;
    }

    public function collection()
    {
        return Patient::where('uhid_no', $this->uhid)->get();
    }
}
