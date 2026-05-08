@extends('layout.console')

@section('content')

<div class="container py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Add Page</h2>
        <a href="/console/pages/list" class="btn btn-secondary">Back to Pages</a>
    </div>

    <!-- Card Form -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            <form method="post" action="/console/pages/add" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-control" required>
                    @error('title')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug:</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-control" required>
                    @error('slug')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Add Page</button>
            </form>

        </div>
    </div>

</div>

@endsection