@extends('layout.frontend')

@section('content')

@php
    use App\Http\Controllers\PageSectionsController;

    // Expecting $page to be supplied
    $grouped = $page->sections->groupBy('section_key');

    $banners = $grouped->get('hero_banner', collect());
    $homeMarquee = $grouped->get('home_marquee', collect())->first();
    $pm = $grouped->get('pm_yojna', collect())->first();
    $ministry = $grouped->get('ministry', collect())->first();
    $aiia = $grouped->get('aiia', collect())->first();
    $rntcp = $grouped->get('rntcp', collect())->first();
    $roles = $grouped->get('roles', collect());
    $enrollYourself = $grouped->get('enroll_yourself', collect())->first();
    $surveyFormPath = route('survey.form', [], false);
    $surveyFormUrl = request()->getSchemeAndHttpHost() . $surveyFormPath;
    $surveyQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($surveyFormUrl);

    if (!function_exists('resolveStorageImage')) {
        function resolveStorageImage($filename) {
            if (empty($filename)) return null;
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($disk->exists($filename)) return $filename;
            if ($disk->exists('banners/'.$filename)) return 'banners/'.$filename;
            if ($disk->exists('images/'.$filename)) return 'images/'.$filename;
            return null;
        }
    }

    $galleryTeaser = \App\Http\Controllers\GalleryController::homeTeaserData($page);
    $galleryHeading = $galleryTeaser['heading'];
    $galleryPreview = $galleryTeaser['preview'];
    $gallerySingles = $galleryTeaser['singles'];
    $galleryAlbums = $galleryTeaser['albums'];
    $showGalleryTeaser = $galleryTeaser['show'];
    $galleryButtonLabel = 'View All Photos';

    $heroCarousel = app(\App\Services\HeroCarouselService::class);
    $carouselSlides = collect();
    foreach ($banners->sortBy('sort_order') as $banner) {
        $carouselSlides = $carouselSlides->concat($heroCarousel->slidesForBanner($banner));
    }
@endphp

