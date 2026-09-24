@extends('layout.console')

@section('content')

@php
    $maxVideoMb = (int) floor(\App\Support\UploadLimits::effectiveMaxBytes() / (1024 * 1024));
    $configuredVideoMb = (int) config('upload_compression.max_video_mb', 0);
    if ($configuredVideoMb > 0) {
        $maxVideoMb = min($maxVideoMb, $configuredVideoMb);
    }
    $maxHighlightSec = (int) config('upload_compression.highlight_video_max_seconds', 10);
    $highlightDurationLabel = $maxHighlightSec > 0 ? "max {$maxHighlightSec} sec, " : '';
@endphp

<section class="w3-padding">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0">Add Section for {{ $page->title }}</h2>
        <a href="/console/pages/sections/{{ $page->id }}/list" class="btn btn-secondary btn-sm">Back to Sections</a>
    </div>

    <form method="post" action="/console/pages/sections/{{ $page->id }}/add" enctype="multipart/form-data" novalidate class="console-form card">

        @csrf

        {{-- Section Key --}}
        <div class="w3-margin-bottom">
            <label for="section_key">Section Key:</label>
            <input type="text" class="form-control" name="section_key" id="section_key" value="{{ old('section_key') }}" required>
            <div class="w3-small">Examples: hero_banner, home_marquee, pm_yojna, roles, moa, aiia, rntcp</div>
            @if($page->slug === 'home')
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    For the scrolling bar links, add one section with key <code>home_marquee</code> only. Links are added inside that section — do not create <code>marquee_link</code> rows manually.
                    For the Photo Gallery hero, use section key <code>gallery</code> (main image + button text). Albums are managed under <strong>Manage Pages → Gallery</strong>.
                </p>
            @endif
            @if($page->slug === 'gallery')
                <p class="w3-small w3-text-grey mt-2 mb-0">
                    Key <code>gallery_single</code> = single photo(s) on Home top. Any other key = album category on Home bottom + <code>/gallery</code>.
                    Main image = cover. Additional images = photos inside the album.
                </p>
            @endif
            @if($errors->first('section_key'))
                <br><span class="w3-text-red">{{ $errors->first('section_key') }}</span>
            @endif
        </div>

        <div id="section_form_extras">
            {{-- Parent Section --}}
            <div class="w3-margin-bottom">
                <label for="parent_id">Parent Section (optional):</label>
                <select name="parent_id" id="parent_id" class="form-control">
                    <option value="">-- None --</option>
                    @foreach($page->sections as $section)
                        <option value="{{ $section->id }}" {{ old('parent_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->key ?? $section->section_key }}
                        </option>
                    @endforeach
                </select>
                <div class="w3-small">Select a parent section to create a subsection (like PM quote).</div>
            </div>

            {{-- Type --}}
            <div class="w3-margin-bottom">
                <label for="type">Section Type:</label>
                <select name="type" id="type" class="form-control">
                    <option value="single" {{ old('type') == 'single' ? 'selected' : '' }}>Single</option>
                    <option value="banner" {{ old('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                    <option value="personal" {{ old('type') == 'personal' ? 'selected' : '' }}>Personal</option>
                </select>
            </div>
        </div>

        {{-- Title --}}
        <div class="w3-margin-bottom">
            <label for="title">Title:</label>
            <input type="text" class="form-control" name="title" id="title" value="{{ old('title') }}">
        </div>

        {{-- Description --}}
        <div class="w3-margin-bottom">
            <label for="description">Description:</label>
            <textarea class="form-control" name="description" id="description" rows="8">{{ old('description') }}</textarea>
            @if($page->slug === 'home')
                <p class="w3-small w3-text-grey mt-2 mb-0" id="home_section_help" style="display:none;"></p>
            @endif
        </div>

        @if($page->slug === 'home')
        <div id="section_form_marquee_colors">
            <div class="w3-margin-bottom">
                <label for="text_color">Text Color (optional, home marquee only):</label>
                <input type="color" id="text_color_picker" value="{{ old('text_color', '#333333') }}" onchange="document.getElementById('text_color').value=this.value">
                <input type="text" class="form-control" style="max-width:160px;display:inline-block;" name="text_color" id="text_color" value="{{ old('text_color') }}" placeholder="#333333">
            </div>

            <div class="w3-margin-bottom">
                <label for="bg_color">Background Color (optional, home marquee only):</label>
                <input type="color" id="bg_color_picker" value="{{ old('bg_color', '#f3f3f3') }}" onchange="document.getElementById('bg_color').value=this.value">
                <input type="text" class="form-control" style="max-width:160px;display:inline-block;" name="bg_color" id="bg_color" value="{{ old('bg_color') }}" placeholder="#F3F3F3">
            </div>
        </div>

        <div id="section_form_marquee_links" style="display:none;">
            <h4>Marquee Links</h4>
            <p class="w3-small">Each row needs a label plus either a website URL or an uploaded PDF.</p>
            <div id="marquee_links_wrapper">
                @php($marqueeLinks = old('marquee_links', []))
                @foreach($marqueeLinks as $idx => $marqueeLink)
                    <div class="w3-border w3-padding w3-margin-bottom marquee-link-item" data-index="{{ $idx }}">
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
                            <input type="file" class="w3-input" name="marquee_links[{{ $idx }}][file]" accept="application/pdf,.pdf,application/vnd.ms-powerpoint,.ppt,application/vnd.openxmlformats-officedocument.presentationml.presentation,.pptx">
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

        <div id="section_form_media">
            {{-- Single Image --}}
            <div class="w3-margin-bottom">
                <label for="image">Image (optional):</label>
                <input type="file" class="form-control" name="image" id="image">
            </div>

            {{-- Multiple Images --}}
            <div class="w3-margin-bottom">
                <label for="images">Additional Images (multiple)</label>
                <input type="file" class="form-control" name="images[]" id="images" multiple>
            </div>

            <div class="w3-margin-bottom">
                <label for="pdf">Upload PDF / PPT (optional)</label>
                <input type="file" class="form-control" name="pdfs[]" accept="application/pdf,.pdf,application/vnd.ms-powerpoint,.ppt,application/vnd.openxmlformats-officedocument.presentationml.presentation,.pptx" multiple>
                <label class="w3-small w3-margin-top">Document title (optional)</label>
                <input type="text" class="form-control mb-2" name="new_pdf_title" value="{{ old('new_pdf_title') }}" placeholder="Short title">
                <label class="w3-small">Short description (optional)</label>
                <textarea class="form-control" name="new_pdf_description" rows="3" placeholder="Short description shown on the website">{{ old('new_pdf_description') }}</textarea>
            </div>

            <div class="w3-margin-bottom">
                <label for="videos">Upload Videos (MP4, MOV, AVI — max {{ $maxVideoMb }} MB each)</label>
                <input type="file" class="form-control" name="videos[]" id="videos" multiple accept="video/mp4,video/quicktime,video/x-msvideo,.mp4,.mov,.avi">
                <p class="w3-small">Upload progress appears at the bottom of the screen.</p>
                <label class="w3-margin-top" for="youtube_links_text">YouTube URL (optional)</label>
                <textarea name="youtube_links_text" id="youtube_links_text" class="form-control" rows="3" placeholder="https://www.youtube.com/watch?v=...&#10;One link per line"></textarea>
                <p class="w3-small">Paste one YouTube link per line.</p>
            </div>

            <div class="w3-margin-bottom">
                <label>Upload Audio Files</label>
                <input type="file" class="form-control" name="audios[]" multiple>
            </div>
        </div>

        <div id="section_form_factsheet_highlights" style="display:none;">
            <h4>Factsheet Highlights</h4>
            <p class="w3-small">Add all highlights here. Each row needs at least one: Image, Video, or YouTube URL.</p>
            <div id="highlight_items_wrapper">
                @php($highlightItems = old('highlight_items', []))
                @forelse($highlightItems as $idx => $highlightItem)
                    <div class="w3-border w3-padding w3-margin-bottom highlight-item" data-index="{{ $idx }}">
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
                        <div class="w3-margin-bottom">
                            <label>Cover Image</label>
                            <input type="file" class="w3-input" name="highlight_items[{{ $idx }}][image]" accept="image/*">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>YouTube URL</label>
                            <input type="url" class="w3-input" name="highlight_items[{{ $idx }}][youtube_url]" value="{{ $highlightItem['youtube_url'] ?? '' }}">
                        </div>
                        <div class="w3-margin-bottom">
                            <label>Upload Video ({{ $highlightDurationLabel }}{{ $maxVideoMb }} MB)</label>
                            <input type="file" class="w3-input" name="highlight_items[{{ $idx }}][video]" accept="video/mp4,video/quicktime,video/x-msvideo,.mp4,.mov,.avi">
                        </div>
                        <button type="button" class="w3-button w3-red remove-highlight-item">Remove</button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" id="add_highlight_item" class="w3-button w3-blue">Add Highlight</button>
        </div>

        <div class="w3-margin-bottom">
            <label for="sort_order">Sort Order:</label>
            <input type="number" class="form-control" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}">
            <p class="w3-small">Example: set 2 and this section becomes 2; the old 2 becomes 3. Or use ↑ ↓ on the list.</p>
        </div>

        <button type="submit" class="btn btn-success">Add Section</button>

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
    var homeSectionHelp = document.getElementById('home_section_help');
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
        var key = keyInput ? keyInput.value.trim() : '';
        var isMarquee = key === 'home_marquee';
        var isGallery = isHome && key === 'gallery';
        var isFactsheetHighlights = isFactsheet && key === 'factsheet_highlights';
        if (extras) extras.style.display = (isMarquee || isFactsheetHighlights) ? 'none' : '';
        if (media) media.style.display = (isMarquee || isFactsheetHighlights) ? 'none' : '';
        if (highlights) highlights.style.display = isFactsheetHighlights ? '' : 'none';
        if (colors) colors.style.display = (isHome && isMarquee) ? '' : 'none';
        if (marqueeLinks) marqueeLinks.style.display = (isHome && isMarquee) ? '' : 'none';
        setBlockInputsEnabled(colors, isHome && isMarquee);
        setBlockInputsEnabled(marqueeLinks, isHome && isMarquee);
        setBlockInputsEnabled(highlights, isFactsheetHighlights);
        if (homeSectionHelp) {
            if (isGallery) {
                homeSectionHelp.style.display = '';
                homeSectionHelp.innerHTML = '<strong>Home gallery hero:</strong> Main image = large Photo Gallery preview. Description = button text (e.g. View Gallery). Albums are managed under <strong>Manage Pages → Gallery</strong>.';
            } else if (isMarquee) {
                homeSectionHelp.style.display = '';
                homeSectionHelp.innerHTML = '<strong>Home marquee:</strong> Add one section with key <code>home_marquee</code>. Each link needs a label plus a website URL or uploaded PDF.';
            } else if (key === 'rntcp') {
                homeSectionHelp.style.display = '';
                homeSectionHelp.innerHTML = 'For <strong>RNTCP</strong>: set section key <code>rntcp</code> for the main left text, or choose parent <strong>rntcp</strong> to add an objective for the right box.';
            } else {
                homeSectionHelp.style.display = 'none';
                homeSectionHelp.innerHTML = '';
            }
        }
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
                '<div class="w3-margin-bottom"><label>Upload PDF / PPT (optional)</label><input type="file" class="w3-input" name="marquee_links[' + index + '][file]" accept="application/pdf,.pdf,application/vnd.ms-powerpoint,.ppt,application/vnd.openxmlformats-officedocument.presentationml.presentation,.pptx"></div>' +
                '<div class="w3-margin-bottom"><label>Sort Order</label><input type="number" class="w3-input" name="marquee_links[' + index + '][sort_order]" value="0"></div>' +
                '<button type="button" class="w3-button w3-red remove-marquee-link">Remove</button>' +
            '</div>';
    }

    function highlightTemplate(index) {
        return '' +
            '<div class="w3-border w3-padding w3-margin-bottom highlight-item" data-index="' + index + '">' +
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