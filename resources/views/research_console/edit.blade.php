@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Edit Research Upload #{{ $record->id }}</h2>
        <a href="{{ route('console.research.show', $record->id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
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
            <form method="POST" action="{{ route('console.research.update', $record->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ltbirs_no">LTBIRS No</label>
                        <input type="text" name="ltbirs_no" id="ltbirs_no" class="form-control" value="{{ old('ltbirs_no', $record->ltbirs_no) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="file">Replace File (optional)</label>
                        <input type="file" name="file" id="file" class="form-control" accept=".pdf,.jpg,.jpeg">
                        <small class="text-muted">Current: {{ basename((string) $record->file_path) }}</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
