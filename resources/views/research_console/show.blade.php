@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Research Upload #{{ $record->id }}</h2>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('console.research.edit', $record->id) }}" class="btn btn-primary btn-sm">Edit</a>
            <form action="{{ route('console.research.destroy', $record->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this research upload?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            <a href="{{ route('console.research.download', $record->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-download me-1"></i> Download File
            </a>
            <a href="{{ route('console.research.list') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3"><strong>LTBIRS No:</strong> {{ $record->ltbirs_no ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>File:</strong> {{ basename((string) $record->file_path) ?: '—' }}</div>
                <div class="col-md-4 mb-3"><strong>Uploaded:</strong> {{ optional($record->created_at)->format('d-m-Y h:i A') ?: '—' }}</div>
            </div>

            @php
                $ext = strtolower(pathinfo((string) $record->file_path, PATHINFO_EXTENSION));
                $isImage = in_array($ext, ['jpg', 'jpeg', 'png'], true);
                $isPdf = $ext === 'pdf';
            @endphp

            @if($record->file_path)
                <hr>
                <h5 class="mb-3">Uploaded Document</h5>
                @if($isImage)
                    <img src="{{ route('console.research.file', $record->id) }}" class="img-fluid rounded border" alt="Uploaded document">
                @elseif($isPdf)
                    <iframe src="{{ route('console.research.file', $record->id) }}" class="w-100 rounded border" style="min-height: 600px;"></iframe>
                @else
                    <p class="text-muted mb-0">Preview not available for this file type.</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
