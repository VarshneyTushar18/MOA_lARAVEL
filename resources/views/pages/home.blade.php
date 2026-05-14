@extends('layout.frontend')

@section('content')

@php
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
    $surveyFormUrl = rtrim(config('app.url') ?: url('/'), '/') . $surveyFormPath;
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
@endphp

{{-- Banner / Carousel --}}
<section class="bannersection">
    <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">

        {{-- 🔵 INDICATORS (DOTS) GO HERE --}}
        <div class="carousel-indicators">
            @php $slideIndex = 0; @endphp

            @foreach($banners as $banner)

                {{-- Multiple Images --}}
                @if($banner->images->count())
                    @foreach($banner->images as $image)
                        <button type="button"
                                data-bs-target="#homeCarousel"
                                data-bs-slide-to="{{ $slideIndex }}"
                                class="{{ $slideIndex == 0 ? 'active' : '' }}"
                                aria-current="{{ $slideIndex == 0 ? 'true' : 'false' }}">
                        </button>
                        @php $slideIndex++; @endphp
                    @endforeach

                {{-- Single Image --}}
                @elseif($banner->image)
                    <button type="button"
                            data-bs-target="#homeCarousel"
                            data-bs-slide-to="{{ $slideIndex }}"
                            class="{{ $slideIndex == 0 ? 'active' : '' }}"
                            aria-current="{{ $slideIndex == 0 ? 'true' : 'false' }}">
                    </button>
                    @php $slideIndex++; @endphp
                @endif

            @endforeach
        </div>


        {{-- SLIDES --}}
        <div class="carousel-inner">

            @php $isFirst = true; @endphp

            @foreach($banners as $banner)

                @if($banner->images->count())
                    @foreach($banner->images as $image)
                        <div class="carousel-item {{ $isFirst ? 'active' : '' }}">
                            <img src="{{ asset('storage/'.$image->image) }}"
                                 class="d-block w-100">
                        </div>
                        @php $isFirst = false; @endphp
                    @endforeach

                @elseif($banner->image)
                    <div class="carousel-item {{ $isFirst ? 'active' : '' }}">
                        <img src="{{ asset('storage/'.$banner->image) }}"
                             class="d-block w-100">
                    </div>
                    @php $isFirst = false; @endphp
                @endif

            @endforeach

        </div>

        {{-- CONTROLS --}}
        <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>

    </div>
</section>

@if($homeMarquee && (filled($homeMarquee->title) || filled($homeMarquee->description)))
    @php
        $marqueeTextColor = $homeMarquee->text_color ?? '#ffffff';
        $marqueeBgColor = $homeMarquee->bg_color ?? '#162f6d';
        $marqueeContent = trim(($homeMarquee->title ? $homeMarquee->title . ' - ' : '') . ($homeMarquee->description ?? ''));
    @endphp
    <section class="home-marquee" style="background-color: {{ $marqueeBgColor }};">
        <div class="home-marquee__track" style="color: {{ $marqueeTextColor }};">
            <div class="home-marquee__inner">
                <span class="home-marquee__text">{{ $marqueeContent }}</span>
                <span class="home-marquee__text" aria-hidden="true">{{ $marqueeContent }}</span>
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
                            <img src="{{ asset('storage/'.$pmImg) }}" class="banner-img" alt="{{ $pm->title }}">
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
                            <p class="mb-0">{!! nl2br(e($pm->description)) !!}</p>
                        </div>

                        {{-- PM subsections --}}
                        @if($pm->subsections->count())
                            <div class="row g-3">
                                @foreach($pm->subsections as $sub)
                                    <div class="col-md-8">
                                        <div class="quote-card shadow-sm">
                                            <p class="mb-3">{{ $sub->description }}</p>
                                            <h5 class="mb-0">{{ $sub->title }}</h5>
                                            <div class="quote-block"><i class="ri-double-quotes-l"></i></div>
                                        </div>
                                    </div>
                                    @if($sub->image)
                                        <div class="col-md-4">
                                            <img src="{{ asset('storage/'.$sub->image) }}" class="pm-image shadow-sm w-100" alt="{{ $sub->title }}">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="action-btn"><a href="#"><i class="ri-arrow-right-up-line"></i></a></div>
                    </div>
                </div>
            </div>
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
                    <p class="about-text">{!! nl2br(e($ministry->description)) !!}</p>

                    {{-- Ministry subsections --}}
                    @if($ministry->subsections->count())
                        @foreach($ministry->subsections as $sub)
                            <div class="highlight-box d-flex align-items-center justify-content-between mt-4">
                                <div class="d-flex align-items-center">
                                    @if($sub->image)
                                        <img src="{{ asset('storage/'.$sub->image) }}" class="rounded-circle me-3" width="60" height="60" alt="">
                                    @endif
                                    <div>
                                        <h5>{{ $sub->title }}</h5>
                                        <small>{{ $sub->description }}</small>
                                    </div>
                                </div>
                                <h4 class="mb-0">FORMED ON 2014</h4>
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
                                <div class="img-card shadow-sm mb-3" style="height:258px;">
                                    <img src="{{ asset('storage/'.$imgPath) }}" class="img-fluid" alt="">
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
                            <div class="img-card shadow-sm" style="height:530px;">
                                <img src="{{ asset('storage/'.$imgPath) }}" class="img-fluid" alt="">
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
@endif

