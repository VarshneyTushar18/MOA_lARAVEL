@extends('layout.console')

@section('content')

<section class="w3-padding">

    <div class="w3-flex w3-justify-between w3-align-center flex-wrap gap-2 console-page-header">
        <h2 class="mb-0">Sections for {{ $page->title }}</h2>
        <div class="console-page-actions">
            <a href="/console/pages/list" class="btn btn-outline-secondary btn-sm">Back to Pages</a>
            <a href="/console/pages/sections/{{ $page->id }}/add" class="w3-button w3-green">Add Section</a>
        </div>
    </div>
    <p class="w3-small">Use <strong>↑ ↓</strong> to change the order on the website. Clicking table headers does not save order.</p>
    @if($page->slug === 'home')
        <p class="w3-small w3-text-grey mb-0">
            <strong>Home marquee links:</strong> edit the single <code>home_marquee</code> section and use <strong>Add Link</strong> there. Each link needs a label plus a website URL or uploaded PDF.
        </p>
        <p class="w3-small w3-text-grey mb-0">
            <strong>Photo Gallery hero:</strong> edit the <code>gallery</code> section here (main image + button text).
            <strong>Albums:</strong> manage under
            <a href="{{ $galleryAdminUrl ?? '/console/pages/list' }}">Manage Pages → Gallery</a>.
        </p>
    @endif
    @if($page->slug === 'gallery')
        <p class="w3-small w3-text-grey mb-0">
            <strong>One gallery for the whole site:</strong>
            use any key <strong>except</strong> <code>album_*</code> for a featured single photo on Home top
            (e.g. <code>gallery_single</code>, <code>featured</code>).
            Use keys like <code>album_events</code> for categorized albums below on Home.
        </p>
    @endif
    @if(session('message'))
        <div class="alert alert-success mt-2 mb-0">{{ session('message') }}</div>
    @endif

    <div class="table-responsive w3-margin-top">
    <table class="w3-table w3-striped w3-bordered datatable datatable-frozen-order">
        <thead>
            <tr>
                <th>Sort</th>
                <th>Key</th>
                <th>Title</th>
                <th>Parent</th>
                <th>Type</th>
                <th>Main Image</th>
                <th>Additional Images</th>
                <th>PDFs</th>
                <th>Videos</th>
                <th>Audios</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sections as $section)
                <tr>
                    <td class="console-actions">
                        <a class="btn btn-sm btn-outline-secondary" href="/console/pages/sections/{{ $page->id }}/move/{{ $section->id }}/up" title="Move up">↑</a>
                        <a class="btn btn-sm btn-outline-secondary" href="/console/pages/sections/{{ $page->id }}/move/{{ $section->id }}/down" title="Move down">↓</a>
                        <span>{{ $section->sort_order }}</span>
                    </td>
                    <td>{{ $section->section_key }}</td>
                    <td>
                        {{ $section->title }}
                        @if($page->slug === 'home' && $section->section_key === 'home_marquee')
                            <br><span class="w3-small w3-text-grey">{{ $section->subsections->count() }} scrolling link(s)</span>
                        @endif
                    </td>
                    <td>{{ $section->parent ? ($section->parent->title ?? $section->parent->section_key) : '-' }}</td>
                    <td>{{ $section->type }}</td>

                    {{-- Main Image --}}
                    <td>
                        @if($section->image)
                            <img src="{{ asset('storage/'.$section->image) }}" class="console-thumb" alt="">
                        @else
                            <span class="w3-text-grey">-</span>
                        @endif
                    </td>

                    {{-- Additional Images --}}
                    <td>
                        @forelse($section->images as $img)
                            <img src="{{ asset('storage/'.$img->image) }}" class="console-thumb" alt="">
                        @empty
                            <span class="w3-text-grey">-</span>
                        @endforelse
                    </td>

                    {{-- PDFs --}}
                    <td>
                        @forelse($section->media->where('type','pdf') as $pdf)
                            <a href="{{ asset('storage/'.$pdf->file_path) }}" target="_blank">PDF</a><br>
                        @empty
                            <span class="w3-text-grey">-</span>
                        @endforelse
                    </td>

                    {{-- Videos --}}
                    <td>
                        @foreach($section->media->where('type','video') as $video)
                            <a href="{{ asset('storage/'.$video->file_path) }}" target="_blank">Local Video</a><br>
                        @endforeach
                        @foreach($section->media->where('type','youtube') as $yt)
                            <a href="{{ $yt->youtube_url }}" target="_blank">YouTube</a><br>
                        @endforeach
                        @if($section->media->whereIn('type', ['video', 'youtube'])->count() === 0)
                            <span class="w3-text-grey">-</span>
                        @endif
                    </td>

                    {{-- Audios --}}
                    <td>
                        @forelse($section->media->where('type','audio') as $audio)
                            <audio class="console-audio" controls>
                                <source src="{{ asset('storage/'.$audio->file_path) }}">
                            </audio>
                            <br>
                        @empty
                            <span class="w3-text-grey">-</span>
                        @endforelse
                    </td>

                    <td class="console-actions">
                        <a class="btn btn-sm btn-primary" href="/console/pages/sections/{{ $page->id }}/edit/{{ $section->id }}">Edit</a>
                        <a class="btn btn-sm btn-danger" href="/console/pages/sections/{{ $page->id }}/delete/{{ $section->id }}" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

</section>

@endsection
