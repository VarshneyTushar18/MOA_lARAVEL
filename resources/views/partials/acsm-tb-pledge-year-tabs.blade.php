@php
    use App\Support\MediaYearResolver;

    $years = [2023, 2024, 2025, 2026];
    $byYear = MediaYearResolver::emptyYearBuckets($years);
    $sectionTitle = $section->title ?: 'TB Pledge';

    foreach ($section->images as $img) {
        $year = MediaYearResolver::fromImage($img);
        if ($year && isset($byYear[$year])) {
            $byYear[$year]['images']->push($img);
        }
    }

    if (!empty($section->image)) {
        $mainYear = MediaYearResolver::fromFilename($section->image) ?? 2024;
        if (isset($byYear[$mainYear])) {
            $byYear[$mainYear]['images']->prepend((object) ['image' => $section->image]);
        }
    }

    foreach ($section->media->where('type', 'video') as $video) {
        $year = MediaYearResolver::fromVideo($video);
        if ($year && isset($byYear[$year])) {
            $byYear[$year]['videos']->push($video);
        }
    }

    $activeYears = collect($years)->filter(function ($year) use ($byYear) {
        return $byYear[$year]['images']->isNotEmpty() || $byYear[$year]['videos']->isNotEmpty();
    })->values();

    $tabPrefix = 'tbPledge'.$section->id;
@endphp

@if($activeYears->isNotEmpty())
<div class="tb-pledge-year-tabs" data-section-id="{{ $section->id }}">
    <ul class="nav nav-tabs tb-pledge-year-tabs__nav" role="tablist">
        @foreach($activeYears as $year)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                        id="{{ $tabPrefix }}-tab-{{ $year }}"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $tabPrefix }}-pane-{{ $year }}"
                        type="button"
                        role="tab"
                        aria-controls="{{ $tabPrefix }}-pane-{{ $year }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $year }}
                    <span class="tb-pledge-year-tabs__count">
                        {{ $byYear[$year]['images']->count() }} photos · {{ $byYear[$year]['videos']->count() }} videos
                    </span>
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content tb-pledge-year-tabs__content">
        @foreach($activeYears as $year)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                 id="{{ $tabPrefix }}-pane-{{ $year }}"
                 role="tabpanel"
                 aria-labelledby="{{ $tabPrefix }}-tab-{{ $year }}"
                 tabindex="0">
                @if($byYear[$year]['images']->isNotEmpty())
                    <div class="tb-pledge-year-block">
                        <h5 class="tb-pledge-year-block__title">Photos — {{ $year }}</h5>
                        @include('partials.acsm-image-gallery', [
                            'images' => $byYear[$year]['images'],
                            'galleryId' => $tabPrefix.'-images-'.$year,
                            'sectionTitle' => $sectionTitle.' '.$year,
                        ])
                    </div>
                @endif

                @if($byYear[$year]['videos']->isNotEmpty())
                    <div class="tb-pledge-year-block {{ $byYear[$year]['images']->isNotEmpty() ? 'mt-4' : '' }}">
                        <h5 class="tb-pledge-year-block__title">Videos — {{ $year }}</h5>
                        @include('partials.acsm-video-gallery', [
                            'videos' => $byYear[$year]['videos'],
                            'youtubes' => collect(),
                        ])
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
