<?php

namespace App\Imports;

use App\Models\Patient;
use App\Support\OpdPatientFields;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PatientsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        $row = $this->normalizeRow($row);

        if (empty($row['name'])) {
            return null;
        }

        $adhaar = isset($row['adhaar_no']) ? trim((string) $row['adhaar_no']) : null;
        if ($adhaar === '') {
            $adhaar = null;
        }

        $date = null;
        if (! empty($row['date'])) {
            if (is_numeric($row['date'])) {
                $date = \Carbon\Carbon::instance(ExcelDate::excelToDateTimeObject($row['date']))->format('Y-m-d');
            } else {
                $date = \Carbon\Carbon::parse($row['date'])->format('Y-m-d');
            }
        }

        $attributes = [
            'date' => $date,
            'file_no' => $row['file_no'] ?? null,
            'adhaar_no' => $adhaar,
        ];

        foreach (OpdPatientFields::definitions() as $field) {
            $key = $field['key'];
            if (array_key_exists($key, $attributes)) {
                continue;
            }

            $attributes[$key] = $row[$key] ?? null;
        }

        return new Patient($attributes);
    }

    private function normalizeRow(array $row): array
    {
        $aliases = [
            'adhaar_no' => ['adhaar_no', 'adhar_no', 'aadhar_no', 'aadhaar_no'],
            'uhid_no' => ['uhid_no', 'uhid_no', 'ltbi_no'],
            'visit_follow_up' => ['visit_follow_up', 'visitfollow_up', 'visit_follow_up'],
            'h_o_tb_other_investigations' => [
                'h_o_tb_other_investigations',
                'h_o_tuberculosis',
                'ho_tuberculosis',
                'history_of_tuberculosis',
            ],
            'cbc_esr' => ['cbc_esr', 'cbc_esr'],
            'xray_cect_hrct' => ['xray_cect_hrct', 'x_ray_cect_chest'],
            'gene_xpert' => [
                'gene_xpert',
                'gene_xpert_for_mtb_dna_pcr_mtb_sputum_for_afb_cbnaat_trunaat',
            ],
            'usg_wa_ct_scan' => ['usg_wa_ct_scan', 'cect_usg_wa'],
            'cd4_cd8' => ['cd4_cd8', 'cd4_cd8'],
            'il2' => ['il2', 'il_2'],
            'vit_d' => ['vit_d', 'vit_d'],
            'ltbi_qs_10' => ['ltbi_qs_10', 'ltbi_qs_10'],
            'ltbi_qs_09' => ['ltbi_qs_09', 'ltbi_qs_9'],
        ];

        $normalized = $row;

        foreach ($aliases as $target => $keys) {
            if (! empty($normalized[$target])) {
                continue;
            }

            foreach ($keys as $key) {
                if (! empty($normalized[$key])) {
                    $normalized[$target] = $normalized[$key];
                    break;
                }
            }
        }

        return $normalized;
    }
}
