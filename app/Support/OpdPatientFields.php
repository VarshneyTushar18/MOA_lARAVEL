<?php

namespace App\Support;

class OpdPatientFields
{
    /**
     * @return array<int, array{no: int, key: string, label: string, type: string, required?: bool, rows?: int}>
     */
    public static function definitions(): array
    {
        return [
            ['no' => 2, 'key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['no' => 3, 'key' => 'uhid_no', 'label' => 'UHID No.', 'type' => 'text', 'required' => true],
            ['no' => 4, 'key' => 'adhaar_no', 'label' => 'Aadhar No.', 'type' => 'text', 'required' => true],
            ['no' => 5, 'key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['no' => 6, 'key' => 'age', 'label' => 'Age', 'type' => 'number'],
            ['no' => 7, 'key' => 'sex', 'label' => 'Sex', 'type' => 'select'],
            ['no' => 8, 'key' => 'visit_follow_up', 'label' => 'Visit/Follow up', 'type' => 'text'],
            ['no' => 9, 'key' => 'address', 'label' => 'Address', 'type' => 'textarea', 'rows' => 2],
            ['no' => 10, 'key' => 'diagnosis', 'label' => 'Diagnosis', 'type' => 'textarea', 'rows' => 2],
            ['no' => 11, 'key' => 'investigation', 'label' => 'Investigation', 'type' => 'textarea', 'rows' => 2],
            ['no' => 12, 'key' => 'medicines', 'label' => 'Medicines', 'type' => 'textarea', 'rows' => 2],
            ['no' => 13, 'key' => 'h_o_tb_other_investigations', 'label' => 'H/o Tuberculosis', 'type' => 'text'],
            ['no' => 14, 'key' => 'tb_gold', 'label' => 'TB Gold', 'type' => 'text'],
            ['no' => 15, 'key' => 'montoux_test', 'label' => 'Mantoux Test', 'type' => 'text'],
            ['no' => 16, 'key' => 'cbc_esr', 'label' => 'CBC+ ESR', 'type' => 'text'],
            ['no' => 17, 'key' => 'xray_cect_hrct', 'label' => 'X-Ray / CECT Chest', 'type' => 'text'],
            ['no' => 18, 'key' => 'gene_xpert', 'label' => 'Gene Xpert for MTB / DNA-PCR-MTB / Sputum for AFB / CBNAAT / TRUNAAT', 'type' => 'text'],
            ['no' => 19, 'key' => 'usg_wa_ct_scan', 'label' => 'CECT / USG W/A', 'type' => 'text'],
            ['no' => 20, 'key' => 'cd4_cd8', 'label' => 'CD4 & CD8', 'type' => 'text'],
            ['no' => 21, 'key' => 'il2', 'label' => 'IL-2', 'type' => 'text'],
            ['no' => 22, 'key' => 'ige', 'label' => 'IgE', 'type' => 'text'],
            ['no' => 23, 'key' => 'vit_d', 'label' => 'Vit-D', 'type' => 'text'],
            ['no' => 24, 'key' => 'contact_details', 'label' => 'Contact Details', 'type' => 'text'],
            ['no' => 25, 'key' => 'ltbi_qs_10', 'label' => 'LTBI QS 10', 'type' => 'text'],
            ['no' => 26, 'key' => 'ltbi_qs_09', 'label' => 'LTBI QS 9', 'type' => 'text'],
            ['no' => 27, 'key' => 'refer', 'label' => 'Refer', 'type' => 'text'],
        ];
    }

    public static function keys(): array
    {
        return array_column(static::definitions(), 'key');
    }

    public static function labels(): array
    {
        return array_column(static::definitions(), 'label');
    }

    public static function labelFor(string $key): string
    {
        foreach (static::definitions() as $field) {
            if ($field['key'] === $key) {
                return $field['label'];
            }
        }

        return ucwords(str_replace('_', ' ', $key));
    }

    public static function exportHeadings(): array
    {
        return array_merge(['S.No'], static::labels());
    }
}
