@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Contact Submissions</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0" data-bulk-root>
        <div class="card-body">
            @include('partials.console_date_filter', [
                'action' => route('console.contacts.index'),
                'dateLabel' => 'Submitted',
            ])

            @include('partials.console_bulk_toolbar', [
                'formId' => 'contactBulkForm',
                'exportRoute' => route('console.contacts.export_selected'),
                'deleteRoute' => route('console.contacts.bulk_destroy'),
                'exportAllRoute' => route('console.contacts.export_all', request()->only(['from_date', 'to_date'])),
            ])

            <form method="POST" action="{{ route('console.contacts.export_selected') }}" id="contactBulkForm" data-bulk-form>
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle console-bulk-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="bulk-check-col">
                                    <input type="checkbox" data-select-all aria-label="Select all on this page">
                                </th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ids[]" value="{{ $contact->id }}" data-row-selector>
                                    </td>
                                    <td>{{ $contact->id }}</td>
                                    <td class="fw-semibold">{{ $contact->full_name }}</td>
                                    <td>{{ $contact->email }}</td>
                                    <td><span class="text-muted">{{ Str::limit($contact->message, 80) }}</span></td>
                                    <td>
                                        @include('partials.console_record_actions', [
                                            'showRoute' => route('console.contacts.show', $contact->id),
                                            'editRoute' => route('console.contacts.edit', $contact->id),
                                            'deleteRoute' => route('console.contacts.destroy', $contact->id),
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No contact submissions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">{{ $contacts->total() }} record(s) found</span>
                {{ $contacts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
