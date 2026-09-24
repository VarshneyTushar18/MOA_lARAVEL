@php
    $path = isset($video) ? resolveStoragePath($video->file_path) : null;
@endphp

@if($path)
<div class="workshop-video-gallery__player">
    @if(filled($video->title) || filled($video->description))
        <div class="acsm-video-gallery__caption text-center mb-2">
            @if(filled($video->title))
                <h6 class="mb-1">{{ $video->title }}</h6>
            @endif
            @if(filled($video->description))
                <p class="small text-muted mb-0">{!! nl2br(e($video->description)) !!}</p>
            @endif
        </div>
    @endif
    <video controls class="workshop-video-gallery__video" preload="metadata">
        <source src="{{ asset('storage/'.$path) }}" type="video/mp4">
        Your browser does not support video playback.
    </video>
</div>
@endif
