@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Patient #{{ $patient->id }}</h2>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('console.patients.edit', $patient->id) }}" class="btn btn-primary btn-sm">Edit</a>
            <form action="{{ route('console.patients.destroy', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this patient record?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            <a href="{{ route('patients.download', $patient->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-download me-1"></i> Download Excel
            </a>
            <a href="{{ route('console.patients.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="mb-3">OPD Patient Form</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:70px;">S.No</th>
                            <th style="width:38%;">Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-semibold">1</td>
                            <td>S.No</td>
                            <td>{{ $patient->id }}</td>
                        </tr>
                        @foreach(\App\Support\OpdPatientFields::definitions() as $field)
                            <tr>
                                <td class="text-center fw-semibold">{{ $field['no'] }}</td>
                                <td>{{ $field['label'] }}</td>
                                <td class="text-muted">{{ $patient->{$field['key']} ?: '—' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-center">—</td>
                            <td>File No.</td>
                            <td class="text-muted">{{ $patient->file_no ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">—</td>
                            <td>Submitted</td>
                            <td class="text-muted">{{ optional($patient->created_at)->format('d-m-Y h:i A') ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
