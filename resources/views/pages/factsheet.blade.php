@extends('layout.frontend')

@section('content')

@php
    $grouped = $page->sections->groupBy('section_key');

    $trainingSurvey = $grouped->get('training_survey', collect())->first();
    $surveyData     = $grouped->get('survey_data', collect())->first();
    $diagnosticFacilities = $grouped->get('diagnostic_facilities', collect())->first();
    $mouSection           = $grouped->get('mou', collect())->first();
    $factsheetHighlightsParent = $grouped->get('factsheet_highlights', collect())->first();
    $factsheetHighlights = $factsheetHighlightsParent ? ($factsheetHighlightsParent->highlightItems ?? collect()) : collect();
    $surveyFormPath = route('survey.form', [], false);
    $surveyFormUrl = request()->getSchemeAndHttpHost() . $surveyFormPath;
    $surveyQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($surveyFormUrl);

    if (!function_exists('resolveStorageImage')) {
        function resolveStorageImage($filename) {
            if (!$filename) return null;
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($disk->exists($filename)) return $filename;
            if ($disk->exists('images/'.$filename)) return 'images/'.$filename;
            return null;
        }
    }

    if (!function_exists('factsheetNormalizePublicDiskPath')) {
        function factsheetNormalizePublicDiskPath(?string $path): ?string
        {
            if (!$path) {
                return null;
            }
            $path = str_replace('\\', '/', trim($path));
            $path = ltrim($path, '/');
            if (strpos($path, 'public/') === 0) {
                $path = substr($path, strlen('public/'));
            }
            if (strpos($path, 'storage/') === 0) {
                $path = substr($path, strlen('storage/'));
            }
            return $path;
        }
    }

    if (!function_exists('factsheetResolveStorageRelativePath')) {
        function factsheetResolveStorageRelativePath(?string $raw): ?string
        {
            if (!$raw || !is_string($raw)) {
                return null;
            }

            $raw = str_replace('\\', '/', trim($raw));

            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                $urlPath = parse_url($raw, PHP_URL_PATH);
                if (is_string($urlPath) && $urlPath !== '') {
                    $normalizedUrlPath = factsheetNormalizePublicDiskPath($urlPath);
                    if ($normalizedUrlPath) {
                        $disk = \Illuminate\Support\Facades\Storage::disk('public');
                        if ($disk->exists($normalizedUrlPath)) {
                            return $normalizedUrlPath;
                        }
                    }
                }
                return null;
            }

            return resolveStorageImage($raw);
        }
    }

    if (!function_exists('factsheetHighlightPublicAssetUrl')) {
        function factsheetHighlightPublicAssetUrl($highlight): ?string
        {
            $raw = $highlight->image ?? null;
            if (!$raw || !is_string($raw)) {
                return null;
            }

            $raw = str_replace('\\', '/', trim($raw));
            if ($raw === '') {
                return null;
            }

            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                return $raw;
            }

            $clean = ltrim($raw, '/');
            if (strpos($clean, 'public/') === 0) {
                $clean = substr($clean, strlen('public/'));
            }

            $absolute = public_path($clean);
            return is_file($absolute) ? asset($clean) : null;
        }
    }

    if (!function_exists('factsheetHighlightImageUrl')) {
        function factsheetHighlightImageUrl($highlight): ?string
        {
            $raw = $highlight->image ?? null;
            if (!$raw || !is_string($raw)) {
                return null;
            }
            $raw = str_replace('\\', '/', trim($raw));
            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                return $raw;
            }

            $relative = factsheetResolveStorageRelativePath($raw);
            if ($relative) {
                return '/storage/' . ltrim($relative, '/');
            }

            return factsheetHighlightPublicAssetUrl($highlight);
        }
    }

    if (!function_exists('factsheetStorageUrlWithVersion')) {
        function factsheetStorageUrlWithVersion(string $relative): string
        {
            $relative = ltrim(str_replace('\\', '/', $relative), '/');
            $url = '/storage/'.$relative;
            $disk = \Illuminate\Support\Facades\Storage::disk('public');

            if ($disk->exists($relative)) {
                $url .= '?v='.$disk->lastModified($relative);
            }

            return $url;
        }
    }

    if (!function_exists('factsheetHighlightVideoUrl')) {
        function factsheetHighlightVideoUrl($highlight): ?string
        {
            $raw = $highlight->video_path ?? null;
            if (!$raw || !is_string($raw)) {
                return null;
            }
            $raw = str_replace('\\', '/', trim($raw));
            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                return $raw;
            }

            $relative = factsheetResolveStorageRelativePath($raw);
            if ($relative) {
                return factsheetStorageUrlWithVersion($relative);
            }

            $clean = ltrim($raw, '/');
            if (strpos($clean, 'public/') === 0) {
                $clean = substr($clean, strlen('public/'));
            }
            $absolute = public_path($clean);
            if (! is_file($absolute)) {
                return null;
            }

            return factsheetStorageUrlWithVersion($clean);
        }
    }

    if (!function_exists('extractYoutubeVideoId')) {
        function extractYoutubeVideoId($url) {
            return \App\Support\Youtube::videoId($url);
        }
    }

    if (!function_exists('moaDisplaySortKey')) {
        function moaDisplaySortKey($item): string
        {
            $order = (int) ($item->sort_order ?? 0);
            if ($order <= 0) {
                $order = 100000 + (int) ($item->id ?? 0);
            }
            return sprintf('%010d-%010d', $order, (int) ($item->id ?? 0));
        }
    }

    $orderedFactsheetSections = $page->sections
        ->filter(function ($section) {
            return !($section->parent_id && (int) $section->parent_id !== (int) $section->id);
        })
        ->sortBy(fn ($item) => moaDisplaySortKey($item))
        ->values();
