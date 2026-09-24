@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Research Patient Uploads</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0" data-bulk-root>
        <div class="card-body">
            @include('partials.console_date_filter', [
                'action' => route('console.research.list'),
                'dateLabel' => 'Uploaded',
            ])

            @include('partials.console_bulk_toolbar', [
                'formId' => 'researchBulkForm',
                'exportRoute' => route('console.research.export_selected'),
                'deleteRoute' => route('console.research.bulk_destroy'),
                'exportAllRoute' => route('console.research.export_all', request()->only(['from_date', 'to_date'])),
            ])

            <form method="POST" action="{{ route('console.research.export_selected') }}" id="researchBulkForm" data-bulk-form>
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle console-bulk-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="bulk-check-col">
                                    <input type="checkbox" data-select-all aria-label="Select all on this page">
                                </th>
                                <th>ID</th>
                                <th>LTBIRS No</th>
                                <th>File</th>
                                <th>Uploaded At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ids[]" value="{{ $record->id }}" data-row-selector>
                                    </td>
                                    <td>{{ $record->id }}</td>
                                    <td class="fw-semibold">{{ $record->ltbirs_no }}</td>
                                    <td>{{ basename((string) $record->file_path) }}</td>
                                    <td>{{ optional($record->created_at)->format('d-m-Y h:i A') }}</td>
                                    <td>
                                        @include('partials.console_record_actions', [
                                            'showRoute' => route('console.research.show', $record->id),
                                            'editRoute' => route('console.research.edit', $record->id),
                                            'deleteRoute' => route('console.research.destroy', $record->id),
                                            'extra' => '<a href="'.route('console.research.download', $record->id).'" class="btn btn-sm btn-outline-primary" title="Download"><i class="fa-solid fa-download"></i></a>',
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No research uploads yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">{{ $records->total() }} record(s) found</span>
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
