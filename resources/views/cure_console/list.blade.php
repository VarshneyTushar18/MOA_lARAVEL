@extends('layout.console')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Cure Patient Uploads</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="cureTable" class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
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
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->ltbi_no }}</td>
                                <td>{{ $record->cc_no ?: '—' }}</td>
                                <td>{{ $record->tr_no ?: '—' }}</td>
                                <td class="fw-semibold">{{ $record->access_code }}</td>
                                <td>{{ optional($record->created_at)->format('d-m-Y h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('console.cure.show', $record->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('console.cure.download', $record->id) }}" class="btn btn-sm btn-primary" title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No cure uploads yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $records->links() }}</div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    if ($('#cureTable tbody tr').length && !$('#cureTable tbody td[colspan]').length) {
        $('#cureTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    }
});
</script>
@endsection
