@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Patient #{{ $patient->id }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.download', $patient->id) }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-download me-1"></i> Download Excel
            </a>
            <a href="/console/patients/list" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">Basic Information</h5>
            <div class="row">
                <div class="col-md-4 mb-3"><strong>Date:</strong> {{ $patient->date ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>UHID No:</strong> {{ $patient->uhid_no ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>File No:</strong> {{ $patient->file_no ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Aadhaar No:</strong> {{ $patient->adhaar_no ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Name:</strong> {{ $patient->name ?: '—' }}</div>
                <div class="col-md-2 mb-3"><strong>Age:</strong> {{ $patient->age ?: '—' }}</div>
                <div class="col-md-2 mb-3"><strong>Sex:</strong> {{ $patient->sex ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Visit / Follow up:</strong> {{ $patient->visit_follow_up ?: '—' }}</div>
                <div class="col-md-8 mb-3"><strong>Address:</strong> {{ $patient->address ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Contact:</strong> {{ $patient->contact_details ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Refer:</strong> {{ $patient->refer ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Submitted:</strong> {{ optional($patient->created_at)->format('d-m-Y h:i A') ?: '—' }}</div>
            </div>

            <hr>

            <h5 class="mb-3">Clinical Details</h5>
            <div class="row">
                @php
                    $fields = [
                        'diagnosis' => 'Diagnosis',
                        'investigation' => 'Investigation',
                        'medicines' => 'Medicines',
                        'h_o_tb_other_investigations' => 'H/O TB / Other Investigations',
                        'tb_gold' => 'TB Gold',
                        'montoux_test' => 'Montoux Test',
                        'cbc_esr' => 'CBC / ESR',
                        'xray_cect_hrct' => 'X-Ray / CECT / HRCT',
                        'gene_xpert' => 'Gene Xpert',
                        'usg_wa_ct_scan' => 'USG / WA / CT Scan',
                        'cd4_cd8' => 'CD4 / CD8',
                        'ige' => 'IgE',
                        'vit_d' => 'Vit D',
                        'lft' => 'LFT',
                        'rft' => 'RFT',
                        'il2' => 'IL2',
                        'ltbi_qs_10' => 'LTBI Qs 10',
                        'ltbi_qs_09' => 'LTBI Qs 09',
                    ];
                @endphp

                @foreach($fields as $key => $label)
                    <div class="col-md-6 mb-3">
                        <strong>{{ $label }}:</strong>
                        <div class="text-muted">{{ $patient->{$key} ?: '—' }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
