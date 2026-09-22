@extends('layout.frontend')

@section('content')
@php
    use App\Http\Controllers\GalleryController;
@endphp

<section class="page-header">
    <div class="container">
        <h1>Gallery</h1>
        <ul class="breadcrumbs">
            <li>Gallery</li>
        </ul>
    </div>
</section>

<section class="gallery-page py-5">
    <div class="container">
        @if($albums->count())
            <div class="row g-4 gallery-album-grid">
                @foreach($albums as $album)
                    @php $cover = GalleryController::albumCover($album); @endphp
                    @if($cover)
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <article class="gallery-album-card">
                                <a href="{{ route('gallery.show', $album) }}" class="gallery-album-card__link">
                                    <div class="gallery-album-card__image-wrap">
                                        <img src="{{ asset('storage/'.$cover) }}"
                                             alt="{{ $album->title ?: 'Gallery album' }}"
                                             class="gallery-album-card__image"
                                             loading="lazy"
                                             decoding="async">
                                        <span class="gallery-album-card__overlay">View Gallery</span>
                                    </div>
                                    <h3 class="gallery-album-card__title">{{ $album->title ?: 'Untitled Album' }}</h3>
                                </a>
                            </article>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="alert alert-light border">
                No gallery albums yet. Add albums in <strong>Admin → Manage Pages → Gallery → Sections</strong>.
            </div>
        @endif
    </div>
</section>
@endsection
