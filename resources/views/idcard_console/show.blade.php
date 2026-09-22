@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">ID Card Upload #{{ $record->id }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('console.idcard.download', $record->id) }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-download me-1"></i> Download File
            </a>
            <a href="{{ route('console.idcard.list') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3"><strong>ID Number:</strong> {{ $record->id_number ?: '—' }}</div>
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
                    <img src="{{ route('console.idcard.file', $record->id) }}" class="img-fluid rounded border" alt="Uploaded document">
                @elseif($isPdf)
                    <iframe src="{{ route('console.idcard.file', $record->id) }}" class="w-100 rounded border" style="min-height: 600px;"></iframe>
                @else
                    <p class="text-muted mb-0">Preview not available for this file type.</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
