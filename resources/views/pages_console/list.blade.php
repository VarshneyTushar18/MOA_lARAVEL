@extends('layout.console')

@section('content')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<div class="container py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Pages</h2>
        <a href="/console/pages/add" class="btn btn-success">Add Page</a>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            <div class="table-responsive">
                <table id="pagesTable" class="table table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($pages as $page)
                        <tr>
                            <td class="fw-semibold">{{ $page->title }}</td>
                            <td>{{ $page->slug }}</td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="/console/pages/edit/{{ $page->id }}">Edit</a>
                                <a class="btn btn-sm btn-danger" href="/console/pages/delete/{{ $page->id }}" onclick="return confirm('Are you sure?')">Delete</a>
                                <a class="btn btn-sm btn-secondary" href="/console/pages/sections/{{ $page->id }}/list">Sections</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
    $('#pagesTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": -1 } // Disable sorting on Actions
        ]
    });
});
</script>
@endpush