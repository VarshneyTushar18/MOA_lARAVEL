@extends('layout.frontend')

@section('content')

@php
    $grouped = $page->sections->groupBy('section_key');

    function getSection($grouped, $key) {
        return $grouped->get($key, collect())->first();
    }

    function resolveStoragePath($path) {
        if (!$path) return null;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        return $disk->exists($path) ? $path : null;
    }
@endphp


{{-- ============================= --}}
{{-- PAGE HEADER --}}
{{-- ============================= --}}
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title}}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li><a href="#">{{ $page->title}}</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>


{{-- ============================= --}}
{{-- 1️⃣ SUCCESS STORIES --}}
{{-- ============================= --}}
@php $success = getSection($grouped,'success_stories'); @endphp
@php $videos = getSection($grouped,'patient_videos'); @endphp
@php $photos = getSection($grouped,'photos'); @endphp
<section class="ntpcsection mt-5 mb-5">
    <div class="container">
        <div class="accordion" id="bestPracticesAccordion">

        @if($success)
        <div class="accordion-item">
            <h2 class="accordion-header" id="bestHeadingSuccess">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#bestCollapseSuccess" aria-expanded="true" aria-controls="bestCollapseSuccess">
                    {{ $success->title ?? 'Success Stories' }}
                </button>
            </h2>
            <div id="bestCollapseSuccess" class="accordion-collapse collapse show" aria-labelledby="bestHeadingSuccess" data-bs-parent="#bestPracticesAccordion">
                <div class="accordion-body">
                    <div class="section-heading text-center mb-4">
                        <h2>{{ $success->title }}</h2>
                    </div>
                    <div class="row g-4">
                        @foreach($success->subsections ?? [] as $story)
                        <div class="col-md-6">
                            <div class="card shadow-sm p-4 h-100">
                                <h5>{{ $story->title }}</h5>
                                <p>{{ $story->description }}</p>
                            </div>
                        </div>
                        @endforeach
                        @foreach($success->media->where('type','pdf') as $file)
                            @php $extension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION)); @endphp
                        <div class="col-md-4">
                            <div class="card shadow-sm p-4 text-center h-100">
                                <h6 class="mb-3">{{ strtoupper($extension) }} File</h6>
                                <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-primary btn-sm mb-2">
                                    View {{ strtoupper($extension) }}
                                </a>
                                <a href="{{ asset('storage/'.$file->file_path) }}" download class="btn btn-outline-secondary btn-sm">
                                    Download
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($videos)
        <div class="accordion-item">
            <h2 class="accordion-header" id="bestHeadingVideos">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bestCollapseVideos" aria-expanded="false" aria-controls="bestCollapseVideos">
                    {{ $videos->title ?? 'Patient Videos' }}
                </button>
            </h2>
            <div id="bestCollapseVideos" class="accordion-collapse collapse" aria-labelledby="bestHeadingVideos" data-bs-parent="#bestPracticesAccordion">
                <div class="accordion-body">
                    <div class="section-heading text-center mb-4">
                        <h2>{{ $videos->title }}</h2>
                    </div>
                    <div class="row g-4">
                        @foreach($videos->media->where('type','video') as $video)
                            @php $path = resolveStoragePath($video->file_path); @endphp
                            @if($path)
                            <div class="col-md-6 col-lg-4">
                                <video width="100%" height="300" controls>
                                    <source src="{{ asset('storage/'.$path) }}" type="video/mp4">
                                </video>
                            </div>
                            @endif
                        @endforeach
                        @foreach($videos->media->where('type','youtube') as $yt)
                            @php
                                preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]+)/', $yt->youtube_url, $m);
                                $id = $m[1] ?? null;
                            @endphp
                            @if($id)
                            <div class="col-md-6 col-lg-4">
                                <iframe width="100%" height="300" src="https://www.youtube.com/embed/{{ $id }}" allowfullscreen></iframe>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($photos)
        <div class="accordion-item">
            <h2 class="accordion-header" id="bestHeadingPhotos">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bestCollapsePhotos" aria-expanded="false" aria-controls="bestCollapsePhotos">
                    {{ $photos->title ?? 'Photos' }}
                </button>
            </h2>
            <div id="bestCollapsePhotos" class="accordion-collapse collapse" aria-labelledby="bestHeadingPhotos" data-bs-parent="#bestPracticesAccordion">
                <div class="accordion-body">

                    <div class="section-heading text-center mb-4">
                        <h2>{{ $photos->title }}</h2>
                    </div>
                    <div class="row g-4">
                        @foreach($photos->images as $img)
                        <div class="col-6 col-md-4 col-lg-3">
                            <img src="{{ asset('storage/'.$img->image) }}" class="img-fluid rounded shadow">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        </div>
    </div>
</section>

@include('partials.page-faq')

@endsection