{{-- AIIA Section --}}
@if($aiia)
    <section class="aiiasection">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    @if($aiia->image)
                        <div class="image-block">
                            <img src="{{ asset('storage/'.$aiia->image) }}" alt="{{ $aiia->title }}" class="img-fluid">
                        </div>
                    @endif
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="section-heading">
                        <span>About AIIA</span>
                        <h2>{{ $aiia->title }}</h2>
                    </div>
                    <p>{!! nl2br(e($aiia->description)) !!}</p>

                    {{-- Subsections --}}
                    @if($aiia->subsections->count())
                        @foreach($aiia->subsections as $sub)
                            @if($sub->image)
                                <img src="{{ asset('storage/'.$sub->image) }}" alt="{{ $sub->title }}" class="img-fluid mb-3">
                            @endif
                            <h5>{{ $sub->title }}</h5>
                            <p>{{ $sub->description }}</p>
                        @endforeach
                    @endif

                    <div class="btn-block"><a href="https://aiia.gov.in/" class="primary-btn">Learn More <i class="ri-arrow-right-up-line"></i><i class="ri-arrow-right-line"></i></a></div>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- RNTCP Section --}}
@if($rntcp)
    <section class="rntcpsection">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="section-heading">
                        <span>About RNTCP</span>
                        <h2>{{ $rntcp->title }}</h2>
                    </div>
                    <p>{!! nl2br(e($rntcp->description)) !!}</p>

                    {{-- Subsections --}}
                    @if($rntcp->subsections->count())
                        @foreach($rntcp->subsections as $sub)
                            <p>{{ $sub->description }}</p>
                        @endforeach
                    @endif
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="objective-block">
                        @if($rntcp->subsections->count())
                            <h4 class="text-white pb-4 fw-semibold">Objectives of RNTCP:</h4>
                            <div class="objective-list">
                                @foreach($rntcp->subsections as $sub)
                                    <div class="list-item d-flex pb-3">
                                        <div class="icon"><i class="ri-arrow-right-circle-fill"></i></div>
                                        <p>{{ $sub->description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="action-btn"><a href="#"><i class="ri-arrow-right-up-line"></i></a></div>
                    </div>
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
            <div class="row">
                @foreach($roles as $role)
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="profile-card d-flex gap-3">
                            @if($role->image)
                                <div class="image">
                                    <img src="{{ asset('storage/'.$role->image) }}" alt="{{ $role->title }}" class="img-fluid">
                                </div>
                            @endif
                            <div class="content h-100">
                                <h4>{{ $role->title }}</h4>
                                <h6>{{ $role->description }}</h6>

                                {{-- Subsections for role --}}
                                @if($role->subsections->count())
                                    @foreach($role->subsections as $sub)
                                        <p>{{ $sub->description }}</p>
                                    @endforeach
                                @endif
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

                {!! $enrollYourself->description !!}

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

@endsection