{{-- Banner / Carousel --}}
@if($carouselSlides->count())
<section class="bannersection">
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

        <div class="carousel-inner">
            @foreach($carouselSlides as $index => $slide)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="banner-carousel-shell">
                        @if($slide['type'] === 'image')
                            <img src="{{ $slide['src'] }}" class="banner-carousel-media banner-carousel-photo" alt="">
                        @elseif($slide['type'] === 'video')
                            <video class="banner-carousel-media banner-carousel-video" playsinline preload="auto" controls controlsList="nodownload noplaybackrate" @if($index === 0) autoplay @endif>
                                <source src="{{ $slide['src'] }}" type="{{ $slide['mime'] ?? 'video/mp4' }}">
                            </video>
                        @elseif($slide['type'] === 'youtube')
                            <div class="banner-carousel-youtube">
                                <iframe src="https://www.youtube.com/embed/{{ $slide['youtube_id'] }}?rel=0"
                                        title="YouTube video"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen></iframe>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($carouselSlides->count() > 1)
            <div class="carousel-indicators banner-carousel-indicators">
                @foreach($carouselSlides as $index => $slide)
                    <button type="button"
                            data-bs-target="#homeCarousel"
                            data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $index + 1 }}">
                    </button>
                @endforeach
            </div>
        @endif

        @php $carouselHasVideo = $carouselSlides->contains(fn ($slide) => ($slide['type'] ?? null) === 'video'); @endphp
        @if($carouselHasVideo)
            <button type="button" id="homeCarouselSound" class="banner-carousel-sound" aria-pressed="false" aria-label="Turn sound on">
                <i class="ri-volume-mute-line banner-carousel-sound__icon-muted" aria-hidden="true"></i>
                <i class="ri-volume-up-line banner-carousel-sound__icon-on d-none" aria-hidden="true"></i>
                <span class="banner-carousel-sound__label">Turn sound on</span>
            </button>
        @endif

        @if($carouselSlides->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        @endif

    </div>
</section>
@endif

@php
    $marqueeLinks = collect();
    if ($homeMarquee) {
        $marqueeLinks = $homeMarquee->subsections
            ->filter(function ($link) {
                return filled($link->title)
                    && filled(PageSectionsController::resolveMarqueeLinkHref($link));
            })
            ->sortBy('sort_order')
            ->values();
    }
@endphp
@if($homeMarquee && $marqueeLinks->count())
    @php
        $marqueeTextColor = $homeMarquee->text_color ?? '#333333';
        $marqueeBgColor = $homeMarquee->bg_color ?? '#f3f3f3';
    @endphp
    <section class="home-marquee" style="background-color: {{ $marqueeBgColor }}; color: {{ $marqueeTextColor }};">
        <div class="container-fluid px-0">
            <div class="home-marquee__bar">
                <div class="home-marquee__track" id="homeMarqueeTrack">
                    <div class="home-marquee__inner" id="homeMarqueeInner">
                        @foreach([1, 2] as $loopPass)
                            @foreach($marqueeLinks as $link)
                                <span class="home-marquee__item">
                                    <span class="home-marquee__chevron" aria-hidden="true">&gt;</span>
                                    <a href="{{ PageSectionsController::resolveMarqueeLinkHref($link) }}" class="home-marquee__link" target="_blank" rel="noopener noreferrer">{{ $link->title }}</a>
                                </span>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                <button type="button" class="home-marquee__pause" id="homeMarqueePause" aria-label="Pause scrolling links" aria-pressed="false">
                    <i class="ri-pause-line home-marquee__pause-icon" aria-hidden="true"></i>
                    <i class="ri-play-line home-marquee__play-icon d-none" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </section>
@endif

{{-- PM Yojna --}}
@if($pm)
    <section class="yojnasection">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="main-card shadow-sm">
                        @php $pmImg = resolveStorageImage($pm->image); @endphp
                        @if($pmImg)
                            <img src="{{ asset('storage/'.$pmImg) }}" class="banner-img" alt="{{ $pm->title }}" loading="lazy" decoding="async">
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="d-flex flex-column gap-4 h-100 position-relative">
                        <div class="info-card shadow-sm">
                            <div class="section-heading">
                                <span>{{ $pm->title ?? 'PM Yojna' }}</span>
                                <h2>{{ $pm->title }}</h2>
                            </div>
                            @include('partials.read-more-text', [
                                'text' => $pm->description,
                                'class' => 'mb-0',
                                'limit' => 50,
                                'readMoreUrl' => route('pm-tb-mukt-bharat'),
                            ])
                        </div>

                        <div class="action-btn"><a href="{{ route('pm-tb-mukt-bharat') }}" aria-label="Read more about Pradhan Mantri TB Mukt Bharat Abhiyan"><i class="ri-arrow-right-up-line"></i></a></div>
                    </div>
                </div>
            </div>

            {{-- Quote strip: full width (same span as big banner row), short height --}}
            @if($pm->subsections->count())
                <div class="row g-3 pm-quote-row mt-1">
                    @foreach($pm->subsections as $sub)
                        <div class="col-lg-9 col-md-8 pm-quote-row__quote">
                            <div class="quote-card shadow-sm">
                                <p class="mb-2">{{ $sub->description }}</p>
                                <h5 class="mb-0">{{ $sub->title }}</h5>
                                <div class="quote-block"><i class="ri-double-quotes-l"></i></div>
                            </div>
                        </div>
                        @if($sub->image)
                            <div class="col-lg-3 col-md-4 pm-quote-row__photo">
                                <img src="{{ asset('storage/'.$sub->image) }}" class="pm-image shadow-sm" alt="{{ $sub->title }}" loading="lazy" decoding="async">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif

{{-- MoA Section --}}
{{-- MoA Section --}}
@if($ministry)
<section class="moasection">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="moacontentblock">
                    <div class="section-heading">
                        <span>Ministry of Ayush</span>
                        <h2>{{ $ministry->title ?? 'About Ministry' }}</h2>
                    </div>
                    @include('partials.read-more-text', ['text' => $ministry->description, 'class' => 'about-text'])

                    {{-- Ministry subsections --}}
                    @if($ministry->subsections->count())
                        @foreach($ministry->subsections as $sub)
                            <div class="highlight-box d-flex align-items-center mt-4">
                                <div class="d-flex align-items-center highlight-box__profile">
                                    @if($sub->image)
                                        <img src="{{ asset('storage/'.$sub->image) }}" class="highlight-box__photo rounded-circle me-3" alt="{{ $sub->title }}" loading="lazy" decoding="async">
                                    @endif
                                    <div>
                                        <h5>{{ $sub->title }}</h5>
                                        <small>{{ $sub->description }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="btn-block"><a href="https://ayush.gov.in/#!/" class="primary-btn">Learn More <i class="ri-arrow-right-up-line"></i><i class="ri-arrow-right-line"></i></a></div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row g-4 image-area">

                    {{-- LEFT column: first 2 images stacked --}}
                    <div class="col-6">
                        @foreach([0,1] as $i)
                            @if(isset($ministry->images[$i]))
                                @php
                                    $imgPath = $ministry->images[$i]->image; // direct path
                                @endphp
                                <div class="img-card img-card--short shadow-sm mb-3">
                                    <img src="{{ asset('storage/'.$imgPath) }}" class="img-fluid" alt="" loading="lazy" decoding="async">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- RIGHT column: 3rd tall image --}}
                    <div class="col-6">
                        @if(isset($ministry->images[2]))
                            @php
                                $imgPath = $ministry->images[2]->image; // direct path
                            @endphp
                            <div class="img-card img-card--tall shadow-sm">
                                <img src="{{ asset('storage/'.$imgPath) }}" class="img-fluid" alt="" loading="lazy" decoding="async">
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
@endif

