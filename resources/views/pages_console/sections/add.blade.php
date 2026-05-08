@extends('layout.console')

@section('content')

<section class="w3-padding">

    <h2>Add Section for {{ $page->title }}</h2>

    <form method="post" action="/console/pages/sections/{{ $page->id }}/add" enctype="multipart/form-data" novalidate class="w3-margin-bottom">

        @csrf

        {{-- Section Key --}}
        <div class="w3-margin-bottom">
            <label for="section_key">Section Key:</label>
            <input type="text" name="section_key" id="section_key" value="{{ old('section_key') }}" required>
            <div class="w3-small">Examples: hero_banner, home_marquee, pm_yojna, roles, moa, aiia, rntcp</div>
            @if($errors->first('section_key'))
                <br><span class="w3-text-red">{{ $errors->first('section_key') }}</span>
            @endif
        </div>

        <div id="section_form_extras">
            {{-- Parent Section --}}
            <div class="w3-margin-bottom">
                <label for="parent_id">Parent Section (optional):</label>
                <select name="parent_id" id="parent_id" class="w3-input">
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
                <select name="type" id="type" class="w3-input">
                    <option value="single" {{ old('type') == 'single' ? 'selected' : '' }}>Single</option>
                    <option value="banner" {{ old('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                    <option value="personal" {{ old('type') == 'personal' ? 'selected' : '' }}>Personal</option>
                </select>
            </div>
        </div>

        {{-- Title --}}
        <div class="w3-margin-bottom">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}">
        </div>

        {{-- Description --}}
        <div class="w3-margin-bottom">
            <label for="description">Description:</label>
            <textarea name="description" id="description">{{ old('description') }}</textarea>
        </div>

        @if($page->slug === 'home')
        <div id="section_form_marquee_colors">
            <div class="w3-margin-bottom">
                <label for="text_color">Text Color (optional, home marquee only):</label>
                <input type="color" id="text_color_picker" value="{{ old('text_color', '#ffffff') }}" onchange="document.getElementById('text_color').value=this.value">
                <input type="text" name="text_color" id="text_color" value="{{ old('text_color') }}" placeholder="#FFFFFF">
            </div>

            <div class="w3-margin-bottom">
                <label for="bg_color">Background Color (optional, home marquee only):</label>
                <input type="color" id="bg_color_picker" value="{{ old('bg_color', '#162f6d') }}" onchange="document.getElementById('bg_color').value=this.value">
                <input type="text" name="bg_color" id="bg_color" value="{{ old('bg_color') }}" placeholder="#162F6D">
            </div>
        </div>
        @endif

        <div id="section_form_media">
            {{-- Single Image --}}
            <div class="w3-margin-bottom">
                <label for="image">Image (optional):</label>
                <input type="file" name="image" id="image">
            </div>

            {{-- Multiple Images --}}
            <div class="w3-margin-bottom">
                <label for="images">Additional Images (multiple)</label>
                <input type="file" name="images[]" id="images" multiple>
            </div>

            <div class="w3-margin-bottom">
                <label for="pdf">Upload PDF (optional)</label>
                <input type="file" name="pdfs[]" multiple>
            </div>

            <div class="w3-margin-bottom">
                <label for="videos">Videos (YouTube embed links, one per line)</label>
                <input type="file" name="videos[]" multiple accept="video/*">
                <textarea name="youtube_links[]" placeholder="YouTube link"></textarea>
            </div>

            <div class="w3-margin-bottom">
                <label>Upload Audio Files</label>
                <input type="file" name="audios[]" multiple>
            </div>


            {{-- Sort Order --}}
            <div class="w3-margin-bottom">
                <label for="sort_order">Sort Order:</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}">
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
                            <label>Upload Video (max 10 sec)</label>
                            <input type="file" class="w3-input" name="highlight_items[{{ $idx }}][video]" accept="video/*">
                        </div>
                        <button type="button" class="w3-button w3-red remove-highlight-item">Remove</button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" id="add_highlight_item" class="w3-button w3-blue">Add Highlight</button>
        </div>

        <button type="submit" class="w3-button w3-green">Add Section</button>

    </form>

    <a href="/console/pages/sections/{{ $page->id }}/list">Back to Sections</a>

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
    var isHome = @json($page->slug === 'home');
    var isFactsheet = @json($page->slug === 'factsheet');
    var highlightIndex = (function() {
        if (!highlightsWrapper) return 0;
        return highlightsWrapper.querySelectorAll('.highlight-item').length;
    })();

    function toggleMarqueeFields() {
        var isMarquee = keyInput && keyInput.value.trim() === 'home_marquee';
        var isFactsheetHighlights = isFactsheet && keyInput && keyInput.value.trim() === 'factsheet_highlights';
        if (extras) extras.style.display = (isMarquee || isFactsheetHighlights) ? 'none' : '';
        if (media) media.style.display = (isMarquee || isFactsheetHighlights) ? 'none' : '';
        if (highlights) highlights.style.display = isFactsheetHighlights ? '' : 'none';
        if (colors) colors.style.display = (isHome && isMarquee) ? '' : 'none';
    }

    function highlightTemplate(index) {
        return '' +
            '<div class="w3-border w3-padding w3-margin-bottom highlight-item" data-index="' + index + '">' +
                '<div class="w3-margin-bottom"><label>Title</label><input type="text" class="w3-input" name="highlight_items[' + index + '][title]"></div>' +
                '<div class="w3-margin-bottom"><label>Description (optional)</label><textarea class="w3-input" name="highlight_items[' + index + '][description]"></textarea></div>' +
                '<div class="w3-margin-bottom"><label>Sort Order</label><input type="number" class="w3-input" name="highlight_items[' + index + '][sort_order]" value="0"></div>' +
                '<div class="w3-margin-bottom"><label>Cover Image</label><input type="file" class="w3-input" name="highlight_items[' + index + '][image]" accept="image/*"></div>' +
                '<div class="w3-margin-bottom"><label>YouTube URL</label><input type="url" class="w3-input" name="highlight_items[' + index + '][youtube_url]"></div>' +
                '<div class="w3-margin-bottom"><label>Upload Video (max 10 sec)</label><input type="file" class="w3-input" name="highlight_items[' + index + '][video]" accept="video/*"></div>' +
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

    toggleMarqueeFields();
});
</script>

@endsection