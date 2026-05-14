@extends('layout.console')

@section('content')

<style>
    .survey-table {
        min-width: 820px;
    }
    .survey-table th {
        white-space: nowrap;
    }
    .survey-table td {
        vertical-align: top;
    }
    .survey-table .cell-action {
        min-width: 125px;
        white-space: nowrap;
    }
    .survey-table .cell-check {
        width: 42px;
    }
    @media (max-width: 767.98px) {
        .survey-filter-actions {
            flex-direction: column;
        }
    }
</style>

<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">Survey Submissions</h2>
        <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card p-3 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
            <h4 class="mb-0">Upload Past Data CSV/Excel</h4>
        </div>

        <form method="POST" action="{{ route('console.survey_responses.import') }}" enctype="multipart/form-data">
            @csrf

            <p class="text-muted small mb-2">Allowed types: CSV or Excel only (.csv, .xlsx, .xls). Maximum size 15&nbsp;MB.</p>
            <input type="file"
                   name="file"
                   class="form-control mb-3 @error('file') is-invalid @enderror"
                   accept=".csv,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                   required>
            @error('file')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    Import Data
                </button>
                <a href="{{ route('console.survey_responses.sample_file') }}" class="btn btn-outline-primary">
                    Download Sample Data File
                </a>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <h4>Survey Responses</h4>
        <form method="GET" action="{{ route('console.survey_responses.index') }}" class="row g-2 mb-3 align-items-end">
            <div class="col-12 col-md-3">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Search name/contact/registration">
            </div>
            <div class="col-6 col-md-2">
                <select name="gender" class="form-control">
                    <option value="">All Gender</option>
                    <option value="Male" @selected(request('gender') === 'Male')>Male</option>
                    <option value="Female" @selected(request('gender') === 'Female')>Female</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="type_of_case" class="form-control">
                    <option value="">All Case Types</option>
                    <option value="MDR" @selected(request('type_of_case') === 'MDR')>MDR</option>
                    <option value="XDR" @selected(request('type_of_case') === 'XDR')>XDR</option>
                    <option value="PRIMARY_CASE" @selected(request('type_of_case') === 'PRIMARY_CASE')>Primary Case</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small text-muted">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small text-muted">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To">
            </div>
            <div class="col-12 col-md-1 d-grid gap-2 d-md-flex survey-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>

        <form method="POST" action="{{ route('console.survey_responses.export_selected') }}" id="selectedExportForm">
            @csrf
            <div class="d-flex justify-content-end mb-2">
                <button type="submit" class="btn btn-success btn-sm" id="exportSelectedBtn" disabled>
                    Export
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0 survey-table">
                <thead>
                    <tr>
                        <th class="cell-check">
                            <input type="checkbox" id="selectAllRows">
                        </th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Registration No.</th>
                        <th>Risk Symptoms (YES count)</th>
                        <th>Type of Case</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $response)
                        @php
                            $answers = is_array($response->answers) ? $response->answers : [];
                            $riskFields = [
                                'frequent_cold_or_respiratory_allergy',
                                'unable_to_gain_weight_or_weight_loss',
                                'excessive_anger_or_stress_irritability',
                                'persistent_bodyache_or_fatigue',
                                'lack_of_enthusiasm_or_energy',
                                'irregular_menses_amenorrhea_or_infertility',
                                'frequent_hospital_visits',
                                'difficulty_or_pain_in_joint_movements',
                                'frequent_headache_dizziness_lightheadedness',
                            ];
                            $yesCount = collect($riskFields)->filter(fn($key) => data_get($answers, $key) === 'YES')->count();
                            $typeOfCase = data_get($answers, 'type_of_case', '-');
                        @endphp
                        <tr>
                            <td class="cell-check">
                                <input type="checkbox" class="row-selector" name="selected_ids[]" value="{{ $response->id }}">
                            </td>
                            <td>{{ $response->id }}</td>
                            <td>{{ $response->name }}</td>
                            <td>{{ $response->registration_number }}</td>
                            <td>{{ $yesCount }}</td>
                            <td>{{ $typeOfCase ?: '-' }}</td>
                            <td>{{ $response->created_at->format('d-m-Y h:i A') }}</td>
                            <td class="cell-action">
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('console.survey_responses.show', $response) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No survey responses found.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </form>

        {{ $responses->appends(request()->query())->links() }}
    </div>
</div>

<script>
    (function () {
        const selectAll = document.getElementById('selectAllRows');
        const exportSelectedBtn = document.getElementById('exportSelectedBtn');
        const exportForm = document.getElementById('selectedExportForm');

        function getRowSelectors() {
            return Array.from(document.querySelectorAll('.row-selector'));
        }

        function updateState() {
            const rows = getRowSelectors();
            const checkedCount = rows.filter((cb) => cb.checked).length;

            exportSelectedBtn.disabled = checkedCount === 0;

            if (rows.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
                return;
            }

            if (checkedCount === rows.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (checkedCount === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }

        selectAll?.addEventListener('change', function () {
            getRowSelectors().forEach((cb) => {
                cb.checked = this.checked;
            });
            updateState();
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('row-selector')) {
                updateState();
            }
        });

        exportForm?.addEventListener('submit', function (e) {
            const selectedCount = getRowSelectors().filter((cb) => cb.checked).length;
            if (selectedCount === 0) {
                e.preventDefault();
            }
        });

        updateState();
    })();
</script>

@endsection