@endphp

{{-- ================= PAGE HEADER (Like Second Layout) ================= --}}
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title ?? 'Training & Survey' }}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li>{{ $page->title ?? 'Training & Survey' }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>

@foreach($orderedFactsheetSections as $section)

{{-- ================= FACTSHEET HIGHLIGHTS ================= --}}
@if($section->section_key === 'factsheet_highlights')
@php
    $factsheetHighlightsParent = $section;
    $factsheetHighlights = collect($section->highlightItems)->sortBy(fn ($item) => moaDisplaySortKey($item))->values();
@endphp
@if($factsheetHighlights->count())
<section class="factsheet-highlights">
    <div class="container">
        <div class="factsheet-highlights__rail">
            @foreach($factsheetHighlights as $index => $highlight)
                @php
                    $imageUrl = factsheetHighlightImageUrl($highlight);
                    $youtubeId = !empty($highlight->youtube_url) ? extractYoutubeVideoId($highlight->youtube_url) : null;
                    $youtubeThumb = \App\Support\Youtube::thumbnailUrl($youtubeId);
                    $fallbackImage = asset('assets/images/page-header-image.webp');
                    $coverImage = $youtubeThumb ?: ($imageUrl ?: $fallbackImage);
                    $videoUrl = factsheetHighlightVideoUrl($highlight);
                    $modalType = $youtubeId ? 'youtube' : ($videoUrl ? 'video' : ($imageUrl ? 'image' : null));
                    $modalId = 'factsheetHighlightModal-' . ($page->id ?? 'page') . '-' . $index;
                @endphp

                @if($modalType)
                <button class="factsheet-highlight-item"
                        type="button"
                        data-highlight-modal="{{ $modalId }}">
                    <span class="factsheet-highlight-item__thumb">
                        <img src="{{ $coverImage }}" alt="{{ $highlight->title ?? 'Highlight' }}" loading="lazy" decoding="async">
                    </span>
                    <span class="factsheet-highlight-item__label">{{ $highlight->title ?? 'Highlight' }}</span>
                </button>
                @endif
            @endforeach
        </div>
    </div>
</section>

@foreach($factsheetHighlights as $index => $highlight)
    @php
        $imageUrl = factsheetHighlightImageUrl($highlight);
        $youtubeId = !empty($highlight->youtube_url) ? extractYoutubeVideoId($highlight->youtube_url) : null;
        $videoUrl = factsheetHighlightVideoUrl($highlight);
        $modalType = $youtubeId ? 'youtube' : ($videoUrl ? 'video' : ($imageUrl ? 'image' : null));
        $modalId = 'factsheetHighlightModal-' . ($page->id ?? 'page') . '-' . $index;
    @endphp
    @if($modalType)
    <div class="modal fade factsheet-highlight-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $highlight->title ?? 'Highlight' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($modalType === 'youtube')
                        @include('partials.youtube-card', ['id' => $youtubeId, 'url' => $highlight->youtube_url])
                    @elseif($modalType === 'video')
                        <div class="factsheet-highlight-modal__media">
                            <video controls class="rounded factsheet-highlight-modal__video">
                                <source src="{{ $videoUrl }}">
                                Your browser does not support video playback.
                            </video>
                        </div>
                    @elseif($modalType === 'image')
                        <div class="factsheet-highlight-modal__media">
                            <img src="{{ $imageUrl }}"
                                 class="rounded factsheet-highlight-modal__image"
                                 alt="{{ $highlight->title ?? 'Highlight image' }}"
                                 onerror="this.onerror=null;this.src='{{ asset('assets/images/page-header-image.webp') }}';">
                        </div>
                    @endif

                    @if(!empty($highlight->description))
                        <p class="mt-3 mb-0">{!! nl2br(e($highlight->description)) !!}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endif

{{-- ================= TRAINING / WORKSHOP SECTION ================= --}}
@elseif($section->section_key === 'training_survey')
@php
    $trainingSurvey = $section;
    $workshopSubs = collect($section->subsections)->sortBy(fn ($item) => moaDisplaySortKey($item))->values();
@endphp
@if($trainingSurvey)
<section class="ntpcsection">
    <div class="container">
        <div class="row align-items-end">

            {{-- LEFT CONTENT --}}
            <div class="col-lg-5">
                <div class="section-heading">
                    <span>{{ $trainingSurvey->subtitle ?? 'Training & Workshops' }}</span>
                    <h2>{{ $trainingSurvey->title ?? 'Workshops & Survey' }}</h2>
                </div>
                <p>{!! nl2br(e($trainingSurvey->description)) !!}</p>
            </div>

            {{-- RIGHT SIDE TABS + GALLERY --}}
            <div class="col-lg-7">

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3 workshop-tabs-scroll" id="workshopTabs" role="tablist">
                    @foreach($workshopSubs as $index => $sub)
                    <li class="nav-item">
                        <button class="nav-link @if($index==0) active @endif"
                                data-bs-toggle="tab"
                                data-bs-target="#content-{{ $sub->id }}"
                                type="button">
                            {{ $sub->title }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content">

                    @foreach($workshopSubs as $index => $sub)
                    <div class="tab-pane fade @if($index==0) show active @endif"
                         id="content-{{ $sub->id }}">

                        {{-- Image Slider --}}
                        @if($sub->images && $sub->images->count())
                        <div class="swiper mySwiper mb-4">
                            <div class="swiper-wrapper">
                                @foreach($sub->images as $img)
                                    @php $imagePath = resolveStorageImage($img->image); @endphp
                                    @if($imagePath)
                                    <div class="swiper-slide">
                                        <a href="{{ asset('storage/'.$imagePath) }}"
                                           class="glightbox"
                                           data-gallery="gallery-{{ $sub->id }}">
                                            <img src="{{ asset('storage/'.$imagePath) }}"
                                                 class="img-fluid"
                                                 loading="lazy"
                                                 decoding="async"
                                                 alt="">
                                        </a>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                        @endif

                        {{-- Videos Grid --}}
                        @php
                            $youtubeUrls = collect($sub->videos ?? [])
                                ->merge(($sub->media ?? collect())->where('type', 'youtube')->pluck('youtube_url'))
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp
                        @if($youtubeUrls->count())
                        <div class="row g-4">
                            @foreach($youtubeUrls as $videoUrl)
                                @php
                                    $videoId = extractYoutubeVideoId($videoUrl);
                                @endphp

                                @if($videoId)
                                <div class="col-md-6">
                                    @include('partials.youtube-card', ['id' => $videoId, 'url' => $videoUrl])
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @endif

                    </div>
                    @endforeach

                </div>

            </div>
        </div>
    </div>
</section>
@endif

{{-- ================= DIAGNOSTIC FACILITIES SECTION ================= --}}
@elseif($section->section_key === 'diagnostic_facilities')
@php $diagnosticFacilities = $section; @endphp
@if($diagnosticFacilities)
<section class="schemesection">
    <div class="container">

        <div class="row">
            <div class="col-lg-8">
                <div class="section-heading">
                    <span>Diagnostic Facilities</span>
                    <h3>{{ $diagnosticFacilities->title ?? 'Diagnostic Facilities' }}</h3>
                </div>
            </div>
        </div>

        <div class="row pt-4 align-items-start">

            {{-- Description --}}
            <div class="col-lg-6">
                {!! $diagnosticFacilities->description !!}
            </div>

            {{-- Image --}}
            <div class="col-lg-6 text-center">
                @if($diagnosticFacilities->image)
                    @php $diagPath = resolveStorageImage($diagnosticFacilities->image); @endphp
                    @if($diagPath)
                        <img src="{{ asset('storage/'.$diagPath) }}"
                             class="img-fluid shadow rounded"
                             loading="lazy"
                             decoding="async"
                             alt="Diagnostic Facilities">
                    @endif
                @endif
            </div>

        </div>
    </div>
</section>
@endif

{{-- ================= MOU SECTION ================= --}}
@elseif($section->section_key === 'mou')
@php $mouSection = $section; @endphp
@if($mouSection)
<section class="ntpcsection">
    <div class="container">

        <div class="row">
            <div class="col-lg-8">
                <div class="section-heading">
                    <span>MOU</span>
                    <h2>{{ $mouSection->title ?? 'Memorandum of Understanding' }}</h2>
                </div>
            </div>
        </div>

        <div class="row pt-4 align-items-center">

            {{-- Description --}}
            <div class="col-lg-6">
                {!! $mouSection->description !!}
            </div>

            {{-- Image --}}
            <div class="col-lg-6 text-center">
                @if($mouSection->image)
                    @php $mouPath = resolveStorageImage($mouSection->image); @endphp
                    @if($mouPath)
                        <img src="{{ asset('storage/'.$mouPath) }}"
                             class="img-fluid shadow rounded"
                             loading="lazy"
                             decoding="async"
                             alt="MOU Image">
                    @endif
                @endif
            </div>

        </div>

    </div>
</section>
@endif

{{-- ================= SURVEY DATA SECTION (Styled Like Scheme Section) ================= --}}
@elseif($section->section_key === 'survey_data')
@php $surveyData = $section; @endphp
@if($surveyData)
<section class="schemesection">
    <div class="container">

        <div class="row align-items-start">

            {{-- Left Content --}}
            <div class="col-lg-6">
                <div class="section-heading">
                    <span>Survey Information</span>
                    <h3>{{ $surveyData->title ?? 'Survey Data' }}</h3>
                </div>
                <p>
                    This page provides survey information and gives direct access to the official screening form.
                    Please complete the form with correct details and submit it to help the team with timely review,
                    follow-up, and better planning of LTBI screening activities.
                </p>
                
                @if(!empty($surveyData->videos[0]))
                <div class="btn-block mt-3">
                    <!-- <a href="{{ $surveyData->videos[0] }}"
                       target="_blank"
                       class="primary-btn">
                        Fill Survey Form
                        <i class="ri-arrow-right-up-line"></i>
                    </a> -->

<a href="{{ $surveyFormUrl }}"
   target="_blank"
   class="primary-btn">
    Fill Survey Form
    <i class="ri-arrow-right-up-line"></i>
</a>

                </div>
                @endif
            </div>

            {{-- QR Code --}}
            <div class="col-lg-6 d-flex flex-column align-items-center  justify-content-start mt-0">
                @if($surveyData->image)
                    @php $qrPath = resolveStorageImage($surveyData->image); @endphp
                    @if($qrPath)
                        <h5 class="mb-2 mt-0 text-center">Scan QR Code</h5>
                    <!-- <img src="{{ asset('storage/'.$qrPath) }}"
                         class="img-fluid shadow rounded"
                         style="max-width:300px;"
                         alt="Survey QR Code"> -->
                         <img src="{{ $surveyQrUrl }}"
                              class="img-fluid shadow rounded"
                              style="max-width:300px;"
                              alt="Survey QR Code">
                    @endif
                @endif
            </div>

        </div>
    </div>
</section>
@endif

@endif
@endforeach

@endsection


@push('scripts')
<script>
document.querySelectorAll(".mySwiper").forEach(function (el) {
    var paginationEl = el.querySelector(".swiper-pagination");
    new Swiper(el, {
        slidesPerView: 1,
        spaceBetween: 16,
        loop: el.querySelectorAll(".swiper-slide").length > 2,
        pagination: paginationEl ? {
            el: paginationEl,
            clickable: true,
            dynamicBullets: true,
            dynamicMainBullets: 5
        } : false,
        breakpoints: {
            0: { slidesPerView: 1 },
            576: { slidesPerView: 2 },
            992: { slidesPerView: 2 }
        }
    });
});

const lightbox = GLightbox({
    selector: ".glightbox"
});

document.querySelectorAll('[data-highlight-modal]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
        var modalId = trigger.getAttribute('data-highlight-modal');
        if (!modalId) return;
        var modalEl = document.getElementById(modalId);
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });
});
</script>
@endpush
