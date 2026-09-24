@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Edit Patient #{{ $patient->id }}</h2>
        <a href="{{ route('console.patients.show', $patient->id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('console.patients.update', $patient->id) }}">
                @csrf
                @method('PUT')

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:70px;">S.No</th>
                                <th style="width:38%;">Field</th>
                                <th>Entry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Support\OpdPatientFields::definitions() as $field)
                                <tr>
                                    <td class="text-center fw-semibold">{{ $field['no'] }}</td>
                                    <td>{{ $field['label'] }}</td>
                                    <td>
                                        @php
                                            $key = $field['key'];
                                            $value = old($key, $patient->{$key});
                                        @endphp

                                        @if($field['type'] === 'date')
                                            <input type="date" name="{{ $key }}" class="form-control" value="{{ $value }}">
                                        @elseif($field['type'] === 'number')
                                            <input type="number" name="{{ $key }}" class="form-control" value="{{ $value }}" min="0" max="120">
                                        @elseif($field['type'] === 'select')
                                            <select name="{{ $key }}" class="form-control">
                                                <option value="">Select</option>
                                                <option value="Male" @selected($value === 'Male')>Male</option>
                                                <option value="Female" @selected($value === 'Female')>Female</option>
                                                <option value="Other" @selected($value === 'Other')>Other</option>
                                            </select>
                                        @elseif($field['type'] === 'textarea')
                                            <textarea name="{{ $key }}" class="form-control" rows="{{ $field['rows'] ?? 2 }}">{{ $value }}</textarea>
                                        @else
                                            <input type="text" name="{{ $key }}" class="form-control" value="{{ $value }}" @if(!empty($field['required'])) required @endif>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">File No.</label>
                        <input type="text" name="file_no" class="form-control" value="{{ old('file_no', $patient->file_no) }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
