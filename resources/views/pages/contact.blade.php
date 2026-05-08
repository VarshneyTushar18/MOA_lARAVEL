@extends('layout.frontend')

@section('content')

@php
    $grouped = $page->sections->groupBy('section_key');

    $contactInfo = $grouped->get('contact_info', collect())->first();
	$contactMap  = $grouped->get('contact_map', collect())->first();
    $contactForm = $grouped->get('contact_form', collect())->first();
@endphp

{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title ?? 'Contact Us' }}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li>
                        <img src="{{ asset('assets/images/double-arrow.svg') }}" alt="">
                    </li>
                    <li>
                        <a href="#">{{ $page->title ?? 'Contact Us' }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Contact Section --}}
<section class="contactsection">
    <div class="container">
        <div class="row">

            {{-- LEFT SIDE --}}
            @if($contactInfo)
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="contact-details">

                    <div class="section-heading">
                        <span>{{ $contactInfo->subtitle ?? 'Get in Touch' }}</span>
                        <h4>{!! nl2br(e($contactInfo->title)) !!}</h4>
                    </div>

                    <div class="desc">
                        {!! nl2br(e($contactInfo->description)) !!}
                    </div>

                </div>

                {{-- Map --}}
                @if($contactMap)
                <div class="map-block pt-4">
                    {!! $contactMap->description !!}
                </div>
                @endif
            </div>
            @endif


            {{-- RIGHT SIDE FORM --}}
            @if($contactForm)
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="form-block">

                    <div class="section-heading">
                        <span>{{ $contactForm->subtitle ?? 'Share Your Story' }}</span>
                        <h2>{{ $contactForm->title ?? 'Feedback Form' }}</h2>
                    </div>

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST">
                        @csrf

                        <div class="form-group pb-3">
                            <input type="text"
                                   name="fname"
                                   class="form-control"
                                   placeholder="Full Name"
                                   value="{{ old('fname') }}">
                            @error('fname')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group pb-3">
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="Email Address"
                                   value="{{ old('email') }}">
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group pb-5">
                            <textarea name="message"
                                      rows="5"
                                      class="form-control"
                                      placeholder="Type Your Message">{{ old('message') }}</textarea>
                            @error('message')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group pb-3">
                            <button type="submit" class="primary-btn">
                                Submit Details
                                <i class="ri-arrow-right-up-line"></i>
                                <i class="ri-arrow-right-line"></i>
                            </button>
                        </div>

                    </form>

                </div>
            </div>
            @endif

        </div>
    </div>
</section>

@endsection