@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Edit Contact #{{ $contact->id }}</h2>
        <a href="{{ route('console.contacts.show', $contact->id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('console.contacts.update', $contact->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="full_name">Name</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name', $contact->full_name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $contact->email) }}" required>
                </div>
                <div class="mb-3">
                    <label for="message">Message</label>
                    <textarea name="message" id="message" class="form-control" rows="6" required>{{ old('message', $contact->message) }}</textarea>
                </div>
                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
