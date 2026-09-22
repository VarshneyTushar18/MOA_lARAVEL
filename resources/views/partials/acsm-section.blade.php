@php
    $videos = $section->media->where('type', 'video');
    $youtubes = $section->media->where('type', 'youtube');
    $audios = $section->media->where('type', 'audio');
    $pdfs = $section->media->where('type', 'pdf');
    $images = $section->images;
    $hasMain = !empty($section->image);
    $headingId = 'acsmHeading'.$section->id;
    $collapseId = 'acsmCollapse'.$section->id;
    $accordionParent = $accordionParent ?? 'acsmIecAccordion';
@endphp

<div class="accordion-item">
    <h2 class="accordion-header" id="{{ $headingId }}">
        <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $isFirst ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
            {{ $section->title ?: ucwords(str_replace('_', ' ', $section->section_key)) }}
        </button>
    </h2>
    <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}" aria-labelledby="{{ $headingId }}" data-bs-parent="#{{ $accordionParent }}">
        <div class="accordion-body">
            <div class="section-heading text-center mb-4">
                <h2>{{ $section->title ?: ucwords(str_replace('_', ' ', $section->section_key)) }}</h2>
                @if(filled($section->description))
                    <p class="text-muted">{!! nl2br(e($section->description)) !!}</p>
                @endif
            </div>

            @if($videos->count() || $youtubes->count())
            <div class="row g-4">
                @foreach($videos as $video)
                    @php $path = resolveStoragePath($video->file_path); @endphp
                    @if($path)
                    <div class="col-md-6 col-lg-4">
                        <video width="100%" height="300" controls>
                            <source src="{{ asset('storage/'.$path) }}" type="video/mp4">
                        </video>
                    </div>
                    @endif
                @endforeach

                @foreach($youtubes as $yt)
                    <div class="col-md-6 col-lg-4">
                        @include('partials.youtube-card', ['url' => $yt->youtube_url])
                    </div>
                @endforeach
            </div>
            @endif

            @if(($videos->count() || $youtubes->count()) && $audios->count())
                <div class="my-5"></div>
            @endif

            @if($audios->count())
            <div class="row g-4">
                @foreach($audios as $audio)
                    @php $path = resolveStoragePath($audio->file_path) ?: $audio->file_path; @endphp
                    @if($path)
                    <div class="col-md-6 col-lg-4">
                        <audio controls style="width:100%;">
                            <source src="{{ asset('storage/'.$path) }}" type="audio/mpeg">
                        </audio>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            @if(($videos->count() || $youtubes->count() || $audios->count()) && ($hasMain || $images->count()))
                <div class="my-5"></div>
            @endif

            @if($hasMain || $images->count())
                @php
                    $galleryImages = collect();
                    if ($hasMain) {
                        $galleryImages->push((object) ['image' => $section->image]);
                    }
                    $galleryImages = $galleryImages->concat($images);
                    $imgCount = $galleryImages->count();
                    $isFew = $imgCount <= 2;
                    $colClass = $isFew ? 'col-12 col-md-6' : 'col-12 col-sm-6 col-lg-3';
                    $galleryClass = $isFew ? 'acsm-gallery acsm-gallery--few' : 'acsm-gallery acsm-gallery--grid';
                @endphp
                <div class="row g-3 g-md-4 {{ $galleryClass }}">
                    @foreach($galleryImages as $img)
                        <div class="{{ $colClass }}">
                            <a href="{{ asset('storage/'.$img->image) }}"
                               class="glightbox acsm-gallery-card"
                               data-gallery="acsm-{{ $section->id }}">
                                <img src="{{ asset('storage/'.$img->image) }}" alt="{{ $section->title }}" loading="lazy" decoding="async">
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(($videos->count() || $youtubes->count() || $audios->count() || $hasMain || $images->count()) && $pdfs->count())
                <div class="my-5"></div>
            @endif

            @if($pdfs->count())
            <div class="row g-4">
                @foreach($pdfs as $pdf)
                    @if($pdf->file_path)
                    <div class="col-md-4">
                        @include('partials.pdf-card', ['pdf' => $pdf, 'section' => $section])
                    </div>
                    @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
