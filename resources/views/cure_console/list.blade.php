@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Cure Patient Uploads</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0" data-bulk-root>
        <div class="card-body">
            @include('partials.console_date_filter', [
                'action' => route('console.cure.list'),
                'dateLabel' => 'Uploaded',
            ])

            @include('partials.console_bulk_toolbar', [
                'formId' => 'cureBulkForm',
                'exportRoute' => route('console.cure.export_selected'),
                'deleteRoute' => route('console.cure.bulk_destroy'),
                'exportAllRoute' => route('console.cure.export_all', request()->only(['from_date', 'to_date'])),
            ])

            <form method="POST" action="{{ route('console.cure.export_selected') }}" id="cureBulkForm" data-bulk-form>
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle console-bulk-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="bulk-check-col">
                                    <input type="checkbox" data-select-all aria-label="Select all on this page">
                                </th>
                                <th>ID</th>
                                <th>LTBI No</th>
                                <th>CC No</th>
                                <th>TR No</th>
                                <th>Access Code</th>
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
                                    <td>{{ $record->ltbi_no }}</td>
                                    <td>{{ $record->cc_no ?: '—' }}</td>
                                    <td>{{ $record->tr_no ?: '—' }}</td>
                                    <td class="fw-semibold">{{ $record->access_code }}</td>
                                    <td>{{ optional($record->created_at)->format('d-m-Y h:i A') }}</td>
                                    <td>
                                        @include('partials.console_record_actions', [
                                            'showRoute' => route('console.cure.show', $record->id),
                                            'editRoute' => route('console.cure.edit', $record->id),
                                            'deleteRoute' => route('console.cure.destroy', $record->id),
                                            'extra' => '<a href="'.route('console.cure.download', $record->id).'" class="btn btn-sm btn-outline-primary" title="Download"><i class="fa-solid fa-download"></i></a>',
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No cure uploads yet.</td>
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
