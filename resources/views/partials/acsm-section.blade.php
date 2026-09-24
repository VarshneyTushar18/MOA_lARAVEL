@php
    $videos = $section->media->where('type', 'video')->values();
    $youtubes = $section->media->where('type', 'youtube')->values();
    $audios = $section->media->where('type', 'audio')->values();
    $pdfs = $section->media->where('type', 'pdf')->values();
    $images = $section->images;
    $hasMain = !empty($section->image);
    $headingId = 'acsmHeading'.$section->id;
    $collapseId = 'acsmCollapse'.$section->id;
    $accordionParent = $accordionParent ?? 'acsmIecAccordion';
    $isTbPledge = $section->section_key === 'tb_pledge';
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

            @if($isTbPledge)
                @include('partials.acsm-tb-pledge-year-tabs', ['section' => $section])
            @else
                @if($videos->count() || $youtubes->count())
                    @include('partials.acsm-video-gallery', ['videos' => $videos, 'youtubes' => $youtubes])
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
                            @if(filled($audio->title))
                                <h6 class="text-center mb-2">{{ $audio->title }}</h6>
                            @endif
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
                    @endphp
                    @include('partials.acsm-image-gallery', [
                        'images' => $galleryImages,
                        'galleryId' => 'acsm-'.$section->id,
                        'sectionTitle' => $section->title,
                    ])
                @endif
            @endif

            @if($pdfs->count())
                @if($isTbPledge || $videos->count() || $youtubes->count() || $audios->count() || $hasMain || $images->count())
                    <div class="my-5"></div>
                @endif
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
