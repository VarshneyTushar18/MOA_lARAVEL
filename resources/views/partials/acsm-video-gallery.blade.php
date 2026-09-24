@php
    use App\Support\MediaGalleryRules;

    $videoItems = $videos ?? collect();
    $youtubeItems = $youtubes ?? collect();
    $slideCount = $videoItems->count() + $youtubeItems->count();
    $carouselThreshold = (int) ($carouselThreshold ?? MediaGalleryRules::CAROUSEL_THRESHOLD);
    $useCarousel = $slideCount > $carouselThreshold;
    $staticGridCount = min($slideCount, MediaGalleryRules::CAROUSEL_THRESHOLD);
@endphp


@if($slideCount > 0)

@if($useCarousel)

<div class="workshop-video-gallery mt-3 acsm-video-gallery acsm-video-gallery--carousel" data-slide-count="{{ $slideCount }}">

    <div class="workshop-video-gallery__stage">

        <div class="swiper workshop-video-swiper">

            <div class="swiper-wrapper">

                @foreach($videoItems as $video)

                    @php $path = resolveStoragePath($video->file_path); @endphp

                    @if($path)

                    <div class="swiper-slide">

                        @include('partials.acsm-video-player', ['video' => $video])

                    </div>

                    @endif

                @endforeach



                @foreach($youtubeItems as $yt)

                    <div class="swiper-slide">

                        <div class="workshop-video-gallery__player workshop-video-gallery__player--youtube">

                            @include('partials.youtube-card', ['url' => $yt->youtube_url])

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

        <button type="button" class="workshop-video-gallery__btn workshop-video-gallery__btn--prev" aria-label="Previous video">

            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>

        </button>

        <button type="button" class="workshop-video-gallery__btn workshop-video-gallery__btn--next" aria-label="Next video">

            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="m10 6-1.41 1.41L13.17 12l-4.58 4.59L10 18l6-6z"/></svg>

        </button>

        <div class="workshop-video-gallery__badge">

            <span class="workshop-video-gallery__fraction swiper-pagination"></span>

            <span class="workshop-video-gallery__total">{{ $slideCount }} video{{ $slideCount === 1 ? '' : 's' }}</span>

        </div>

    </div>

</div>

@else

<div class="workshop-video-gallery mt-3 acsm-video-gallery acsm-video-gallery--static" data-slide-count="{{ $slideCount }}">

    <div class="acsm-video-gallery__grid acsm-video-gallery__grid--{{ $staticGridCount }}">

        @foreach($videoItems as $video)

            @php $path = resolveStoragePath($video->file_path); @endphp

            @if($path)

            <div class="acsm-video-gallery__item">

                @include('partials.acsm-video-player', ['video' => $video])

            </div>

            @endif

        @endforeach



        @foreach($youtubeItems as $yt)

            <div class="acsm-video-gallery__item">

                <div class="workshop-video-gallery__player workshop-video-gallery__player--youtube">

                    @include('partials.youtube-card', ['url' => $yt->youtube_url])

                </div>

            </div>

        @endforeach

    </div>

    <p class="acsm-video-gallery__static-count text-center text-muted small mb-0">

        {{ $slideCount }} video{{ $slideCount === 1 ? '' : 's' }}

    </p>

</div>

@endif

@endif

