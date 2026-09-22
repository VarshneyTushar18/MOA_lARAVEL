@extends('layout.console')

@section('content')

@php
    $maxVideoMb = (int) config('upload_compression.max_video_mb', 200);
    $maxHighlightSec = (int) config('upload_compression.highlight_video_max_seconds', 10);
    $highlightDurationLabel = $maxHighlightSec > 0 ? "max {$maxHighlightSec} sec, " : '';
@endphp

<section class="w3-padding">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0">Edit Section for {{ $page->title }}</h2>
        <a href="/console/pages/sections/{{ $page->id }}/list" class="btn btn-secondary btn-sm">Back to Sections</a>
    </div>

    <form method="post" action="/console/pages/sections/{{ $page->id }}/edit/{{ $section->id }}" enctype="multipart/form-data" novalidate class="console-form card">

        @csrf

        {{-- Section Key --}}
        <div class="w3-margin-bottom">
            <label for="section_key">Section Key:</label>
            <input type="text" class="form-control" name="section_key" id="section_key" value="{{ old('section_key', $section->section_key) }}" required>
            <div class="w3-small">Examples: hero_banner, home_marquee, pm_yojna, roles, moa, aiia, rntcp</div>
        </div>

        <div id="section_form_extras">
            {{-- Parent Section --}}
            <div class="w3-margin-bottom">
                <label for="parent_id">Parent Section (optional):</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">-- None --</option>
                    @foreach($page->sections as $s)
                        <option value="{{ $s->id }}" {{ old('parent_id', $section->parent_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->key ?? $s->section_key }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Type --}}
            <div class="w3-margin-bottom">
                <label for="type">Section Type:</label>
                <select name="type" id="type" class="form-control">
                    <option value="single" {{ old('type', $section->type) == 'single' ? 'selected' : '' }}>Single</option>
                    <option value="banner" {{ old('type', $section->type) == 'banner' ? 'selected' : '' }}>Banner</option>
                    <option value="personal" {{ old('type', $section->type) == 'personal' ? 'selected' : '' }}>Personal</option>
                </select>
            </div>
        </div>

        {{-- Title --}}
        <div class="w3-margin-bottom">
            <label for="title">Title:</label>
            <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $section->title) }}">
        </div>

        {{-- Description --}}
        <div class="w3-margin-bottom">
            <label for="description">
                @if($page->slug === 'home' && $section->section_key === 'gallery' && !$section->parent_id)
                    Button Text:
                @else
                    Description:
                @endif
            </label>
            <textarea class="form-control" name="description" id="description" rows="{{ ($page->slug === 'home' && $section->section_key === 'gallery' && !$section->parent_id) ? 2 : 8 }}">{{ old('description', $section->description) }}</textarea>
            @if($page->slug === 'home' && $section->section_key === 'gallery' && !$section->parent_id)
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    <strong>Home Photo Gallery hero:</strong> Main image = large preview on Home. Description = button text.
                    Album categories are managed under <strong>Manage Pages → Gallery</strong>.
                </p>
            @elseif($page->slug === 'home' && $section->section_key === 'rntcp' && !$section->parent_id)
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    <strong>Home → RNTCP block:</strong> This description appears on the left (with Read More).
                    Add child sections with <strong>Parent = rntcp</strong> for each objective shown in the right-side list.
                </p>
            @elseif($page->slug === 'home' && $section->section_key === 'home_marquee' && !$section->parent_id)
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    <strong>Home marquee:</strong> Add scrolling links below. Each link needs a label plus a website URL or uploaded PDF. Optional colors control bar styling.
                </p>
            @elseif($page->slug === 'home' && $section->section_key === 'hero_banner' && !$section->parent_id)
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    <strong>Hero banner carousel:</strong> Slides show as Banner image → Video → Additional images. Use sort numbers below to fine-tune order within each group.
                </p>
            @elseif($page->slug === 'gallery' && !$section->parent_id)
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    <strong>Gallery (unified):</strong> Section key <code>gallery_single</code> = single photo(s) for the top area on Home.
                    Any other key = categorized album (shown below on Home + on <code>/gallery</code>).
                    Main image = cover. Additional images = photos inside the album.
                </p>
            @elseif($page->slug === 'home' && $section->parent && $section->parent->section_key === 'rntcp')
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    <strong>RNTCP objective:</strong> This text appears as a bullet in the Objectives box on the home page.
                    Optional <strong>Title</strong> above shows as a bold line before the description.
                </p>
            @endif
        </div>

        @if($page->slug === 'home' && $section->section_key === 'home_marquee')
        <div id="section_form_marquee_colors">
            <div class="w3-margin-bottom">
                <label for="text_color">Text Color (optional, home marquee only):</label>
                <input type="color" id="text_color_picker" value="{{ old('text_color', $section->text_color ?? '#333333') }}" onchange="document.getElementById('text_color').value=this.value">
                <input type="text" class="form-control d-inline-block" style="max-width:160px;display:inline-block;" name="text_color" id="text_color" value="{{ old('text_color', $section->text_color) }}" placeholder="#333333">
            </div>

            <div class="w3-margin-bottom">
                <label for="bg_color">Background Color (optional, home marquee only):</label>
                <input type="color" id="bg_color_picker" value="{{ old('bg_color', $section->bg_color ?? '#f3f3f3') }}" onchange="document.getElementById('bg_color').value=this.value">
                <input type="text" class="form-control d-inline-block" style="max-width:160px;display:inline-block;" name="bg_color" id="bg_color" value="{{ old('bg_color', $section->bg_color) }}" placeholder="#F3F3F3">
            </div>
        </div>

        <div id="section_form_marquee_links" style="display:none;">
            <h4>Marquee Links</h4>
            <p class="w3-small">Each row needs a label plus either a website URL or an uploaded PDF (or both — PDF opens when clicked).</p>
            <div id="marquee_links_wrapper">
                @foreach($marqueeLinks as $idx => $marqueeLink)
                    <div class="w3-border w3-padding w3-margin-bottom marquee-link-item" data-index="{{ $idx }}">
                        <input type="hidden" name="marquee_links[{{ $idx }}][id]" value="{{ $marqueeLink['id'] ?? '' }}">
                        <div class="w3-margin-bottom">
                            <label>Link Label</label>
                            <input type="text" class="w3-input" name="marquee_links[{{ $idx }}][title]" value="{{ $marqueeLink['title'] ?? '' }}">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>Website URL (optional if PDF uploaded)</label>
                            <input type="url" class="w3-input" name="marquee_links[{{ $idx }}][url]" value="{{ $marqueeLink['url'] ?? '' }}" placeholder="https://...">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>Upload PDF (optional)</label>
                            @if(!empty($marqueeLink['existing_pdf']))
                                <div class="mb-2">
                                    <a href="{{ asset('storage/'.$marqueeLink['existing_pdf']) }}" target="_blank">View current PDF</a>
                                </div>
                            @endif
                            <input type="file" class="w3-input" name="marquee_links[{{ $idx }}][file]" accept="application/pdf,.pdf">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>Sort Order</label>
                            <input type="number" class="w3-input" name="marquee_links[{{ $idx }}][sort_order]" value="{{ $marqueeLink['sort_order'] ?? 0 }}">
                        </div>
                        <button type="button" class="w3-button w3-red remove-marquee-link">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add_marquee_link" class="w3-button w3-blue">Add Link</button>
        </div>
        @endif

        @if($page->slug === 'home' && $section->section_key === 'hero_banner' && !empty($carouselSlides))
        <div class="w3-margin-bottom">
            <h4>Carousel Slide Order</h4>
            <p class="w3-small text-muted">Lower sort numbers appear first on the home page. Put your video as 1, then photos as 2, 3, etc.</p>
            <div id="carousel_slides_wrapper">
                @foreach($carouselSlides as $idx => $slide)
                    <div class="w3-border w3-padding w3-margin-bottom">
                        <input type="hidden" name="carousel_slides[{{ $idx }}][k]" value="{{ $slide['k'] }}">
                        @if(!empty($slide['id']))
                            <input type="hidden" name="carousel_slides[{{ $idx }}][id]" value="{{ $slide['id'] }}">
                        @endif
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <strong>{{ $slide['label'] }}</strong>
                            <div>
                                <label class="mb-0 me-2">Sort Order</label>
                                <input type="number" class="form-control d-inline-block" style="width:100px;" name="carousel_slides[{{ $idx }}][sort_order]" value="{{ $slide['sort_order'] ?? ($idx + 1) }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div id="section_form_media">
            {{-- Single Image --}}
            <div class="w3-margin-bottom">
                <label for="image">Main Image (optional):</label>
                @if($section->image)
                    <div class="console-preview mb-2 d-flex align-items-start gap-2">
                        <img src="{{ asset('storage/'.$section->image) }}" alt="">
                        <a href="/console/pages/sections/{{ $page->id }}/image/delete-main/{{ $section->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Remove this image?')">Remove</a>
                    </div>
                @endif
                <input type="file" class="form-control" name="image" id="image">
            </div>

            {{-- Multiple Images --}}
            <div class="w3-margin-bottom">
                <label for="images">Additional Images (optional, multiple allowed):</label>
                <input type="file" class="form-control" name="images[]" id="images" multiple>

                @if($section->images->count() > 0)
                    <div class="w3-margin-top">
                        @foreach($section->images as $img)
                            <div style="display:inline-block; position:relative; margin:5px;">
                                <img src="{{ asset('storage/'.$img->image) }}" width="120">
                                <a href="/console/pages/sections/image/delete/{{ $img->id }}" 
                                   style="position:absolute; top:0; right:0; background:red; color:white; padding:2px 6px;">X</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- PDF --}}
            <div class="w3-margin-bottom">
                <label for="pdf">PDF (optional):</label>
                @forelse($section->media->where('type','pdf') as $pdf)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                            <a href="{{ asset('storage/'.$pdf->file_path) }}" target="_blank">{{ basename($pdf->file_path) ?: 'View PDF' }}</a>
                            <a href="/console/pages/sections/media/delete/{{ $pdf->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Remove this PDF?')">Remove</a>
                        </div>
                        <label class="w3-small">PDF title</label>
                        <input type="text" class="form-control mb-2" name="pdf_meta[{{ $pdf->id }}][title]" value="{{ old('pdf_meta.'.$pdf->id.'.title', $pdf->title) }}" placeholder="Short title">
                        <label class="w3-small">Short description</label>
                        <textarea class="form-control" name="pdf_meta[{{ $pdf->id }}][description]" rows="3" placeholder="Short description shown on the website">{{ old('pdf_meta.'.$pdf->id.'.description', $pdf->description) }}</textarea>
                    </div>
                @empty
                    @if($section->pdf)
                        <div class="mb-2"><a href="{{ asset('storage/'.$section->pdf) }}" target="_blank">View PDF</a></div>
                    @endif
                @endforelse
                <input type="file" class="form-control" name="pdfs[]" id="pdf" accept="application/pdf,.pdf" multiple>
                <label class="w3-small w3-margin-top">New PDF title (optional)</label>
                <input type="text" class="form-control mb-2" name="new_pdf_title" value="{{ old('new_pdf_title') }}" placeholder="Short title">
                <label class="w3-small">New PDF short description (optional)</label>
                <textarea class="form-control" name="new_pdf_description" rows="3" placeholder="Short description shown on the website">{{ old('new_pdf_description') }}</textarea>
                <p class="w3-small">Click Remove to delete a PDF. Uploading a new PDF replaces the old ones.</p>
            </div>

            {{-- Videos --}}
            <div class="w3-margin-bottom">
                <label for="videos">Upload Videos (MP4, MOV, AVI — max {{ $maxVideoMb }} MB each)</label>
                @foreach($section->media->where('type', 'video') as $media)
                    @if($media->file_path)
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                            <a href="{{ asset('storage/'.$media->file_path) }}" target="_blank">{{ basename($media->file_path) }}</a>
                            <a href="/console/pages/sections/media/delete/{{ $media->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Remove this video?')">Remove</a>
                        </div>
                    @endif
                @endforeach
                @foreach($section->media->where('type', 'youtube') as $yt)
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <a href="{{ $yt->youtube_url }}" target="_blank">{{ $yt->youtube_url }}</a>
                        <a href="/console/pages/sections/media/delete/{{ $yt->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Remove this YouTube link?')">Remove</a>
                    </div>
                @endforeach
                <input type="file" class="form-control" name="videos[]" id="videos" multiple accept="video/mp4,video/quicktime,video/x-msvideo,.mp4,.mov,.avi">
                <p class="w3-small">Upload progress appears at the bottom of the screen. New uploads are added — use Remove to delete old videos. For hero banner, videos appear in the home carousel with photos.</p>
                <label class="w3-margin-top" for="youtube_links_text">YouTube URL (optional)</label>
                <textarea name="youtube_links_text" id="youtube_links_text" class="form-control" rows="3" placeholder="https://www.youtube.com/watch?v=...&#10;One link per line"></textarea>
                <p class="w3-small">Paste one YouTube link per line. Existing links stay unless you click Remove.</p>
            </div>

            <div class="w3-margin-bottom">
                <label>Upload Audio Files</label>
                @foreach($section->media->where('type', 'audio') as $audio)
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <audio class="console-audio" controls>
                            <source src="{{ asset('storage/'.$audio->file_path) }}">
                        </audio>
                        <a href="/console/pages/sections/media/delete/{{ $audio->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Remove this audio?')">Remove</a>
                    </div>
                @endforeach
                <input type="file" class="form-control" name="audios[]" multiple>
            </div>
        </div>

        <div id="section_form_factsheet_highlights" style="display:none;">
            <h4>Factsheet Highlights</h4>
            <p class="w3-small">Each row needs at least one: Image, Video, or YouTube URL.</p>
            <div id="highlight_items_wrapper">
                @foreach($highlightItems as $idx => $highlightItem)
                    <div class="w3-border w3-padding w3-margin-bottom highlight-item" data-index="{{ $idx }}">
                        <input type="hidden" name="highlight_items[{{ $idx }}][id]" value="{{ $highlightItem['id'] ?? '' }}">
                        <div class="w3-margin-bottom">
                            <label>Title</label>
                            <input type="text" class="w3-input" name="highlight_items[{{ $idx }}][title]" value="{{ $highlightItem['title'] ?? '' }}">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>Description (optional)</label>
                            <textarea class="w3-input" name="highlight_items[{{ $idx }}][description]">{{ $highlightItem['description'] ?? '' }}</textarea>
                        </div>
                        <div class="w3-margin-bottom">
                            <label>Sort Order</label>
                            <input type="number" class="w3-input" name="highlight_items[{{ $idx }}][sort_order]" value="{{ $highlightItem['sort_order'] ?? 0 }}">
                        </div>
                        @if(!empty($highlightItem['existing_image']))
                            <div class="w3-margin-bottom">
                                <small>Current Image:</small><br>
                                <img src="{{ asset('storage/'.$highlightItem['existing_image']) }}" width="120">
                            </div>
                        @endif
                        <div class="w3-margin-bottom">
                            <label>Cover Image</label>
                            <input type="file" class="w3-input" name="highlight_items[{{ $idx }}][image]" accept="image/*">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>YouTube URL</label>
                            <input type="url" class="w3-input" name="highlight_items[{{ $idx }}][youtube_url]" value="{{ $highlightItem['youtube_url'] ?? '' }}">
                        </div>
                        @if(!empty($highlightItem['existing_video_path']))
                            <div class="w3-margin-bottom">
                                <small>Current Video:</small>
                                <a href="{{ asset('storage/'.$highlightItem['existing_video_path']) }}" target="_blank">View</a>
                            </div>
                        @endif
                        <div class="w3-margin-bottom">
                            <label>Upload Video ({{ $highlightDurationLabel }}{{ $maxVideoMb }} MB)</label>
                            <input type="file" class="w3-input" name="highlight_items[{{ $idx }}][video]" accept="video/mp4,video/quicktime,video/x-msvideo,.mp4,.mov,.avi">
                        </div>
                        <button type="button" class="w3-button w3-red remove-highlight-item">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add_highlight_item" class="w3-button w3-blue">Add Highlight</button>
        </div>

        <div class="w3-margin-bottom">
            <label for="sort_order">Sort Order:</label>
            <input type="number" class="form-control" name="sort_order" id="sort_order" value="{{ old('sort_order', $section->sort_order) }}">
            <p class="w3-small">Example: set 2 and this section becomes 2; the old 2 becomes 3. Or use ↑ ↓ on the list.</p>
        </div>

        <button type="submit" class="btn btn-success">Save</button>

    </form>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var keyInput = document.getElementById('section_key');
    var extras = document.getElementById('section_form_extras');
    var media = document.getElementById('section_form_media');
    var highlights = document.getElementById('section_form_factsheet_highlights');
    var highlightsWrapper = document.getElementById('highlight_items_wrapper');
    var addHighlightBtn = document.getElementById('add_highlight_item');
    var colors = document.getElementById('section_form_marquee_colors');
    var marqueeLinks = document.getElementById('section_form_marquee_links');
    var marqueeLinksWrapper = document.getElementById('marquee_links_wrapper');
    var addMarqueeLinkBtn = document.getElementById('add_marquee_link');
    var isHome = @json($page->slug === 'home');
    var isFactsheet = @json($page->slug === 'factsheet');
    var maxVideoMb = @json($maxVideoMb);
    var highlightDurationLabel = @json($highlightDurationLabel);
    var highlightIndex = (function() {
        if (!highlightsWrapper) return 0;
        return highlightsWrapper.querySelectorAll('.highlight-item').length;
    })();
    var marqueeLinkIndex = (function() {
        if (!marqueeLinksWrapper) return 0;
        return marqueeLinksWrapper.querySelectorAll('.marquee-link-item').length;
    })();

    function toggleMarqueeFields() {
        var isMarquee = keyInput && keyInput.value.trim() === 'home_marquee';
        var isFactsheetHighlights = isFactsheet && keyInput && keyInput.value.trim() === 'factsheet_highlights';
        if (extras) extras.style.display = (isMarquee || isFactsheetHighlights) ? 'none' : '';
        if (media) media.style.display = (isMarquee || isFactsheetHighlights) ? 'none' : '';
        if (highlights) highlights.style.display = isFactsheetHighlights ? '' : 'none';
        if (colors) colors.style.display = (isHome && isMarquee) ? '' : 'none';
        if (marqueeLinks) marqueeLinks.style.display = (isHome && isMarquee) ? '' : 'none';
        setBlockInputsEnabled(colors, isHome && isMarquee);
        setBlockInputsEnabled(marqueeLinks, isHome && isMarquee);
        setBlockInputsEnabled(highlights, isFactsheetHighlights);
    }

    function setBlockInputsEnabled(block, enabled) {
        if (!block) return;
        block.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.disabled = !enabled;
        });
    }

    function marqueeLinkTemplate(index) {
        return '' +
            '<div class="w3-border w3-padding w3-margin-bottom marquee-link-item" data-index="' + index + '">' +
                '<input type="hidden" name="marquee_links[' + index + '][id]" value="">' +
                '<div class="w3-margin-bottom"><label>Link Label</label><input type="text" class="w3-input" name="marquee_links[' + index + '][title]"></div>' +
                '<div class="w3-margin-bottom"><label>Website URL (optional if PDF uploaded)</label><input type="url" class="w3-input" name="marquee_links[' + index + '][url]" placeholder="https://..."></div>' +
                '<div class="w3-margin-bottom"><label>Upload PDF (optional)</label><input type="file" class="w3-input" name="marquee_links[' + index + '][file]" accept="application/pdf,.pdf"></div>' +
                '<div class="w3-margin-bottom"><label>Sort Order</label><input type="number" class="w3-input" name="marquee_links[' + index + '][sort_order]" value="0"></div>' +
                '<button type="button" class="w3-button w3-red remove-marquee-link">Remove</button>' +
            '</div>';
    }

    function highlightTemplate(index) {
        return '' +
            '<div class="w3-border w3-padding w3-margin-bottom highlight-item" data-index="' + index + '">' +
                '<input type="hidden" name="highlight_items[' + index + '][id]" value="">' +
                '<div class="w3-margin-bottom"><label>Title</label><input type="text" class="w3-input" name="highlight_items[' + index + '][title]"></div>' +
                '<div class="w3-margin-bottom"><label>Description (optional)</label><textarea class="w3-input" name="highlight_items[' + index + '][description]"></textarea></div>' +
                '<div class="w3-margin-bottom"><label>Sort Order</label><input type="number" class="w3-input" name="highlight_items[' + index + '][sort_order]" value="0"></div>' +
                '<div class="w3-margin-bottom"><label>Cover Image</label><input type="file" class="w3-input" name="highlight_items[' + index + '][image]" accept="image/*"></div>' +
                '<div class="w3-margin-bottom"><label>YouTube URL</label><input type="url" class="w3-input" name="highlight_items[' + index + '][youtube_url]"></div>' +
                '<div class="w3-margin-bottom"><label>Upload Video (' + highlightDurationLabel + maxVideoMb + ' MB)</label><input type="file" class="w3-input" name="highlight_items[' + index + '][video]" accept="video/mp4,video/quicktime,video/x-msvideo,.mp4,.mov,.avi"></div>' +
                '<button type="button" class="w3-button w3-red remove-highlight-item">Remove</button>' +
            '</div>';
    }

    if (keyInput) {
        keyInput.addEventListener('input', toggleMarqueeFields);
        keyInput.addEventListener('change', toggleMarqueeFields);
    }

    if (addHighlightBtn && highlightsWrapper) {
        addHighlightBtn.addEventListener('click', function () {
            highlightsWrapper.insertAdjacentHTML('beforeend', highlightTemplate(highlightIndex));
            highlightIndex += 1;
        });

        highlightsWrapper.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-highlight-item')) {
                var block = event.target.closest('.highlight-item');
                if (block) block.remove();
            }
        });
    }

    if (addMarqueeLinkBtn && marqueeLinksWrapper) {
        addMarqueeLinkBtn.addEventListener('click', function () {
            marqueeLinksWrapper.insertAdjacentHTML('beforeend', marqueeLinkTemplate(marqueeLinkIndex));
            marqueeLinkIndex += 1;
        });

        marqueeLinksWrapper.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-marquee-link')) {
                var block = event.target.closest('.marquee-link-item');
                if (block) block.remove();
            }
        });
    }

    toggleMarqueeFields();
});
</script>

@endsection