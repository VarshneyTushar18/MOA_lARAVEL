@extends('layout.console')

@section('content')

<section class="w3-padding">

    <div class="w3-flex w3-justify-between w3-align-center">
        <h2>Sections for {{ $page->title }}</h2>
        <a href="/console/pages/sections/{{ $page->id }}/add" class="w3-button w3-green">Add Section</a>
    </div>
    <p class="w3-small">Use <strong>↑ ↓</strong> to change the order on the website. Clicking table headers does not save order.</p>

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
                    <td>{{ $section->title }}</td>
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

    <a href="/console/pages/list" class="w3-button w3-light-grey w3-margin-top">Back to Pages</a>

</section>

@endsection
