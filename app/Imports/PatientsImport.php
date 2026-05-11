<?php

namespace App\Imports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PatientsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
{
    if (empty($row['name'])) {
        return null;
    }

    // Normalize Aadhaar field (Indian Aadhaar is 12 digits; CSV may use dashes/spaces)
    $adhaar = $row['adhaar_no'] ?? $row['adahar_no'] ?? null;
    if ($adhaar !== null && $adhaar !== '') {
        $digits = preg_replace('/\D/', '', (string) $adhaar);
        $adhaar = strlen($digits) === 12 ? $digits : null;
    }

    return new Patient([
        'date' => isset($row['date']) 
            ? \Carbon\Carbon::instance(ExcelDate::excelToDateTimeObject($row['date']))->format('Y-m-d')
            : null,
        'uhid_no' => $row['uhid_no'] ?? null,
        'file_no' => $row['file_no'] ?? null,
        'adhaar_no' => $adhaar,
        'name' => $row['name'],
        'age' => $row['age'] ?? null,
        'sex' => $row['sex'] ?? null,
        'visit_follow_up' => $row['visit_follow_up'] ?? null,
        'address' => $row['address'] ?? null,
        'diagnosis' => $row['diagnosis'] ?? null,
        'investigation' => $row['investigation'] ?? null,
        'medicines' => $row['medicines'] ?? null,
        'h_o_tb_other_investigations' => $row['h_o_tb_other_investigations'] ?? null,
        'tb_gold' => $row['tb_gold'] ?? null,
        'montoux_test' => $row['montoux_test'] ?? null,
        'cbc_esr' => $row['cbc_esr'] ?? null,
        'xray_cect_hrct' => $row['xray_cect_hrct'] ?? null,
        'gene_xpert' => $row['gene_xpert'] ?? null,
        'usg_wa_ct_scan' => $row['usg_wa_ct_scan'] ?? null,
        'cd4_cd8' => $row['cd4_cd8'] ?? null,
        'ige' => $row['ige'] ?? null,
        'vit_d' => $row['vit_d'] ?? null,
        'lft' => $row['lft'] ?? null,
        'rft' => $row['rft'] ?? null,
        'il2' => $row['il2'] ?? null,
        'contact_details' => $row['contact_details'] ?? null,
        'ltbi_qs_10' => $row['ltbi_qs_10'] ?? null,
        'ltbi_qs_09' => $row['ltbi_qs_09'] ?? null,
        'refer' => $row['refer'] ?? null,
    ]);
    }
}