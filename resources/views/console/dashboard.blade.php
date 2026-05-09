@extends ('layout.console')

@section ('content')

<div class="container dashboard-section py-5">

    <!-- Title -->
    <h2 class="mb-4 fw-bold">Dashboard</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 text-center p-3">
                <h6 class="text-muted mb-1">Total Patients</h6>
                <h3 class="mb-0">{{ $stats['total_patients'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center p-3">
                <h6 class="text-muted mb-1">Patients Today</h6>
                <h3 class="mb-0">{{ $stats['today_patients'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center p-3">
                <h6 class="text-muted mb-1">Total Surveys</h6>
                <h3 class="mb-0">{{ $stats['total_surveys'] ?? 0 }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center p-3">
                <h6 class="text-muted mb-1">Surveys Today</h6>
                <h3 class="mb-0">{{ $stats['today_surveys'] ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <!-- Cards -->
    <div class="row g-4">

        <!-- Manage Pages -->
        <div class="col-md-4 col-lg-4">
            <a href="/console/pages/list" class="text-decoration-none">
                <div class="card shadow-sm h-100 text-center p-4 hover-shadow">
                    <div class="mb-3">
                        <i class="fa-solid fa-file-lines fa-2x text-primary"></i>
                    </div>
                    <h5 class="mb-0">Manage Pages</h5>
                </div>
            </a>
        </div>

        <!-- Contacts -->
        <div class="col-md-4 col-lg-4">
            <a href="/console/contacts/list" class="text-decoration-none">
                <div class="card shadow-sm h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-envelope fa-2x text-success"></i>
                    </div>
                    <h5 class="mb-0">Contact Submissions</h5>
                </div>
            </a>
        </div>

        <!-- Patients -->
        <div class="col-md-4 col-lg-4">
            <a href="/console/patients/list" class="text-decoration-none">
                <div class="card shadow-sm h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-user-injured fa-2x text-warning"></i>
                    </div>
                    <h5 class="mb-0">Patient Submissions</h5>
                </div>
            </a>
        </div>
        
<div class="col-md-4 mb-4">
    <a href="{{ route('console.survey_responses.index') }}" class="text-decoration-none">
        <div class="card shadow-sm border-0 h-100 text-center p-4">
            <div class="mb-3">
                <i class="fa fa-file-excel fa-3x text-success"></i>
            </div>

            <h5 class="mb-0">Survey Submissions</h5>
        </div>
    </a>
</div>

    </div>

</div>
@endsection
