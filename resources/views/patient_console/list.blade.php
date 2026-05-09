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
        <h2 class="fw-bold">Patient Submissions</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            <div class="table-responsive">
                <table id="patientsTable" class="table table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
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
                        @foreach($patients as $patient)
                        <tr>
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
                                <form action="{{ route('patients.download', $patient->id) }}" method="GET">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-download"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#patientsTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "order": [[0, "asc"]], // default sort by first column (S.No)
        "columnDefs": [
            { "orderable": false, "targets": -1 } // disable sorting on last column (Actions)
        ]
    });
});
</script>

@endsection