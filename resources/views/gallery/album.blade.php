@extends('layout.frontend')

@section('content')

<section class="page-header">
    <div class="container">
        <h1>{{ $section->title ?: 'Gallery Album' }}</h1>
        <ul class="breadcrumbs">
            <li><a href="/">Home</a></li>
            <li><a href="{{ route('gallery.index') }}">Gallery</a></li>
            <li>{{ $section->title ?: 'Album' }}</li>
        </ul>
    </div>
</section>

<section class="gallery-album-page py-5">
    <div class="container">
        @if(filled($section->description))
            <p class="gallery-album-page__intro mb-4">{!! nl2br(e($section->description)) !!}</p>
        @endif

        <div class="row g-3 g-md-4 gallery-photo-grid">
            @foreach($photos as $photo)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ asset('storage/'.$photo) }}"
                       class="gallery-photo-card glightbox"
                       data-gallery="album-{{ $section->id }}">
                        <img src="{{ asset('storage/'.$photo) }}"
                             alt="{{ $section->title ?: 'Gallery photo' }}"
                             class="gallery-photo-card__image"
                             loading="lazy"
                             decoding="async">
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            <a href="{{ route('gallery.index') }}" class="primary-btn">
                Back to Gallery <i class="ri-arrow-left-line"></i>
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof GLightbox !== 'undefined') {
            GLightbox({ selector: '.glightbox' });
        }
    });
</script>
@endpush
