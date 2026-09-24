@extends('layout.frontend')

@section('content')

@php
    if (!function_exists('resolveStoragePath')) {
        function resolveStoragePath($path) {
            if (!$path) return null;
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            $normalized = ltrim(str_replace('\\', '/', $path), '/');
            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, strlen('storage/'));
            }
            if ($disk->exists($normalized) || $disk->exists($path)) {
                return $normalized;
            }
            if (is_file(public_path('storage/'.$normalized))) {
                return $normalized;
            }
            return $path;
        }
    }

    $bestSections = $page->sections
        ->filter(function ($section) {
            return empty($section->parent_id);
        })
        ->sortBy(function ($section) {
            $order = (int) ($section->sort_order ?? 0);
            if ($order <= 0) {
                $order = 100000 + (int) $section->id;
            }
            return sprintf('%010d-%010d', $order, (int) $section->id);
        })
        ->values();
@endphp

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title }}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li><a href="#">{{ $page->title }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="ntpcsection mt-5 mb-5">
    <div class="container">
        <div class="accordion" id="bestPracticesAccordion">
            @foreach($bestSections as $section)
                @include('partials.acsm-section', [
                    'section' => $section,
                    'isFirst' => $loop->first,
                    'accordionParent' => 'bestPracticesAccordion',
                ])
            @endforeach
        </div>
    </div>
</section>

@include('partials.page-faq')

@endsection

@push('scripts')
@include('partials.acsm-gallery-scripts')
<script>
if (typeof GLightbox === 'function') {
    GLightbox({ selector: '.glightbox' });
}

document.querySelectorAll('#bestPracticesAccordion .accordion-collapse').forEach(function (panel) {
    panel.addEventListener('shown.bs.collapse', function () {
        pauseAcsmVideos(panel);
        initAcsmCarouselsIn(panel);
    });
});

document.querySelectorAll('#bestPracticesAccordion .accordion-collapse.show').forEach(function (panel) {
    initAcsmCarouselsIn(panel);
});
</script>
@endpush
