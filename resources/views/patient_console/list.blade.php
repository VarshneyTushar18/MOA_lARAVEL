@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Patient Submissions</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-3" data-bulk-root>
        <div class="card-body">
            @include('partials.console_date_filter', [
                'action' => route('console.patients.index'),
                'dateLabel' => 'Visit date',
            ])

            @include('partials.console_bulk_toolbar', [
                'formId' => 'patientBulkForm',
                'exportRoute' => route('console.patients.export_selected'),
                'deleteRoute' => route('console.patients.bulk_destroy'),
                'exportAllRoute' => route('console.patients.export_all', request()->only(['from_date', 'to_date'])),
            ])

            <form method="POST" action="{{ route('console.patients.export_selected') }}" id="patientBulkForm" data-bulk-form>
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle console-bulk-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="bulk-check-col">
                                    <input type="checkbox" data-select-all aria-label="Select all on this page">
                                </th>
                                <th>ID</th>
                                <th>Date</th>
                                <th>UHID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Visit</th>
                                <th>Diagnosis</th>
                                <th>Medicines</th>
                                <th>Contact</th>
                                <th>Refer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patients as $patient)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ids[]" value="{{ $patient->id }}" data-row-selector>
                                    </td>
                                    <td>{{ $patient->id }}</td>
                                    <td>{{ $patient->date }}</td>
                                    <td>{{ $patient->uhid_no }}</td>
                                    <td class="fw-semibold">{{ $patient->name }}</td>
                                    <td>{{ $patient->age }}</td>
                                    <td>{{ $patient->sex }}</td>
                                    <td>{{ $patient->visit_follow_up }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($patient->diagnosis, 40) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($patient->medicines, 40) }}</td>
                                    <td>{{ $patient->contact_details }}</td>
                                    <td>{{ $patient->refer }}</td>
                                    <td>
                                        @include('partials.console_record_actions', [
                                            'showRoute' => route('console.patients.show', $patient->id),
                                            'editRoute' => route('console.patients.edit', $patient->id),
                                            'deleteRoute' => route('console.patients.destroy', $patient->id),
                                            'extra' => '<a href="'.route('patients.download', $patient->id).'" class="btn btn-sm btn-outline-primary" title="Download"><i class="fa-solid fa-download"></i></a>',
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">No patient submissions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">{{ $patients->total() }} record(s) found</span>
                {{ $patients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
