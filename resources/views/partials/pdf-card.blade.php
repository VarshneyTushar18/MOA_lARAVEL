@php
    $pdfItem = $pdf ?? $file ?? null;
    $heading = $heading ?? null;
    $pdfTitle = $pdfItem->title
        ?? $heading
        ?? ($section->title ?? 'Document');
    $pdfDesc = $pdfItem->description ?? ($section->description ?? null);
    $path = $pdfItem->file_path ?? null;
    $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
    $isPresentation = in_array($extension, ['ppt', 'pptx'], true);
    $viewLabel = $isPresentation ? 'View Presentation' : 'View PDF';
    $fileLabel = $isPresentation ? 'PPT' : 'PDF';
@endphp

@if($path)
<div class="card shadow-sm p-4 text-center h-100">
    <h6 class="mb-2">{{ $pdfTitle }}</h6>
    @if(filled($pdfDesc))
        <p class="small text-muted mb-3">{!! nl2br(e($pdfDesc)) !!}</p>
    @endif
    <a href="{{ asset('storage/'.$path) }}" target="_blank" class="btn btn-primary btn-sm mb-2">
        {{ $viewLabel }}
    </a>
    <a href="{{ asset('storage/'.$path) }}" download="{{ pathinfo($path, PATHINFO_BASENAME) }}" class="btn btn-outline-secondary btn-sm">
        Download {{ $fileLabel }}
    </a>
</div>
@endif
