@extends('layout.frontend')

@section('content')

@php
    $grouped = $page->sections->groupBy('section_key');

    function getSection($grouped, $key) {
        return $grouped->get($key, collect())->first();
    }

    function resolveStoragePath($path) {
        if (!$path) return null;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        return $disk->exists($path) ? $path : null;
    }
@endphp



{{-- ================= PAGE HEADER ================= --}}
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $page->title ?? 'Performance Report' }}</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li>{{ $page->title ?? 'Performance Report' }}</li>
                </ul>
            </div>
        </div>
    </div>
</section>


{{-- ============================= --}}
{{-- PERFORMANCE REPORT --}}
{{-- ============================= --}}
{{-- ============================= --}}
{{-- PERFORMANCE REPORT --}}
{{-- ============================= --}}
@php $performance = getSection($grouped,'performance_report'); @endphp

@if($performance && $performance->media->where('type','pdf')->count())
<section class="ntpcsection mt-5">
    <div class="container">

        <div class="row g-4">
            @foreach($performance->media->where('type','pdf') as $pdf)

                @php $path = resolveStoragePath($pdf->file_path); @endphp

                @if($path)
                <div class="col-md-4">
                    <div class="card shadow-sm p-4 text-center h-100">

                        {{-- SECTION TITLE --}}
                        @if($performance->title)
                            <h5 class="mb-2 fw-bold">
                                {{ $performance->title }}
                            </h5>
                        @endif

                        {{-- SECTION DESCRIPTION --}}
                        @if($performance->description)
                            <p class="small text-muted mb-3">
                                {!! nl2br(e($performance->description)) !!}
                            </p>
                        @endif

                        <hr>

                        {{-- PDF TITLE --}}
                        @if($pdf->title)
                            <h6 class="mb-2">{{ $pdf->title }}</h6>
                        @endif

                        {{-- PDF DESCRIPTION --}}
                        @if($pdf->description)
                            <p class="small text-muted">
                                {!! nl2br(e($pdf->description)) !!}
                            </p>
                        @endif

                        <a href="{{ asset('storage/'.$pdf->file_path) }}"
               download="{{ pathinfo($pdf->file_path, PATHINFO_BASENAME) }}"
               class="btn btn-primary btn-sm">
               Download PDF
            </a>
            <a href="{{ asset('storage/'.$pdf->file_path) }}"
               target="_blank"
               class="btn btn-secondary btn-sm mt-2">
               View Online
            </a>

                    </div>
                </div>
                @endif

            @endforeach
        </div>

    </div>
</section>
@endif

@endsection