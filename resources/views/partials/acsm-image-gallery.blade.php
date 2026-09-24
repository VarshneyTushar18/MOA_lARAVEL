@php
    use App\Support\MediaGalleryRules;

    $galleryImages = $images ?? collect();
    $galleryId = $galleryId ?? 'acsm-images';
    $sectionTitle = $sectionTitle ?? 'Photo';
    $slideCount = $galleryImages->count();
    $carouselThreshold = (int) ($carouselThreshold ?? MediaGalleryRules::CAROUSEL_THRESHOLD);
    $useCarousel = $slideCount > $carouselThreshold;
@endphp

@if($slideCount > 0)
@if($useCarousel)
<div class="workshop-gallery acsm-image-gallery acsm-image-gallery--carousel" data-slide-count="{{ $slideCount }}" id="{{ $galleryId }}">
    <div class="workshop-gallery__stage">
        <div class="swiper workshop-swiper acsm-image-swiper">
            <div class="swiper-wrapper">
                @foreach($galleryImages as $img)
                    @php
                        $path = is_object($img) && isset($img->image) ? $img->image : (string) $img;
                        $resolved = function_exists('resolveStoragePath') ? resolveStoragePath($path) : $path;
                        if (!$resolved && function_exists('resolveStorageImage')) {
                            $resolved = resolveStorageImage($path);
                        }
                    @endphp
                    @if($resolved)
                    <div class="swiper-slide">
                        <a href="{{ asset('storage/'.$resolved) }}"
                           class="glightbox workshop-gallery__link"
                           data-gallery="{{ $galleryId }}">
                            <img src="{{ asset('storage/'.$resolved) }}"
                                 alt="{{ $sectionTitle }}"
                                 class="workshop-gallery__img"
                                 loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                 decoding="async">
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        <button type="button" class="workshop-gallery__btn workshop-gallery__btn--prev" aria-label="Previous photo">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        </button>
        <button type="button" class="workshop-gallery__btn workshop-gallery__btn--next" aria-label="Next photo">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="m10 6-1.41 1.41L13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
        </button>
        <div class="workshop-gallery__badge">
            <span class="workshop-gallery__fraction swiper-pagination"></span>
            <span class="workshop-gallery__total">{{ $slideCount }} photo{{ $slideCount === 1 ? '' : 's' }}</span>
        </div>
    </div>
</div>
@else
<div class="workshop-gallery acsm-image-gallery acsm-image-gallery--static" data-slide-count="{{ $slideCount }}" id="{{ $galleryId }}">
    <div class="acsm-image-gallery__grid acsm-image-gallery__grid--{{ min($slideCount, MediaGalleryRules::CAROUSEL_THRESHOLD) }}">
        @foreach($galleryImages as $img)
            @php
                $path = is_object($img) && isset($img->image) ? $img->image : (string) $img;
                $resolved = function_exists('resolveStoragePath') ? resolveStoragePath($path) : $path;
                if (!$resolved && function_exists('resolveStorageImage')) {
                    $resolved = resolveStorageImage($path);
                }
            @endphp
            @if($resolved)
            <a href="{{ asset('storage/'.$resolved) }}"
               class="glightbox acsm-gallery-card acsm-image-gallery__item"
               data-gallery="{{ $galleryId }}">
                <img src="{{ asset('storage/'.$resolved) }}"
                     alt="{{ $sectionTitle }}"
                     loading="lazy"
                     decoding="async">
            </a>
            @endif
        @endforeach
    </div>
    <p class="acsm-image-gallery__static-count text-center text-muted small mb-0">
        {{ $slideCount }} photo{{ $slideCount === 1 ? '' : 's' }}
    </p>
</div>
@endif
@endif
