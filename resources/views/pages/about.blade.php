@extends('layout.frontend')

@section('content')

@php
    $grouped = $page->sections->groupBy('section_key');

    $project = $grouped->get('project_detail', collect())->first();
    $scheme  = $grouped->get('scheme', collect())->first();
    $ntpc    = $grouped->get('ntpc', collect())->first();

    if (!function_exists('resolveStorageImage')) {
        function resolveStorageImage($filename) {
            if (!$filename) return null;
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($disk->exists($filename)) return $filename;
            if ($disk->exists('images/'.$filename)) return 'images/'.$filename;
            return null;
        }
    }
@endphp

{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title ?? 'About Us' }}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li><a href="#">{{ $page->title ?? 'About Us' }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Project Detail Section --}}
@if($project)
<section class="projectdetailsection">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                @if($project->image)
                    <div class="image-block">
                        <img src="{{ asset('storage/'.resolveStorageImage($project->image)) }}" alt="{{ $project->title }}" class="img-fluid">
                    </div>
                @endif
            </div>
            <div class="col-lg-4">
                <div class="content-block">
                    <div class="section-heading">
                        <span>{{ $project->subtitle ?? 'Project Details' }}</span>
                        <h3>{{ $project->title }}</h3>
                    </div>
                    <p>{!! nl2br(e($project->description)) !!}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Scheme Section --}}
@if($scheme)
<section class="schemesection">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="section-heading">
                    <span>{{ $scheme->subtitle ?? 'About MoA Scheme' }}</span>
                    <h3>{{ $scheme->title }}</h3>
                </div>
            </div>
            <div class="col-12">
                <div class="desc">
                    {!! nl2br(e($scheme->description)) !!}
                </div>
            </div>
        </div>

        <div class="row pt-3">

    {{-- LEFT COLUMN --}}
    <div class="col-lg-6 col-md-6 col-sm-12">

        @if(isset($scheme->subsections[0]))
            <div class="customcard grey">
                <h4>{{ $scheme->subsections[0]->title }}</h4>
                <div class="list-group">
                    @foreach(explode("\n", $scheme->subsections[0]->description) as $item)
                        @if(trim($item))
                        <div class="list-item d-flex">
                            <div class="icon">
                                <i class="ri-arrow-right-circle-fill"></i>
                            </div>
                            <p>{{ $item }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif


        @if(isset($scheme->subsections[1]))
            <div class="customcard blue text-white">
                <h4>{{ $scheme->subsections[1]->title }}</h4>
                <div class="list-group">
                    @foreach(explode("\n", $scheme->subsections[1]->description) as $item)
                        @if(trim($item))
                        <div class="list-item d-flex">
                            <div class="icon">
                                <i class="ri-arrow-right-circle-fill"></i>
                            </div>
                            <p>{{ $item }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

    </div>


    {{-- RIGHT COLUMN --}}
    <div class="col-lg-6 col-md-6 col-sm-12">

        @if(isset($scheme->subsections[2]))
            <div class="customcard green">
                <h4>{{ $scheme->subsections[2]->title }}</h4>
                <p>Roll out of authentic classical AYUSH interventions with Following objectives:</p>

                <div class="list-group">
                    @foreach(explode("\n", $scheme->subsections[2]->description) as $item)
                        @if(trim($item))
                        <div class="list-item d-flex">
                            <div class="icon">
                                <i class="ri-arrow-right-circle-fill"></i>
                            </div>
                            <p>{{ $item }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Button under green card --}}
            <div class="btn-block mt-3">
                <a href="#" class="primary-btn">
                    Download Guidelines
                    <i class="ri-arrow-right-up-line"></i>
                    <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        @endif

    </div>

</div>
    </div>
</section>
@endif

{{-- NTPC / Gallery Section --}}
@if($ntpc)
<section class="ntpcsection">
    <div class="container">
        <div class="row align-items-end">

            <div class="col-lg-5">
                <div class="section-heading">
                    <span>{{ $ntpc->subtitle ?? 'Project Implementation' }}</span>
                    <h2>{{ $ntpc->title }}</h2>
                </div>
                <p>{!! nl2br(e($ntpc->description)) !!}</p>
            </div>

            <div class="col-lg-7 position-relative">

                <div class="custom-nav">
                    <button class="custom-prev">&#10094;</button>
                    <button class="custom-next">&#10095;</button>
                </div>

                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">

                        @foreach($ntpc->images as $img)
                            @php
                                $imagePath = resolveStorageImage($img->image);
                            @endphp

                            @if($imagePath)
                                <div class="swiper-slide">
                                    <a href="{{ asset('storage/'.$imagePath) }}"
                                       class="glightbox"
                                       data-gallery="gallery1">
                                        <img src="{{ asset('storage/'.$imagePath) }}"
                                             class="img-fluid"
                                             alt="">
                                    </a>
                                </div>
                            @endif
                        @endforeach

                    </div>

                    <div class="swiper-pagination"></div>
                </div>

            </div>
        </div>
    </div>
</section>
@endif

@endsection
@push('scripts')
<script>
var swiper = new Swiper(".mySwiper", {
    slidesPerView: 2,
    spaceBetween: 20,
    loop: true,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".custom-next",
        prevEl: ".custom-prev",
    },
    breakpoints: {
        0: { slidesPerView: 1 },
        576: { slidesPerView: 2 },
        992: { slidesPerView: 2 }
    }
});

const lightbox = GLightbox({
    selector: ".glightbox"
});
</script>
@endpush