{{-- AIIA Section (50/50 split with gallery when available) --}}
@if($aiia)
    <section class="aiiasection">
        <div class="container">
            <div class="row aiiasection__split-row g-4 g-lg-5">
                <div class="col-lg-{{ $showGalleryTeaser ? '6' : '12' }} col-md-12">
                    <div class="aiiasection__panel aiia-content-panel">
                        <div class="section-heading aiiasection__split-heading">
                            <span>About AIIA</span>
                            <h2>{{ $aiia->title }}</h2>
                        </div>

                        @if($aiia->image)
                            <div class="aiia-content-panel__image">
                                <img src="{{ asset('storage/'.$aiia->image) }}" alt="{{ $aiia->title }}" loading="lazy" decoding="async">
                            </div>
                        @endif

                        <div class="aiiasection__panel-body">
                            @include('partials.read-more-text', ['text' => $aiia->description])

                            @if($aiia->subsections->count())
                                @foreach($aiia->subsections as $sub)
                                    @if($sub->image)
                                        <img src="{{ asset('storage/'.$sub->image) }}" alt="{{ $sub->title }}" class="img-fluid mb-3 mt-3" loading="lazy" decoding="async">
                                    @endif
                                    <h5>{{ $sub->title }}</h5>
                                    <p>{{ $sub->description }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="btn-block aiiasection__panel-action">
                            <a href="https://aiia.gov.in/" class="primary-btn">Learn More <i class="ri-arrow-right-up-line"></i><i class="ri-arrow-right-line"></i></a>
                        </div>
                    </div>
                </div>

                @if($showGalleryTeaser)
                    <div class="col-lg-6 col-md-12">
                        <aside class="aiiasection__panel aiia-gallery-panel">
                            <div class="section-heading aiiasection__split-heading">
                                <h2>{{ $galleryHeading }}</h2>
                            </div>

                            @if($galleryPreview)
                                <a href="{{ route('gallery.index') }}" class="aiia-gallery-panel__hero">
                                    <img src="{{ asset('storage/'.$galleryPreview) }}"
                                         alt="{{ $galleryHeading }}"
                                         loading="lazy"
                                         decoding="async">
                                    <span class="aiia-gallery-panel__overlay">
                                        <span class="aiia-gallery-panel__overlay-btn">View Gallery</span>
                                    </span>
                                </a>
                            @endif

                            @if($gallerySingles->count() > 1)
                                <p class="aiia-gallery-panel__categories-label">Featured Photos</p>
                                <div class="aiia-gallery-panel__singles">
                                    @foreach($gallerySingles->skip(1)->take(6) as $single)
                                        <a href="{{ $single['url'] }}" class="aiia-gallery-panel__single">
                                            <img src="{{ asset('storage/'.$single['path']) }}"
                                                 alt="{{ $single['title'] ?? 'Gallery photo' }}"
                                                 loading="lazy"
                                                 decoding="async">
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if($galleryAlbums->isNotEmpty())
                                <p class="aiia-gallery-panel__categories-label">Albums</p>
                                <div class="aiia-gallery-panel__albums">
                                    @foreach($galleryAlbums->take(6) as $album)
                                        <a href="{{ $album['url'] }}" class="aiia-gallery-panel__album">
                                            <span class="aiia-gallery-panel__album-thumb">
                                                <img src="{{ asset('storage/'.$album['cover']) }}"
                                                     alt="{{ $album['title'] }}"
                                                     loading="lazy"
                                                     decoding="async">
                                            </span>
                                            <span class="aiia-gallery-panel__album-title">{{ \Illuminate\Support\Str::limit($album['title'], 40) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="btn-block aiiasection__panel-action">
                                <a href="{{ route('gallery.index') }}" class="primary-btn">
                                    {{ $galleryButtonLabel }}
                                    <i class="ri-arrow-right-up-line"></i><i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </aside>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

{{-- RNTCP Section --}}
@if($rntcp)
    <section class="rntcpsection">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-7 col-lg-8 col-md-10">
                    <div class="section-heading">
                        <span>About RNTCP</span>
                        <h2>{{ $rntcp->title }}</h2>
                    </div>
                    @include('partials.read-more-text', [
                        'text' => $rntcp->description,
                        'limit' => 35,
                        'class' => 'read-more-text--light read-more-text--compact mb-0',
                        'readMoreUrl' => route('about-rntcp'),
                    ])
                </div>
            </div>
        </div>
    </section>
@endif

{{-- Roles Section --}}
@if($roles->count())
    <section class="rolsresponsibilitysection">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center pb-5">Personals Roles and Responsibility</h2>
                </div>
            </div>
            <div class="row roles-row g-3 g-lg-4">
                @foreach($roles as $role)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="profile-card d-flex gap-3">
                            @if($role->image)
                                <div class="image">
                                    <img src="{{ asset('storage/'.$role->image) }}" alt="{{ $role->title }}" loading="lazy" decoding="async">
                                </div>
                            @endif
                            <div class="content h-100">
                                <h4>{{ $role->title }}</h4>
                                <h6>{{ $role->description }}</h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if($enrollYourself)
<section class="schemesection">
    <div class="container">

        <div class="row align-items-start">

            {{-- Left Content --}}
            <div class="col-lg-6">
                <div class="section-heading">
                    <span>Screening Performa</span>
                    <h3>{{ $enrollYourself->title ?? 'Enroll Yourself' }}</h3>
                </div>

                <p>
                    This page helps you complete the screening process and share your details through the official survey form.
                    Please review the instructions, fill in accurate information, and submit the form to support timely assessment
                    and follow-up by the concerned team.
                </p>

                <div class="btn-block mt-3">
                    <a href="{{ $surveyFormUrl }}" class="primary-btn">
                        Fill Survey Form
                        <i class="ri-arrow-right-up-line"></i>
                    </a>
                </div>
            </div>

            {{-- QR Code --}}
            <div class="col-lg-6 d-flex flex-column align-items-center justify-content-start mt-0">
                <h5 class="mb-2 mt-0">Scan QR Code</h5>
                <img src="{{ $surveyQrUrl }}"
                     class="img-fluid shadow rounded"
                     style="max-width:300px;"
                     alt="Survey QR Code">
            </div>

        </div>
    </div>
</section>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var carousel = document.getElementById('homeCarousel');
    if (carousel) {
        var soundButton = document.getElementById('homeCarouselSound');
        var carouselAudioEnabled = false;
        var bsCarousel = (typeof bootstrap !== 'undefined')
            ? bootstrap.Carousel.getOrCreateInstance(carousel, { interval: 5000, ride: false })
            : null;

        function activeCarouselVideo() {
            var activeItem = carousel.querySelector('.carousel-item.active');
            return activeItem ? activeItem.querySelector('.banner-carousel-video') : null;
        }

        function updateSoundButton() {
            if (!soundButton) {
                return;
            }

            var activeVideo = activeCarouselVideo();
            if (!activeVideo) {
                soundButton.hidden = true;
                return;
            }

            soundButton.hidden = false;
            soundButton.setAttribute('aria-pressed', carouselAudioEnabled ? 'true' : 'false');
            soundButton.setAttribute('aria-label', carouselAudioEnabled ? 'Turn sound off' : 'Turn sound on');
            soundButton.querySelector('.banner-carousel-sound__label').textContent = carouselAudioEnabled ? 'Sound on' : 'Turn sound on';
            soundButton.querySelector('.banner-carousel-sound__icon-muted').classList.toggle('d-none', carouselAudioEnabled);
            soundButton.querySelector('.banner-carousel-sound__icon-on').classList.toggle('d-none', !carouselAudioEnabled);
        }

        function setCarouselVideoMuted(muted) {
            carouselAudioEnabled = !muted;
            carousel.querySelectorAll('.banner-carousel-video').forEach(function (video) {
                video.muted = muted;
                video.volume = muted ? 0 : 1;
            });
            updateSoundButton();
        }

        function manageCarouselAdvance() {
            carousel.querySelectorAll('.banner-carousel-video').forEach(function (video) {
                video.onended = null;
                video.loop = false;
            });

            var activeVideo = activeCarouselVideo();
            if (activeVideo) {
                if (bsCarousel) {
                    bsCarousel.pause();
                }
                activeVideo.onended = function () {
                    if (bsCarousel) {
                        bsCarousel.next();
                    }
                };
                return;
            }

            if (bsCarousel) {
                bsCarousel.cycle();
            }
        }

        function playActiveCarouselVideo() {
            carousel.querySelectorAll('.banner-carousel-video').forEach(function (video) {
                video.pause();
                video.removeAttribute('autoplay');
            });

            var activeVideo = activeCarouselVideo();
            if (!activeVideo) {
                updateSoundButton();
                manageCarouselAdvance();
                return;
            }

            activeVideo.setAttribute('autoplay', '');
            activeVideo.muted = !carouselAudioEnabled;
            activeVideo.volume = carouselAudioEnabled ? 1 : 0;
            activeVideo.currentTime = 0;
            activeVideo.loop = false;

            var playPromise = activeVideo.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {
                    if (!carouselAudioEnabled) {
                        activeVideo.muted = true;
                        activeVideo.play().catch(function () {});
                    }
                });
            }

            updateSoundButton();
            manageCarouselAdvance();
        }

        if (soundButton) {
            soundButton.addEventListener('click', function () {
                var activeVideo = activeCarouselVideo();
                if (!activeVideo) {
                    return;
                }

                setCarouselVideoMuted(carouselAudioEnabled);
                activeVideo.play().catch(function () {});
            });
        }

        carousel.addEventListener('slide.bs.carousel', playActiveCarouselVideo);
        carousel.addEventListener('slid.bs.carousel', playActiveCarouselVideo);
        playActiveCarouselVideo();
    }

    var inner = document.getElementById('homeMarqueeInner');
    var pauseBtn = document.getElementById('homeMarqueePause');
    if (!inner || !pauseBtn) return;

    pauseBtn.addEventListener('click', function () {
        var paused = inner.classList.toggle('is-paused');
        pauseBtn.setAttribute('aria-pressed', paused ? 'true' : 'false');
        pauseBtn.setAttribute('aria-label', paused ? 'Play scrolling links' : 'Pause scrolling links');
        pauseBtn.querySelector('.home-marquee__pause-icon').classList.toggle('d-none', paused);
        pauseBtn.querySelector('.home-marquee__play-icon').classList.toggle('d-none', !paused);
    });
});
</script>
@endpush

@endsection