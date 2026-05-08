@extends('layout.console')

@section('content')

<section class="w3-padding">

    <div class="w3-flex w3-justify-between w3-align-center">
        <h2>Sections for {{ $page->title }}</h2>
        <a href="/console/pages/sections/{{ $page->id }}/add" class="w3-button w3-green">Add Section</a>
    </div>

    <table class="w3-table w3-striped w3-bordered w3-margin-top datatable">
        <thead>
            <tr>
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
                    <td>{{ $section->section_key }}</td>
                    <td>{{ $section->title }}</td>
                    <td>{{ $section->parent ? ($section->parent->title ?? $section->parent->section_key) : '-' }}</td>
                    <td>{{ $section->type }}</td>

                    {{-- Main Image --}}
                    <td>
                        @if($section->image)
                            <img src="{{ asset('storage/'.$section->image) }}" width="80">
                        @endif
                    </td>

                    {{-- Additional Images --}}
                    <td>
                        @foreach($section->images as $img)
                            <img src="{{ asset('storage/'.$img->image) }}" width="50" style="margin:2px;">
                        @endforeach
                    </td>

                    {{-- PDFs --}}
                    <td>
                        @foreach($section->media->where('type','pdf') as $pdf)
                            <a href="{{ asset('storage/'.$pdf->file_path) }}" target="_blank">PDF</a><br>
                        @endforeach
                    </td>

                    {{-- Videos --}}
                    <td>
                        @foreach($section->media->where('type','video') as $video)
                            <a href="{{ asset('storage/'.$video->file_path) }}" target="_blank">Local Video</a><br>
                        @endforeach
                        @foreach($section->media->where('type','youtube') as $yt)
                            <a href="{{ $yt->youtube_url }}" target="_blank">YouTube</a><br>
                        @endforeach
                    </td>

                    {{-- Audios --}}
                    <td>
                        @foreach($section->media->where('type','audio') as $audio)
                            <audio controls style="width:150px; margin-bottom:5px;">
                                <source src="{{ asset('storage/'.$audio->file_path) }}">
                                Your browser does not support audio.
                            </audio>
                            <br>
                        @endforeach
                    </td>

                    <td>
                        <a class="w3-button w3-small w3-blue" href="/console/pages/sections/{{ $page->id }}/edit/{{ $section->id }}">Edit</a>
                        <a class="w3-button w3-small w3-red" href="/console/pages/sections/{{ $page->id }}/delete/{{ $section->id }}" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="/console/pages/list" class="w3-button w3-light-grey w3-margin-top">Back to Pages</a>

</section>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.datatable').each(function () {
            new DataTable(this, {
                pageLength: 10,
                responsive: true
            });
        });
    });
</script>
@endpush