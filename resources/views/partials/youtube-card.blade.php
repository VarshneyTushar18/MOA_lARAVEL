@php
    $youtubeId = $id ?? \App\Support\Youtube::videoId($url ?? null);
    $watchUrl = \App\Support\Youtube::watchUrl($url ?? null, $youtubeId);
    $thumb = \App\Support\Youtube::thumbnailUrl($youtubeId);
    $fallbackThumb = \App\Support\Youtube::thumbnailUrl($youtubeId, 'mqdefault');
    $tryEmbed = request()->secure();
@endphp

@if($youtubeId && $watchUrl)
<div class="youtube-card video-card shadow rounded h-100">
    @if($tryEmbed)
        <div class="youtube-card__embed">
            <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}"
                    title="YouTube video"
                    allowfullscreen
                    loading="lazy"></iframe>
        </div>
    @endif
    <a href="{{ $watchUrl }}" target="_blank" rel="noopener" class="youtube-card__thumb">
        @if($thumb)
            <img src="{{ $thumb }}"
                 alt="YouTube video"
                 onerror="this.onerror=null;this.src='{{ $fallbackThumb }}';">
        @endif
        <span class="youtube-card__play" aria-hidden="true"><i class="ri-play-fill"></i></span>
    </a>
    <div class="p-2 text-center">
        <a href="{{ $watchUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-danger">Watch on YouTube</a>
    </div>
</div>
@endif
