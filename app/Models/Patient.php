<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'uhid_no', 'adhaar_no', 'name', 'age', 'sex', 'visit_follow_up', 'address',
        'diagnosis', 'investigation', 'medicines', 'h_o_tb_other_investigations', 'tb_gold',
        'montoux_test', 'cbc_esr', 'xray_cect_hrct', 'gene_xpert', 'usg_wa_ct_scan', 'cd4_cd8',
        'ige', 'vit_d', 'lft', 'rft', 'il2', 'contact_details', 'ltbi_qs_10', 'ltbi_qs_09', 'refer'
    ];
}