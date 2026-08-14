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

    $acsmSections = $page->sections
        ->filter(function ($section) {
            if ($section->parent_id && (int) $section->parent_id !== (int) $section->id) {
                return false;
            }
            return filled($section->description)
                || !empty($section->image)
                || $section->images->count()
                || $section->media->count();
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

<section class="ntpcsection mt-5">
    <div class="container">
        <div class="accordion" id="acsmIecAccordion">
            @foreach($acsmSections as $section)
                @include('partials.acsm-section', [
                    'section' => $section,
                    'isFirst' => $loop->first,
                ])
            @endforeach
        </div>
    </div>
</section>

@include('partials.page-faq')

@endsection

@push('scripts')
<script>
if (typeof GLightbox === 'function') {
    GLightbox({ selector: '.glightbox' });
}
</script>
@endpush
