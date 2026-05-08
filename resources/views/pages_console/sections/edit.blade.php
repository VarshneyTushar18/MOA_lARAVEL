@extends('layout.console')

@section('content')

<section class="w3-padding">

    <h2>Edit Section for {{ $page->title }}</h2>

    <form method="post" action="/console/pages/sections/{{ $page->id }}/edit/{{ $section->id }}" enctype="multipart/form-data" novalidate class="w3-margin-bottom">

        @csrf

        {{-- Section Key --}}
        <div class="w3-margin-bottom">
            <label for="section_key">Section Key:</label>
            <input type="text" name="section_key" id="section_key" value="{{ old('section_key', $section->section_key) }}" required>
            <div class="w3-small">Examples: hero_banner, home_marquee, pm_yojna, roles, moa, aiia, rntcp</div>
        </div>

        <div id="section_form_extras">
            {{-- Parent Section --}}
            <div class="w3-margin-bottom">
                <label for="parent_id">Parent Section (optional):</label>
                <select name="parent_id" id="parent_id" class="w3-input">
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
                <select name="type" id="type" class="w3-input">
                    <option value="single" {{ old('type', $section->type) == 'single' ? 'selected' : '' }}>Single</option>
                    <option value="banner" {{ old('type', $section->type) == 'banner' ? 'selected' : '' }}>Banner</option>
                    <option value="personal" {{ old('type', $section->type) == 'personal' ? 'selected' : '' }}>Personal</option>
                </select>
            </div>
        </div>

        {{-- Title --}}
        <div class="w3-margin-bottom">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="{{ old('title', $section->title) }}">
        </div>

        {{-- Description --}}
        <div class="w3-margin-bottom">
            <label for="description">Description:</label>
            <textarea name="description" id="description">{{ old('description', $section->description) }}</textarea>
        </div>

        @if($page->slug === 'home')
        <div id="section_form_marquee_colors">
            <div class="w3-margin-bottom">
                <label for="text_color">Text Color (optional, home marquee only):</label>
                <input type="color" id="text_color_picker" value="{{ old('text_color', $section->text_color ?? '#ffffff') }}" onchange="document.getElementById('text_color').value=this.value">
                <input type="text" name="text_color" id="text_color" value="{{ old('text_color', $section->text_color) }}" placeholder="#FFFFFF">
            </div>

            <div class="w3-margin-bottom">
                <label for="bg_color">Background Color (optional, home marquee only):</label>
                <input type="color" id="bg_color_picker" value="{{ old('bg_color', $section->bg_color ?? '#162f6d') }}" onchange="document.getElementById('bg_color').value=this.value">
                <input type="text" name="bg_color" id="bg_color" value="{{ old('bg_color', $section->bg_color) }}" placeholder="#162F6D">
            </div>
        </div>
        @endif

        <div id="section_form_media">
            {{-- Single Image --}}
            <div class="w3-margin-bottom">
                <label for="image">Main Image (optional):</label>
                @if($section->image)
                    <div><img src="{{ asset('storage/'.$section->image) }}" width="240"></div>
                @endif
                <input type="file" name="image" id="image">
            </div>

            {{-- Multiple Images --}}
            <div class="w3-margin-bottom">
                <label for="images">Additional Images (optional, multiple allowed):</label>
                <input type="file" name="images[]" id="images" multiple>

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
                @if($section->pdf)
                    <div><a href="{{ asset('storage/'.$section->pdf) }}" target="_blank">View PDF</a></div>
                @endif
                <input type="file" name="pdf" id="pdf">
            </div>

            {{-- Videos --}}
            <div class="w3-margin-bottom">
                <label for="videos">Videos (YouTube links, one per line)</label>
                <textarea name="videos" id="videos" rows="4">@if($section->videos){{ implode("\n", $section->videos) }}@endif</textarea>
            </div>

            <div class="w3-margin-bottom">
                <label>Upload Audio Files</label>
                <input type="file" name="audios[]" multiple>
            </div>

            {{-- Sort Order --}}
            <div class="w3-margin-bottom">
                <label for="sort_order">Sort Order:</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $section->sort_order) }}">
            </div>
        </div>

        <div id="section_form_factsheet_highlights" style="display:none;">
            <h4>Factsheet Highlights</h4>
            <p class="w3-small">Each row needs at least one: Image, Video, or YouTube URL.</p>
            <div id="highlight_items_wrapper">
                @php
                    $oldHighlightItems = old('highlight_items');
                    $highlightItems = is_array($oldHighlightItems)
                        ? $oldHighlightItems
                        : $section->highlightItems->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'title' => $item->title,
                                'description' => $item->description,
                                'sort_order' => $item->sort_order,
                                'youtube_url' => $item->youtube_url,
                                'existing_image' => $item->image,
                                'existing_video_path' => $item->video_path,
                            ];
                        })->toArray();
                @endphp

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
                            <label>Upload Video (max 10 sec)</label>
                            <input type="file" class="w3-input" name="highlight_items[{{ $idx }}][video]" accept="video/*">
                        </div>
                        <button type="button" class="w3-button w3-red remove-highlight-item">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add_highlight_item" class="w3-button w3-blue">Add Highlight</button>
        </div>

        <button type="submit" class="w3-button w3-green">Save</button>

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
                '<input type="hidden" name="highlight_items[' + index + '][id]" value="">' +
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